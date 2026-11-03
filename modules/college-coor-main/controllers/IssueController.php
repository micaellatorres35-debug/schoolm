<?php
// Buffer everything so any stray warning/notice from an include lands here instead of
// corrupting the JSON response.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the response
error_reporting(E_ALL);         // still log real errors

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

// Used only by the "file" action (the response is opened in an <iframe>/new tab, not parsed as JSON).
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

// Last-resort safety net for fatals outside try/catch. Details go to the log, not the browser.
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[IssueController] Fatal: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
        if (ob_get_length() !== false) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => false, 'message' => 'A fatal server error occurred.']);
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
    $isDirectress = Issues::isDirectress($userInfo);
    $viewer       = Issues::viewerFrom($userInfo);

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {

        // ------------------------------------------------------------------
        // GET list. Visibility is decided server-side (see Issues::visibilitySql).
        // Only the Directress may choose a department; everyone else is scoped
        // to their own department + their own concerns no matter what is sent.
        // ------------------------------------------------------------------
        case 'list':
            $departmentId = concern_department_filter($isDirectress);
            $status = trim((string) ($_GET['status'] ?? ''));
            $status = in_array($status, Issues::STATUSES, true) ? $status : null;
            $search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);

            $concerns = $issuesClass->getConcerns($departmentId, $status, $search, $viewer);
            concern_respond([
                'success' => true,
                'data'    => array_map(function ($c) use ($viewer) {
                    return concern_list_public($c, $viewer);
                }, $concerns),
            ]);
            break;

        // ------------------------------------------------------------------
        // GET overview: summary counts + "needs attention" list.
        // ------------------------------------------------------------------
        case 'overview':
            $departmentId = concern_department_filter($isDirectress);
            $summary   = $issuesClass->getSummaryCounts($viewer, $departmentId);
            $attention = $issuesClass->getAttentionConcerns($viewer, $departmentId, 5);
            concern_respond([
                'success' => true,
                'data'    => [
                    'summary'   => $summary,
                    'attention' => array_map(function ($c) use ($viewer) {
                        return concern_list_public($c, $viewer);
                    }, $attention),
                ],
            ]);
            break;

        // ------------------------------------------------------------------
        // GET single concern (case detail)
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
            concern_respond(['success' => true, 'data' => concern_detail_public($concern, $isDirectress, $viewer)]);
            break;

        // ------------------------------------------------------------------
        // GET stream the optional attachment.
        //   ?action=file&issue_id=12[&download=1]
        // Files are never linked directly; every request passes the session
        // and visibility check here first.
        // ------------------------------------------------------------------
        case 'file':
            $issueId  = (int) ($_GET['issue_id'] ?? 0);
            $download = ($_GET['download'] ?? '') === '1';

            $concern = $issueId ? $issuesClass->getConcernById($issueId) : null;
            if (!$concern) {
                concern_file_error(404, 'Concern not found.');
            }
            if (!concern_can_access($concern, $userInfo, $isDirectress)) {
                concern_file_error(403, 'You do not have access to this file.');
            }

            $relative = $concern['file_path'] ?? null;
            if (!$relative) {
                concern_file_error(404, 'This concern has no attachment.');
            }

            $name = !empty($concern['file_name']) ? $concern['file_name'] : basename((string) $relative);
            $mime = FileStorage::mimeFor($relative);

            // Only PDFs and images are shown inline; anything else is always a download.
            $previewable = ($mime === 'application/pdf' || strpos($mime, 'image/') === 0);
            if (!$previewable) {
                $download = true;
            }

            $absolute = FileStorage::resolve($relative);
            if (!$absolute) {
                error_log('[IssueController] File missing on disk for concern ' . $issueId . ': ' . $relative);
                concern_file_error(404, 'The file is missing on the server.');
            }

            FileStorage::stream($absolute, $name, $mime, $download); // ends the request
            break;

        // ------------------------------------------------------------------
        // POST save as draft (multipart/form-data, optional "file" upload)
        // ------------------------------------------------------------------
        case 'save_draft':
            concern_require_post();
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

            concern_respond(['success' => true, 'message' => 'Draft saved.', 'data' => ['issue_id' => (int) $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST submit a concern — creates/promotes to 'submitted'.
        // (No PDF is generated any more; the attachment is viewable in the system.)
        // ------------------------------------------------------------------
        case 'submit':
            concern_require_post();
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

            concern_respond(['success' => true, 'message' => 'Concern submitted.', 'data' => ['issue_id' => (int) $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted concern as reviewed
        // ------------------------------------------------------------------
        case 'review':
            concern_require_post();
            if (!$isDirectress) {
                concern_respond(['success' => false, 'message' => 'Only the School Directress can review concerns.']);
            }
            if (!$viewer['employee_id']) {
                concern_respond(['success' => false, 'message' => 'Your employee record could not be found.']);
            }
            $issueId = (int) ($_POST['issue_id'] ?? 0);
            $notes   = trim((string) ($_POST['notes'] ?? ''));
            if (!$issueId) {
                concern_respond(['success' => false, 'message' => 'Issue ID is required.']);
            }
            if (mb_strlen($notes) > 5000) {
                concern_respond(['success' => false, 'message' => 'Review notes are too long (maximum 5,000 characters).']);
            }
            $ok = $issuesClass->markReviewed($issueId, $viewer['employee_id'], $notes !== '' ? $notes : null);
            concern_respond([
                'success' => $ok,
                'message' => $ok ? 'Marked as reviewed.' : 'This concern is no longer waiting for review. Refresh and try again.',
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress resolves/dismisses a reviewed concern.
        // A written summary is required so a case is never closed silently.
        // ------------------------------------------------------------------
        case 'decide':
            concern_require_post();
            if (!$isDirectress) {
                concern_respond(['success' => false, 'message' => 'Only the School Directress can resolve concerns.']);
            }
            if (!$viewer['employee_id']) {
                concern_respond(['success' => false, 'message' => 'Your employee record could not be found.']);
            }
            $issueId  = (int) ($_POST['issue_id'] ?? 0);
            $decision = (string) ($_POST['decision'] ?? '');
            $notes    = trim((string) ($_POST['notes'] ?? ''));
            if (!$issueId || !in_array($decision, ['resolved', 'dismissed'], true)) {
                concern_respond(['success' => false, 'message' => 'A valid issue ID and decision are required.']);
            }
            if (mb_strlen($notes) < 10) {
                concern_respond(['success' => false, 'message' => 'Please write a summary of at least 10 characters before closing this concern.']);
            }
            if (mb_strlen($notes) > 5000) {
                concern_respond(['success' => false, 'message' => 'The summary is too long (maximum 5,000 characters).']);
            }
            $ok = $issuesClass->resolve($issueId, $viewer['employee_id'], $decision, $notes);
            concern_respond([
                'success' => $ok,
                'message' => $ok ? "Concern {$decision}." : 'This concern is not in the reviewed stage. Refresh and try again.',
            ]);
            break;

        // ------------------------------------------------------------------
        // POST generate an AI summary (only for concerns the user may open)
        // ------------------------------------------------------------------
        case 'summarize':
            concern_require_post();
            $issueId = (int) ($_POST['issue_id'] ?? $_GET['issue_id'] ?? 0);
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
            concern_respond($issuesClass->generateAiSummary($issueId));
            break;

        default:
            concern_respond(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (\Throwable $e) {
    error_log('[IssueController] Unhandled error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (($_REQUEST['action'] ?? '') === 'file') {
        concern_file_error(500, 'The file could not be served.');
    }
    concern_respond(['success' => false, 'message' => 'An unexpected server error occurred.']);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function concern_require_post() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        concern_respond(['success' => false, 'message' => 'Method not allowed.']);
    }
}

// The department filter is honoured only for the Directress.
function concern_department_filter($isDirectress) {
    if ($isDirectress && isset($_GET['department_id']) && $_GET['department_id'] !== '') {
        return (int) $_GET['department_id'];
    }
    return null;
}

function validateConcernInput($post, $userInfo) {
    $title              = trim((string) ($post['title'] ?? ''));
    $details            = trim((string) ($post['details'] ?? ''));
    $desiredResolution  = trim((string) ($post['desired_resolution'] ?? ''));
    $departmentId       = $userInfo['department_id'] ?? null;

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if (mb_strlen($title) > 255) {
        return ['valid' => false, 'message' => 'Title must be 255 characters or fewer.'];
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

// Drafts are private to their author. Submitted concerns follow the same rule as the list:
// the Directress sees everything; everyone else sees their own department's concerns
// and anything they submitted themselves.
function concern_can_access($concern, $userInfo, $isDirectress) {
    $myDept = (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0);
    $myId   = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);
    $isOwner = $myId && (int) ($concern['submitted_by'] ?? 0) === $myId;

    if (($concern['status'] ?? '') === 'draft') {
        return $isOwner;
    }
    if ($isDirectress || $isOwner) {
        return true;
    }
    return $myDept && (int) ($concern['department'] ?? 0) === $myDept;
}

// List rows: whitelist only. No description, notes, summary or file paths.
function concern_list_public($c, $viewer) {
    return [
        'issue_id'        => (int) $c['issue_id'],
        'title'           => $c['title'] ?? '',
        'department_name' => $c['department_name'] ?? null,
        'submitted_by'    => $c['submitted_by'] ?? null,
        'status'          => $c['status'],
        'submitted_on'    => $c['submitted_on'] ?? null,
        'created_at'      => $c['created_at'] ?? null,
        'updated_at'      => $c['updated_at'] ?? null,
        'days_waiting'    => isset($c['days_waiting']) ? (int) $c['days_waiting'] : null,
        'has_attachment'  => !empty($c['has_attachment']),
        'is_mine'         => $viewer['employee_id'] && (int) ($c['submitted_by_id'] ?? 0) === $viewer['employee_id'],
    ];
}

// Case detail: whitelist only. Never sends file paths or raw employee ids.
// review_notes (the Directress's working notes) are sent to the Directress only.
function concern_detail_public($c, $isDirectress, $viewer) {
    $status = $c['status'];
    $isMine = $viewer['employee_id'] && (int) $c['submitted_by'] === $viewer['employee_id'];

    $out = [
        'issue_id'           => (int) $c['issue_id'],
        'title'              => $c['title'] ?? '',
        'details'            => $c['details'] ?? '',
        'desired_resolution' => $c['desired_resolution'] ?? '',
        'department_name'    => $c['department_name'] ?? null,
        'submitted_by_name'  => $c['submitted_by_name'] ?? null,
        'status'             => $status,
        'created_at'         => $c['created_at'] ?? null,
        'submitted_on'       => $c['submitted_on'] ?? null,
        'updated_at'         => $c['updated_at'] ?? null,
        'reviewed_by_name'   => $c['reviewed_by_name'] ?? null,
        'reviewed_at'        => $c['reviewed_at'] ?? null,
        'resolved_by_name'   => $c['resolved_by_name'] ?? null,
        'resolved_at'        => $c['resolved_at'] ?? null,
        'resolution_notes'   => $c['resolution_notes'] ?? null,
        'ai_summary'         => $c['ai_summary'] ?? null,
        'is_mine'            => $isMine,
        'can_review'         => $isDirectress && $status === 'submitted',
        'can_resolve'        => $isDirectress && $status === 'reviewed',
        'has_attachment'     => !empty($c['file_path']),
    ];

    if ($isDirectress) {
        $out['review_notes'] = $c['review_notes'] ?? null;
    }

    if ($out['has_attachment']) {
        $out['attachment_name'] = !empty($c['file_name']) ? $c['file_name'] : basename($c['file_path']);
        $out['attachment_mime'] = FileStorage::mimeFor($c['file_path']);
        $out['attachment_size'] = $c['file_size'] ?? null;
    }

    return $out;
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
        concern_respond(['success' => false, 'message' => 'The attachment could not be saved on the server.']);
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