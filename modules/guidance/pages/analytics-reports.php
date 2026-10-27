<?php
/**
 * analytics-reports.php
 * Module: Analytics & Reports (Guidance)
 *
 * The 3 breakdown mini-charts render server-side. The generator and the
 * saved-report history are AJAX-driven via
 * controller/AnalyticsReportsController.php. No <link>/<script> tags here.
 */

include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Analytics.php';
include_once __DIR__ . '/../classes/GuidanceReport.php';

$analyticsClass = new Analytics();

$casesByStatus        = $analyticsClass->getCasesByStatus();
$appointmentsByStatus = $analyticsClass->getAppointmentsByStatus();
$incidentsBySeverity  = $analyticsClass->getIncidentsBySeverity();

function rprt_max_count(array $rows): int
{
    $max = 1;
    foreach ($rows as $row) {
        $max = max($max, (int) $row['count']);
    }
    return $max;
}
?>

<div class="module-header">
    <h1>Analytics &amp; Reports</h1>
</div>

<div class="module-content">
<div class="rprt-page" data-page-key="analytics-reports">

    <!-- Breakdown mini-charts -->
    <div class="rprt-breakdown-grid">
        <div class="rprt-breakdown-card">
            <h3>Cases by Status</h3>
            <?php $max = rprt_max_count($casesByStatus); ?>
            <?php foreach ($casesByStatus as $row): ?>
                <div class="rprt-bar-row">
                    <span class="rprt-bar-label"><?= htmlspecialchars($row['label']) ?></span>
                    <div class="rprt-bar-track">
                        <div class="rprt-bar-fill" style="width: <?= round(($row['count'] / $max) * 100) ?>%;"></div>
                    </div>
                    <span class="rprt-bar-value"><?= htmlspecialchars($row['count']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($casesByStatus)): ?><div class="rprt-empty">No case data yet.</div><?php endif; ?>
        </div>

        <div class="rprt-breakdown-card">
            <h3>Appointments by Status</h3>
            <?php $max = rprt_max_count($appointmentsByStatus); ?>
            <?php foreach ($appointmentsByStatus as $row): ?>
                <div class="rprt-bar-row">
                    <span class="rprt-bar-label"><?= htmlspecialchars($row['label']) ?></span>
                    <div class="rprt-bar-track">
                        <div class="rprt-bar-fill" style="width: <?= round(($row['count'] / $max) * 100) ?>%;"></div>
                    </div>
                    <span class="rprt-bar-value"><?= htmlspecialchars($row['count']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($appointmentsByStatus)): ?><div class="rprt-empty">No appointment data yet.</div><?php endif; ?>
        </div>

        <div class="rprt-breakdown-card">
            <h3>Incidents by Severity</h3>
            <?php $max = rprt_max_count($incidentsBySeverity); ?>
            <?php foreach ($incidentsBySeverity as $row): ?>
                <div class="rprt-bar-row">
                    <span class="rprt-bar-label"><?= htmlspecialchars($row['label']) ?></span>
                    <div class="rprt-bar-track">
                        <div class="rprt-bar-fill rprt-bar-fill--severity" style="width: <?= round(($row['count'] / $max) * 100) ?>%;"></div>
                    </div>
                    <span class="rprt-bar-value"><?= htmlspecialchars($row['count']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($incidentsBySeverity)): ?><div class="rprt-empty">No incident data yet.</div><?php endif; ?>
        </div>
    </div>

    <!-- Report generator -->
    <div class="rprt-generator">
        <div class="rprt-generator__header">
            <h3>Generate a Report</h3>
        </div>

        <div class="rprt-generator__controls">
            <select class="rprt-filter-select" id="rprtReportType">
                <?php foreach (GuidanceReport::TYPES as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>

            <select class="rprt-filter-select" id="rprtPreset">
                <option value="this_month">This month</option>
                <option value="last_month">Last month</option>
                <option value="last_30">Last 30 days</option>
                <option value="this_year">This year</option>
                <option value="custom">Custom range</option>
            </select>

            <div class="rprt-filter-group" id="rprtCustomGroup" hidden>
                <input type="date" class="rprt-filter-date" id="rprtDateFrom">
                <span class="rprt-filter-sep">to</span>
                <input type="date" class="rprt-filter-date" id="rprtDateTo">
            </div>

            <button type="button" class="rprt-btn" id="rprtGenerateBtn">Generate</button>
        </div>

        <div class="rprt-status" id="rprtStatus" hidden></div>
    </div>

    <!-- Saved reports -->
    <div class="rprt-generator">
        <div class="rprt-generator__header">
            <h3>Saved Reports</h3>
        </div>
        <div class="rprt-results-wrapper" id="rprtHistoryWrapper">
            <div class="rprt-empty">Loading...</div>
        </div>
    </div>

    <!-- In-modal PDF viewer -->
    <div class="rprt-modal" id="rprtViewerModal" hidden>
        <div class="rprt-modal__dialog">
            <div class="rprt-modal__header">
                <h3 id="rprtViewerTitle">Report</h3>
                <button type="button" class="rprt-btn rprt-btn--sm" id="rprtViewerClose">Close</button>
            </div>
            <iframe class="rprt-modal__frame" id="rprtViewerFrame" title="Report viewer"></iframe>
        </div>
    </div>

</div>
</div>