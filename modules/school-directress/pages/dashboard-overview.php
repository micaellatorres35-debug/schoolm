<?php

include_once __DIR__ . "/../../../auth/session.php";
include_once __DIR__ . "/../../../database/db.php";
include_once __DIR__ . "/../classes/Employee.php";
include_once __DIR__ . "/../classes/Overview.php";
include_once __DIR__ . "/../classes/StudentMonitoring.php";
include_once __DIR__ . "/../classes/DirectressDashboard.php";

$database = new Database();
$conn = $database->getConnection();

$employeeClass = new Employee();
$employeeName = $employeeClass->getEmployeeName();

$studentClass = new StudentMonitoring();
$totalStudents = count($studentClass->getStudents());

/* ── New dashboard data (all read-only) ─────────────── */
$dashboard = new DirectressDashboard($conn);

$trends         = $dashboard->getKpiTrends((int) $todayClinic);
$upcomingCount  = $dashboard->getUpcomingEventCount();
$activity       = $dashboard->getRecentActivity(5);
$departments    = $dashboard->getDepartmentStatus();
$enrollment     = $dashboard->getEnrollmentTrend();
$population     = $dashboard->getCoursePopulation(5);
$announcements  = $dashboard->getAnnouncements(4);
$events         = $dashboard->getUpcomingEvents(4);

/* ── Greeting ───────────────────────────────────────── */
$hour      = (int) date('G');
$greeting  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$nameParts = explode(' ', trim((string) $employeeName));
$firstName = $nameParts[0] !== '' ? $nameParts[0] : 'there';

