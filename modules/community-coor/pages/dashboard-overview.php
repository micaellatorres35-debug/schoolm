<?php
// Dashboard Overview Page - Main analytics and summary for College Coordinator
require_once dirname(__DIR__) . '/classes/DatabaseHelper.php';
require_once dirname(__DIR__) . '/classes/EventManager.php';

$helper = new DatabaseHelper();

// Fetch upcoming appointments and events
$upcomingEvents = $helper->getUpcomingEvents(5);

// Fetch ALL calendar events from Events Management (cc_events table)
require_once dirname(dirname(dirname(__DIR__))) . '/database/db.php';
$database = new Database();
$conn = $database->getConnection();
$eventManager = new EventManager($conn);

try {
    $calendarEventsData = $eventManager->getAllEvents();
} catch (Exception $e) {
    $calendarEventsData = [];
}

// Format calendar events by date for JavaScript with full details
$eventsByDate = [];
foreach ($calendarEventsData as $event) {
    $eventDate = $event['event_date'];
    if (!isset($eventsByDate[$eventDate])) {
        $eventsByDate[$eventDate] = [];
    }
    
    // Create event object with all details
    $eventEntry = [
        'title' => $event['event_title'],
        'type' => $event['event_type'] ?? 'Academic',
        'time' => $event['start_time'],
        'endTime' => $event['end_time'],
        'location' => $event['location'] ?? '',
        'description' => $event['description'] ?? '',
        'status' => $event['status'] ?? 'upcoming',
        'audience' => $event['target_audience'] ?? ''
    ];
    
    $eventsByDate[$eventDate][] = $eventEntry;
}

// Fetch recent activities from all modules
$recentActivities = $helper->getRecentActivities(15);

// ===== Priority Alerts Logic (automatic priority based on time remaining) =====
// Use Asia/Manila timezone for all event time computations
$tz = new DateTimeZone('Asia/Manila');
$now = new DateTime('now', $tz);
$priorityAlerts = [];
$notificationAlerts = [];

$addCompletedNotification = function (array $event, DateTime $eventDateTime, ?DateTime $endDateTime) use (&$notificationAlerts, $tz) {
    $eventDateStr = $eventDateTime->format('Y-m-d');
    $eventTimeStr = $eventDateTime->format('H:i:s');
    $eventId = isset($event['event_id'])
        ? $event['event_id']
        : md5(($event['event_title'] ?? $event['title'] ?? '') . $eventDateStr . $eventTimeStr);

    $createdAt = null;
    if (!empty($event['created_at'])) {
        try {
            $createdAt = new DateTime($event['created_at'], $tz);
        } catch (Exception $e) {
            $createdAt = null;
        }
    }
    if (!$createdAt) {
        $createdAt = $eventDateTime;
    }

    $notificationAlerts[] = [
        'id' => $eventId,
        'title' => $event['event_title'] ?? $event['title'] ?? 'Untitled Event',
        'date' => $eventDateStr,
        'day' => $eventDateTime->format('j'),
        'priority' => 'normal',
        'icon' => '...',
        'badgeClass' => 'bg-secondary',
        'remaining' => 'Completed',
        'startTime' => $eventDateTime->format('g:i A'),
        'notification' => $createdAt->format('g:i A'),
        'notification_created_iso' => $createdAt->format(DateTime::ATOM),
        'start_iso' => $eventDateTime->format(DateTime::ATOM),
        'end_iso' => $endDateTime ? $endDateTime->format(DateTime::ATOM) : null,
    ];
};

