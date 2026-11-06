<?php
require_once dirname(dirname(dirname(__DIR__))) . '/database/db.php';

$database = new Database();
$conn = $database->getConnection();
$tz = new DateTimeZone('Asia/Manila');
$now = new DateTime('now', $tz);
$notificationAlerts = [];

$currentEmployeeId = (int)($_SESSION['employee_id'] ?? 0);
$currentUserId = 0;
if ($currentEmployeeId > 0) {
    $userLookup = $conn->prepare('SELECT user_id FROM cc_user_account WHERE employee_id = :employee_id LIMIT 1');
    $userLookup->execute([':employee_id' => $currentEmployeeId]);
    $currentUserId = (int)$userLookup->fetchColumn();
}

if ($currentUserId > 0) {
    $announcementStmt = $conn->prepare(
        'SELECT n.notification_id, n.title, n.message, n.notification_type, n.module, n.record_id, n.created_at,
                a.image_file,
                TRIM(CONCAT_WS(" ", e.first_name, e.last_name)) AS author_name,
                COALESCE(r.role_name, "Admin College Coordinator") AS author_role
         FROM cc_notifications n
         LEFT JOIN cc_announcements a ON a.announcement_id = n.record_id
         LEFT JOIN em_employees e ON e.employee_id = a.created_by
         LEFT JOIN cc_roles r ON r.role_id = e.role_id
         WHERE n.user_id = :user_id AND n.module = :module
         ORDER BY n.created_at DESC
         LIMIT 20'
    );
    $announcementStmt->execute([
        ':user_id' => $currentUserId,
        ':module' => 'communications',
    ]);

    foreach ($announcementStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $createdAt = new DateTime($row['created_at'], $tz);
        $author = trim((string)($row['author_role'] ?? ''));
        if ($author === '') {
            $author = trim((string)($row['author_name'] ?? '')) ?: 'Admin College Coordinator';
        }
        $imageFile = trim((string)($row['image_file'] ?? ''));
        $notificationAlerts[] = [
            'id' => 'announcement_' . (int)$row['notification_id'],
            'title' => trim((string)$row['title']) !== '' ? $row['title'] : 'Announcement',
            'message' => trim((string)($row['message'] ?? '')) ?: 'No announcement details provided.',
            'author' => $author,
            'image_url' => $imageFile !== ''
                ? '/schoolm/modules/college-coor-main/uploads/announcements/' . ltrim($imageFile, '/')
                : '',
            'date' => $createdAt->format('Y-m-d'),
            'day' => $createdAt->format('j'),
            'priority' => strtolower((string)($row['notification_type'] ?? '')) === 'urgent' ? 'urgent' : 'normal',
            'icon' => '📣',
            'badgeClass' => 'bg-info',
            'remaining' => 'Announcement',
            'startTime' => $createdAt->format('g:i A'),
            'notification' => $createdAt->format('g:i A'),
            'notification_created_iso' => $createdAt->format(DateTime::ATOM),
            'start_iso' => null,
            'end_iso' => null,
        ];
    }
}

$serverTimestampIso = $now->format(DateTime::ATOM);
?>

<div class="module-header gened-dashboard-header">
    <div>
        <h1>GenEd Coordinator Dashboard</h1>
        <p class="text-muted small">Overview of GenEd subjects, faculty, approvals, concerns, and events.</p>
    </div>
</div>