/* ── View helpers ───────────────────────────────────── */
if (!function_exists('ddE')) {
    function ddE($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('ddIcon')) {
    function ddIcon($name)
    {
        $paths = [
            'bell'      => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'clock'     => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'file'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
            'check'     => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            'alert'     => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            'x'         => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
            'megaphone' => '<path d="M3 11v2a1 1 0 0 0 1 1h3l5 4V6L7 10H4a1 1 0 0 0-1 1z"/><path d="M16 8a5 5 0 0 1 0 8"/>',
            'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'chevron'   => '<polyline points="9 18 15 12 9 6"/>',
            'building'  => '<rect x="4" y="3" width="16" height="18" rx="1"/><line x1="9" y1="8" x2="9.01" y2="8"/><line x1="15" y1="8" x2="15.01" y2="8"/><line x1="9" y1="12" x2="9.01" y2="12"/><line x1="15" y1="12" x2="15.01" y2="12"/><path d="M10 21v-4h4v4"/>',
            'bars'      => '<line x1="6" y1="20" x2="6" y2="12"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="18" y1="20" x2="18" y2="9"/>',
            'pie'       => '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
        ];
        $body = isset($paths[$name]) ? $paths[$name] : '';
        return '<svg class="dd-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
    }
}

if (!function_exists('ddTrend')) {
    function ddTrend($diff, $unit)
    {
        $diff = (int) $diff;
        if ($diff > 0) {
            return '<div class="card-trend trend-up">&#9650; ' . $diff . ' ' . ddE($unit) . '</div>';
        }
        if ($diff < 0) {
            return '<div class="card-trend trend-down">&#9660; ' . abs($diff) . ' ' . ddE($unit) . '</div>';
        }
        return '<div class="card-trend trend-flat">&#8212; No change</div>';
    }
}

if (!function_exists('ddShort')) {
    function ddShort($text, $max = 60)
    {
        return ddE(mb_strimwidth((string) $text, 0, $max, '…', 'UTF-8'));
    }
}

/* ── Pending Actions rows (counts reuse the card queries) ── */
$pendingActions = [
    [
        'title' => 'Approval Requests',
        'desc'  => 'Requests waiting for your decision.',
        'count' => (int) $pendingApprovals,
        'tone'  => 'warn',
        'icon'  => 'bell',
        'go'    => 'approval-decision-support',
    ],
    [
        'title' => 'Department Concerns',
        'desc'  => 'Concerns that need your attention.',
        'count' => (int) $openIssues,
        'tone'  => 'alert',
        'icon'  => 'alert',
        'go'    => 'concerns-issue-tracking',
    ],
    [
        'title' => 'Reports for Review',
        'desc'  => 'Submitted reports waiting for review.',
        'count' => (int) $pendingReports,
        'tone'  => 'info',
        'icon'  => 'file',
        'go'    => 'report-submission-management',
    ],
    [
        'title' => 'Upcoming Events',
        'desc'  => 'Scheduled in the next 30 days.',
        'count' => (int) $upcomingCount,
        'tone'  => 'info',
        'icon'  => 'calendar',
        'go'    => '',
    ],
];

/* ── Departments that are behind on reports ─────────── */
$behindCount = 0;
foreach ($departments as $dept) {
    if ($dept['submitted_reports'] < $dept['required_reports']) {
        $behindCount++;
    }
}

/* ── Enrollment bars ────────────────────────────────── */
$trendSets = [
    'six'  => $enrollment['six'],
    'year' => $enrollment['year'],
];

/* ── Donut gradient ─────────────────────────────────── */
$donutStops = [];
$donutAcc   = 0;
foreach ($population['slices'] as $slice) {
    $start        = $donutAcc;
    $donutAcc    += $slice['pct'];
    $donutStops[] = 'var(--dd-s' . (int) $slice['tone'] . ') '
                  . number_format($start, 2, '.', '') . '% '
                  . number_format(min($donutAcc, 100), 2, '.', '') . '%';
}
$donutBackground = !empty($donutStops)
    ? 'conic-gradient(' . implode(', ', $donutStops) . ')'
    : 'var(--color9)';

?>


<div class="dd-root" data-dd-root="dashboard-overview">

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h2><?= ddE($greeting) ?>, <?= ddE($firstName) ?>!</h2>
        <p>Here's what's happening with the school today.</p>
    </div>
    <div class="welcome-quote">
        &ldquo;Quality education builds brighter futures.&rdquo;
        <span>School Directress</span>
    </div>
    <div class="welcome-date">
        <div class="date-day"><?= date('d') ?></div>
        <div class="date-month"><?= date('F Y') ?></div>
    </div>
</div>

<!-- Row 1: Enrollment & Personnel -->
<p class="cards-section-label">Enrollment &amp; Personnel</p>
<div class="summary-cards-grid">

    <div class="summary-card card-accent-primary">
        <div class="card-value"><?= number_format($totalStudents) ?></div>
        <div class="card-label">Total Enrolled Students</div>
        <?= ddTrend($trends['students_month'], 'this month') ?>
    </div>

    <div class="summary-card card-accent-secondary">
        <div class="card-value"><?= number_format($pendingApplicants) ?></div>
        <div class="card-label">Pending Applicants</div>
    </div>

    <div class="summary-card card-accent-muted">
        <div class="card-value"><?= number_format($activeEmployees) ?></div>
        <div class="card-label">Active Employees</div>
        <?= ddTrend($trends['employees_month'], 'this month') ?>
    </div>

    <div class="summary-card card-accent-primary">
        <div class="card-value"><?= number_format($todayClinic) ?></div>
        <div class="card-label">Clinic Visits Today</div>
        <?= ddTrend($trends['clinic_diff'], 'vs yesterday') ?>
    </div>

</div>

<!-- Row 2: Reports & Operations -->
<p class="cards-section-label">Reports &amp; Operations</p>
<div class="summary-cards-grid">

    <div class="summary-card card-accent-primary">
        <div class="card-value"><?= number_format($pendingReports) ?></div>
        <div class="card-label">Pending Reports</div>
    </div>

    <div class="summary-card card-accent-secondary">
        <div class="card-value"><?= number_format($pendingApprovals) ?></div>
        <div class="card-label">Pending Approvals</div>
    </div>

    <div class="summary-card card-accent-muted">
        <div class="card-value"><?= number_format($openIssues) ?></div>
        <div class="card-label">Open Department Issues</div>
    </div>

    <div class="summary-card card-accent-primary">
        <div class="card-value"><?= number_format($overdueBooks) ?></div>
        <div class="card-label">Overdue Library Books</div>
    </div>

</div>

<!-- Row 3: Pending Actions · Recent Activity · Department Status -->
<p class="cards-section-label">Needs Your Attention</p>
<div class="dd-grid">

    <!-- Pending Actions -->
    <section class="dd-panel">
        <div class="dd-panel-header">
            <h3 class="dd-panel-title"><?= ddIcon('bell') ?>Pending Actions</h3>
        </div>

        <ul class="dd-action-list">
            <?php foreach ($pendingActions as $action):
                $tone    = $action['count'] > 0 ? $action['tone'] : 'zero';
                $isLink  = $action['go'] !== '';
            ?>
                <li class="dd-action<?= $isLink ? ' is-link' : '' ?>"
                    <?= $isLink ? 'data-dd-go="' . ddE($action['go']) . '" role="button" tabindex="0"' : '' ?>>
                    <span class="dd-action-icon tone-<?= ddE($action['tone']) ?>"><?= ddIcon($action['icon']) ?></span>
                    <span class="dd-action-text">
                        <strong><?= ddE($action['title']) ?></strong>
                        <small><?= ddE($action['desc']) ?></small>
                    </span>
                    <span class="dd-count tone-<?= ddE($tone) ?>"><?= (int) $action['count'] ?></span>
                    <?php if ($isLink): ?><span class="dd-chevron"><?= ddIcon('chevron') ?></span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <button type="button" class="dd-btn" data-dd-go="approval-decision-support">Go to Approval &amp; Decision Support</button>
    </section>

    <!-- Recent Activity -->
    <section class="dd-panel">
        <div class="dd-panel-header">
            <h3 class="dd-panel-title"><?= ddIcon('clock') ?>Recent Activity</h3>
        </div>

        <?php if (empty($activity)): ?>
            <p class="dd-empty">No activity yet. New reports, approvals and concerns will show up here.</p>
        <?php else: ?>
            <ul class="dd-activity-list">
                <?php foreach ($activity as $row):
                    list($label, $icon, $tone) = DirectressDashboard::describeActivity($row);
                ?>
                    <li class="dd-activity">
                        <span class="dd-activity-icon tone-<?= ddE($tone) ?>"><?= ddIcon($icon) ?></span>
                        <span class="dd-activity-body">
                            <strong><?= ddE($label) ?></strong>
                            <small><?= ddShort($row['title'], 56) ?></small>
                            <?php if (!empty($row['department'])): ?>
                                <em class="dd-chip"><?= ddE($row['department']) ?></em>
                            <?php endif; ?>
                        </span>
                        <span class="dd-activity-time"><?= ddE(DirectressDashboard::timeAgo($row['mins_ago'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Department Status -->
    <section class="dd-panel dd-span-wide">
        <div class="dd-panel-header">
            <h3 class="dd-panel-title"><?= ddIcon('building') ?>Department Status</h3>
            <a class="dd-link" role="button" tabindex="0" data-dd-go="report-submission-management">View details</a>
        </div>

        <?php if (empty($departments)): ?>
            <p class="dd-empty">No departments have required report types yet.</p>
        <?php else: ?>
            <div class="dd-dept-table">
                <div class="dd-dept-row dd-dept-head">
                    <span>Department</span>
                    <span>Reports</span>
                    <span class="dd-center">Concerns</span>
                    <span class="dd-center">Approvals</span>
                </div>

                <?php foreach ($departments as $dept):
                    $req   = $dept['required_reports'];
                    $sub   = $dept['submitted_reports'];
                    $pct   = $req > 0 ? round(($sub / $req) * 100) : 0;
                    $state = $sub >= $req ? 'complete' : ($sub > 0 ? 'partial' : 'none');
                ?>
                    <div class="dd-dept-row">
                        <span class="dd-dept-name"><?= ddE($dept['department_name']) ?></span>
                        <span class="dd-dept-progress">
                            <small><?= (int) $sub ?> / <?= (int) $req ?></small>
                            <span class="dd-track"><span class="dd-fill is-<?= $state ?>" style="width: <?= (int) $pct ?>%"></span></span>
                        </span>
                        <span class="dd-center"><span class="dd-count tone-<?= $dept['concerns'] > 0 ? 'alert' : 'zero' ?>"><?= (int) $dept['concerns'] ?></span></span>
                        <span class="dd-center"><span class="dd-count tone-<?= $dept['approvals'] > 0 ? 'warn' : 'zero' ?>"><?= (int) $dept['approvals'] ?></span></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($behindCount > 0): ?>
                <div class="dd-note tone-alert">
                    <?= ddIcon('alert') ?>
                    <span><?= (int) $behindCount ?> <?= $behindCount === 1 ? 'department has' : 'departments have' ?> not submitted all required reports this month.</span>
                </div>
            <?php else: ?>
                <div class="dd-note tone-ok">
                    <?= ddIcon('check') ?>
                    <span>All departments have submitted their required reports this month.</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</div>

<!-- Row 4: Enrollment Trend · Population · Announcements -->
<p class="cards-section-label">Students &amp; Announcements</p>
<div class="dd-grid">

    <!-- Enrollment Trend -->
    <section class="dd-panel" data-dd-range-scope>
        <div class="dd-panel-header">
            <h3 class="dd-panel-title"><?= ddIcon('bars') ?>Enrollment Trend</h3>
            <div class="dd-toggle" role="group" aria-label="Enrollment range">
                <button type="button" class="dd-toggle-btn is-active" data-dd-range="six" aria-pressed="true">6 months</button>
                <button type="button" class="dd-toggle-btn" data-dd-range="year" aria-pressed="false">This year</button>
            </div>
        </div>

        <?php foreach ($trendSets as $key => $set):
            $max = max(array_merge([1], array_column($set, 'value')));
            $sum = array_sum(array_column($set, 'value'));
        ?>
            <div data-dd-bars="<?= ddE($key) ?>" <?= $key === 'six' ? '' : 'hidden' ?>>
                <div class="dd-bars">
                    <?php foreach ($set as $bar):
                        $h = $bar['value'] > 0 ? max(4, round(($bar['value'] / $max) * 100)) : 0;
                    ?>
                        <div class="dd-bar">
                            <div class="dd-bar-track">
                                <div class="dd-bar-fill<?= $bar['value'] === 0 ? ' is-empty' : '' ?>" style="height: <?= (int) $h ?>%">
                                    <span class="dd-bar-value"><?= (int) $bar['value'] ?></span>
                                </div>
                            </div>
                            <span class="dd-bar-label"><?= ddE($bar['label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($sum === 0): ?>
                    <p class="dd-empty dd-empty-inline">No new enrollments in this period.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <p class="dd-caption">New enrollments per month</p>
    </section>

    <!-- Student Population by Course -->
    <section class="dd-panel">
        <div class="dd-panel-header">
            <h3 class="dd-panel-title"><?= ddIcon('pie') ?>Student Population by Course</h3>
        </div>

        <?php if ($population['total'] === 0): ?>
            <p class="dd-empty">No enrolled students yet.</p>
        <?php else: ?>
            <div class="dd-population">
                <div class="dd-donut" style="background: <?= $donutBackground ?>">
                    <div class="dd-donut-hole">
                        <strong><?= (int) $population['total'] ?></strong>
                        <span>Total students</span>
                    </div>
                </div>

                <ul class="dd-legend">
                    <?php foreach ($population['slices'] as $slice): ?>
                        <li>
                            <span class="dd-dot" style="background: var(--dd-s<?= (int) $slice['tone'] ?>)"></span>
                            <span class="dd-legend-label"><?= ddE($slice['label']) ?></span>
                            <span class="dd-legend-count"><?= (int) $slice['count'] ?></span>
                            <span class="dd-legend-pct"><?= (int) round($slice['pct']) ?>%</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>

    <!-- Announcements & Upcoming Events -->
    <section class="dd-panel dd-span-wide" data-dd-tabs>
        <div class="dd-panel-header">
            <h3 class="dd-panel-title"><?= ddIcon('megaphone') ?>Announcements &amp; Events</h3>
            <a class="dd-link" role="button" tabindex="0" data-dd-go="communications-announcements">View all</a>
        </div>

        <div class="dd-tabs" role="tablist">
            <button type="button" class="dd-tab is-active" role="tab" aria-selected="true" data-dd-tab="announcements">Announcements</button>
            <button type="button" class="dd-tab" role="tab" aria-selected="false" data-dd-tab="events">Upcoming Events</button>
        </div>

        <div data-dd-pane="announcements">
            <?php if (empty($announcements)): ?>
                <p class="dd-empty">No announcements published yet.</p>
            <?php else: ?>
                <ul class="dd-feed">
                    <?php foreach ($announcements as $item): ?>
                        <li>
                            <span class="dd-feed-icon"><?= ddIcon('megaphone') ?></span>
                            <span class="dd-feed-title"><?= ddShort($item['title'], 70) ?></span>
                            <?php if ((int) $item['days_ago'] <= 3): ?><span class="dd-new">New</span><?php endif; ?>
                            <span class="dd-feed-date"><?= ddE(date('M j, Y', strtotime($item['publish_date']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div data-dd-pane="events" hidden>
            <?php if (empty($events)): ?>
                <p class="dd-empty">No upcoming events scheduled.</p>
            <?php else: ?>
                <ul class="dd-feed">
                    <?php foreach ($events as $item): ?>
                        <li>
                            <span class="dd-feed-icon"><?= ddIcon('calendar') ?></span>
                            <span class="dd-feed-title"><?= ddShort($item['event_title'], 70) ?></span>
                            <span class="dd-feed-date"><?= ddE(date('M j, Y', strtotime($item['event_date']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

</div>

</div>