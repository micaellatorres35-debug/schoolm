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
                        <a href="?page=report-submission" class="view-link">View details</a>
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
                                    <?php if (($submission['source'] ?? '') === 'approval' && !$userCanReviewApprovals): ?>
                                        <span class="tiny-link">View only</span>
                                    <?php else: ?>
                                        <a href="<?= dashboardEscape($submission['action_target'] ?? '?page=report-submission') ?>" class="tiny-link">View</a>
                                    <?php endif; ?>

                                    <span class="status-badge <?= dashboardStatusClass($submission['status'] ?? 'submitted') ?>">
                                        <?= dashboardEscape(ucfirst($submission['status'] ?? 'submitted')) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-panel">No pending submissions found.</div>
                    <?php endif; ?>

                    <a href="?page=report-submission" class="primary-cta">Go to Reports</a>
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
                            <h3>Upcoming Activities</h3>
                        </div>
                    </div>

                    <?php if (!empty($upcomingActivities)): ?>
                        <?php foreach ($upcomingActivities as $activity): ?>
                            <div class="attention-item attention-item-activity">
                                <div class="attention-item-icon calendar-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M8 2v4"></path>
                                        <path d="M16 2v4"></path>
                                        <path d="M3 10h18"></path>
                                    </svg>
                                </div>

                                <div class="attention-item-body">
                                    <div class="item-title"><?= dashboardEscape($activity['event_title'] ?? 'Activity') ?></div>
                                    <div class="item-subtitle"><?= dashboardEscape($activity['department_name'] ?? 'College Wide') ?></div>
                                    <span class="mini-status <?= dashboardStatusClass($activity['status'] ?? 'Upcoming') ?>">
                                        <?= dashboardEscape(ucfirst($activity['status'] ?? 'Upcoming')) ?>
                                    </span>
                                </div>

                                <div class="item-date">
                                    <span><?= dashboardEscape(!empty($activity['event_date']) ? date('M d', strtotime($activity['event_date'])) : 'N/A') ?></span>
                                    <small>
                                        <?= dashboardEscape(trim(($activity['start_time'] ?? '') . (!empty($activity['end_time']) ? ' - ' . $activity['end_time'] : ''))) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-panel">No upcoming activities found in the event registry.</div>
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
                        <a href="?page=report-submission" class="view-link">View details</a>
                    </div>

                    <?php if (!empty($departmentOverview)): ?>
                        <div class="coord-table-wrap">
                            <table class="coord-status-table">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Coordinator</th>
                                        <th>Pending Tasks</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departmentOverview as $department): ?>
                                        <tr>
                                            <td class="department-name">
                                                <?= dashboardEscape($department['department_name'] ?? 'Department') ?>
                                            </td>
                                            <td><?= dashboardEscape($department['assigned_coordinator'] ?? 'Unassigned') ?></td>
                                            <td>
                                                <span class="task-pill <?= ((int) ($department['pending_tasks'] ?? 0) > 0) ? 'has-tasks' : 'no-tasks' ?>">
                                                    <?= dashboardEscape((int) ($department['pending_tasks'] ?? 0)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?= dashboardStatusClass($department['status'] ?? 'On Track') ?>">
                                                    <?= dashboardEscape($department['status'] ?? 'On Track') ?>
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

        <section class="dashboard-section" style="display:none;">
            <div class="section-header">
                <h2>Coordinator Status</h2>
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

        <section class="dashboard-section" style="display:none;">
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

        <section class="dashboard-section" style="display:none;">
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
    </div>
</div>