<div class="module-content gened-dashboard-content" id="genedCoordinatorDashboard">
    <p class="gened-dashboard-period" id="genedDashboardPeriod">Loading active semester...</p>

    <section class="gened-dashboard-metrics" aria-label="GenEd dashboard summary">
        <article class="gened-dashboard-metric">
            <p>Total GenEd Subjects</p>
            <strong id="genedDashboardSubjects">--</strong>
            <small>Active subjects</small>
        </article>
        <article class="gened-dashboard-metric">
            <p>Total GenEd Faculty</p>
            <strong id="genedDashboardFaculty">--</strong>
            <small>Assigned faculty</small>
        </article>
        <article class="gened-dashboard-metric">
            <p>Pending Approvals</p>
            <strong id="genedDashboardApprovals">--</strong>
            <small>Activities and events</small>
        </article>
        <article class="gened-dashboard-metric">
            <p>Open Concerns</p>
            <strong id="genedDashboardConcerns">--</strong>
            <small id="genedDashboardConcernsNote">Need a reply</small>
        </article>
        <article class="gened-dashboard-metric">
            <p>Pending Book Reports</p>
            <strong id="genedDashboardBookReports">--</strong>
            <small>Not yet reviewed</small>
        </article>
    </section>

    <section class="gened-dashboard-item-analysis" aria-labelledby="genedItemAnalysisTitle">
        <div class="gened-dashboard-events-heading">
            <div>
                <h2 id="genedItemAnalysisTitle">Item Analysis Summary</h2>
                <p>Common Exam activities marked as Item Analysis for the active semester</p>
            </div>
        </div>
        <div class="gened-item-analysis-layout">
            <div class="gened-item-analysis-chart-wrap">
                <div id="genedDashboardItemAnalysisChart" class="gened-item-analysis-chart gened-item-analysis-chart-empty" role="img" aria-label="No Item Analysis activities recorded this semester"></div>
                <div class="gened-item-analysis-total">
                    <strong id="genedDashboardItemAnalysisTotal">--</strong>
                    <span>Activities</span>
                </div>
            </div>
            <ul class="gened-item-analysis-legend" aria-label="Item Analysis activity status">
                <li>
                    <span class="gened-item-analysis-swatch gened-item-analysis-swatch-pending"></span>
                    <span>Pending Approval</span>
                    <strong id="genedDashboardItemAnalysisPending">--</strong>
                    <small id="genedDashboardItemAnalysisPendingPercent">0%</small>
                </li>
                <li>
                    <span class="gened-item-analysis-swatch gened-item-analysis-swatch-approved"></span>
                    <span>Approved</span>
                    <strong id="genedDashboardItemAnalysisApproved">--</strong>
                    <small id="genedDashboardItemAnalysisApprovedPercent">0%</small>
                </li>
                <li>
                    <span class="gened-item-analysis-swatch gened-item-analysis-swatch-completed"></span>
                    <span>Completed</span>
                    <strong id="genedDashboardItemAnalysisCompleted">--</strong>
                    <small id="genedDashboardItemAnalysisCompletedPercent">0%</small>
                </li>
                <li>
                    <span class="gened-item-analysis-swatch gened-item-analysis-swatch-rejected"></span>
                    <span>Rejected</span>
                    <strong id="genedDashboardItemAnalysisRejected">--</strong>
                    <small id="genedDashboardItemAnalysisRejectedPercent">0%</small>
                </li>
            </ul>
        </div>
        <p class="gened-item-analysis-note">This chart shows activity status only. Student scores, passing/failure rates, average scores, and analyzed question totals are not recorded in this system.</p>
    </section>

    <div class="gened-dashboard-events-layout">
        <section class="gened-dashboard-calendar-card" aria-labelledby="genedCalendarTitle">
            <div class="gened-dashboard-events-heading">
                <div>
                    <h2 id="genedCalendarTitle">GenEd Events Calendar</h2>
                    <p>Select a date to view its events</p>
                </div>
                <a href="#" data-page="events-management">View events</a>
            </div>
            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevMonth" class="btn-nav" type="button" onclick="previousMonth()" aria-label="Previous month">&lsaquo; Previous</button>
                    <h3 id="monthYear"></h3>
                    <button id="nextMonth" class="btn-nav" type="button" onclick="nextMonth()" aria-label="Next month">Next &rsaquo;</button>
                </div>
                <div class="calendar-grid" role="grid" aria-label="Monthly events calendar">
                    <div class="calendar-day-header" role="columnheader">Sun</div>
                    <div class="calendar-day-header" role="columnheader">Mon</div>
                    <div class="calendar-day-header" role="columnheader">Tue</div>
                    <div class="calendar-day-header" role="columnheader">Wed</div>
                    <div class="calendar-day-header" role="columnheader">Thu</div>
                    <div class="calendar-day-header" role="columnheader">Fri</div>
                    <div class="calendar-day-header" role="columnheader">Sat</div>
                    <div id="calendarDays" class="calendar-days"></div>
                </div>
            </div>
        </section>

        <section class="gened-dashboard-events" aria-labelledby="genedUpcomingEventsTitle">
            <div class="gened-dashboard-events-heading">
                <div>
                    <h2 id="genedUpcomingEventsTitle">Upcoming Events</h2>
                    <p>School-wide and department-level GenEd events</p>
                </div>
            </div>
            <div id="genedDashboardUpcomingEvents" class="gened-dashboard-event-list" aria-live="polite">
                <p class="gened-dashboard-empty">Loading upcoming events...</p>
            </div>
        </section>
    </div>

    <div id="eventsModal" class="events-modal" style="display:none" role="dialog" aria-modal="true" aria-labelledby="modalDateTitle">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeEventsModal()" aria-label="Close">&times;</button>
            <h2 id="modalDateTitle">Events</h2>
            <div id="modalEventsList" class="modal-events-list"></div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>
    <div id="activitiesModal" class="activities-modal" style="display:none">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeActivitiesModal()" aria-label="Close">&times;</button>
            <h2>All Recent Activities</h2>
            <div id="allActivitiesList"></div>
        </div>
    </div>
</div>

<script>
window.eventsByDate = {};
window.notificationEvents = <?php echo json_encode($notificationAlerts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
window.chartData = {};
window.serverTimestamp = <?php echo json_encode($serverTimestampIso); ?>;
window.allActivities = [];
</script>
