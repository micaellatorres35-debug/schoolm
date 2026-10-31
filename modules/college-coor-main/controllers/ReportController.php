<?php
// Buffer everything from this point on. If ANY warning/notice/deprecation gets
// printed by an include below, it lands in this buffer instead of corrupting
// the JSON response — we discard the buffer right before echoing our own JSON.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);

// NOTE: the JSON Content-Type header is now set inside rsm_respond() instead of here,
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
    include_once __DIR__ . '/../classes/Department.php';
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
            $page = max(1, (int) ($_GET['page'] ?? 1));

            if ($isDirectress) {
                $departmentId = null;
            } elseif ($departmentId === null) {
                $departmentId = $userInfo['department_id'] ?? null;
            }

            $reports = $reportClass->getReports($departmentId, $status);
            $publicReports = array_map('rsm_public_report', $reports);
            $perPage = 25;
            $total = count($publicReports);
            $pages = max(1, (int) ceil($total / $perPage));
            $page = min($page, $pages);
            $offset = ($page - 1) * $perPage;
            $pageData = array_slice($publicReports, $offset, $perPage);

            rsm_respond([
                'success' => true,
                'data' => $pageData,
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'pages' => $pages,
                    'total' => $total,
                ],
            ]);
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
        // GET stream the generated PDF or the optional attachment.
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
                rsm_file_error(404, $kind === 'attachment' ? 'This report has no attachment.' : 'The PDF has not been generated yet.');
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

            // Validate the optional attachment BEFORE saving anything, so a bad file
            // doesn't leave a half-created draft behind.
            list($attachment, $attachmentError) = rsm_prepare_attachment();
            if ($attachmentError) {
                rsm_respond(['success' => false, 'message' => $attachmentError]);
            }

            $validated = validateReportInput($_POST);
            if (!$validated['valid']) {
                rsm_respond(['success' => false, 'message' => $validated['message']]);
            }
            $reportId = (int) ($_POST['report_id'] ?? 0) ?: null;
            $newId    = $reportClass->saveDraft($validated['fields'], $reportId);

            $warning = null;
            $debug   = null;
            if ($attachment) {
                try {
                    rsm_store_attachment($reportClass, $newId, $attachment);
                } catch (\Throwable $e) {
                    error_log('[ReportController] Attachment failed for report ' . $newId . ': ' . $e->getMessage());
                    $warning = 'Draft saved, but the attachment could not be saved.';
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
        // POST submit a report — creates/promotes to 'submitted' and auto-generates the PDF.
        // PDF generation / attachment failure never blocks the submission itself.
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
            $reportId = (int) ($_POST['report_id'] ?? 0) ?: null;
            $newId    = $reportClass->submitReport($validated['fields'], $reportId);

            $warnings = [];
            $debug    = [];

            if ($attachment) {
                try {
                    rsm_store_attachment($reportClass, $newId, $attachment);
                } catch (\Throwable $e) {
                    error_log('[ReportController] Attachment failed for report ' . $newId . ': ' . $e->getMessage());
                    $warnings[] = 'Report submitted, but the attachment could not be saved.';
                    // TEMP debug info — remove once things are stable.
                    $debug[] = 'attachment: ' . $e->getMessage();
                }
            }

            $pdfGenerated = false;
            try {
                $pdfGenerated = (bool) $reportClass->generatePdf($newId);
            } catch (\Throwable $e) {
                error_log('[ReportController] PDF generation failed for report ' . $newId . ': ' . $e->getMessage());
                $warnings[] = 'Report submitted, but the PDF could not be generated. Contact your administrator.';
                // TEMP debug info — remove once things are stable.
                $debug[] = 'pdf: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            }

            rsm_respond([
                'success' => true,
                'message' => 'Report submitted successfully.',
                'warning' => $warnings ? implode(' ', $warnings) : null,
                'debug'   => $debug ? implode(' | ', $debug) : null,
                'data'    => ['report_id' => $newId, 'has_pdf' => $pdfGenerated],
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
        return [null, null]; // attachment is optional
    }

    $error = FileStorage::validateUpload($file);
    return $error ? [null, $error] : [$file, null];
}

// Saves the validated upload and records it on the report; removes the previous attachment if replaced.
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
        throw new \RuntimeException('Attachment could not be linked to this report (not the owner, or report is no longer editable).');
    }

    if ($oldPath && $oldPath !== $relative) {
        FileStorage::delete($oldPath);
    }
}

function validateReportInput($post) {
    $title           = trim($post['title'] ?? '');
    $reportType      = (int) ($post['report_type'] ?? 0);
    $summary         = trim($post['summary'] ?? '');
    $findings        = trim($post['findings'] ?? '');
    $recommendations = trim($post['recommendations'] ?? '');

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if ($reportType === 0) {
        return ['valid' => false, 'message' => 'Please select a report type.'];
    }
    if ($summary === '') {
        return ['valid' => false, 'message' => 'Summary is required.'];
    }

    return [
        'valid'  => true,
        'fields' => [
            'title'           => $title,
            'report_type'     => $reportType,
            'summary'         => $summary,
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ],
    ];
}