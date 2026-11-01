<?php
// Buffer everything from this point on. If ANY warning/notice/deprecation gets
// printed by an include below, it lands in this buffer instead of corrupting
// the JSON response — we discard the buffer right before echoing our own JSON.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);

const RSM_PER_PAGE = 10;

// NOTE: the JSON Content-Type header is set inside rsm_respond() instead of here,
// because the "file" action streams a PDF/attachment and must send its own headers.

function rsm_respond($payload) {
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
function rsm_file_error($httpCode, $message) {
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

// Last-resort safety net: catches fatals that happen even outside try/catch
// (e.g. a parse error while including a file, or memory exhaustion) so the
// response is always valid JSON instead of an empty/HTML body.
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
            // TEMP debug info — remove the "debug" key once this is fixed.
            'debug' => $error['message'] . ' in ' . $error['file'] . ':' . $error['line'],
        ]);
    }
});

try {
    // Includes are inside the try so a parse/fatal error in any of these files
    // gets caught below and returned as JSON instead of an empty 500 response.
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/FileStorage.php';
    include_once __DIR__ . '/../classes/Report.php';
    include_once __DIR__ . '/../classes/User.php';

    // ── Guard ──────────────────────────────────────────────────────────────
    $userClass = new User();
    $userInfo  = $userClass->userSession();

    $action = $_REQUEST['action'] ?? '';

    if (!$userInfo) {
        if ($action === 'file') {
            rsm_file_error(401, 'Session expired. Please log in again.');
        }
        rsm_respond(['success' => false, 'message' => 'Session expired.']);
    }

    $reportClass  = new Report();
    $roleName     = $_SESSION['role_name'] ?? $userInfo['role'] ?? '';
$isDirectress = in_array($roleName, ['School Directress', 'Admin College Coordinator'], true);

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {

        // ------------------------------------------------------------------
        // GET paginated list, filtered by department / type / status / dates / search.
        // The School Directress sees every department; everyone else is always
        // limited to their own department, whatever the request says.
        // ------------------------------------------------------------------
        case 'list':
            $filters = rsm_filters($isDirectress, $userInfo);
            $total   = $reportClass->countReports($filters);
            $pages   = max(1, (int) ceil($total / RSM_PER_PAGE));
            $page    = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

            $rows = $reportClass->listReports($filters, RSM_PER_PAGE, ($page - 1) * RSM_PER_PAGE);

            rsm_respond([
                'success' => true,
                'data'    => $rows,
                'meta'    => ['total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => RSM_PER_PAGE],
            ]);
            break;

        // ------------------------------------------------------------------
        // GET analytics for the dashboard (stat cards + three charts).
        // Same department scoping as "list".
        // ------------------------------------------------------------------
        case 'analytics':
            $filters  = rsm_filters($isDirectress, $userInfo);
            $statuses = $reportClass->getStatusCounts($filters);

            rsm_respond([
                'success' => true,
                'data'    => [
                    'stats' => [
                        'total'    => array_sum($statuses),
                        'pending'  => $statuses['submitted'],
                        'approved' => $statuses['approved'],
                        'rejected' => $statuses['rejected'],
                    ],
                    'statuses'    => $statuses,
                    'trend'       => $reportClass->getMonthlyTrend($filters),
                    'departments' => $reportClass->getDepartmentCounts($filters),
                ],
            ]);
            break;

        // ------------------------------------------------------------------
        // GET report type names for the type filter (narrowed by department)
        // ------------------------------------------------------------------
        case 'report_types':
            $filters = rsm_filters($isDirectress, $userInfo);
            $deptId  = ($filters['department_id'] !== null && $filters['department_id'] > 0) ? $filters['department_id'] : null;
            rsm_respond(['success' => true, 'data' => $reportClass->getReportTypeNames($deptId)]);
            break;

        // ------------------------------------------------------------------
        // GET single report (for the detail/review modal)
        // ------------------------------------------------------------------
        case 'get':
            $reportId = (int) ($_GET['report_id'] ?? 0);
            if (!$reportId) {
                rsm_respond(['success' => false, 'message' => 'Report ID is required.']);
            }
            $report = $reportClass->getReportById($reportId);
            if (!$report) {
                rsm_respond(['success' => false, 'message' => 'Report not found.']);
            }
            if (!rsm_can_access($report, $userInfo, $isDirectress)) {
                rsm_respond(['success' => false, 'message' => 'You do not have access to this report.']);
            }
            rsm_respond(['success' => true, 'data' => rsm_public_report($report)]);
            break;

        // ------------------------------------------------------------------
        // GET stream the report file (attachment) or an older generated PDF.
        //   ?action=file&report_id=12&kind=pdf|attachment[&download=1]
        // Files are never linked directly; every request passes the session
        // and department/role check here first.
        // ------------------------------------------------------------------
        case 'file':
            $reportId = (int) ($_GET['report_id'] ?? 0);
            $kind     = ($_GET['kind'] ?? 'pdf') === 'attachment' ? 'attachment' : 'pdf';
            $download = ($_GET['download'] ?? '') === '1';

            $report = $reportId ? $reportClass->getReportById($reportId) : null;
            if (!$report) {
                rsm_file_error(404, 'Report not found.');
            }
            if (!rsm_can_access($report, $userInfo, $isDirectress)) {
                rsm_file_error(403, 'You do not have access to this file.');
            }

            if ($kind === 'attachment') {
                $relative = $report['attachment_path'] ?? null;
                $name     = $report['attachment_name'] ?: 'attachment';
                $mime     = $report['attachment_mime'] ?: 'application/octet-stream';
            } else {
                $relative = $report['pdf_path'] ?? null;
                $name     = 'report_' . $reportId . '.pdf';
                $mime     = 'application/pdf';
            }

            if (!$relative) {
                rsm_file_error(404, $kind === 'attachment' ? 'This report has no file attached.' : 'No PDF is stored for this report.');
            }

            $absolute = FileStorage::resolve($relative);
            if (!$absolute) {
                error_log('[ReportController] File missing on disk for report ' . $reportId . ' (' . $kind . '): ' . $relative);
                rsm_file_error(404, 'The file is missing on the server.');
            }

            FileStorage::stream($absolute, $name, $mime, $download); // ends the request
            break;

        // ------------------------------------------------------------------
        // POST save as draft (multipart/form-data, optional "attachment" file)
        // ------------------------------------------------------------------
        case 'save_draft':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                rsm_respond(['success' => false, 'message' => 'Method not allowed.']);
            }

            // Validate the file BEFORE saving anything, so a bad file
            // doesn't leave a half-created draft behind.
            list($attachment, $attachmentError) = rsm_prepare_attachment();
            if ($attachmentError) {
                rsm_respond(['success' => false, 'message' => $attachmentError]);
            }

            $validated = validateReportInput($_POST);
            if (!$validated['valid']) {
                rsm_respond(['success' => false, 'message' => $validated['message']]);
            }
            if (!$reportClass->reportTypeExists($validated['fields']['report_type'])) {
                rsm_respond(['success' => false, 'message' => 'The selected report type does not exist.']);
            }

            $reportId = (int) ($_POST['report_id'] ?? 0) ?: null;
            if ($reportId) {
                $existing = $reportClass->getReportById($reportId);
                if (!$existing || (int) $existing['submitted_by'] !== (int) $_SESSION['employee_id'] || $existing['status'] !== 'draft') {
                    rsm_respond(['success' => false, 'message' => 'This draft can no longer be edited.']);
                }
            }
            $newId = $reportClass->saveDraft($validated['fields'], $reportId);

            $warning = null;
            $debug   = null;
            if ($attachment) {
                try {
                    rsm_store_attachment($reportClass, $newId, $attachment);
                } catch (\Throwable $e) {
                    error_log('[ReportController] Attachment failed for report ' . $newId . ': ' . $e->getMessage());
                    $warning = 'Draft saved, but the file could not be saved.';
                    // TEMP debug info — remove once things are stable.
                    $debug = $e->getMessage();
                }
            }

            rsm_respond([
                'success' => true,
                'message' => 'Draft saved.',
                'warning' => $warning,
                'debug'   => $debug,
                'data'    => ['report_id' => $newId],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST submit a report. A report file is required; nothing is generated
        // by the system. If the file can't be stored the report stays a draft.
        // ------------------------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                rsm_respond(['success' => false, 'message' => 'Method not allowed.']);
            }

            list($attachment, $attachmentError) = rsm_prepare_attachment();
            if ($attachmentError) {
                rsm_respond(['success' => false, 'message' => $attachmentError]);
            }

            $validated = validateReportInput($_POST);
            if (!$validated['valid']) {
                rsm_respond(['success' => false, 'message' => $validated['message']]);
            }
            if (!$reportClass->reportTypeExists($validated['fields']['report_type'])) {
                rsm_respond(['success' => false, 'message' => 'The selected report type does not exist.']);
            }

            $reportId = (int) ($_POST['report_id'] ?? 0) ?: null;
            $existing = null;
            if ($reportId) {
                $existing = $reportClass->getReportById($reportId);
                if (!$existing || (int) $existing['submitted_by'] !== (int) $_SESSION['employee_id'] || $existing['status'] !== 'draft') {
                    rsm_respond(['success' => false, 'message' => 'This draft can no longer be submitted.']);
                }
            }

            $hasFile = $attachment || ($existing && !empty($existing['attachment_path']));
            if (!$hasFile) {
                rsm_respond(['success' => false, 'message' => 'Please attach the report file before submitting.']);
            }

            $newId = $reportClass->saveDraft($validated['fields'], $reportId);

            if ($attachment) {
                try {
                    rsm_store_attachment($reportClass, $newId, $attachment);
                } catch (\Throwable $e) {
                    error_log('[ReportController] Attachment failed for report ' . $newId . ': ' . $e->getMessage());
                    rsm_respond([
                        'success' => false,
                        'message' => 'The file could not be saved, so the report was kept as a draft. Please try again.',
                        // TEMP debug info — remove once things are stable.
                        'debug'   => $e->getMessage(),
                    ]);
                }
            }

            if (!$reportClass->submitDraft($newId)) {
                rsm_respond(['success' => false, 'message' => 'This report could not be submitted.']);
            }

            rsm_respond([
                'success' => true,
                'message' => 'Report submitted successfully.',
                'data'    => ['report_id' => $newId],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted report as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if (!$isDirectress) {
                rsm_respond(['success' => false, 'message' => 'Only the School Directress can review reports.']);
            }
            $reportId = (int) ($_POST['report_id'] ?? 0);
            $notes    = trim($_POST['notes'] ?? '');
            if (!$reportId) {
                rsm_respond(['success' => false, 'message' => 'Report ID is required.']);
            }
            $ok = $reportClass->markReviewed($reportId, $userInfo['employee_id'], $notes !== '' ? $notes : null);
            rsm_respond(['success' => $ok, 'message' => $ok ? 'Report marked as reviewed.' : 'Unable to review this report.']);
            break;

        // ------------------------------------------------------------------
        // POST Directress approves/rejects a reviewed report
        // ------------------------------------------------------------------
        case 'decide':
            if (!$isDirectress) {
                rsm_respond(['success' => false, 'message' => 'Only the School Directress can decide on reports.']);
            }
            $reportId = (int) ($_POST['report_id'] ?? 0);
            $decision = $_POST['decision'] ?? '';
            $notes    = trim($_POST['notes'] ?? '');
            if (!$reportId || !in_array($decision, ['approved', 'rejected'], true)) {
                rsm_respond(['success' => false, 'message' => 'A valid report ID and decision are required.']);
            }
            $ok = $reportClass->decide($reportId, $userInfo['employee_id'], $decision, $notes !== '' ? $notes : null);
            rsm_respond(['success' => $ok, 'message' => $ok ? "Report {$decision}." : 'Unable to decide on this report.']);
            break;

        // ------------------------------------------------------------------
        // POST/GET generate an AI summary for a report
        // ------------------------------------------------------------------
        case 'summarize':
            $reportId = (int) ($_REQUEST['report_id'] ?? 0);
            if (!$reportId) {
                rsm_respond(['success' => false, 'message' => 'Report ID is required.']);
            }
            $report = $reportClass->getReportById($reportId);
            if (!$report || !rsm_can_access($report, $userInfo, $isDirectress)) {
                rsm_respond(['success' => false, 'message' => 'You do not have access to this report.']);
            }
            $result = $reportClass->generateAiSummary($reportId);
            rsm_respond($result);
            break;

        default:
            rsm_respond(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (\Throwable $e) {
    error_log('[ReportController] Unhandled error: ' . $e->getMessage());
    if (($_REQUEST['action'] ?? '') === 'file') {
        rsm_file_error(500, 'The file could not be served.');
    }
    rsm_respond([
        'success' => false,
        'message' => 'An unexpected server error occurred.',
        // TEMP debug info — remove the "debug" key once things are stable.
        'debug' => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
    ]);
}

// ── Helpers ────────────────────────────────────────────────────────────────

// Reads and validates the filter query parameters shared by list, analytics and report_types.
// Directress: any department (or all). Everyone else: forced to their own department;
// -1 means "no department", which matches nothing.
function rsm_filters($isDirectress, $userInfo) {
    if ($isDirectress) {
        $dept = (int) ($_GET['department_id'] ?? 0);
        $dept = $dept > 0 ? $dept : null;
    } else {
        $own  = (int) ($userInfo['department_id'] ?? 0);
        $dept = $own > 0 ? $own : -1;
    }

    $status = (string) ($_GET['status'] ?? '');
    if (!in_array($status, Report::STATUSES, true)) {
        $status = '';
    }

    $from = rsm_valid_date($_GET['date_from'] ?? '');
    $to   = rsm_valid_date($_GET['date_to'] ?? '');
    if ($from && $to && $from > $to) {
        list($from, $to) = [$to, $from];
    }

    return [
        'department_id' => $dept,
        'type'          => mb_substr(trim((string) ($_GET['type'] ?? '')), 0, 100),
        'status'        => $status,
        'date_from'     => $from,
        'date_to'       => $to,
        'search'        => mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100),
    ];
}

// Returns the date back as Y-m-d if it is a real calendar date, otherwise null.
function rsm_valid_date($value) {
    $value = trim((string) $value);
    $d = DateTime::createFromFormat('!Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : null;
}

// Same visibility rule as the "list" action: Directress sees everything;
// everyone else sees their own department's reports (and anything they submitted).
function rsm_can_access($report, $userInfo, $isDirectress) {
    if ($isDirectress) {
        return true;
    }
    $myDept = (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0);
    $myId   = (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0);

    if ($myId && (int) ($report['submitted_by'] ?? 0) === $myId) {
        return true;
    }
    return $myDept && (int) ($report['department_id'] ?? 0) === $myDept;
}

// Never send server file paths to the browser — only flags. Files are fetched via action=file.
function rsm_public_report($report) {
    $report['has_pdf']        = !empty($report['pdf_path']);
    $report['has_attachment'] = !empty($report['attachment_path']);
    unset($report['pdf_path'], $report['attachment_path']);
    return $report;
}

// Returns [uploadedFileArray|null, errorMessage|null]
function rsm_prepare_attachment() {
    // If the file is bigger than PHP's post_max_size, PHP drops $_POST and $_FILES entirely.
    // Without this check the user would just see "Title is required."
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        return [null, 'The upload is larger than the server allows. Please choose a smaller file (max 10 MB).'];
    }

    $file = $_FILES['attachment'] ?? null;
    if (!FileStorage::hasUpload($file)) {
        return [null, null]; // no file chosen (the caller decides whether that is allowed)
    }

    $error = FileStorage::validateUpload($file);
    return $error ? [null, $error] : [$file, null];
}

// Saves the validated upload and records it on the report; removes the previous file if replaced.
function rsm_store_attachment($reportClass, $reportId, $file) {
    $existing = $reportClass->getReportById($reportId);
    $oldPath  = $existing['attachment_path'] ?? null;

    $relative = FileStorage::store($file, 'reports/attachments', 'report_' . (int) $reportId);

    $saved = $reportClass->setAttachment(
        $reportId,
        $relative,
        FileStorage::cleanName($file['name']),
        FileStorage::mimeFor($file['name']),
        (int) $file['size']
    );

    if (!$saved) {
        FileStorage::delete($relative); // don't leave an orphan file behind
        throw new \RuntimeException('The file could not be linked to this report (not the owner, or report is no longer editable).');
    }

    if ($oldPath && $oldPath !== $relative) {
        FileStorage::delete($oldPath);
    }
}

function validateReportInput($post) {
    $title       = trim($post['title'] ?? '');
    $reportType  = (int) ($post['report_type'] ?? 0);
    $description = trim($post['description'] ?? '');

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if (mb_strlen($title) > 255) {
        return ['valid' => false, 'message' => 'Title must be 255 characters or fewer.'];
    }
    if ($reportType === 0) {
        return ['valid' => false, 'message' => 'Please select a report type.'];
    }
    if (mb_strlen($description) > 5000) {
        return ['valid' => false, 'message' => 'The description must be 5,000 characters or fewer.'];
    }

    return [
        'valid'  => true,
        'fields' => [
            'title'       => $title,
            'report_type' => $reportType,
            'description' => $description !== '' ? $description : null,
        ],
    ];
}