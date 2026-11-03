<?php
// Buffer everything from this point on so any stray warning/notice from an
// include below lands in this buffer instead of corrupting the JSON response.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);         // still log real errors — do NOT silently suppress them

// ── Module settings ────────────────────────────────────────────────────────
// Set to true on a development machine only. When false, no file paths or stack details reach the browser.
if (!defined('APPROVAL_DEBUG')) {
    define('APPROVAL_DEBUG', false);
}
// A request must have an uploaded document before it can be submitted (drafts may be saved without one).
if (!defined('APPROVAL_REQUIRE_ATTACHMENT')) {
    define('APPROVAL_REQUIRE_ATTACHMENT', true);
}
// Only formats the browser can display inside the system. Older Word/Excel attachments still download.
if (!defined('APPROVAL_UPLOAD_EXTENSIONS')) {
    define('APPROVAL_UPLOAD_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);
}

function approval_debug($message) {
    return APPROVAL_DEBUG ? $message : null;
}

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

// Used only by the "file" action (the response is opened inside an <iframe>/<img>/new tab, not parsed as JSON).
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

// Last-resort safety net for fatals that happen even outside try/catch.
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length() !== false) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'message' => 'A fatal server error occurred.',
            'debug'   => approval_debug($error['message'] . ' in ' . $error['file'] . ':' . $error['line']),
        ]);
    }
});

