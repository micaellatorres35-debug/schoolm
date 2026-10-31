<?php
if (!defined('APPROVAL_DEBUG')) {
    define('APPROVAL_DEBUG', false);
}

// Buffer everything from this point on so any stray warning/notice from an
// include below lands in this buffer instead of corrupting the JSON response.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);         // still log real errors — do NOT silently suppress them

function approval_respond($payload) {
    if (ob_get_length() !== false) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($payload);
    exit;
}

function approval_file_error($httpCode, $message) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code($httpCode);
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo $message;
    exit;
}

function approval_debug_value($message) {
    return (defined('APPROVAL_DEBUG') && APPROVAL_DEBUG) ? ['debug' => $message] : [];
}

// Last-resort safety net for fatals that happen even outside try/catch
// (e.g. a parse error while including a file, or memory exhaustion).
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length() !== false) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $payload = [
            'success' => false,
            'message' => 'A fatal server error occurred.',
        ];
        if (APPROVAL_DEBUG) {
            $payload['debug'] = $error['message'] . ' in ' . $error['file'] . ':' . $error['line'];
        }
        echo json_encode($payload);
    }
});

try {
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/FileStorage.php';
    include_once __DIR__ . '/../classes/Approval.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    $userClass = new User();
    $userInfo = $userClass->userSession();
    $action = $_REQUEST['action'] ?? '';

    if (!$userInfo) {
        if ($action === 'file') {
            approval_file_error(401, 'Session expired. Please log in again.');
        }
        approval_respond(['success' => false, 'message' => 'Session expired.']);
    }

    $approvalClass = new Approval();
$isApprover = (int) ($_SESSION['role'] ?? 0) === 27; // Admin College Coordinator
    $viewer = [
        'is_approver' => $isApprover,
        'employee_id' => (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0),
        'department_id' => (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0),
    ];

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {
        // ------------------------------------------------------------------
        // GET overview data for the dashboard
        // ------------------------------------------------------------------
        case 'overview':
            $departmentId = null;
            if ($isApprover && isset($_GET['department_id']) && $_GET['department_id'] !== '') {
                $departmentId = (int) $_GET['department_id'];
            }
            approval_respond(['success' => true, 'data' => $approvalClass->getOverview($viewer, $departmentId)]);
            break;

        // ------------------------------------------------------------------
        // GET approval queue (pending/reviewed/approved/rejected requests)
        // ------------------------------------------------------------------
        case 'queue':
            $status = isset($_GET['status']) && $_GET['status'] !== '' ? strtolower((string) $_GET['status']) : 'pending';
            $validStatuses = ['all', 'pending', 'draft', 'submitted', 'reviewed', 'approved', 'rejected'];
            if (!in_array($status, $validStatuses, true)) {
                approval_respond(['success' => false, 'message' => 'Invalid status filter.']);
            }

            $departmentId = null;
            if ($isApprover && isset($_GET['department_id']) && $_GET['department_id'] !== '') {
                $departmentId = (int) $_GET['department_id'];
            }

            $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
            $dateTo = trim((string) ($_GET['date_to'] ?? ''));
            if ($dateFrom !== '' && !validateApprovalDate($dateFrom)) {
                approval_respond(['success' => false, 'message' => 'Submitted from date is invalid.']);
            }
            if ($dateTo !== '' && !validateApprovalDate($dateTo)) {
                approval_respond(['success' => false, 'message' => 'Submitted to date is invalid.']);
            }
            if ($dateFrom !== '' && $dateTo !== '' && strtotime($dateFrom) > strtotime($dateTo)) {
                approval_respond(['success' => false, 'message' => 'Submitted from date cannot be later than the submitted to date.']);
            }

            $search = trim((string) ($_GET['search'] ?? ''));
            $rows = $approvalClass->getQueue($viewer, [
                'status' => $status,
                'department_id' => $departmentId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
            ]);
            $hasMore = count($rows) > 100;
            $data = array_slice($rows, 0, 100);
            approval_respond(['success' => true, 'data' => $data, 'has_more' => $hasMore]);
            break;

        // ------------------------------------------------------------------
        // GET list (optionally filtered by department / status)
        // ------------------------------------------------------------------
        case 'list':
            $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? (int) $_GET['department_id'] : null;
            $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;

            if ($isApprover) {
                $departmentId = null;
            } elseif ($departmentId === null) {
                $departmentId = $viewer['department_id'];
            }

            $approvals = $approvalClass->getApprovals($departmentId, $status);
            $filtered = array_values(array_filter($approvals, function ($approval) {
                return ($approval['status'] ?? '') !== 'draft';
            }));
            approval_respond(['success' => true, 'data' => array_map('approval_public', $filtered)]);
            break;

        // ------------------------------------------------------------------
        // GET single approval (for the detail/review modal)
        // ------------------------------------------------------------------
        case 'get':
            $approvalId = (int) ($_GET['approval_id'] ?? 0);
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            $approval = $approvalClass->getApprovalById($approvalId);
            if (!$approval) {
                approval_respond(['success' => false, 'message' => 'Approval request not found.']);
            }
            if (!approval_can_access($approval, $userInfo, $isApprover)) {
                approval_respond(['success' => false, 'message' => 'You do not have access to this approval request.']);
            }
            approval_respond(['success' => true, 'data' => approval_public($approval)]);
            break;

        // ------------------------------------------------------------------
        // GET stream the generated PDF or the optional attachment.
        //   ?action=file&approval_id=12&kind=pdf|attachment[&download=1]
        // Files are never linked directly; every request passes the session
        // and department/role check here first.
        // ------------------------------------------------------------------
        case 'file':
            $approvalId = (int) ($_GET['approval_id'] ?? 0);
            $kind = ($_GET['kind'] ?? 'attachment') === 'pdf' ? 'pdf' : 'attachment';
            $download = ($_GET['download'] ?? '') === '1';

            $approval = $approvalId ? $approvalClass->getApprovalById($approvalId) : null;
            if (!$approval) {
                approval_file_error(404, 'Approval request not found.');
            }
            if (!approval_can_access($approval, $userInfo, $isApprover)) {
                approval_file_error(403, 'You do not have access to this file.');
            }

            if ($kind === 'attachment') {
                $relative = $approval['file_path'] ?? null;
                $name = !empty($approval['file_name']) ? $approval['file_name'] : basename((string) $relative);
                $mime = FileStorage::mimeFor($relative);
            } else {
                $relative = $approval['pdf_path'] ?? null;
                $name = 'approval_' . $approvalId . '.pdf';
                $mime = 'application/pdf';
            }

            if (!$relative) {
                approval_file_error(404, $kind === 'attachment' ? 'This request has no attachment.' : 'The document is not available.');
            }

            $absolute = FileStorage::resolve($relative);
            if (!$absolute) {
                error_log('[ApprovalController] File missing on disk for approval ' . $approvalId . ' (' . $kind . '): ' . $relative);
                approval_file_error(404, 'The file is missing on the server.');
            }

            FileStorage::stream($absolute, $name, $mime, $download);
            break;

        // ------------------------------------------------------------------
        // POST save as draft (multipart/form-data, optional "attachment" file)
        // ------------------------------------------------------------------
        case 'save_draft':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateApprovalInput($_POST);
            if (!$validated['valid']) {
                approval_respond(['success' => false, 'message' => $validated['message']]);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0) ?: null;
            $prep = approval_prepare_attachment($approvalClass, $approvalId, $userInfo, ['pdf', 'jpg', 'jpeg', 'png']);
            $fields = array_merge($validated['fields'], $prep['extra']);
            try {
                $newId = $approvalClass->saveDraft($fields, $approvalId);
            } catch (\Throwable $e) {
                if ($prep['newPath']) {
                    FileStorage::delete($prep['newPath']);
                }
                throw $e;
            }
            approval_discard_replaced_file($prep);
            approval_respond(['success' => true, 'message' => 'Draft saved.', 'data' => ['approval_id' => $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST submit an approval request — creates/promotes to 'submitted'
        // and auto-generates the PDF. PDF failure never blocks submission.
        // ------------------------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateApprovalInput($_POST);
            if (!$validated['valid']) {
                approval_respond(['success' => false, 'message' => $validated['message']]);
            }
            if (!FileStorage::hasUpload($_FILES['attachment'] ?? null)) {
                approval_respond(['success' => false, 'message' => 'A request document is required to submit.']);
            }

            $approvalId = (int) ($_POST['approval_id'] ?? 0) ?: null;
            $prep = approval_prepare_attachment($approvalClass, $approvalId, $userInfo, ['pdf', 'jpg', 'jpeg', 'png']);
            $fields = array_merge($validated['fields'], $prep['extra']);

            try {
                $newId = $approvalClass->submitApproval($fields, $approvalId);
            } catch (\Throwable $e) {
                if ($prep['newPath']) {
                    FileStorage::delete($prep['newPath']);
                }
                throw $e;
            }
            approval_discard_replaced_file($prep);
            approval_respond(['success' => true, 'message' => 'Approval request submitted successfully.', 'data' => ['approval_id' => $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted request as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            if (!$isApprover) {
                approval_respond(['success' => false, 'message' => 'Only the College Coordinator can review approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $notes = trim((string) ($_POST['notes'] ?? ''));
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            if (mb_strlen($notes) > 2000) {
                approval_respond(['success' => false, 'message' => 'Review notes must be 2000 characters or less.']);
            }
            $ok = $approvalClass->markReviewed($approvalId, $userInfo['employee_id'], $notes !== '' ? $notes : null);
            approval_respond(['success' => $ok, 'message' => $ok ? 'Marked as reviewed.' : 'Unable to review this request.']);
            break;

        // ------------------------------------------------------------------
        // POST Directress approves/rejects a reviewed request
        // ------------------------------------------------------------------
        case 'decide':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            if (!$isApprover) {
                approval_respond(['success' => false, 'message' => 'Only the College Coordinator can decide on approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $decision = strtolower((string) ($_POST['decision'] ?? ''));
            $notes = trim((string) ($_POST['notes'] ?? ''));
            if (!$approvalId || !in_array($decision, ['approved', 'rejected'], true)) {
                approval_respond(['success' => false, 'message' => 'A valid approval ID and decision are required.']);
            }
            if ($decision === 'rejected' && $notes === '') {
                approval_respond(['success' => false, 'message' => 'A rejection reason is required.']);
            }
            if (mb_strlen($notes) > 2000) {
                approval_respond(['success' => false, 'message' => 'Decision notes must be 2000 characters or less.']);
            }
            $ok = $approvalClass->decide($approvalId, $userInfo['employee_id'], $decision, $notes !== '' ? $notes : null);
            approval_respond(['success' => $ok, 'message' => $ok ? "Request {$decision}." : 'Unable to decide on this request.']);
            break;

        // ------------------------------------------------------------------
        // POST/GET generate an AI summary
        // ------------------------------------------------------------------
        case 'summarize':
            $approvalId = (int) ($_REQUEST['approval_id'] ?? 0);
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            $result = $approvalClass->generateAiSummary($approvalId);
            approval_respond($result);
            break;

        default:
            approval_respond(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (\Throwable $e) {
    error_log('[ApprovalController] Unhandled error: ' . $e->getMessage());
    if (($_REQUEST['action'] ?? '') === 'file') {
        approval_file_error(500, 'The file could not be served.');
    }
    $payload = [
        'success' => false,
        'message' => 'An unexpected server error occurred.',
    ];
    if (APPROVAL_DEBUG) {
        $payload['debug'] = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
    }
    approval_respond($payload);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function validateApprovalInput($post) {
    $title = trim((string) ($post['title'] ?? ''));
    $description = trim((string) ($post['description'] ?? ''));
    $justification = trim((string) ($post['justification'] ?? ''));

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if ($description === '') {
        return ['valid' => false, 'message' => 'Description is required.'];
    }

    return [
        'valid' => true,
        'fields' => [
            'title' => $title,
            'description' => $description,
            'justification' => $justification,
        ],
    ];
}

function validateApprovalDate($value) {
    $date = trim((string) $value);
    if ($date === '') {
        return true;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    $parts = explode('-', $date);
    return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}

function approval_can_access($approval, $userInfo, $isApprover) {
    if ($isApprover) {
        return ($approval['status'] ?? '') !== 'draft' || ((int) ($approval['submit_by'] ?? 0) === (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0));
    }

    $myDept = (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0);
    $myId = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);
    $approvalStatus = (string) ($approval['status'] ?? '');

    if ($approvalStatus === 'draft') {
        return $myId > 0 && (int) ($approval['submit_by'] ?? 0) === $myId;
    }
    if ($myId > 0 && (int) ($approval['submit_by'] ?? 0) === $myId) {
        return true;
    }
    $approvalDept = (int) ($approval['submitter_department_id'] ?? $approval['department'] ?? 0);
    return $myDept > 0 && $approvalDept === $myDept;
}

function approval_public($approval) {
    $approval['has_pdf'] = !empty($approval['pdf_path']);
    $approval['has_attachment'] = !empty($approval['file_path']);

    if ($approval['has_attachment']) {
        $approval['attachment_name'] = !empty($approval['file_name']) ? $approval['file_name'] : basename((string) $approval['file_path']);
        $approval['attachment_mime'] = FileStorage::mimeFor($approval['file_path']);
        $approval['attachment_size'] = $approval['file_size'] ?? null;
    }

    $approval['history'] = [];
    if (!empty($approval['created_at'])) {
        $approval['history'][] = ['event' => 'Created', 'date' => $approval['created_at'], 'by' => $approval['submit_by_name'] ?? null, 'notes' => null];
    }
    if (!empty($approval['submitted_on'])) {
        $approval['history'][] = ['event' => 'Submitted', 'date' => $approval['submitted_on'], 'by' => $approval['submit_by_name'] ?? null, 'notes' => null];
    }
    if (!empty($approval['reviewed_at'])) {
        $approval['history'][] = ['event' => 'Reviewed', 'date' => $approval['reviewed_at'], 'by' => $approval['reviewed_by_name'] ?? null, 'notes' => $approval['review_notes'] ?? null];
    }
    if (!empty($approval['approved_at'])) {
        $approval['history'][] = ['event' => ($approval['decision'] === 'rejected' ? 'Rejected' : 'Approved'), 'date' => $approval['approved_at'], 'by' => $approval['decided_by_name'] ?? null, 'notes' => $approval['remarks'] ?? null];
    }

    unset($approval['pdf_path'], $approval['file_path']);
    return $approval;
}

function approval_prepare_attachment($approvalClass, $approvalId, $userInfo, $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png']) {
    $result = ['extra' => [], 'newPath' => null, 'oldPath' => null];
    // If the file is bigger than PHP's post_max_size, PHP drops $_POST and $_FILES entirely.
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        approval_respond(['success' => false, 'message' => 'The upload is larger than the server allows. Please choose a smaller file (max 10 MB).']);
    }

    if ($approvalId) {
        $existing = $approvalClass->getApprovalById($approvalId);
        $myId = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);
        if (!$existing || (int) $existing['submit_by'] !== $myId || $existing['status'] !== 'draft') {
            approval_respond(['success' => false, 'message' => 'Only your own drafts can be edited.']);
        }
        $result['oldPath'] = $existing['file_path'] ?? null;
    }

    $file = $_FILES['attachment'] ?? null;
    if (!FileStorage::hasUpload($file)) {
        return $result;
    }

    $error = FileStorage::validateUpload($file, $allowedExtensions);
    if ($error) {
        approval_respond(['success' => false, 'message' => $error]);
    }

    try {
        $relative = FileStorage::store($file, 'approvals/attachments', 'approval');
    } catch (\Throwable $e) {
        error_log('[ApprovalController] Attachment store failed: ' . $e->getMessage());
        $payload = ['success' => false, 'message' => 'The attachment could not be saved on the server.'];
        if (APPROVAL_DEBUG) {
            $payload['debug'] = $e->getMessage();
        }
        approval_respond($payload);
    }

    $result['newPath'] = $relative;
    $result['extra'] = [
        'file_path' => $relative,
        'file_name' => FileStorage::cleanName($file['name']),
        'file_size' => (int) $file['size'],
    ];
    return $result;
}

function approval_discard_replaced_file($prep) {
    if ($prep['newPath'] && $prep['oldPath'] && $prep['oldPath'] !== $prep['newPath']) {
        FileStorage::delete($prep['oldPath']);
    }
}