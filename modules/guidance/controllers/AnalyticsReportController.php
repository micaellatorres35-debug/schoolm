<?php
/**
 * AnalyticsReportsController.php
 * Module: Guidance Analytics & Reports
 *
 * Actions: list | chart_data | generate (POST) | view | delete (POST)
 * Access: role_name "Guidance Counselor" or "School Directress".
 */

ob_start();

const RPRT_ALLOWED_ROLES = ['Guidance Counselor', 'School Directress'];

$rprtResponded = false;

function rprt_respond(array $payload, int $status = 200): void
{
    global $rprtResponded;
    $rprtResponded = true;

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

register_shutdown_function(function () {
    global $rprtResponded;
    if ($rprtResponded) {
        return;
    }

    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    }
});

/** Validates date_from / date_to from the given request array or responds 422. */
function rprt_period(array $source): array
{
    $period = GuidanceReport::normalizePeriod(
        (string) ($source['date_from'] ?? ''),
        (string) ($source['date_to'] ?? '')
    );

    if (!$period) {
        rprt_respond(['success' => false, 'message' => 'Choose a valid date range (max 5 years).'], 422);
    }

    return $period;
}

/** Keeps only genuine PNG data URIs (max 4, 1.5 MB each) and returns base64 strings. */
function rprt_clean_charts($raw): array
{
    $out = [];
    if (!is_array($raw)) {
        return $out;
    }

    foreach (array_slice($raw, 0, 4) as $uri) {
        if (!is_string($uri) || strpos($uri, 'data:image/png;base64,') !== 0) {
            continue;
        }

        $bin = base64_decode(substr($uri, 22), true);
        if ($bin === false || strlen($bin) > 1500000) {
            continue;
        }

        $info = @getimagesizefromstring($bin);
        if (!$info || $info[2] !== IMAGETYPE_PNG) {
            continue;
        }

        $out[] = base64_encode($bin);
    }

    return $out;
}

/** Resolves a saved report's PDF path, or null if the stored name/file is invalid. */
function rprt_pdf_path(array $row): ?string
{
    if (!preg_match(GuidanceReport::FILE_PATTERN, (string) $row['file_name'])) {
        return null;
    }

    $path = GuidanceReport::storageDir() . DIRECTORY_SEPARATOR . $row['file_name'];

    return is_file($path) ? $path : null;
}

try {
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/User.php';
    include_once __DIR__ . '/../classes/GuidanceReport.php';
    include_once __DIR__ . '/../classes/GuidanceReportPdf.php';

    $userClass = new User();
    $userInfo  = $userClass->userSession();

    if (!$userInfo) {
        rprt_respond(['success' => false, 'message' => 'Session expired.'], 401);
    }

    $userInfo = (array) $userInfo;

    if (!in_array($userInfo['role'] ?? '', RPRT_ALLOWED_ROLES, true)) {
        rprt_respond(['success' => false, 'message' => 'You do not have access to Guidance reports.'], 403);
    }

    $reports = new GuidanceReport();
    $action  = $_REQUEST['action'] ?? '';
    $isPost  = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

    switch ($action) {

        // GET -- saved report history
        case 'list':
            $employeeId = $reports->resolveEmployeeId($userInfo);
            rprt_respond([
                'success' => true,
                'data'    => ['reports' => $reports->listReports($employeeId)],
            ]);
            break;

        // GET -- aggregate data the browser turns into Chart.js PNGs
        case 'chart_data':
            $type = (string) ($_GET['type'] ?? '');
            if (!GuidanceReport::isValidType($type)) {
                rprt_respond(['success' => false, 'message' => 'Invalid report type.'], 422);
            }

            $period = rprt_period($_GET);
            $data   = $reports->build($type, $period, false);

            rprt_respond([
                'success' => true,
                'data'    => ['total' => $data['total'], 'charts' => $data['charts']],
            ]);
            break;

        // POST -- build the PDF, save it, record it
        case 'generate':
            if (!$isPost) {
                rprt_respond(['success' => false, 'message' => 'POST required.'], 405);
            }

            $type = (string) ($_POST['type'] ?? '');
            if (!GuidanceReport::isValidType($type)) {
                rprt_respond(['success' => false, 'message' => 'Invalid report type.'], 422);
            }

            $period     = rprt_period($_POST);
            $employeeId = $reports->resolveEmployeeId($userInfo);
            if ($employeeId <= 0) {
                rprt_respond(['success' => false, 'message' => 'Your account is not linked to an employee record.'], 403);
            }

            @set_time_limit(60);
            @ini_set('memory_limit', '256M');

            $report = $reports->build($type, $period, true);
            if ($report['total'] === 0) {
                rprt_respond(['success' => false, 'message' => 'No records found for the selected period.'], 422);
            }

            $images = rprt_clean_charts($_POST['charts'] ?? []);
            $pdf    = GuidanceReportPdf::render($report, $images, $reports->employeeName($employeeId));

            $fileName = GuidanceReport::newFileName($type);
            $path     = GuidanceReport::storageDir() . DIRECTORY_SEPARATOR . $fileName;

            if (file_put_contents($path, $pdf) === false) {
                throw new RuntimeException('Could not write the PDF file.');
            }

            try {
                $id = $reports->saveRecord($type, $period, $report['total'], $fileName, $employeeId);
            } catch (\Throwable $e) {
                @unlink($path);
                throw $e;
            }

            rprt_respond([
                'success' => true,
                'message' => 'Report generated.',
                'data'    => ['report_id' => $id, 'title' => GuidanceReport::TYPES[$type]],
            ]);
            break;

        // GET -- stream the saved PDF inline for the modal viewer
        case 'view':
            $row = $reports->getReport((int) ($_GET['id'] ?? 0));
            if (!$row) {
                rprt_respond(['success' => false, 'message' => 'Report not found.'], 404);
            }

            $path = rprt_pdf_path($row);
            if (!$path) {
                rprt_respond(['success' => false, 'message' => 'The PDF file is missing.'], 404);
            }

            $rprtResponded = true;
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="guidance-report-' . (int) $row['report_id'] . '.pdf"');
            header('Content-Length: ' . filesize($path));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, no-store');
            readfile($path);
            exit;

        // POST -- only the person who generated a report can delete it
        case 'delete':
            if (!$isPost) {
                rprt_respond(['success' => false, 'message' => 'POST required.'], 405);
            }

            $row = $reports->getReport((int) ($_POST['id'] ?? 0));
            if (!$row) {
                rprt_respond(['success' => false, 'message' => 'Report not found.'], 404);
            }

            $employeeId = $reports->resolveEmployeeId($userInfo);
            if ($employeeId <= 0 || (int) $row['generated_by'] !== $employeeId) {
                rprt_respond(['success' => false, 'message' => 'Only the person who generated a report can delete it.'], 403);
            }

            $path = rprt_pdf_path($row);
            if ($path) {
                @unlink($path);
            }

            $reports->deleteRecord((int) $row['report_id']);
            rprt_respond(['success' => true, 'message' => 'Report deleted.']);
            break;

        default:
            rprt_respond(['success' => false, 'message' => 'Unknown action.'], 400);
    }
} catch (\Throwable $e) {
    error_log('[AnalyticsReports] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    rprt_respond(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}