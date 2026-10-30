<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Department.php';
include_once __DIR__ . '/../classes/User.php';

$userClass = new User();
$userInfo = $userClass->userSession();
$roleName = $_SESSION['role_name'] ?? ($userInfo['role'] ?? '');
$isApprover = trim((string) $roleName) === 'College Coordinator';

$departmentClass = new Department();
$departments = $departmentClass->getAllDepartments();
$myDepartmentId = $isApprover ? null : ((int) ($userInfo['department_id'] ?? 0));
?>

<div class="approval-module" data-is-approver="<?= $isApprover ? '1' : '0' ?>" data-user-department="<?= htmlspecialchars((string) ($userInfo['department_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <div class="approval-header-card">
        <h1>Approval &amp; Decision Support</h1>
        <p>Review requests, make informed decisions, and monitor approval activity.</p>
    </div>

    <div class="approval-stats" aria-label="Approval overview statistics">
        <div class="approval-stat-card">
            <div class="approval-stat-number" data-stat="submitted">0</div>
            <div class="approval-stat-label">Pending Decisions</div>
            <div class="approval-stat-caption">Submitted, not yet reviewed</div>
        </div>
        <div class="approval-stat-card">
            <div class="approval-stat-number" data-stat="reviewed">0</div>
            <div class="approval-stat-label">Under Review</div>
            <div class="approval-stat-caption">Reviewed, awaiting decision</div>
        </div>
        <div class="approval-stat-card">
            <div class="approval-stat-number" data-stat="approved">0</div>
            <div class="approval-stat-label">Approved</div>
            <div class="approval-stat-caption">Decision recorded</div>
        </div>
        <div class="approval-stat-card">
            <div class="approval-stat-number" data-stat="rejected">0</div>
            <div class="approval-stat-label">Rejected</div>
            <div class="approval-stat-caption">Decision recorded</div>
        </div>
    </div>

    <div class="approval-board">
        <section class="approval-panel approval-panel-main">
            <div class="approval-panel-header">
                <h2>Needs your decision</h2>
                <span id="approval-queue-count" class="approval-panel-count">0 requests</span>
            </div>

            <div class="approval-filter-bar">
                <input id="approval-search" type="search" placeholder="Search title, request ID or submitter" aria-label="Search requests">

                <select id="approval-department-filter" class="approval-select" aria-label="Filter by department">
                    <option value="">All departments</option>
                    <?php foreach ($departments as $dept) : ?>
                        <option value="<?= (int) ($dept['department_id'] ?? 0) ?>"><?= htmlspecialchars((string) ($dept['department_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="approval-status-filter" class="approval-select" aria-label="Filter by status">
                    <option value="pending">Needs a decision</option>
                    <option value="all">All</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>

                <input id="approval-date-from" type="date" aria-label="Submitted from">
                <input id="approval-date-to" type="date" aria-label="Submitted to">

                <button type="button" id="approval-clear-filters" class="approval-clear-link">Clear filters</button>
                <button type="button" id="approval-open-modal" class="approval-primary-btn">Submit for Approval</button>
            </div>

            <div id="approval-queue-loading" class="approval-loading-state" hidden>Loading…</div>
            <div id="approval-queue-list" class="approval-queue-list" aria-live="polite"></div>
            <div id="approval-queue-empty" class="approval-empty-state" hidden>
                <div class="approval-empty-icon">No requests</div>
                <p>No requests match your filters.</p>
            </div>
            <div id="approval-queue-more" class="approval-more-results" hidden>Showing the first 100 results. Narrow your filters.</div>
        </section>

        <aside class="approval-panel approval-panel-side">
            <div class="approval-panel-header">
                <h2>Recent decisions</h2>
            </div>
            <div id="approval-recent-list" class="approval-recent-list" aria-live="polite"></div>
        </aside>
    </div>

    <div class="approval-modal-overlay" id="approval-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="approval-modal-title">
        <div class="approval-modal approval-modal-sm">
            <div class="approval-modal-header">
                <h3 id="approval-modal-title">Submit for Approval</h3>
                <button type="button" class="approval-close-button" id="approval-modal-close" aria-label="Close">&times;</button>
            </div>

            <div class="approval-modal-body">
                <form id="approval-form" enctype="multipart/form-data">
                    <div class="approval-form-group">
                        <label for="approval-title">Title</label>
                        <input type="text" id="approval-title" name="title" maxlength="200" required>
                    </div>

                    <div class="approval-form-group">
                        <label for="approval-description">Description</label>
                        <textarea id="approval-description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="approval-form-group">
                        <label for="approval-justification">Justification</label>
                        <textarea id="approval-justification" name="justification" rows="4"></textarea>
                    </div>

                    <div class="approval-form-group">
                        <label for="approval-attachment">Request document</label>
                        <input class="approval-file-input" id="approval-attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="approval-helper-text">Required to submit. PDF, JPG or PNG, up to 10 MB. The College Coordinator views it here in the system.</small>
                    </div>

                    <input type="hidden" id="approval-id" name="approval_id" value="">
                    <div id="approval-form-error" class="approval-inline-error" hidden></div>

                    <div class="approval-modal-actions">
                        <button type="submit" class="approval-primary-btn" id="approval-submit-btn">Submit for Approval</button>
                        <button type="button" class="approval-secondary-btn" id="approval-draft-btn">Save as Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="approval-modal-overlay" id="approval-view-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="approval-view-modal-title">
        <div class="approval-modal approval-modal-lg">
            <div class="approval-modal-header">
                <h3 id="approval-view-modal-title">Approval Request Details</h3>
                <button type="button" class="approval-close-button" id="approval-view-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="approval-modal-body" id="approval-view-modal-body"></div>
        </div>
    </div>

    <div class="approval-modal-overlay" id="approval-pdf-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="approval-pdf-modal-title">
        <div class="approval-modal approval-modal-xl">
            <div class="approval-modal-header">
                <h3 id="approval-pdf-modal-title">Approval document</h3>
                <button type="button" class="approval-close-button" id="approval-pdf-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="approval-modal-body approval-pdf-body">
                <iframe id="approval-pdf-frame" title="Approval document preview"></iframe>
            </div>
        </div>
    </div>
</div>