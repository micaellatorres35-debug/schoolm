<?php
require_once(__DIR__ . '/../classes/FacultyProfileManager.php');
require_once(__DIR__ . '/../../../database/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$conn = $database->getConnection();
$manager = new FacultyProfileManager($conn);

$engagementId = isset($_GET['engagement_id']) ? (int)$_GET['engagement_id'] : 0;
if ($engagementId <= 0) {
    echo '<p>Invalid engagement id.</p>';
    exit;
}

$engagement = $manager->getEngagementById($engagementId);
if (!$engagement) {
    echo '<p>Engagement record not found.</p>';
    exit;
}

$employee = $manager->getEmployeeById((int)($engagement['employee_id'] ?? 0));

$facultyName = $employee
    ? trim(($employee['first_name'] ?? '') . ' ' . ($employee['middle_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))
    : 'N/A';
$employeeCode = $employee['employee_code'] ?? 'N/A';
$engagementType = $engagement['engagement_type'] ?? 'N/A';
$title = $engagement['title'] ?? 'N/A';
$organization = $engagement['organization'] ?? 'N/A';
$startDateRaw = $engagement['start_date'] ?? null;
$endDateRaw = $engagement['end_date'] ?? null;
$status = strtoupper((string)($engagement['status'] ?? 'Pending'));
$isPending = $status === 'PENDING';
$isApprovedOrCompleted = in_array($status, ['APPROVED', 'COMPLETED'], true);
$approvedAt = $engagement['approved_at'] ?? null;
$dateIssued = $engagement['certificate_generated_at'] ?? null;

function formatCertificateDate($dateValue) {
    if (!$dateValue) {
        return 'N/A';
    }

    $timestamp = strtotime((string)$dateValue);
    if ($timestamp === false) {
        return htmlspecialchars((string)$dateValue);
    }

    return date('F j, Y', $timestamp);
}

$formattedStart = formatCertificateDate($startDateRaw);
$formattedEnd = formatCertificateDate($endDateRaw);
$formattedIssued = formatCertificateDate($dateIssued);
$formattedApproved = formatCertificateDate($approvedAt);

if ($isPending) {
    $certificateTitle = 'ENGAGEMENT CERTIFICATE';
    $subtitle = 'PENDING APPROVAL';
    $mainLead = ($engagementType === 'Part-time')
        ? 'This document acknowledges that'
        : 'This engagement certificate is pending approval for';
    $detailLine = ($engagementType === 'Part-time')
        ? 'has been proposed for a Part-time engagement for the activity:'
        : 'has been proposed for OJT/Training for the activity:';
    $closingText = 'This engagement remains subject to approval by the College Coordinator.';
} else {
    $certificateTitle = ($engagementType === 'Part-time') ? 'CERTIFICATE OF ENGAGEMENT' : 'CERTIFICATE OF COMPLETION';
    $subtitle = 'This certifies that';
    $mainLead = ($engagementType === 'Part-time')
        ? 'has been officially engaged as'
        : 'has completed OJT/Training for the activity:';
    $detailLine = ($engagementType === 'Part-time')
        ? 'has been officially engaged as a part-time faculty member for the activity:'
        : 'has completed OJT/Training for the activity:';
    $closingText = 'We found him/her sincere, hardworking, dedicated and result oriented. He/She worked well as part of the team during his/her tenure. We take this opportunity to thank him/her and wish him/her all the best for his/her future.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Engagement Certificate</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        :root {
            --navy: #0d2d4b;
            --navy-2: #123f68;
            --gold: #c7a56a;
            --gold-soft: #e7d3a1;
            --paper: #ffffff;
            --ink: #1b2a38;
            --muted: #536170;
            --line: #d9d2bf;
            --shadow: rgba(9, 27, 44, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #edf1f4;
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            padding: 20px;
        }

        .certificate-page-wrap {
            display: flex;
            justify-content: center;
        }

        .certificate {
            position: relative;
            width: 297mm;
            height: 210mm;
            background: var(--paper);
            border: 12px solid var(--navy);
            box-shadow: 0 12px 28px var(--shadow);
            overflow: hidden;
            padding: 18mm 20mm 12mm;
        }

        .certificate::before,
        .certificate::after {
            content: "";
            position: absolute;
            width: 70px;
            height: 70px;
            border: 3px solid var(--gold);
            background: transparent;
        }

        .certificate::before {
            top: 18px;
            left: 18px;
            border-right: 0;
            border-bottom: 0;
        }

        .certificate::after {
            right: 18px;
            bottom: 18px;
            border-left: 0;
            border-top: 0;
        }

        .corner {
            position: absolute;
            width: 70px;
            height: 70px;
            border: 3px solid var(--gold);
            background: transparent;
        }

        .corner.tl {
            top: 18px;
            left: 18px;
            border-right: 0;
            border-bottom: 0;
        }

        .corner.tr {
            top: 18px;
            right: 18px;
            border-left: 0;
            border-bottom: 0;
        }

        .corner.bl {
            bottom: 18px;
            left: 18px;
            border-right: 0;
            border-top: 0;
        }

        .corner.br {
            bottom: 18px;
            right: 18px;
            border-left: 0;
            border-top: 0;
        }

        .inner-frame {
            position: absolute;
            inset: 18px;
            border: 2px solid var(--gold-soft);
            pointer-events: none;
        }

        .content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .header-block {
            width: 100%;
            text-align: center;
            margin-top: 6px;
        }

        .school-name {
            font-size: 28px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--navy);
        }

        .school-address {
            margin-top: 8px;
            font-size: 13px;
            line-height: 1.6;
            letter-spacing: 0.02em;
            color: var(--muted);
        }

        .divider {
            width: 72%;
            margin: 14px auto 8px;
            border-top: 1px solid var(--gold);
        }

        .office-line {
            margin-top: 10px;
            font-size: 13px;
            color: var(--navy-2);
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .title-block {
            margin-top: 22px;
            text-align: center;
        }

        .document-title {
            margin: 0;
            font-size: 34px;
            line-height: 1.2;
            letter-spacing: 0.12em;
            color: var(--navy);
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-pill {
            display: inline-block;
            margin-top: 12px;
            padding: 7px 18px;
            border: 1px solid var(--gold);
            background: #f8f1e0;
            color: var(--navy);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border-radius: 16px;
        }

        .lead {
            margin-top: 24px;
            font-size: 18px;
            color: var(--muted);
            text-transform: none;
            letter-spacing: 0.02em;
        }

        .faculty-name {
            margin-top: 8px;
            font-size: 38px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: 0.08em;
            color: var(--navy);
            text-transform: uppercase;
        }

        .detail-copy {
            margin-top: 18px;
            font-size: 17px;
            line-height: 1.7;
            color: var(--ink);
            max-width: 820px;
        }

        .highlights {
            margin-top: 18px;
            font-size: 15px;
            color: var(--ink);
            line-height: 1.8;
            font-weight: 500;
        }

        .highlight-line {
            display: block;
            margin: 3px 0;
        }

        .signature-block {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: auto;
            padding-top: 18px;
        }

        .signature-area {
            width: 250px;
            text-align: center;
            margin: 0 auto;
        }

        .signature-line {
            width: 100%;
            border-top: 2px solid var(--navy);
            margin-bottom: 8px;
        }

        .signature-name {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--navy);
            text-transform: uppercase;
        }

        .signature-role {
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-top: 3px;
        }

        .issued-line {
            margin-top: 12px;
            font-size: 14px;
            color: var(--ink);
            text-align: center;
        }

        .controls {
            text-align: center;
            margin-top: 18px;
            margin-bottom: 12px;
        }

        .btn {
            display: inline-block;
            padding: 9px 16px;
            margin: 0 6px;
            border-radius: 4px;
            background: var(--navy);
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn.secondary {
            background: var(--muted);
        }

        @media print {
            html, body {
                background: #fff;
                padding: 0;
            }

            .controls {
                display: none !important;
            }

            .certificate {
                box-shadow: none;
                margin: 0;
                border-width: 10px;
                page-break-inside: avoid;
            }

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-page-wrap">
        <div class="certificate">
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>
            <div class="inner-frame"></div>

            <div class="content">
                <div class="header-block">
                    <div class="school-name">BESTLINK COLLEGE OF THE PHILIPPINES</div>
                    <div class="school-address">1071 Brgy. Kaligayahan, Quirino Highway<br>Novaliches, Quezon City, Philippines 1116</div>
                    <div class="divider"></div>
                    <div class="office-line">College Coordinator Office</div>
                </div>

                <div class="title-block">
                    <h1 class="document-title"><?php echo htmlspecialchars($certificateTitle); ?></h1>
                </div>

                <div class="lead"><?php echo htmlspecialchars($isPending ? 'This document acknowledges that' : 'This certifies that'); ?></div>
                <div class="faculty-name"><?php echo htmlspecialchars($facultyName); ?></div>

                <div class="detail-copy">
                    <?php if ($isPending): ?>
                        <?php echo htmlspecialchars($engagementType === 'Part-time' ? 'has been proposed for a Part-time engagement' : 'has been proposed for OJT/Training'); ?>
                        <?php echo ' for the activity:'; ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($engagementType === 'Part-time' ? 'has been officially engaged as' : 'has completed OJT/Training for the activity:'); ?>
                    <?php endif; ?>
                </div>

                <div class="faculty-name" style="font-size:30px; margin-top:12px; letter-spacing:0.04em; text-transform:none;"><?php echo htmlspecialchars($title); ?></div>

                <div class="highlights" style="margin-top:18px; text-align:center;">
                    <span class="highlight-line"><strong><?php echo htmlspecialchars($formattedStart); ?> to <?php echo htmlspecialchars($formattedEnd); ?></strong></span>
                </div>

                <?php if ($isPending): ?>
                    <div class="detail-copy" style="margin-top:16px; font-size:15px; text-align:center; max-width:780px;">
                        This document serves as an engagement record indicating that the faculty member has been proposed for the specified engagement. This engagement remains subject to approval by the College Coordinator.
                    </div>
                <?php else: ?>
                    <div class="detail-copy" style="margin-top:16px; font-size:15px; text-align:center; max-width:760px;">
                        We found him/her sincere, hardworking, dedicated and result oriented. He/She worked well as part of the team during his/her tenure. We take this opportunity to thank him/her and wish him/her all the best for his/her future.
                    </div>
                <?php endif; ?>

                <div class="issued-line">
                    <?php if ($isPending): ?>
                        <strong>Pending:</strong> This engagement certificate is awaiting approval by the College Coordinator.
                    <?php else: ?>
                        <strong>Given this <?php echo htmlspecialchars($formattedIssued); ?>.</strong>
                    <?php endif; ?>
                </div>

                <div class="signature-block">
                    <div class="signature-area">
                        <div class="signature-line"></div>
                        <div class="signature-name">COLLEGE COORDINATOR</div>
                        <div class="signature-role">Prepared / Approved by</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="controls">
        <a href="#" class="btn" id="printBtn">Print</a>
        <a href="#" class="btn secondary" id="markGeneratedBtn"><?php echo $dateIssued ? 'Re-mark Generated' : 'Mark Generated'; ?></a>
    </div>

    <script>
        document.getElementById('printBtn').addEventListener('click', function (e) {
            e.preventDefault();
            window.print();
        });

        document.getElementById('markGeneratedBtn').addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Mark this certificate as generated? This will save a timestamp.')) return;

            const payload = { action: 'generate_engagement_certificate', engagement_id: <?php echo (int)$engagementId; ?> };
            fetch('<?php echo htmlspecialchars((dirname($_SERVER['SCRIPT_NAME'])) . '/faculty-management.php'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    alert(data.message || 'Timestamp saved');
                    location.reload();
                    return;
                }
                alert((data && data.message) || 'Unable to save timestamp');
            })
            .catch(() => {
                alert('Unable to save timestamp');
            });
        });
    </script>
</body>
</html>
