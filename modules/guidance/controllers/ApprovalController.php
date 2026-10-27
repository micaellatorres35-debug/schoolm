<?php
// Buffer everything from this point on so any stray warning/notice from an
// include below lands in this buffer instead of corrupting the JSON response.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);         // still log real errors — do NOT silently suppress them

// NOTE: the JSON Content-Type header is now set inside approval_respond() instead of here,
// because the "file" action streams a PDF/attachment and must send its own headers.

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

// Used only by the "file" action (the response is opened inside an <iframe>/new tab, not parsed as JSON).
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
        echo json_encode([
            'success' => false,
            'message' => 'A fatal server error occurred.',
            // TEMP debug info — remove the "debug" key once things are stable.
            'debug' => $error['message'] . ' in ' . $error['file'] . ':' . $error['line'],
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
    ($_SESSION['role_name'] ?? $userInfo['role'] ?? '') === 'School Directress'
    );

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {

        // ------------------------------------------------------------------
        // GET list (optionally filtered by department / status)
        // ------------------------------------------------------------------
        case 'list':
            $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== ''
                ? (int) $_GET['department_id']
                : null;
            $status = isset($_GET['status']) && $_GET['status'] !== ''
                ? $_GET['status']
                : null;

            if ($isDirectress) {
                // School Directress can view approvals across all departments.
                $departmentId = null;
            } elseif ($departmentId === null) {
                // Other users default to their own department.
                $departmentId = $userInfo['department_id'] ?? null;
            }

            $approvals = $approvalClass->getApprovals($departmentId, $status);
            approval_respond(['success' => true, 'data' => array_map('approval_public', $approvals)]);
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
            if (!approval_can_access($approval, $userInfo, $isDirectress)) {
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
            $kind       = ($_GET['kind'] ?? 'pdf') === 'attachment' ? 'attachment' : 'pdf';
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
                approval_file_error(404, $kind === 'attachment' ? 'This request has no attachment.' : 'The PDF has not been generated yet.');
            }

            $absolute = FileStorage::resolve($relative);
            if (!$absolute) {
                error_log('[ApprovalController] File missing on disk for approval ' . $approvalId . ' (' . $kind . '): ' . $relative);
                approval_file_error(404, 'The file is missing on the server.');
            }

            FileStorage::stream($absolute, $name, $mime, $download); // ends the request
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

            $approvalId = (int) ($_POST['approval_id'] ?? 0) ?: null;
            $prep       = approval_prepare_attachment($approvalClass, $approvalId, $userInfo); // responds on error
            $fields     = array_merge($validated['fields'], $prep['extra']);

            try {
                $newId = $approvalClass->submitApproval($fields, $approvalId);
            } catch (\Throwable $e) {
                FileStorage::delete($prep['newPath']);
                throw $e;
            }
            approval_discard_replaced_file($prep);

            $pdfGenerated = false;
            $warning      = null;
            $debug        = null;
            try {
                $pdfGenerated = (bool) $approvalClass->generatePdf($newId);
            } catch (\Throwable $e) {
                error_log('[ApprovalController] PDF generation failed for approval ' . $newId . ': ' . $e->getMessage());
                $warning = 'Request submitted, but the PDF could not be generated. Contact your administrator.';
                // TEMP debug info — remove once things are stable.
                $debug = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            }

            approval_respond([
                'success' => true,
                'message' => 'Approval request submitted successfully.',
                'warning' => $warning,
                'debug'   => $debug,
                'data'    => ['approval_id' => $newId, 'has_pdf' => $pdfGenerated],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted request as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if (!$isDirectress) {
                approval_respond(['success' => false, 'message' => 'Only the School Directress can review approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $notes      = trim($_POST['notes'] ?? '');
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            $ok = $approvalClass->markReviewed($approvalId, $userInfo['employee_id'], $notes !== '' ? $notes : null);
            approval_respond(['success' => $ok, 'message' => $ok ? 'Marked as reviewed.' : 'Unable to review this request.']);
            break;

        // ------------------------------------------------------------------
        // POST Directress approves/rejects a reviewed request
        // ------------------------------------------------------------------
        case 'decide':
            if (!$isDirectress) {
                approval_respond(['success' => false, 'message' => 'Only the School Directress can decide on approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $decision   = $_POST['decision'] ?? '';
            $notes      = trim($_POST['notes'] ?? '');
            if (!$approvalId || !in_array($decision, ['approved', 'rejected'], true)) {
                approval_respond(['success' => false, 'message' => 'A valid approval ID and decision are required.']);
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
    approval_respond([
        'success' => false,
        'message' => 'An unexpected server error occurred.',
        // TEMP debug info — remove the "debug" key once things are stable.
        'debug'   => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
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

    return [
        'valid'  => true,
        'fields' => [
            'title'         => $title,
            'description'   => $description,
            'justification' => $justification,
        ],
    ];
}

// Same visibility rule as the "list" action: Directress sees everything; everyone else sees
// approvals submitted from their own department (and anything they submitted themselves).
function approval_can_access($approval, $userInfo, $isDirectress) {
    if ($isDirectress) {
        return true;
    }
    $myDept = (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0);
    $myId   = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);

    if ($myId && (int) ($approval['submit_by'] ?? 0) === $myId) {
        return true;
    }
    $approvalDept = (int) ($approval['submitter_department_id'] ?? $approval['department'] ?? 0);
    return $myDept && $approvalDept === $myDept;
}

// Never send server file paths to the browser — only flags and display metadata.
// Files are fetched through ?action=file.
function approval_public($approval) {
    $approval['has_pdf']        = !empty($approval['pdf_path']);
    $approval['has_attachment'] = !empty($approval['file_path']);

    if ($approval['has_attachment']) {
        $approval['attachment_name'] = !empty($approval['file_name']) ? $approval['file_name'] : basename($approval['file_path']);
        $approval['attachment_mime'] = FileStorage::mimeFor($approval['file_path']);
        $approval['attachment_size'] = $approval['file_size'] ?? null;
    }

    unset($approval['pdf_path'], $approval['file_path']);
    return $approval;
}

// Validates the optional upload and moves it into storage BEFORE the database write.
// Also refuses to edit anything that is not the current user's own draft, so a rejected
// edit can never replace (or delete) another request's attachment.
// Responds with JSON and exits on any validation problem.
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
        return $result; // attachment is optional
    }

    $error = FileStorage::validateUpload($file);
    if ($error) {
        approval_respond(['success' => false, 'message' => $error]);
    }

    try {
        $relative = FileStorage::store($file, 'approvals/attachments', 'approval');
    } catch (\Throwable $e) {
        error_log('[ApprovalController] Attachment store failed: ' . $e->getMessage());
        approval_respond([
            'success' => false,
            'message' => 'The attachment could not be saved on the server.',
            // TEMP debug info — remove once things are stable.
            'debug'   => $e->getMessage(),
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