foreach ($calendarEventsData as $event) {
    $eventDateStr = $event['event_date'] ?? $event['date'] ?? null;
    $eventTimeStr = $event['start_time'] ?? $event['time'] ?? '00:00:00';

    if (!$eventDateStr) {
        continue;
    }

    // Build start DateTime in Asia/Manila
    try {
        $eventDateTime = new DateTime($eventDateStr . ' ' . $eventTimeStr, $tz);
    } catch (Exception $e) {
        continue;
    }

    // Build end DateTime if provided and adjust to next day when necessary
    $endTimeStr = $event['end_time'] ?? null;
    $endDateTime = null;
    if ($endTimeStr) {
        try {
            $endDateTime = new DateTime($eventDateStr . ' ' . $endTimeStr, $tz);
            // If end is earlier or equal to start, assume it continues to next day
            if ($endDateTime <= $eventDateTime) {
                $endDateTime->modify('+1 day');
            }
        } catch (Exception $e) {
            $endDateTime = null;
        }
    }

    // Determine if event is already completed (end exists and in past) or, if no end, started in the past
    if ($endDateTime) {
        if ($endDateTime < $now) {
            // Keep completed events in the bell as history, but exclude them
            // from the active Priority Alerts panel.
            $addCompletedNotification($event, $eventDateTime, $endDateTime);
            continue;
        }
    } else {
        // No end time â€” if start already passed, keep it as a completed
        // historical notification but do not show it as a priority alert.
        if ($eventDateTime < $now) {
            $addCompletedNotification($event, $eventDateTime, null);
            continue;
        }
    }

    // Calculate minutes until start
    $interval = $now->diff($eventDateTime);
    $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    // Automatic priority classification (based on minutes until start)
    if ($totalMinutes <= 48 * 60) {
        $priority   = 'urgent';
        $icon       = '...';
        $badgeClass = 'bg-danger';
    } elseif ($totalMinutes <= 72 * 60) {
        $priority   = 'high';
        $icon       = '...';
        $badgeClass = 'bg-warning text-dark';
    } else {
        $priority   = 'normal';
        $icon       = '...';
        $badgeClass = 'bg-success';
    }

    // Human-readable initial remaining value (will be kept updated client-side)
    if ($now < $eventDateTime) {
        if ($totalMinutes < 60) {
            $minutes = max(1, $totalMinutes);
            $remaining = 'Starts in ' . $minutes . ' Minute' . ($minutes == 1 ? '' : 's');
        } elseif ($interval->days === 0) {
            $hours = ceil($totalMinutes / 60);
            $remaining = 'Starts in ' . $hours . ' Hour' . ($hours == 1 ? '' : 's');
        } elseif ($interval->days === 1) {
            $remaining = 'Starts Tomorrow at ' . $eventDateTime->format('g:i A');
        } else {
            $days = $interval->days;
            $remaining = 'Starts in ' . $days . ' Day' . ($days == 1 ? '' : 's');
        }
    } elseif ($endDateTime && $now >= $eventDateTime && $now < $endDateTime) {
        $remaining = 'ðŸŸ¢ Ongoing';
    } else {
        // Fallback â€” mark completed and skip
        continue;
    }

    $eventId = isset($event['event_id']) ? $event['event_id'] : md5($event['event_title'] . $eventDateStr . $eventTimeStr);
    $dateDay = $eventDateTime->format('j');
    $dateKey = $eventDateTime->format('Y-m-d');

    $notificationCreatedAt = null;
    if (!empty($event['created_at'])) {
        try {
            $notificationCreatedAt = new DateTime($event['created_at'], $tz);
        } catch (Exception $e) {
            $notificationCreatedAt = null;
        }
    }

    if (!$notificationCreatedAt) {
        $notificationCreatedAt = new DateTime('now', $tz);
    }

    $notificationAlerts[] = [
        'id'                  => $eventId,
        'title'               => $event['event_title'] ?? $event['title'] ?? 'Untitled Event',
        'date'                => $dateKey,
        'day'                 => $dateDay,
        'priority'            => $priority,
        'icon'                => $icon,
        'badgeClass'          => $badgeClass,
        'remaining'           => $remaining,
        'startTime'           => $eventDateTime->format('g:i A'),
        'notification'        => $notificationCreatedAt->format('g:i A'),
        'notification_created_iso' => $notificationCreatedAt->format(DateTime::ATOM),
        'start_iso'           => $eventDateTime->format(DateTime::ATOM),
        'end_iso'             => $endDateTime ? $endDateTime->format(DateTime::ATOM) : null,
    ];

    $priorityAlerts[] = [
        'title'           => $event['event_title'] ?? $event['title'] ?? 'Untitled Event',
        'datetime'        => $eventDateTime,
        'priority'        => $priority,
        'icon'            => $icon,
        'badgeClass'      => $badgeClass,
        'remaining'       => $remaining,
        'start_iso'       => $eventDateTime->format(DateTime::ATOM),
        'end_iso'         => $endDateTime ? $endDateTime->format(DateTime::ATOM) : null,
        'start_time_disp' => $eventDateTime->format('g:i A'),
    ];
}

