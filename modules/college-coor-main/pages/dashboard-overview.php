<?php
// Dashboard Overview Page - Main analytics and summary for College Coordinator
require_once dirname(__DIR__) . '/classes/DashboardOverview.php';

$database = new Database();
$conn = $database->getConnection();
$dashboardOverview = new DashboardOverview($conn);

$roleName = $_SESSION['role_name'] ?? '';
$userCanReviewApprovals = stripos($roleName, 'directress') !== false || stripos($roleName, 'head') !== false;

function dashboardEscape($value) {
    return htmlspecialchars((string) ($value ?? 'N/A'), ENT_QUOTES, 'UTF-8');
}

function dashboardStatusClass($status) {
    $normalized = strtolower((string) $status);

    if (in_array($normalized, ['approved', 'resolved', 'on track', 'completed', 'active'], true)) {
        return 'status-success';
    }

    if (in_array($normalized, ['reviewed', 'needs attention', 'submitted', 'pending', 'upcoming', 'ongoing'], true)) {
        return 'status-warning';
    }

    if (in_array($normalized, ['overdue', 'rejected', 'cancelled', 'urgent'], true)) {
        return 'status-danger';
    }

    return 'status-neutral';
}

$summary = $dashboardOverview->getSummary();
$departmentOverview = $dashboardOverview->getDepartmentOverview();
$pendingSubmissions = $dashboardOverview->getPendingSubmissions();
$upcomingActivities = $dashboardOverview->getUpcomingActivities();
$alertItems = $dashboardOverview->getAlertItems();
$recentActivities = $dashboardOverview->getRecentActivities(8);
$activityTrend = $dashboardOverview->getActivityTrend(6);
$activityTrendYear = $dashboardOverview->getActivityTrend(12);
$eventStatusBreakdown = $dashboardOverview->getEventStatusBreakdown();
$recentAnnouncements = $dashboardOverview->getRecentAnnouncements(4);
$recentEvents = $dashboardOverview->getRecentEvents(4);
?>

<?php
$formattedActivityTrend = json_encode($activityTrend['labels'] ?? []);
$formattedEventTrend = json_encode($activityTrend['event_data'] ?? []);
$formattedAnnouncementTrend = json_encode($activityTrend['announcement_data'] ?? []);
$formattedActivityTrendYear = json_encode($activityTrendYear['labels'] ?? []);
$formattedEventTrendYear = json_encode($activityTrendYear['event_data'] ?? []);
$formattedAnnouncementTrendYear = json_encode($activityTrendYear['announcement_data'] ?? []);
$formattedEventStatusLabels = json_encode($eventStatusBreakdown['labels'] ?? []);
$formattedEventStatusData = json_encode($eventStatusBreakdown['data'] ?? []);
?>

