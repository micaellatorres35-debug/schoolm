<?php
require_once dirname(__DIR__) . '/classes/EventManager.php';
require_once dirname(__DIR__) . '/classes/Approval.php';
require_once dirname(__DIR__) . '/classes/Report.php';
require_once dirname(dirname(dirname(__DIR__))) . '/database/db.php';

$database = new Database();
$conn = $database->getConnection();
$eventManager = new EventManager($conn);
$calendarEventsData = [];
$calendarLoadError = null;

try {
    $calendarEventsData = $eventManager->getAllEvents();
} catch (Exception $e) {
    error_log('Research dashboard calendar load failed: ' . $e->getMessage());
    $calendarLoadError = 'Research activities calendar could not be loaded.';
}

$eventsByDate = [];
$notificationAlerts = [];
$tz = new DateTimeZone('Asia/Manila');
$now = new DateTime('now', $tz);

foreach ($calendarEventsData as $event) {
    $eventDate = $event['event_date'] ?? null;
    if (!$eventDate) {
        continue;
    }

    $eventsByDate[$eventDate][] = [
        'title' => $event['event_title'] ?? 'Untitled Event',
        'type' => $event['event_type'] ?? 'Academic',
        'time' => $event['start_time'] ?? '',
        'endTime' => $event['end_time'] ?? '',
        'location' => $event['location'] ?? '',
        'description' => $event['description'] ?? '',
        'status' => $event['status'] ?? 'upcoming',
        'audience' => $event['target_audience'] ?? '',
    ];

    try {
        $eventDateTime = new DateTime(
            $eventDate . ' ' . ($event['start_time'] ?? '00:00:00'),
            $tz
        );
        $endDateTime = null;
        if (!empty($event['end_time'])) {
            $endDateTime = new DateTime($eventDate . ' ' . $event['end_time'], $tz);
            if ($endDateTime <= $eventDateTime) {
                $endDateTime->modify('+1 day');
            }
        }
    } catch (Exception $e) {
        error_log('Research dashboard event date is invalid: ' . $e->getMessage());
        continue;
    }

    $remaining = 'Completed';
    $priority = 'normal';
    $badgeClass = 'bg-secondary';
    if (($endDateTime && $endDateTime >= $now) || (!$endDateTime && $eventDateTime >= $now)) {
        $minutesRemaining = max(0, (int) floor(($eventDateTime->getTimestamp() - $now->getTimestamp()) / 60));
        if ($minutesRemaining <= 48 * 60) {
            $priority = 'urgent';
            $badgeClass = 'bg-danger';
        } elseif ($minutesRemaining <= 72 * 60) {
            $priority = 'high';
            $badgeClass = 'bg-warning text-dark';
        } else {
            $badgeClass = 'bg-success';
        }

        if ($eventDateTime <= $now && $endDateTime && $endDateTime > $now) {
            $remaining = 'Ongoing';
        } elseif ($minutesRemaining < 60) {
            $remaining = 'Starts in ' . max(1, $minutesRemaining) . ' minute' . ($minutesRemaining === 1 ? '' : 's');
        } elseif ($minutesRemaining < 24 * 60) {
            $hoursRemaining = (int) ceil($minutesRemaining / 60);
            $remaining = 'Starts in ' . $hoursRemaining . ' hour' . ($hoursRemaining === 1 ? '' : 's');
        } else {
            $daysRemaining = (int) ceil($minutesRemaining / (24 * 60));
            $remaining = 'Starts in ' . $daysRemaining . ' day' . ($daysRemaining === 1 ? '' : 's');
        }
    }

    $createdAt = $eventDateTime;
    if (!empty($event['created_at'])) {
        try {
            $createdAt = new DateTime($event['created_at'], $tz);
        } catch (Exception $e) {
            error_log('Research dashboard event creation date is invalid: ' . $e->getMessage());
        }
    }

    $notificationAlerts[] = [
        'id' => $event['event_id'] ?? md5(($event['event_title'] ?? '') . $eventDate),
        'title' => $event['event_title'] ?? 'Untitled Event',
        'date' => $eventDateTime->format('Y-m-d'),
        'day' => $eventDateTime->format('j'),
        'priority' => $priority,
        'badgeClass' => $badgeClass,
        'remaining' => $remaining,
        'startTime' => $eventDateTime->format('g:i A'),
        'notification' => $createdAt->format('g:i A'),
        'notification_created_iso' => $createdAt->format(DateTime::ATOM),
        'start_iso' => $eventDateTime->format(DateTime::ATOM),
        'end_iso' => $endDateTime ? $endDateTime->format(DateTime::ATOM) : null,
    ];
}

