<?php
// Buffer everything from this point on so any stray warning/notice from an
// include below lands in this buffer instead of corrupting the JSON response.
// (The original file had this comment but never actually called ob_start(), so the
// buffer-clearing in concern_respond() did nothing. Report and Approval both start it.)
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);         // still log real errors — do NOT silently suppress them

// NOTE: the JSON Content-Type header is now set inside concern_respond() instead of here,
// because the "file" action streams a PDF/attachment and must send its own headers.

function concern_respond($payload) {
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
function concern_file_error($httpCode, $message) {
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
    include_once __DIR__ . '/../classes/Issues.php';
    include_once __DIR__ . '/../classes/User.php';

    // ── Guard ──────────────────────────────────────────────────────────────
    $userClass = new User();
    $userInfo  = $userClass->userSession();

    $action = $_REQUEST['action'] ?? '';

    if (!$userInfo) {
        if ($action === 'file') {
            concern_file_error(401, 'Session expired. Please log in again.');
        }
        concern_respond(['success' => false, 'message' => 'Session expired.']);
    }

    $issuesClass  = new Issues();
    $isDirectress = (
    ($_SESSION['role_name'] ?? $userInfo['role'] ?? '') === 'School Directress'
    );

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {

        // ------------------------------------------------------------------
        // GET list (optionally filtered by department / status / search)
        // ------------------------------------------------------------------
        case 'list':
            $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== ''
                ? (int) $_GET['department_id']
                : null;
            $status = isset($_GET['status']) && $_GET['status'] !== ''
                ? $_GET['status']
                : null;
            $search = trim($_GET['search'] ?? '');

            if ($isDirectress) {
                // School Directress can view approvals across all departments.
                $departmentId = null;
            } elseif ($departmentId === null) {
                // Other users default to their own department.
                $departmentId = $userInfo['department_id'] ?? null;
            }

            $concerns = $issuesClass->getConcerns($departmentId, $status, $search);
            concern_respond(['success' => true, 'data' => array_map('concern_public', $concerns)]);
            break;

        // ------------------------------------------------------------------
        // GET single concern (for the detail/review modal)
        // ------------------------------------------------------------------
        case 'get':
            $issueId = (int) ($_GET['issue_id'] ?? 0);
            if (!$issueId) {
                concern_respond(['success' => false, 'message' => 'Issue ID is required.']);
            }
            $concern = $issuesClass->getConcernById($issueId);
            if (!$concern) {
                concern_respond(['success' => false, 'message' => 'Concern not found.']);
            }
            if (!concern_can_access($concern, $userInfo, $isDirectress)) {
                concern_respond(['success' => false, 'message' => 'You do not have access to this concern.']);
            }
            concern_respond(['success' => true, 'data' => concern_public($concern)]);
            break;

        // ------------------------------------------------------------------
        // GET stream the generated PDF or the optional attachment.
        //   ?action=file&issue_id=12&kind=pdf|attachment[&download=1]
        // Files are never linked directly; every request passes the session
        // and department/role check here first.
        // ------------------------------------------------------------------
        case 'file':
            $issueId  = (int) ($_GET['issue_id'] ?? 0);
            $kind     = ($_GET['kind'] ?? 'pdf') === 'attachment' ? 'attachment' : 'pdf';
            $download = ($_GET['download'] ?? '') === '1';

            $concern = $issueId ? $issuesClass->getConcernById($issueId) : null;
            if (!$concern) {
                concern_file_error(404, 'Concern not found.');
            }
            if (!concern_can_access($concern, $userInfo, $isDirectress)) {
                concern_file_error(403, 'You do not have access to this file.');
            }

            if ($kind === 'attachment') {
                $relative = $concern['file_path'] ?? null;
                $name     = !empty($concern['file_name']) ? $concern['file_name'] : basename((string) $relative);
                $mime     = FileStorage::mimeFor($relative);
            } else {
                $relative = $concern['pdf_path'] ?? null;
                $name     = 'concern_' . $issueId . '.pdf';
                $mime     = 'application/pdf';
            }

            if (!$relative) {
                concern_file_error(404, $kind === 'attachment' ? 'This concern has no attachment.' : 'The PDF has not been generated yet.');
            }

            $absolute = FileStorage::resolve($relative);
            if (!$absolute) {
                error_log('[IssueController] File missing on disk for concern ' . $issueId . ' (' . $kind . '): ' . $relative);
                concern_file_error(404, 'The file is missing on the server.');
            }

            FileStorage::stream($absolute, $name, $mime, $download); // ends the request
            break;

        // ------------------------------------------------------------------
        // POST save as draft (multipart/form-data, optional "file" upload)
        // ------------------------------------------------------------------
        case 'save_draft':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                concern_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateConcernInput($_POST, $userInfo);
            if (!$validated['valid']) {
                concern_respond(['success' => false, 'message' => $validated['message']]);
            }

            $issueId = (int) ($_POST['issue_id'] ?? 0) ?: null;
            $prep    = concern_prepare_attachment($issuesClass, $issueId, $userInfo); // responds on error
            $fields  = array_merge($validated['fields'], $prep['extra']);

            try {
                $newId = $issuesClass->saveDraft($fields, $issueId);
            } catch (\Throwable $e) {
                FileStorage::delete($prep['newPath']); // don't leave an orphan file behind
                throw $e;
            }
            concern_discard_replaced_file($prep);

            concern_respond(['success' => true, 'message' => 'Draft saved.', 'data' => ['issue_id' => $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST submit a concern — creates/promotes to 'submitted' and auto-
        // generates the PDF. PDF failure never blocks the submission.
        // ------------------------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                concern_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateConcernInput($_POST, $userInfo);
            if (!$validated['valid']) {
                concern_respond(['success' => false, 'message' => $validated['message']]);
            }

            $issueId = (int) ($_POST['issue_id'] ?? 0) ?: null;
            $prep    = concern_prepare_attachment($issuesClass, $issueId, $userInfo); // responds on error
            $fields  = array_merge($validated['fields'], $prep['extra']);

            try {
                $newId = $issuesClass->submitConcern($fields, $issueId);
            } catch (\Throwable $e) {
                FileStorage::delete($prep['newPath']);
                throw $e;
            }
            concern_discard_replaced_file($prep);

            $pdfGenerated = false;
            $warning      = null;
            $debug        = null;
            try {
                $pdfGenerated = (bool) $issuesClass->generatePdf($newId);
            } catch (\Throwable $e) {
                error_log('[IssueController] PDF generation failed for concern ' . $newId . ': ' . $e->getMessage());
                $warning = 'Concern logged, but the PDF could not be generated. Contact your administrator.';
                // TEMP debug info — remove once things are stable.
                $debug = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            }

            concern_respond([
                'success' => true,
                'message' => 'Concern logged successfully.',
                'warning' => $warning,
                'debug'   => $debug,
                'data'    => ['issue_id' => $newId, 'has_pdf' => $pdfGenerated],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted concern as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if (!$isDirectress) {
                concern_respond(['success' => false, 'message' => 'Only the School Directress can review concerns.']);
            }
            $issueId = (int) ($_POST['issue_id'] ?? 0);
            $notes   = trim($_POST['notes'] ?? '');
            if (!$issueId) {
                concern_respond(['success' => false, 'message' => 'Issue ID is required.']);
            }
            $ok = $issuesClass->markReviewed($issueId, $userInfo['employee_id'], $notes !== '' ? $notes : null);
            concern_respond(['success' => $ok, 'message' => $ok ? 'Marked as reviewed.' : 'Unable to review this concern.']);
            break;

        // ------------------------------------------------------------------
        // POST Directress resolves/dismisses a reviewed concern
        // ------------------------------------------------------------------
        case 'decide':
            if (!$isDirectress) {
                concern_respond(['success' => false, 'message' => 'Only the School Directress can resolve concerns.']);
            }
            $issueId  = (int) ($_POST['issue_id'] ?? 0);
            $decision = $_POST['decision'] ?? '';
            $notes    = trim($_POST['notes'] ?? '');
            if (!$issueId || !in_array($decision, ['resolved', 'dismissed'], true)) {
                concern_respond(['success' => false, 'message' => 'A valid issue ID and decision are required.']);
            }
            $ok = $issuesClass->resolve($issueId, $userInfo['employee_id'], $decision, $notes !== '' ? $notes : null);
            concern_respond(['success' => $ok, 'message' => $ok ? "Concern {$decision}." : 'Unable to update this concern.']);
            break;

        // ------------------------------------------------------------------
        // POST/GET generate an AI summary
        // ------------------------------------------------------------------
        case 'summarize':
            $issueId = (int) ($_REQUEST['issue_id'] ?? 0);
            if (!$issueId) {
                concern_respond(['success' => false, 'message' => 'Issue ID is required.']);
            }
            $result = $issuesClass->generateAiSummary($issueId);
            concern_respond($result);
            break;

        default:
            concern_respond(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (\Throwable $e) {
    error_log('[IssueController] Unhandled error: ' . $e->getMessage());
    if (($_REQUEST['action'] ?? '') === 'file') {
        concern_file_error(500, 'The file could not be served.');
    }
    concern_respond([
        'success' => false,
        'message' => 'An unexpected server error occurred.',
        // TEMP debug info — remove the "debug" key once things are stable.
        'debug'   => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
    ]);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function validateConcernInput($post, $userInfo) {
    $title              = trim($post['title'] ?? '');
    $details            = trim($post['details'] ?? '');
    $desiredResolution  = trim($post['desired_resolution'] ?? '');
    $departmentId       = $userInfo['department_id'] ?? null;

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if ($details === '') {
        return ['valid' => false, 'message' => 'Details are required.'];
    }
    if (!$departmentId) {
        return ['valid' => false, 'message' => 'Department not found for your account.'];
    }

    return [
        'valid'  => true,
        'fields' => [
            'title'              => $title,
            'details'            => $details,
            'desired_resolution' => $desiredResolution,
            'department_id'      => $departmentId,
        ],
    ];
}

// Same visibility rule as the "list" action: Directress sees everything; everyone else sees
// concerns from their own department (and anything they logged themselves).
function concern_can_access($concern, $userInfo, $isDirectress) {
    if ($isDirectress) {
        return true;
    }
    $myDept = (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0);
    $myId   = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);

    if ($myId && (int) ($concern['submitted_by'] ?? 0) === $myId) {
        return true;
    }
    return $myDept && (int) ($concern['department'] ?? 0) === $myDept;
}

// Never send server file paths to the browser — only flags and display metadata.
// Files are fetched through ?action=file.
function concern_public($concern) {
    $concern['has_pdf']        = !empty($concern['pdf_path']);
    $concern['has_attachment'] = !empty($concern['file_path']);

    if ($concern['has_attachment']) {
        $concern['attachment_name'] = !empty($concern['file_name']) ? $concern['file_name'] : basename($concern['file_path']);
        $concern['attachment_mime'] = FileStorage::mimeFor($concern['file_path']);
        $concern['attachment_size'] = $concern['file_size'] ?? null;
    }

    unset($concern['pdf_path'], $concern['file_path']);
    return $concern;
}

// Validates the optional upload (form field name: "file") and moves it into storage BEFORE
// the database write. Also refuses to edit anything that is not the current user's own draft,
// so a rejected edit can never replace (or delete) another concern's attachment.
// Responds with JSON and exits on any validation problem.
// Returns ['extra' => fields to merge, 'newPath' => ?string, 'oldPath' => ?string]
function concern_prepare_attachment($issuesClass, $issueId, $userInfo) {
    $result = ['extra' => [], 'newPath' => null, 'oldPath' => null];

    // If the file is bigger than PHP's post_max_size, PHP drops $_POST and $_FILES entirely.
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        concern_respond(['success' => false, 'message' => 'The upload is larger than the server allows. Please choose a smaller file (max 10 MB).']);
    }

    if ($issueId) {
        $existing = $issuesClass->getConcernById($issueId);
        $myId     = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);
        if (!$existing || (int) $existing['submitted_by'] !== $myId || $existing['status'] !== 'draft') {
            concern_respond(['success' => false, 'message' => 'Only your own drafts can be edited.']);
        }
        $result['oldPath'] = $existing['file_path'] ?? null;
    }

    $file = $_FILES['file'] ?? null;
    if (!FileStorage::hasUpload($file)) {
        return $result; // attachment is optional
    }

    $error = FileStorage::validateUpload($file);
    if ($error) {
        concern_respond(['success' => false, 'message' => $error]);
    }

    try {
        $relative = FileStorage::store($file, 'issues/attachments', 'issue');
    } catch (\Throwable $e) {
        error_log('[IssueController] Attachment store failed: ' . $e->getMessage());
        concern_respond([
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
function concern_discard_replaced_file($prep) {
    if ($prep['newPath'] && $prep['oldPath'] && $prep['oldPath'] !== $prep['newPath']) {
        FileStorage::delete($prep['oldPath']);
    }
}