<div class="monitoring-dashboard">
    <div class="module-header dashboard-header">
        <div>
            <h1 style="color: black;">Dashboard Overview</h1>
            <p class="text-muted small">Central monitoring for departments, submissions, activities, and operational alerts.</p>
        </div>
    </div>

    <div class="module-content dashboard-shell">
        <section class="dashboard-summary" aria-label="Summary statistics">
            <div class="summary-group">
                <div class="summary-group-label">Departments &amp; Personnel</div>
                <div class="summary-card-grid">
                    <article class="summary-card">
                        <div class="summary-meta">
                            <span class="summary-label">Total Coordinators</span>
                            <strong class="summary-value"><?= dashboardEscape($summary['total_departments'] ?? 0) ?></strong>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="summary-meta">
                            <span class="summary-label">Active Coordinators</span>
                            <strong class="summary-value"><?= dashboardEscape($summary['active_coordinators'] ?? 0) ?></strong>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="summary-meta">
                            <span class="summary-label">Open Concerns</span>
                            <strong class="summary-value"><?= dashboardEscape($summary['open_concerns'] ?? 0) ?></strong>
                        </div>
                    </article>
                </div>
            </div>

            <div class="summary-group">
                <div class="summary-group-label">Reports &amp; Operations</div>
                <div class="summary-card-grid">
                    <article class="summary-card">
                        <div class="summary-meta">
                            <span class="summary-label">Pending Reports</span>
                            <strong class="summary-value"><?= dashboardEscape($summary['pending_reports'] ?? 0) ?></strong>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="summary-meta">
                            <span class="summary-label">Pending Approvals</span>
                            <strong class="summary-value"><?= dashboardEscape($summary['pending_approvals'] ?? 0) ?></strong>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="summary-meta">
                            <span class="summary-label">Overdue Tasks</span>
                            <strong class="summary-value"><?= dashboardEscape($summary['overdue_tasks'] ?? 0) ?></strong>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="dashboard-attention" aria-label="Needs your attention">
            <div class="attention-header">
                <span class="attention-label">Needs Your Attention</span>
            </div>

            <div class="attention-grid">
                <article class="attention-card">
                    <div class="attention-card-header">
                        <div class="attention-card-title">
                            <span class="attention-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                                    <path d="M10 20a2 2 0 0 0 4 0"></path>
                                </svg>
                            </span>
                            <h3>Pending Submissions</h3>
                        </div>
                    </div>

                    <?php if (!empty($pendingSubmissions)): ?>
                        <?php foreach ($pendingSubmissions as $submission): ?>
                            <div class="attention-item">
                                <div class="attention-item-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 3h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path>
                                        <path d="M14 3v6h6"></path>
                                        <path d="M8 13h8"></path>
                                        <path d="M8 17h8"></path>
                                    </svg>
                                </div>

                                <div class="attention-item-body">
                                    <div class="item-title"><?= dashboardEscape($submission['submission_title'] ?? 'Untitled submission') ?></div>
                                    <div class="item-subtitle">
                                        <?= dashboardEscape($submission['department_name'] ?? 'Department') ?>
                                        •
                                        <?= dashboardEscape(!empty($submission['submitted_at']) ? date('M d, Y', strtotime($submission['submitted_at'])) : 'N/A') ?>
                                    </div>
                                </div>

                                <div class="attention-item-actions">
                                    <span class="status-badge <?= dashboardStatusClass($submission['status'] ?? 'submitted') ?>">
                                        <?= dashboardEscape(ucfirst($submission['status'] ?? 'submitted')) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-panel">No pending submissions found.</div>
                    <?php endif; ?>

                    <a href="?page=report-submission-management" class="primary-cta">Go to Reports</a>
                </article>

                <article class="attention-card">
                    <div class="attention-card-header">
                        <div class="attention-card-title">
                            <span class="attention-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                    <path d="M16 2v4"></path>
                                    <path d="M8 2v4"></path>
                                    <path d="M3 10h18"></path>
                                </svg>
                            </span>
                            <h3>Event Status</h3>
                        </div>
                    </div>

                    <?php if (!empty($eventStatusBreakdown['labels']) && !empty(array_filter($eventStatusBreakdown['data'], fn($count) => (int) $count > 0))): ?>
                        <div class="event-status-shell">
                            <div class="donut-chart-wrap">
                                <canvas id="eventStatusChart" aria-label="Event status doughnut chart"></canvas>
                            </div>
                            <div class="status-summary-panel">
                                <div class="status-total"><?= dashboardEscape((int) ($eventStatusBreakdown['total'] ?? 0)) ?></div>
                                <div class="status-total-label">Total Events</div>
                                <?php foreach (($eventStatusBreakdown['items'] ?? []) as $item): ?>
                                    <div class="status-row">
                                        <span class="status-name-wrap">
                                            <span class="status-dot" style="background: <?= dashboardEscape($item['label'] === 'Upcoming' ? '#3b82f6' : ($item['label'] === 'Ongoing' ? '#f59e0b' : ($item['label'] === 'Completed' ? '#10b981' : ($item['label'] === 'Cancelled' ? '#ef4444' : '#94a3b8')))) ?>;"></span>
                                            <?= dashboardEscape($item['label']) ?>
                                        </span>
                                        <span class="status-figure"><?= dashboardEscape((int) ($item['count'] ?? 0)) ?> (<?= dashboardEscape((float) ($item['percent'] ?? 0)) ?>%)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-panel">No event status data available.</div>
                    <?php endif; ?>
                </article>

                <article class="attention-card">
                    <div class="attention-card-header">
                        <div class="attention-card-title">
                            <span class="attention-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3v18"></path>
                                    <path d="M4 7h16"></path>
                                    <path d="M4 17h16"></path>
                                </svg>
                            </span>
                            <h3>Coordinator Status</h3>
                        </div>
                    </div>

                    <?php if (!empty($departmentOverview)): ?>
                        <div class="coord-table-wrap">
                            <table class="coord-status-table">
                                <thead>
                                    <tr>
                                        <th>Coordinator</th>
                                        <th>Reports</th>
                                        <th>Concern</th>
                                        <th>Approvals</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departmentOverview as $department): ?>
                                        <tr>
                                            <td class="department-name">
                                                <?= dashboardEscape($department['coordinator_role_name'] ?? 'Coordinator') ?>
                                            </td>
                                            <td>
                                                <span class="task-pill <?= ((int) ($department['report_count'] ?? 0) > 0) ? 'has-tasks' : 'no-tasks' ?>">
                                                    <?= dashboardEscape((int) ($department['report_count'] ?? 0)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="task-pill <?= ((int) ($department['concern_count'] ?? 0) > 0) ? 'has-tasks' : 'no-tasks' ?>">
                                                    <?= dashboardEscape((int) ($department['concern_count'] ?? 0)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="task-pill <?= ((int) ($department['approval_count'] ?? 0) > 0) ? 'has-tasks' : 'no-tasks' ?>">
                                                    <?= dashboardEscape((int) ($department['approval_count'] ?? 0)) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php
                        $needsAttentionDepartments = 0;
                        foreach ($departmentOverview as $department) {
                            $status = strtolower((string) ($department['status'] ?? ''));
                            if (in_array($status, ['needs attention', 'overdue'], true)) {
                                $needsAttentionDepartments++;
                            }
                        }
                        if ($needsAttentionDepartments > 0):
                        ?>
                            <div class="inline-alert">
                                <span class="alert-marker">!</span>
                                <span><?= dashboardEscape($needsAttentionDepartments) ?> departments need attention.</span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state-panel">No department records are available yet.</div>
                    <?php endif; ?>
                </article>
            </div>
        </section>

        <section class="dashboard-activity-overview" aria-label="Activities and announcements overview">
            <div class="attention-header">
                <span class="attention-label">Activities &amp; Announcements</span>
            </div>

            <div class="dashboard-activity-layout">
                <article class="dashboard-activity-card">
                    <div class="analytics-header">
                        <div>
                            <h3>Activity Overview</h3>
                        </div>
                        <div class="analytics-range-toggle" aria-label="Activity overview date range">
                            <button type="button" class="range-button active" data-range="6m">6 Months</button>
                            <button type="button" class="range-button" data-range="1y">This Year</button>
                        </div>
                    </div>

                    <div class="activity-chart-shell">
                        <?php if (!empty($activityTrend['labels'])): ?>
                            <canvas id="activityOverviewChart" aria-label="Monthly activity overview chart"></canvas>
                        <?php else: ?>
                            <div class="empty-chart-state">No activity data available for the selected period.</div>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="dashboard-activity-card">
                    <div class="analytics-header">
                        <div>
                            <h3>Announcements</h3>
                        </div>
                        <a href="?page=communications-announcements" class="tiny-link">View All</a>
                    </div>

                    <?php if (!empty($recentAnnouncements)): ?>
                        <div class="announcement-list">
                            <?php foreach ($recentAnnouncements as $announcement): ?>
                                <div class="announcement-item">
                                    <div class="announcement-badge <?= dashboardStatusClass($announcement['status'] ?? 'Published') ?>"><?= dashboardEscape(ucfirst((string) ($announcement['status'] ?? 'Published'))) ?></div>
                                    <div class="announcement-copy">
                                        <div class="announcement-title"><?= dashboardEscape($announcement['title'] ?? 'Announcement') ?></div>
                                        <div class="announcement-meta"><?= dashboardEscape($announcement['announcement_type'] ?? 'General') ?></div>
                                        <div class="announcement-meta small-meta">
                                            <?= dashboardEscape($announcement['publisher_name'] ?? 'System') ?>
                                            •
                                            <?= dashboardEscape(!empty($announcement['publish_date']) ? date('M d, Y', strtotime($announcement['publish_date'])) : 'N/A') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-panel">No announcements have been published yet.</div>
                    <?php endif; ?>
                </article>

                <article class="dashboard-activity-card">
                    <div class="analytics-header">
                        <div>
                            <h3>Recent Activity</h3>
                        </div>
                    </div>

                    <?php if (!empty($recentActivities)): ?>
                        <div class="upcoming-events-list">
                            <?php foreach (array_slice($recentActivities, 0, 4) as $activity): ?>
                                <div class="upcoming-event-item">
                                    <div class="event-date-box">
                                        <span><?= dashboardEscape(!empty($activity['timestamp']) ? date('M', strtotime($activity['timestamp'])) : 'N/A') ?></span>
                                        <strong><?= dashboardEscape(!empty($activity['timestamp']) ? date('d', strtotime($activity['timestamp'])) : 'N/A') ?></strong>
                                    </div>
                                    <div class="event-copy">
                                        <div class="event-title"><?= dashboardEscape($activity['type'] ?? 'Activity') ?></div>
                                        <div class="event-meta"><?= dashboardEscape($activity['description'] ?? 'Recent activity recorded.') ?></div>
                                        <div class="event-meta small-meta"><?= dashboardEscape($activity['module'] ?? 'College Coordinator') ?> • <?= dashboardEscape(!empty($activity['timestamp']) ? date('M d, Y', strtotime($activity['timestamp'])) : 'N/A') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-panel">No recent activity recorded in this module yet.</div>
                    <?php endif; ?>
                </article>
            </div>
        </section>
    </div>
</div>

<script>
    (function () {
        function loadChartJs() {
            return new Promise(function (resolve, reject) {
                if (window.Chart) {
                    resolve(window.Chart);
                    return;
                }

                var script = document.createElement('script');
                script.src = 'js/assets/vendor/chart.umd.js';
                script.onload = function () {
                    if (window.Chart) resolve(window.Chart);
                    else reject(new Error('Chart.js failed to initialize.'));
                };
                script.onerror = function () {
                    reject(new Error('Chart.js could not be loaded.'));
                };
                document.head.appendChild(script);
            });
        }

        var activityConfig = {
            labels: <?= $formattedActivityTrend ?>,
            data: <?= $formattedEventTrend ?>,
            announcementData: <?= $formattedAnnouncementTrend ?>,
            labelsYear: <?= $formattedActivityTrendYear ?>,
            dataYear: <?= $formattedEventTrendYear ?>,
            announcementDataYear: <?= $formattedAnnouncementTrendYear ?>
        };

        var chartState = {
            activeRange: '6m'
        };

        function renderActivityChart() {
            var canvas = document.getElementById('activityOverviewChart');
            if (!canvas) return;

            var labels = chartState.activeRange === '1y' ? activityConfig.labelsYear : activityConfig.labels;
            var eventData = chartState.activeRange === '1y' ? activityConfig.dataYear : activityConfig.data;
            var announcementData = chartState.activeRange === '1y' ? activityConfig.announcementDataYear : activityConfig.announcementData;
            var validValues = Array.isArray(eventData) && eventData.some(function (value) { return Number(value) > 0; });

            if (!labels || !labels.length || !validValues) {
                canvas.parentElement.innerHTML = '<div class="empty-chart-state">No activity data available for the selected period.</div>';
                return;
            }

            if (canvas.chartInstance) {
                canvas.chartInstance.destroy();
            }

            canvas.chartInstance = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Activities',
                        data: eventData,
                        borderColor: '#5146b7',
                        backgroundColor: 'rgba(81, 70, 183, 0.14)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#5146b7',
                        pointRadius: 4,
                        pointHoverRadius: 5
                    }, {
                        label: 'Announcements',
                        data: announcementData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.35,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 3,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        function renderEventStatusChart() {
            var canvas = document.getElementById('eventStatusChart');
            if (!canvas) return;

            var labels = <?= $formattedEventStatusLabels ?>;
            var data = <?= $formattedEventStatusData ?>;
            var validValues = Array.isArray(data) && data.some(function (value) { return Number(value) > 0; });

            if (!labels || !labels.length || !validValues) {
                canvas.parentElement.innerHTML = '<div class="empty-chart-state">No event status data available.</div>';
                return;
            }

            if (canvas.chartInstance) {
                canvas.chartInstance.destroy();
            }

            canvas.chartInstance = new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#94a3b8'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                padding: 10
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadChartJs().then(function () {
                renderActivityChart();
                renderEventStatusChart();
            }).catch(function () {
                var activityCanvas = document.getElementById('activityOverviewChart');
                if (activityCanvas) activityCanvas.parentElement.innerHTML = '<div class="empty-chart-state">Chart library could not be loaded.</div>';
                var statusCanvas = document.getElementById('eventStatusChart');
                if (statusCanvas) statusCanvas.parentElement.innerHTML = '<div class="empty-chart-state">Chart library could not be loaded.</div>';
            });

            var rangeButtons = document.querySelectorAll('.range-button');
            rangeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    chartState.activeRange = button.dataset.range || '6m';
                    rangeButtons.forEach(function (btn) { btn.classList.toggle('active', btn === button); });
                    if (window.Chart) {
                        renderActivityChart();
                    }
                });
            });
        });
    })();
</script>

