<?php
require_once('../../../database/db.php');
require_once('../classes/FacultyProfileManager.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$source = (string)($_GET['source'] ?? '');
$recordId = (int)($_GET['record_id'] ?? 0);
if ($recordId <= 0 || !in_array($source, ['em_certifications', 'cc_certification_engagements'], true)) {
    http_response_code(400);
    exit('Invalid certificate request.');
}

try {
    $database = new Database();
    $manager = new FacultyProfileManager($database->getConnection());

    if ($source === 'cc_certification_engagements') {
        $engagement = $manager->getEngagementById($recordId);
        if (!$engagement) {
            http_response_code(404);
            exit('Engagement record not found.');
        }

        $employee = $manager->getEmployeeById((int)($engagement['employee_id'] ?? 0));
        if (!$employee) {
            http_response_code(404);
            exit('Faculty record not found.');
        }

        $manager->generateEngagementCertificate($recordId);
        $facultyName = trim(implode(' ', array_filter([
            $employee['first_name'] ?? '',
            $employee['middle_name'] ?? '',
            $employee['last_name'] ?? '',
            $employee['suffix'] ?? '',
        ])));
        $engagementType = (string)($engagement['engagement_type'] ?? 'OJT/Training');
        $activityTitle = (string)($engagement['title'] ?? 'Engagement');
        $organization = (string)($engagement['organization'] ?? 'N/A');
        $startDate = !empty($engagement['start_date']) ? date('F j, Y', strtotime($engagement['start_date'])) : 'N/A';
        $endDate = !empty($engagement['end_date']) ? date('F j, Y', strtotime($engagement['end_date'])) : 'N/A';
        $isPending = ($engagement['status'] ?? '') === 'Pending';
        $certificateTitle = $isPending
            ? 'ENGAGEMENT CERTIFICATE'
            : ($engagementType === 'Part-time' ? 'CERTIFICATE OF ENGAGEMENT' : 'CERTIFICATE OF COMPLETION');
        $description = $isPending
            ? 'has been proposed for ' . ($engagementType === 'Part-time' ? 'a Part-time engagement' : 'OJT/Training')
            : ($engagementType === 'Part-time' ? 'has been officially engaged for' : 'has completed OJT/Training for');
        $e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>
            @page { size: A4 landscape; margin: 12mm; }
            body { margin: 0; color: #1b2a38; font-family: DejaVu Sans, sans-serif; text-align: center; }
            .certificate { height: 178mm; border: 8px double #0d2d4b; padding: 18mm 22mm 12mm; box-sizing: border-box; position: relative; }
            .school { color: #0d2d4b; font-size: 15pt; font-weight: bold; letter-spacing: 1.5px; }
            .address { color: #536170; font-size: 9pt; margin-top: 5px; }
            .office { border-top: 1px solid #c7a56a; display: inline-block; margin-top: 9px; padding-top: 7px; color: #536170; font-size: 10pt; }
            h1 { margin: 16mm 0 7mm; color: #0d2d4b; font-size: 25pt; }
            .lead { font-size: 12pt; color: #536170; }
            .faculty { margin: 5mm 0; font-size: 25pt; font-weight: bold; color: #123f68; }
            .description { font-size: 12pt; color: #536170; }
            .activity { margin: 4mm auto; font-size: 20pt; font-weight: bold; color: #0d2d4b; }
            .details { margin-top: 5mm; font-size: 11pt; }
            .organization { margin-top: 2mm; font-size: 10pt; color: #536170; }
            .status { margin-top: 5mm; font-size: 10pt; color: #536170; }
            .signature { position: absolute; bottom: 13mm; right: 22mm; width: 65mm; border-top: 1px solid #0d2d4b; padding-top: 4px; font-size: 9pt; font-weight: bold; color: #0d2d4b; }
        </style></head><body><main class="certificate">
            <div class="school">BESTLINK COLLEGE OF THE PHILIPPINES</div>
            <div class="address">1071 Brgy. Kaligayahan, Quirino Highway<br>Novaliches, Quezon City, Philippines 1116</div>
            <div class="office">College Coordinator Office</div>
            <h1>' . $e($certificateTitle) . '</h1>
            <div class="lead">This certifies that</div>
            <div class="faculty">' . $e($facultyName) . '</div>
            <div class="description">' . $e($description) . ' the activity</div>
            <div class="activity">' . $e($activityTitle) . '</div>
            <div class="details">' . $e($startDate) . ' to ' . $e($endDate) . '</div>
            <div class="organization">' . $e($organization) . '</div>
            <div class="status">' . ($isPending ? 'Pending approval by the College Coordinator' : 'Issued ' . $e(date('F j, Y'))) . '</div>
            <div class="signature">COLLEGE COORDINATOR<br><span style="font-weight:normal">Prepared / Approved by</span></div>
        </main></body></html>';

        require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
        $pdf = new \Dompdf\Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $facultyName . '_' . $activityTitle);
        $downloadName = trim((string)$baseName, '_') ?: 'Engagement_Certificate';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $downloadName . '.pdf"');
        header('Content-Length: ' . strlen($pdf->output()));
        header('X-Content-Type-Options: nosniff');
        echo $pdf->output();
        exit;
    }

    $storedPath = $manager->getTrainingCertificatePath($source, $recordId);
    if (!$storedPath || !str_starts_with($storedPath, 'uploads/faculty_certificates/')) {
        http_response_code(404);
        exit('Certificate not found.');
    }

    $projectRoot = dirname(__DIR__, 3);
    $storageRoot = realpath($projectRoot . '/uploads/faculty_certificates');
    $filePath = realpath($projectRoot . '/' . $storedPath);
    if (!$storageRoot || !$filePath || !str_starts_with($filePath, $storageRoot . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
        http_response_code(404);
        exit('Certificate file not found.');
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($filePath) ?: 'application/octet-stream';
    $fileName = basename($filePath);
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
} catch (Throwable $e) {
    error_log('Training certificate download failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to download certificate.');
}