try {
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/FileStorage.php';
    include_once __DIR__ . '/../classes/Approval.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    // ── Guard ──────────────────────────────────────────────────────────────
    $userClass = new User();
    $userInfo  = $userClass->userSession();

    $action = $_REQUEST['action'] ?? '';

    if (!$userInfo) {
        if ($action === 'file') {
            approval_file_error(401, 'Session expired. Please log in again.');
        }
        approval_respond(['success' => false, 'message' => 'Session expired.']);
    }

    $approvalClass = new Approval();
    $isDirectress = (
        ($_SESSION['role_name'] ?? $userInfo['role'] ?? '') === 'Admin College Coordinator' ||
        ($_SESSION['role_name'] ?? $userInfo['role'] ?? '') === 'School Directress'
    );

    $viewer = [
        'is_directress' => $isDirectress,
        'employee_id'   => (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0),
        'department_id' => (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0),
    ];

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {

        // ------------------------------------------------------------------
        // GET summary counts + recent decisions (scoped to what the viewer may see)
        // ------------------------------------------------------------------
        case 'overview':
            $departmentId = ($isDirectress && ctype_digit((string) ($_GET['department_id'] ?? '')))
                ? (int) $_GET['department_id']
                : null;
            approval_respond(['success' => true, 'data' => $approvalClass->getOverview($viewer, $departmentId)]);
            break;

        // ------------------------------------------------------------------
        // GET the filtered queue
        // ------------------------------------------------------------------
        case 'queue':
            $filters = approval_read_filters($isDirectress);
            $rows    = $approvalClass->getQueue($viewer, $filters);
            $hasMore = count($rows) > 100;
            if ($hasMore) {
                $rows = array_slice($rows, 0, 100);
            }
            approval_respond(['success' => true, 'data' => $rows, 'has_more' => $hasMore]);
            break;

        // ------------------------------------------------------------------
        // GET list (legacy; optionally filtered by department / status)
        // ------------------------------------------------------------------
        case 'list':
            $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== ''
                ? (int) $_GET['department_id']
                : null;
            $status = isset($_GET['status']) && $_GET['status'] !== ''
                ? $_GET['status']
                : null;

            if (!$isDirectress) {
                // Other users are always limited to their own department.
                $departmentId = $viewer['department_id'] ?: null;
            }

            // Drafts are private; this legacy endpoint never returns them.
            // (The redesigned page uses the "queue" action, which includes the viewer's own drafts.)
            $approvals = array_filter(
                $approvalClass->getApprovals($departmentId, $status),
                function ($row) {
                    return $row['status'] !== 'draft';
                }
            );
            approval_respond(['success' => true, 'data' => array_values(array_map('approval_public', $approvals))]);
            break;

        // ------------------------------------------------------------------
        // GET single approval (for the review workspace)
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
            if (!approval_can_access($approval, $userInfo, $isDirectress)) {
                approval_respond(['success' => false, 'message' => 'You do not have access to this approval request.']);
            }
            approval_respond(['success' => true, 'data' => approval_public($approval)]);
            break;

        // ------------------------------------------------------------------
        // GET stream the uploaded document.
        //   ?action=file&approval_id=12&kind=attachment[&download=1]
        // kind=pdf only serves PDFs generated by the old system (legacy rows).
        // Files are never linked directly; every request passes the session
        // and department/role check here first.
        // ------------------------------------------------------------------
        case 'file':
            $approvalId = (int) ($_GET['approval_id'] ?? 0);
            $kind       = ($_GET['kind'] ?? 'attachment') === 'pdf' ? 'pdf' : 'attachment';
            $download   = ($_GET['download'] ?? '') === '1';

            $approval = $approvalId ? $approvalClass->getApprovalById($approvalId) : null;
            if (!$approval) {
                approval_file_error(404, 'Approval request not found.');
            }
            if (!approval_can_access($approval, $userInfo, $isDirectress)) {
                approval_file_error(403, 'You do not have access to this file.');
            }

            if ($kind === 'attachment') {
                $relative = $approval['file_path'] ?? null;
                $name     = !empty($approval['file_name']) ? $approval['file_name'] : basename((string) $relative);
                $mime     = FileStorage::mimeFor($relative);
            } else {
                $relative = $approval['pdf_path'] ?? null;
                $name     = 'approval_' . $approvalId . '.pdf';
                $mime     = 'application/pdf';
            }

            if (!$relative) {
                approval_file_error(404, 'This request has no document attached.');
            }

            $absolute = FileStorage::resolve($relative);
            if (!$absolute) {
                error_log('[ApprovalController] File missing on disk for approval ' . $approvalId . ' (' . $kind . '): ' . $relative);
                approval_file_error(404, 'The file is missing on the server.');
            }

            FileStorage::stream($absolute, $name, $mime, $download); // ends the request
            break;

        // ------------------------------------------------------------------
        // POST save as draft (multipart/form-data, "attachment" file)
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
            $prep       = approval_prepare_attachment($approvalClass, $approvalId, $userInfo); // responds on error
            $fields     = array_merge($validated['fields'], $prep['extra']);

            try {
                $newId = $approvalClass->saveDraft($fields, $approvalId);
            } catch (\Throwable $e) {
                FileStorage::delete($prep['newPath']); // don't leave an orphan file behind
                throw $e;
            }
            approval_discard_replaced_file($prep);

            approval_respond(['success' => true, 'message' => 'Draft saved.', 'data' => ['approval_id' => $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST submit an approval request — creates/promotes to 'submitted'.
        // The uploaded document is the request document; nothing is generated.
        // ------------------------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateApprovalInput($_POST);
            if (!$validated['valid']) {
                approval_respond(['success' => false, 'message' => $validated['message']]);
            }

            $approvalId = (int) ($_POST['approval_id'] ?? 0) ?: null;
            $prep       = approval_prepare_attachment($approvalClass, $approvalId, $userInfo); // responds on error

            if (APPROVAL_REQUIRE_ATTACHMENT && !$prep['newPath'] && !$prep['oldPath']) {
                approval_respond(['success' => false, 'message' => 'Upload the request document (PDF, JPG or PNG) before submitting.']);
            }

            $fields = array_merge($validated['fields'], $prep['extra']);

            try {
                $newId = $approvalClass->submitApproval($fields, $approvalId);
            } catch (\Throwable $e) {
                FileStorage::delete($prep['newPath']);
                throw $e;
            }
            approval_discard_replaced_file($prep);

            approval_respond([
                'success' => true,
                'message' => 'Approval request submitted successfully.',
                'data'    => ['approval_id' => $newId],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted request as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            if (!$isDirectress) {
                approval_respond(['success' => false, 'message' => 'Only the School Directress can review approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $notes      = trim($_POST['notes'] ?? '');
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            if (mb_strlen($notes) > 2000) {
                approval_respond(['success' => false, 'message' => 'Notes must be 2,000 characters or fewer.']);
            }
            $ok = $approvalClass->markReviewed($approvalId, $viewer['employee_id'], $notes !== '' ? $notes : null);
            approval_respond([
                'success' => $ok,
                'message' => $ok ? 'Marked as reviewed.' : 'This request is no longer waiting for review. Refresh and try again.',
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress approves/rejects a reviewed request
        // ------------------------------------------------------------------
        case 'decide':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            if (!$isDirectress) {
                approval_respond(['success' => false, 'message' => 'Only the School Directress can decide on approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $decision   = $_POST['decision'] ?? '';
            $notes      = trim($_POST['notes'] ?? '');
            if (!$approvalId || !in_array($decision, ['approved', 'rejected'], true)) {
                approval_respond(['success' => false, 'message' => 'A valid approval ID and decision are required.']);
            }
            if ($decision === 'rejected' && $notes === '') {
                approval_respond(['success' => false, 'message' => 'Enter a reason before rejecting this request.']);
            }
            if (mb_strlen($notes) > 2000) {
                approval_respond(['success' => false, 'message' => 'Notes must be 2,000 characters or fewer.']);
            }
            $ok = $approvalClass->decide($approvalId, $viewer['employee_id'], $decision, $notes !== '' ? $notes : null);
            approval_respond([
                'success' => $ok,
                'message' => $ok ? "Request {$decision}." : 'This request is no longer waiting for a decision. Refresh and try again.',
            ]);
            break;

        // ------------------------------------------------------------------
        // POST/GET generate an AI summary (now access-checked like every other read)
        // ------------------------------------------------------------------
        case 'summarize':
            $approvalId = (int) ($_REQUEST['approval_id'] ?? 0);
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            $approval = $approvalClass->getApprovalById($approvalId);
            if (!$approval || !approval_can_access($approval, $userInfo, $isDirectress)) {
                approval_respond(['success' => false, 'message' => 'You do not have access to this approval request.']);
            }
            approval_respond($approvalClass->generateAiSummary($approvalId));
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
    approval_respond([
        'success' => false,
        'message' => 'An unexpected server error occurred.',
        'debug'   => approval_debug($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()),
    ]);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function validateApprovalInput($post) {
    $title         = trim($post['title'] ?? '');
    $description   = trim($post['description'] ?? '');
    $justification = trim($post['justification'] ?? '');

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if ($description === '') {
        return ['valid' => false, 'message' => 'Description is required.'];
    }
    if (mb_strlen($title) > 255) {
        return ['valid' => false, 'message' => 'Title must be 255 characters or fewer.'];
    }

    return [
        'valid'  => true,
        'fields' => [
            'title'         => $title,
            'description'   => $description,
            'justification' => $justification,
        ],
    ];
}

function approval_valid_date($value) {
    if (!is_string($value) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
        return null;
    }
    return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : null;
}

// Reads and validates queue filters. Unknown values fall back to safe defaults.
function approval_read_filters($isDirectress) {
    $allowed = ['pending', 'all', 'draft', 'submitted', 'reviewed', 'approved', 'rejected'];
    $status  = $_GET['status'] ?? '';
    if (!in_array($status, $allowed, true)) {
        $status = $isDirectress ? 'pending' : 'all';
    }

    $dept = ($isDirectress && ctype_digit((string) ($_GET['department_id'] ?? '')))
        ? (int) $_GET['department_id']
        : null;

    return [
        'status'        => $status,
        'department_id' => $dept,
        'date_from'     => approval_valid_date($_GET['date_from'] ?? ''),
        'date_to'       => approval_valid_date($_GET['date_to'] ?? ''),
        'search'        => mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100),
    ];
}

// Visibility rule shared by get / file / summarize (and mirrored by Approval::scopeClause):
//  - A draft is visible only to the person who created it.
//  - The Directress sees every submitted request in every department.
//  - Everyone else sees requests from their own department and anything they submitted.
function approval_can_access($approval, $userInfo, $isDirectress) {
    $myDept = (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0);
    $myId   = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);
    $isOwner = $myId && (int) ($approval['submit_by'] ?? 0) === $myId;

    if (($approval['status'] ?? '') === 'draft') {
        return $isOwner;
    }
    if ($isDirectress || $isOwner) {
        return true;
    }
    $approvalDept = (int) ($approval['submitter_department_id'] ?? $approval['department'] ?? 0);
    return $myDept && $approvalDept === $myDept;
}

// The decision history is built from what the sd_approvals row actually records:
// creation, submission, review (who/when/notes) and the final decision (who/when/note).
function approval_history($a) {
    $history = [];
    if (!empty($a['created_at'])) {
        $history[] = ['event' => 'created', 'label' => 'Request created', 'at' => $a['created_at'], 'by' => $a['submit_by_name'] ?? null, 'note' => null];
    }
    if (!empty($a['submitted_on'])) {
        $history[] = ['event' => 'submitted', 'label' => 'Submitted for approval', 'at' => $a['submitted_on'], 'by' => $a['submit_by_name'] ?? null, 'note' => null];
    }
    if (!empty($a['reviewed_at'])) {
        $history[] = ['event' => 'reviewed', 'label' => 'Marked as reviewed', 'at' => $a['reviewed_at'], 'by' => $a['reviewed_by_name'] ?? null, 'note' => $a['review_notes'] ?? null];
    }
    if (!empty($a['approved_at']) && in_array($a['status'] ?? '', ['approved', 'rejected'], true)) {
        $history[] = [
            'event' => $a['status'],
            'label' => $a['status'] === 'approved' ? 'Approved' : 'Rejected',
            'at'    => $a['approved_at'],
            'by'    => $a['decided_by_name'] ?? null,
            'note'  => $a['remarks'] ?? null,
        ];
    }
    return $history;
}

// Never send server file paths to the browser — only flags and display metadata.
// Files are fetched through ?action=file.
function approval_public($approval) {
    $approval['has_pdf']        = !empty($approval['pdf_path']);       // legacy generated PDFs only
    $approval['has_attachment'] = !empty($approval['file_path']);

    if ($approval['has_attachment']) {
        $approval['attachment_name'] = !empty($approval['file_name']) ? $approval['file_name'] : basename($approval['file_path']);
        $approval['attachment_mime'] = FileStorage::mimeFor($approval['file_path']);
        $approval['attachment_size'] = $approval['file_size'] ?? null;
    }

    if (isset($approval['approval_id']) && array_key_exists('created_at', $approval)) {
        $approval['history'] = approval_history($approval);
    }

    unset($approval['pdf_path'], $approval['file_path']);
    return $approval;
}

// Validates the optional upload and moves it into storage BEFORE the database write.
// Also refuses to edit anything that is not the current user's own draft, so a rejected
// edit can never replace (or delete) another request's attachment.
// Returns ['extra' => fields to merge, 'newPath' => ?string, 'oldPath' => ?string]
function approval_prepare_attachment($approvalClass, $approvalId, $userInfo) {
    $result = ['extra' => [], 'newPath' => null, 'oldPath' => null];

    // If the file is bigger than PHP's post_max_size, PHP drops $_POST and $_FILES entirely.
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        approval_respond(['success' => false, 'message' => 'The upload is larger than the server allows. Please choose a smaller file (max 10 MB).']);
    }

    if ($approvalId) {
        $existing = $approvalClass->getApprovalById($approvalId);
        $myId     = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);
        if (!$existing || (int) $existing['submit_by'] !== $myId || $existing['status'] !== 'draft') {
            approval_respond(['success' => false, 'message' => 'Only your own drafts can be edited.']);
        }
        $result['oldPath'] = $existing['file_path'] ?? null;
    }

    $file = $_FILES['attachment'] ?? null;
    if (!FileStorage::hasUpload($file)) {
        return $result; // required-ness is checked by the caller (submit only)
    }

    $error = FileStorage::validateUpload($file, APPROVAL_UPLOAD_EXTENSIONS);
    if ($error) {
        approval_respond(['success' => false, 'message' => $error]);
    }

    try {
        $relative = FileStorage::store($file, 'approvals/attachments', 'approval');
    } catch (\Throwable $e) {
        error_log('[ApprovalController] Attachment store failed: ' . $e->getMessage());
        approval_respond([
            'success' => false,
            'message' => 'The document could not be saved on the server.',
            'debug'   => approval_debug($e->getMessage()),
        ]);
    }

    $result['newPath'] = $relative;
    $result['extra']   = [
        'file_path' => $relative,
        'file_name' => FileStorage::cleanName($file['name']),
        'file_size' => (int) $file['size'],
    ];
    return $result;
}

// After a successful save, remove the previous attachment if a new one replaced it.
function approval_discard_replaced_file($prep) {
    if ($prep['newPath'] && $prep['oldPath'] && $prep['oldPath'] !== $prep['newPath']) {
        FileStorage::delete($prep['oldPath']);
    }
}