// Sort by nearest event first
usort($priorityAlerts, function ($a, $b) {
    return $a['datetime'] <=> $b['datetime'];
});

// Show only the latest 4 priority alerts
$priorityAlerts = array_slice($priorityAlerts, 0, 4);

// Server timestamp (Asia/Manila) for client baseline
$serverTimestampIso = $now->format(DateTime::ATOM);

// ===== Chart Data =====

// 1. Students per Program
$studentsPerProgram = $helper->getStudentsPerProgram();
$programLabels = array_map(function($item) { return $item['program_code']; }, $studentsPerProgram);
$programData = array_map(function($item) { return (int)$item['student_count']; }, $studentsPerProgram);

// 2. Faculty Load Distribution
$facultyLoad = $helper->getFacultyLoadDistribution();
$facultyLabels = array_map(function($item) { return substr($item['faculty_name'], 0, 15); }, $facultyLoad);
$facultyUnits = array_map(function($item) { return (int)$item['total_units']; }, $facultyLoad);

// 3. Student Academic Status
$studentStatusData = $helper->getStudentAcademicStatus();
$statusLabels = array_map(function($item) { return $item['status']; }, $studentStatusData);
$statusCounts = array_map(function($item) { return (int)$item['count']; }, $studentStatusData);

// Convert to JSON for JavaScript
$chartDataJson = json_encode([
    'programLabels' => $programLabels,
    'programData' => $programData,
    'facultyLabels' => $facultyLabels,
    'facultyUnits' => $facultyUnits,
    'statusLabels' => $statusLabels,
    'statusCounts' => $statusCounts
]);
?>

<div class="module-header">
    <h1>Dashboard Overview</h1>
    <p class="text-muted small">Summary of academic analytics, events, and recent activities</p>
</div>

<div class="module-content">
    <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>
    <!-- Analytics Charts Section -->
    <section class="analytics-section">
        
        <div class="charts-grid">
            <!-- Students per Program Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Students per Program</h3>
                <div class="chart-wrapper">
                    <canvas id="studentsPerProgramChart"></canvas>
                </div>
            </div>

            <!-- Faculty Load Distribution Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Faculty Load Distribution</h3>
                <div id="faultyLoadSummary" class="chart-subtitle" style="font-size: 13px; color: #6b7280; margin: 4px 0 10px;">Loading faculty load countsâ€¦</div>
                <div class="chart-wrapper">
                    <canvas id="facultyLoadChart"></canvas>
                </div>
            </div>

            <!-- Student Academic Status Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Student Academic Status</h3>
                <div class="chart-wrapper">
                    <canvas id="studentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Upcoming Events / Academic Calendar -->
        <section class="upcoming-events-section">
            <div class="section-header">
                <h2 class="section-title">Academic Events Calendar</h2>
            </div>
            
            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevMonth" class="btn-nav" onclick="previousMonth()">< Previous</button>
                    <h3 id="monthYear">March 2026</h3>
                    <button id="nextMonth" class="btn-nav" onclick="nextMonth()">Next ></button>
                </div>
                
                <div class="calendar-grid">
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>
                    
                    <div id="calendarDays" class="calendar-days">
                        <!-- Days will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Priority Alerts -->
        <section class="priority-alerts-section">
            <div class="priority-alerts-card">
                <h2 class="section-title" style="padding: 20px;">Priority Alerts</h2>
                <ul class="list-group list-group-flush mb-0">
                    <?php if (empty($priorityAlerts)): ?>
                        <li class="list-group-item text-center text-muted py-4 border-0">
                            No upcoming priority alerts
                        </li>
                    <?php else: ?>
                        <?php foreach ($priorityAlerts as $idx => $alert): ?>
    <li class="list-group-item alert-item <?php echo $idx === count($priorityAlerts) - 1 ? 'no-border' : ''; ?>" data-start="<?php echo htmlspecialchars($alert['start_iso']); ?>" data-end="<?php echo htmlspecialchars($alert['end_iso'] ?? ''); ?>">
        <div class="d-flex justify-content-between align-items-center" style="width:100%;">
            <div class="d-flex flex-column flex-grow-1 align-items-start" style="gap: 6px;">
                <div class="d-flex align-items-center gap-3" style="width:100%;">
                    <div class="alert-text" style="flex:1;">
                        <div class="alert-title"><?php echo htmlspecialchars($alert['title']); ?></div>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-start" style="gap: 4px; width:100%;">
                    <div class="alert-time"><?php echo htmlspecialchars($alert['remaining']); ?></div>
                    <span class="badge <?php echo $alert['badgeClass']; ?> alert-badge">
                        <?php echo ucfirst($alert['priority']); ?>
                    </span>
                </div>
            </div>
        </div>
    </li>