$departmentId = isset($_SESSION['department_id']) ? (int) $_SESSION['department_id'] : null;
$pendingReportCount = null;
$pendingApprovalCount = null;
$workflowErrors = [];

if ($departmentId === null || $departmentId <= 0) {
    $workflowErrors[] = 'Department records are unavailable for this account.';
} else {
    try {
        $reports = (new Report($conn))->getReports($departmentId);
        $pendingReportCount = count(array_filter($reports, static function ($report) {
            return in_array(strtolower($report['status'] ?? ''), ['submitted', 'reviewed'], true);
        }));
    } catch (Exception $e) {
        error_log('Research dashboard report count failed: ' . $e->getMessage());
        $workflowErrors[] = 'Pending report count could not be loaded.';
    }

    try {
        $approvals = (new Approval($conn))->getApprovals($departmentId);
        $pendingApprovalCount = count(array_filter($approvals, static function ($approval) {
            return in_array(strtolower($approval['status'] ?? ''), ['submitted', 'reviewed'], true);
        }));
    } catch (Exception $e) {
        error_log('Research dashboard approval count failed: ' . $e->getMessage());
        $workflowErrors[] = 'Pending requests and approvals count could not be loaded.';
    }
}

$serverTimestampIso = $now->format(DateTime::ATOM);
$researchDashboardData = [
    'pendingReports' => $pendingReportCount,
    'pendingApprovals' => $pendingApprovalCount,
    'workflowErrors' => $workflowErrors,
    'calendarError' => $calendarLoadError,
];
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<div class="module-header research-dashboard-header">
    <div class="research-dashboard-heading">
        <span class="research-dashboard-eyebrow"><i class="fas fa-chart-line" aria-hidden="true"></i> Research workspace</span>
        <h1>Research Coordinator Dashboard</h1>
        <p class="text-muted small">Overview of research projects, proposal workflows, deadlines, and activities</p>
    </div>
    <div class="research-dashboard-header-actions">
        <a class="research-dashboard-action research-dashboard-action-primary" href="?page=research-project" data-page="research-project">
            <i class="fas fa-flask" aria-hidden="true"></i> Manage projects
        </a>
        <a class="research-dashboard-action" href="?page=approval-submission" data-page="approval-submission">
            <i class="fas fa-clipboard-check" aria-hidden="true"></i> Review requests
        </a>
    </div>
</div>

