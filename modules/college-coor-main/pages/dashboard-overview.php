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
?>

<div class="monitoring-dashboard">
    <div class="module-header dashboard-header">
        <div>
            <h1>Dashboard Overview</h1>
            <p class="text-muted small">Central monitoring for departments, submissions, activities, and operational alerts.</p>
        </div>
    </div>

    <div class="module-content dashboard-shell">
        <section class="summary-cards-section" aria-label="Summary statistics">
            <div class="summary-cards">
                <article class="summary-card summary-card-primary">
                    <div class="summary-meta">
                        <span class="summary-label">Total Departments</span>
                        <strong class="summary-value"><?= dashboardEscape($summary['total_departments']) ?></strong>
                    </div>
                </article>

                <article class="summary-card summary-card-indigo">
                    <div class="summary-meta">
                        <span class="summary-label">Active Coordinators</span>
                        <strong class="summary-value"><?= dashboardEscape($summary['active_coordinators']) ?></strong>
                    </div>
                </article>

                <article class="summary-card summary-card-amber">
                    <div class="summary-meta">
                        <span class="summary-label">Pending Reports</span>
                        <strong class="summary-value"><?= dashboardEscape($summary['pending_reports']) ?></strong>
                    </div>
                </article>

                <article class="summary-card summary-card-success">
                    <div class="summary-meta">
                        <span class="summary-label">Pending Approvals</span>
                        <strong class="summary-value"><?= dashboardEscape($summary['pending_approvals']) ?></strong>
                    </div>
                </article>

                <article class="summary-card summary-card-red">
                    <div class="summary-meta">
                        <span class="summary-label">Overdue Tasks</span>
                        <strong class="summary-value"><?= dashboardEscape($summary['overdue_tasks']) ?></strong>
                    </div>
                </article>

                <article class="summary-card summary-card-slate">
                    <div class="summary-meta">
                        <span class="summary-label">Open Concerns</span>
                        <strong class="summary-value"><?= dashboardEscape($summary['open_concerns']) ?></strong>
                    </div>
                </article>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Department Overview</h2>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Assigned Coordinator</th>
                            <th>Pending Tasks</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($departmentOverview)): ?>
                            <?php foreach ($departmentOverview as $department): ?>
                                <tr>
                                    <td><?= dashboardEscape($department['department_name'] ?? 'Department') ?></td>
                                    <td><?= dashboardEscape($department['assigned_coordinator'] ?? 'Unassigned') ?></td>
                                    <td><?= dashboardEscape($department['pending_tasks'] ?? 0) ?></td>
                                    <td>
                                        <span class="status-badge <?= dashboardStatusClass($department['status'] ?? 'On Track') ?>">
                                            <?= dashboardEscape($department['status'] ?? 'On Track') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="action-link" href="?page=report-submission">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state-cell">No department records are available yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Pending Submissions</h2>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Submission Title</th>
                            <th>Department</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pendingSubmissions)): ?>
                            <?php foreach ($pendingSubmissions as $submission): ?>
                                <tr>
                                    <td><?= dashboardEscape($submission['submission_title'] ?? 'Untitled submission') ?></td>
                                    <td><?= dashboardEscape($submission['department_name'] ?? 'Department') ?></td>
                                    <td><?= dashboardEscape($submission['submitted_at'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="status-badge <?= dashboardStatusClass($submission['status'] ?? 'submitted') ?>">
                                            <?= dashboardEscape(ucfirst($submission['status'] ?? 'submitted')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($submission['source'] ?? '') === 'approval' && !$userCanReviewApprovals): ?>
                                            <span class="muted-link">View only</span>
                                        <?php else: ?>
                                            <a class="action-link" href="<?= dashboardEscape($submission['action_target'] ?? '?page=report-submission') ?>">View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state-cell">No pending submissions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Upcoming Activities</h2>
            </div>

            <div class="timeline-list">
                <?php if (!empty($upcomingActivities)): ?>
                    <?php foreach ($upcomingActivities as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-title"><?= dashboardEscape($activity['event_title'] ?? 'Activity') ?></div>
                                <div class="timeline-meta"><?= dashboardEscape($activity['department_name'] ?? 'College Wide') ?></div>
                                <div class="timeline-meta"><?= dashboardEscape($activity['event_date'] ?? 'N/A') ?> <?= dashboardEscape($activity['start_time'] ?? '') ?></div>
                                <span class="status-badge <?= dashboardStatusClass($activity['status'] ?? 'Upcoming') ?>"><?= dashboardEscape(ucfirst($activity['status'] ?? 'Upcoming')) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state-card">No upcoming activities found in the event registry.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Alerts & Notifications</h2>
            </div>

            <div class="alert-list">
                <?php if (!empty($alertItems)): ?>
                    <?php foreach (array_slice($alertItems, 0, 8) as $alert): ?>
                        <a class="alert-item alert-<?= dashboardEscape($alert['level'] ?? 'info') ?>" href="<?= dashboardEscape($alert['href'] ?? '?page=dashboard-overview') ?>">
                            <div class="alert-tag"><?= dashboardEscape($alert['type'] ?? 'Notification') ?></div>
                            <div class="alert-copy"><?= dashboardEscape($alert['message'] ?? 'No detail provided') ?></div>
                            <?php if (!empty($alert['when'])): ?>
                                <div class="alert-date"><?= dashboardEscape(date('M d, Y', strtotime($alert['when']))) ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state-card">No active alerts at this time.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-section full-width-section">
            <div class="section-header">
                <h2>Recent Activities</h2>
            </div>

            <div class="activity-list">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-bullet"></div>
                            <div class="activity-content">
                                <div class="activity-type"><?= dashboardEscape($activity['type'] ?? 'System activity') ?></div>
                                <div class="activity-description"><?= dashboardEscape($activity['description'] ?? 'No description available') ?></div>
                                <div class="activity-meta"><?= dashboardEscape($activity['module'] ?? 'System') ?> • <?= dashboardEscape(isset($activity['timestamp']) ? date('M d, Y h:i A', strtotime($activity['timestamp'])) : 'N/A') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state-card">No recent activity records were found in the existing system logs.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