<?php endforeach; ?>

                    <?php endif; ?>
                </ul>
            </div>
        </section>

        <!-- Events Modal -->
        <div id="eventsModal" class="events-modal" style="display: none;">
            <div class="modal-content">
                <button class="modal-close" onclick="closeEventsModal()">&times;</button>
                <h2 id="modalDateTitle">Events</h2>
                <div id="modalEventsList" class="modal-events-list">
                    <!-- Events will be populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <section class="recent-activities-section" style="grid-column: 1 / -1;">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="section-title">Recent Activities</h2>
                <button onclick="openActivitiesModal()" style="padding: 6px 12px; background-color: rgb(14, 28, 90); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">View All</button>
            </div>
            
            <div class="activities-list" id="activitiesList">
                <?php if (empty($recentActivities)): ?>
                    <div class="activity-item" style="padding: 15px; text-align: center; color: #999;">
                        No recent activities yet
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($recentActivities, 0, 6) as $activity): ?>
                        <div class="activity-item" style="padding: 12px; border-radius: 5px; background-color: #f0f9ff; border-left: 4px solid #3b82f6; margin-bottom: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #1f2937; font-size: 14px;">
                                        <?php echo htmlspecialchars($activity['type']); ?>
                                    </div>
                                    <div style="color: #4b5563; font-size: 13px; margin: 4px 0;">
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </div>
                                    <div style="display: flex; justify-content: flex-start; align-items: center; gap: 10px;">
                                        <span style="font-size: 11px; color: #6b7280;"><?php echo htmlspecialchars($activity['module']); ?></span>
                                        <span class="activity-time" data-timestamp="<?php echo htmlspecialchars($activity['timestamp']); ?>" style="font-size: 11px; color: #9ca3af;">
                                            Loading time…
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Activities Modal -->
        <div id="activitiesModal" class="activities-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99999; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 8px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; position: relative;">
                <button onclick="closeActivitiesModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
                
                <h2 style="margin-top: 0; margin-bottom: 20px; color: #1f2937; font-size: 22px;">All Recent Activities</h2>
                
                <div id="allActivitiesList" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Activities will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
    

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
window.eventsByDate = <?php echo json_encode($eventsByDate); ?>;
window.notificationEvents = <?php echo json_encode($notificationAlerts); ?>;
window.chartData = <?php echo $chartDataJson; ?>;
window.serverTimestamp = <?php echo json_encode($serverTimestampIso); ?>;
window.allActivities = <?php echo json_encode($recentActivities); ?>;
</script>
<script src="js/modules/dashboard-overview.js"></script>