<div class="module-content research-dashboard">
    <div id="researchDashboardNotice" class="research-dashboard-notice" role="status" aria-live="polite" hidden></div>

    <section class="research-kpis" aria-labelledby="researchSnapshotTitle">
        <div class="research-dashboard-section-heading">
            <h2 id="researchSnapshotTitle">Research Coordinator Snapshot</h2>
            <p>Current project and workflow totals</p>
        </div>
        <ul class="research-kpi-list">
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-flask"></i></span>
                <span class="research-kpi-label">Total Research Projects</span>
                <strong class="research-kpi-value" data-research-count="total">—</strong>
                <a class="research-kpi-link" href="?page=research-project" data-page="research-project">View projects</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-spinner"></i></span>
                <span class="research-kpi-label">Ongoing Research Projects</span>
                <strong class="research-kpi-value" data-research-count="ongoing">—</strong>
                <a class="research-kpi-link" href="?page=research-project" data-page="research-project">View projects</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                <span class="research-kpi-label">Completed Research Projects</span>
                <strong class="research-kpi-value" data-research-count="completed">—</strong>
                <a class="research-kpi-link" href="?page=research-project" data-page="research-project">View projects</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-file-alt"></i></span>
                <span class="research-kpi-label">Pending Research Proposals</span>
                <strong class="research-kpi-value" data-research-count="pendingProposals">—</strong>
                <a class="research-kpi-link" href="?page=research-proposal" data-page="research-proposal">View proposals</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-file-signature"></i></span>
                <span class="research-kpi-label">Approved Research Proposals</span>
                <strong class="research-kpi-value" data-research-count="approvedProposals">—</strong>
                <a class="research-kpi-link" href="?page=research-proposal" data-page="research-proposal">View proposals</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
                <span class="research-kpi-label">Pending Research Reports</span>
                <strong class="research-kpi-value" data-research-count="pendingReports">—</strong>
                <a class="research-kpi-link" href="?page=report-submission" data-page="report-submission">View reports</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-folder-open"></i></span>
                <span class="research-kpi-label">Research Documents</span>
                <strong class="research-kpi-value" data-research-count="researchDocuments">—</strong>
                <a class="research-kpi-link" href="?page=research-documentation" data-page="research-documentation">Manage documents</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-calendar-check"></i></span>
                <span class="research-kpi-label">Upcoming Research Activities</span>
                <strong class="research-kpi-value" data-research-count="upcomingActivities">—</strong>
                <a class="research-kpi-link" href="?page=events-management" data-page="events-management">View activities</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></span>
                <span class="research-kpi-label">Delayed Research Projects</span>
                <strong class="research-kpi-value" data-research-count="delayed">—</strong>
                <a class="research-kpi-link" href="?page=research-project" data-page="research-project">View projects</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-stamp"></i></span>
                <span class="research-kpi-label">Pending Requests and Approvals</span>
                <strong class="research-kpi-value" data-research-count="pendingApprovals">—</strong>
                <a class="research-kpi-link" href="?page=approval-submission" data-page="approval-submission">View requests</a>
            </li>
            <li class="research-kpi-card">
                <span class="research-kpi-bullet" aria-hidden="true"><i class="fas fa-shield-heart"></i></span>
                <span class="research-kpi-label">Ethics and Compliance Records</span>
                <strong class="research-kpi-value" data-research-count="ethicsRecords">—</strong>
                <a class="research-kpi-link" href="?page=ethics-compliance" data-page="ethics-compliance">View compliance</a>
            </li>
        </ul>
    </section>

    <div class="research-dashboard-grid research-overview-grid">
        <section class="research-recent-section" aria-labelledby="researchRecentTitle">
            <div class="research-dashboard-section-heading">
                <div>
                    <h2 id="researchRecentTitle">Recent Research Projects</h2>
                    <p>Latest registered projects and their current status</p>
                </div>
                <a class="research-view-all" href="?page=research-project" data-page="research-project">View all</a>
            </div>
            <div class="research-recent-list" id="researchRecentProjects">
                <p class="research-empty-state">Loading research projects…</p>
            </div>
        </section>
        <section class="research-status-section" aria-labelledby="researchStatusTitle">
            <div class="research-dashboard-section-heading">
                <div>
                    <h2 id="researchStatusTitle">Project status</h2>
                    <p>Distribution across the research workflow</p>
                </div>
                <a class="research-view-all" href="?page=research-project" data-page="research-project">View projects</a>
            </div>
            <div class="research-status-overview" id="researchStatusOverview">
                <p class="research-empty-state">Loading research project status…</p>
            </div>
        </section>
    </div>

    <div class="research-dashboard-grid research-activity-grid">
        <section class="research-deadlines-section" aria-labelledby="researchDeadlinesTitle">
            <div class="research-dashboard-section-heading">
                <div>
                    <h2 id="researchDeadlinesTitle">Research Deadlines and Notifications</h2>
                    <p>Upcoming milestones and overdue research work</p>
                </div>
                <a class="research-view-all" href="?page=research-project" data-page="research-project">Monitor</a>
            </div>
            <ul class="research-deadline-list" id="researchDeadlineList">
                <li class="research-empty-state">Loading research deadlines…</li>
            </ul>
        </section>

        <section class="upcoming-events-section research-calendar-section" aria-labelledby="researchCalendarTitle">
            <div class="research-dashboard-section-heading">
                <div>
                    <h2 id="researchCalendarTitle">Academic Events Calendar</h2>
                    <p>Academic events, research milestones, and project deadlines</p>
                </div>
                <a class="research-view-all" href="?page=events-management" data-page="events-management">Events</a>
            </div>
            <?php if ($calendarLoadError): ?>
                <p class="research-inline-error"><?php echo htmlspecialchars($calendarLoadError); ?></p>
            <?php endif; ?>
            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevMonth" class="btn-nav" type="button" onclick="previousMonth()" aria-label="Previous month">&lt; Previous</button>
                    <h3 id="monthYear"></h3>
                    <button id="nextMonth" class="btn-nav" type="button" onclick="nextMonth()" aria-label="Next month">Next </button>
                </div>
                <div class="calendar-grid">
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>
                    <div id="calendarDays" class="calendar-days"></div>
                </div>
            </div>
        </section>
    </div>

    <section class="research-quick-access" aria-labelledby="researchQuickAccessTitle">
        <div class="research-dashboard-section-heading">
            <div>
                <h2 id="researchQuickAccessTitle">Quick access</h2>
                <p>Go straight to a research management module</p>
            </div>
        </div>
        <div class="research-quick-access-grid">
            <a class="research-quick-access-card" href="?page=research-project" data-page="research-project">
                <span class="research-quick-access-icon"><i class="fas fa-flask" aria-hidden="true"></i></span>
                <span><strong>Research Projects</strong><small>Register and monitor projects</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=research-proposal" data-page="research-proposal">
                <span class="research-quick-access-icon"><i class="fas fa-file-signature" aria-hidden="true"></i></span>
                <span><strong>Research Proposals</strong><small>Track proposal submissions</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=research-documentation" data-page="research-documentation">
                <span class="research-quick-access-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
                <span><strong>Documentation</strong><small>Manage research files</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=ethics-compliance" data-page="ethics-compliance">
                <span class="research-quick-access-icon"><i class="fas fa-shield-heart" aria-hidden="true"></i></span>
                <span><strong>Ethics &amp; Compliance</strong><small>Track clearance and evidence</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=events-management" data-page="events-management">
                <span class="research-quick-access-icon"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
                <span><strong>Events &amp; Calendar</strong><small>Plan research activities</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=report-submission" data-page="report-submission">
                <span class="research-quick-access-icon"><i class="fas fa-file-lines" aria-hidden="true"></i></span>
                <span><strong>Report Submission</strong><small>Review submitted reports</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=approval-submission" data-page="approval-submission">
                <span class="research-quick-access-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
                <span><strong>Requests &amp; Approvals</strong><small>Review pending requests</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=concern-submission" data-page="concern-submission">
                <span class="research-quick-access-icon"><i class="fas fa-comment-dots" aria-hidden="true"></i></span>
                <span><strong>Concern Submission</strong><small>Manage research concerns</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
            <a class="research-quick-access-card" href="?page=defense-scheduling" data-page="defense-scheduling">
                <span class="research-quick-access-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                <span><strong>Defense Scheduling</strong><small>Assign research defense schedules</small></span>
                <i class="fas fa-arrow-right research-quick-access-arrow" aria-hidden="true"></i>
            </a>
        </div>
    </section>

    <div id="eventsModal" class="events-modal" style="display:none;">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalDateTitle">
            <button class="modal-close" type="button" onclick="closeEventsModal()" aria-label="Close">&times;</button>
            <h2 id="modalDateTitle">Research Activities</h2>
            <div id="modalEventsList" class="modal-events-list"></div>
        </div>
    </div>
</div>

<script type="application/json" id="researchDashboardEventsData"><?php echo json_encode($eventsByDate, $jsonFlags); ?></script>
<script type="application/json" id="researchDashboardNotificationsData"><?php echo json_encode($notificationAlerts, $jsonFlags); ?></script>
<script type="application/json" id="researchDashboardServerData"><?php echo json_encode($researchDashboardData, $jsonFlags); ?></script>
<script type="application/json" id="researchDashboardTimestampData"><?php echo json_encode($serverTimestampIso, $jsonFlags); ?></script>
