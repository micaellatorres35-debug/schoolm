<?php
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    /* User */
    $userClass    = new User();
    $userInfo     = $userClass->userSession();
    $roleName = $_SESSION['role_name'] ?? $userInfo['role'] ?? '';
$isDirectress = in_array($roleName, ['School Directress', 'Admin College Coordinator'], true);

    /* Departments (only the Directress can filter across departments) */
    $departments = [];
    if ($isDirectress) {
        $departmentClass = new Department();
        $departments     = $departmentClass->getAllDepartments();
    }

    /* Status filter options. "pending" = submitted + reviewed (the work waiting on the Directress). */
    if ($isDirectress) {
        $statusOptions = [
            'pending'   => 'Needs a decision',
            'submitted' => 'Submitted',
            'reviewed'  => 'Reviewed',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'all'       => 'All statuses',
        ];
    } else {
        $statusOptions = [
            'all'       => 'All statuses',
            'draft'     => 'Draft',
            'submitted' => 'Submitted',
            'reviewed'  => 'Reviewed',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
        ];
    }
?>

<div class="sd-approval-module" id="sd-approval-module" data-is-directress="<?= $isDirectress ? '1' : '0' ?>">

    <!-- A. Header -->
    <div class="module-header sd-approval-head">
        <div>
            <h1>Approval &amp; Decision Support</h1>
            <p>Review requests, make informed decisions, and monitor approval activity.</p>
        </div>
    </div>

    <div class="module-content">

        <!-- B. Summary: one segment per real workflow status -->
        <section class="sd-approval-pipeline" aria-label="Request summary">
            <article class="sd-approval-stage">
                <div>
                    <strong class="sd-approval-stage-count" data-count="submitted">–</strong>
                    <span class="sd-approval-stage-label">Pending Decisions</span>
                    <span class="sd-approval-stage-hint">Submitted, not yet reviewed</span>
                </div>
            </article>
            <article class="sd-approval-stage">
                <div>
                    <strong class="sd-approval-stage-count" data-count="reviewed">–</strong>
                    <span class="sd-approval-stage-label">Under Review</span>
                    <span class="sd-approval-stage-hint">Reviewed, awaiting decision</span>
                </div>
            </article>
            <article class="sd-approval-stage">
                <div>
                    <strong class="sd-approval-stage-count" data-count="approved">–</strong>
                    <span class="sd-approval-stage-label">Approved</span>
                    <span class="sd-approval-stage-hint">Decision recorded</span>
                </div>
            </article>
            <article class="sd-approval-stage">
                <div>
                    <strong class="sd-approval-stage-count" data-count="rejected">–</strong>
                    <span class="sd-approval-stage-label">Rejected</span>
                    <span class="sd-approval-stage-hint">Decision recorded</span>
                </div>
            </article>
        </section>

        <div class="sd-approval-main">

            <!-- C + F. Queue with filters -->
            <section class="sd-approval-panel" aria-labelledby="sd-approval-queue-title">
                <div class="sd-approval-panel-head">
                    <h2 id="sd-approval-queue-title">Needs your decision</h2>
                    <span class="sd-approval-count" id="sd-approval-queue-count" aria-live="polite"></span>
                </div>

                <div class="sd-approval-filters">
                    <input type="search" id="sd-approval-search" class="sd-approval-input sd-approval-input--search"
                           placeholder="Search title, request ID or submitter" aria-label="Search requests" maxlength="100">

                    <?php if ($isDirectress) : ?>
                        <select id="sd-approval-department" class="sd-approval-input" aria-label="Department">
                            <option value="">All departments</option>
                            <?php foreach ($departments as $dept) : ?>
                                <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <select id="sd-approval-status" class="sd-approval-input" aria-label="Status">
                        <?php foreach ($statusOptions as $value => $label) : ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="sd-approval-datefield">
                        <span>Submitted from</span>
                        <input type="date" id="sd-approval-date-from" class="sd-approval-input">
                    </label>
                    <label class="sd-approval-datefield">
                        <span>to</span>
                        <input type="date" id="sd-approval-date-to" class="sd-approval-input">
                    </label>

                    <button type="button" class="sd-approval-btn sd-approval-btn--ghost" data-action="clear-filters">Clear filters</button>
                </div>

                <div id="sd-approval-queue-list" class="sd-approval-list" aria-busy="true">
                    <div class="sd-approval-state">Loading requests…</div>
                </div>
            </section>

            <!-- E. Recent decisions -->
            <aside class="sd-approval-panel" aria-labelledby="sd-approval-recent-title">
                <div class="sd-approval-panel-head">
                    <h2 id="sd-approval-recent-title">Recent decisions</h2>
                </div>
                <div id="sd-approval-recent-list" class="sd-approval-recent" aria-busy="true">
                    <div class="sd-approval-state">Loading…</div>
                </div>
            </aside>
        </div>
    </div>

    <!-- D. Request details modal (same layout as the report details modal) -->
    <div class="sd-approval-overlay" id="sd-approval-view-overlay">
        <div class="sd-approval-modal sd-approval-modal--lg" role="dialog" aria-modal="true" aria-labelledby="sd-approval-view-title">
            <div class="sd-approval-modal-head">
                <h2 id="sd-approval-view-title">Request Details</h2>
                <button type="button" class="sd-approval-close" data-action="close-view" aria-label="Close">&times;</button>
            </div>
            <div class="sd-approval-modal-body sd-approval-view-body" id="sd-approval-view-body">
                <!-- populated by JS -->
            </div>
        </div>
    </div>

    <!-- File viewer modal (Full screen) -->
    <div class="sd-approval-overlay sd-approval-overlay--file" id="sd-approval-file-overlay">
        <div class="sd-approval-modal sd-approval-modal--lg" role="dialog" aria-modal="true" aria-labelledby="sd-approval-file-title">
            <div class="sd-approval-modal-head">
                <h2 id="sd-approval-file-title">Request document</h2>
                <button type="button" class="sd-approval-close" data-action="close-file" aria-label="Close">&times;</button>
            </div>
            <div class="sd-approval-modal-body sd-approval-file-body">
                <iframe id="sd-approval-file-frame" class="sd-approval-file-frame" src="" title="Request document"></iframe>
            </div>
        </div>
    </div>

    <!-- Submit for Approval modal -->
    <div class="sd-approval-overlay" id="sd-approval-create-overlay">
        <div class="sd-approval-modal" role="dialog" aria-modal="true" aria-labelledby="sd-approval-create-title">
            <div class="sd-approval-modal-head">
                <h2 id="sd-approval-create-title">Submit for Approval</h2>
                <button type="button" class="sd-approval-close" data-action="close-create" aria-label="Close">&times;</button>
            </div>
            <div class="sd-approval-modal-body">
                <form id="sd-approval-form" enctype="multipart/form-data" data-skip novalidate>
                    <div class="sd-approval-field">
                        <label for="sd-approval-title">Title</label>
                        <input type="text" id="sd-approval-title" name="title" class="sd-approval-input" placeholder="Enter request title" maxlength="255" required>
                    </div>
                    <div class="sd-approval-field">
                        <label for="sd-approval-description">Description</label>
                        <textarea id="sd-approval-description" name="description" class="sd-approval-input" rows="3" placeholder="What is being requested" required></textarea>
                    </div>
                    <div class="sd-approval-field">
                        <label for="sd-approval-justification">Justification</label>
                        <textarea id="sd-approval-justification" name="justification" class="sd-approval-input" rows="3" placeholder="Why is this needed"></textarea>
                    </div>
                    <div class="sd-approval-field">
                        <label for="sd-approval-attachment">Request document</label>
                        <input type="file" id="sd-approval-attachment" name="attachment" class="sd-approval-input sd-approval-file"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <small class="sd-approval-muted">Required to submit. PDF, JPG or PNG, up to 10 MB. The School Directress views it here in the system.</small>
                    </div>

                    <input type="hidden" id="sd-approval-id" name="approval_id" value="">
                    <div id="sd-approval-form-error" class="sd-approval-error" role="alert" hidden></div>

                    <div class="sd-approval-actions">
                        <button type="submit" class="sd-approval-btn sd-approval-btn--primary" id="sd-approval-submit-btn">Submit for Approval</button>
                        <button type="button" class="sd-approval-btn" id="sd-approval-draft-btn" data-action="save-draft">Save as Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation dialog for consequential actions -->
    <div class="sd-approval-overlay sd-approval-overlay--confirm" id="sd-approval-confirm-overlay">
        <div class="sd-approval-modal sd-approval-modal--small" role="alertdialog" aria-modal="true"
             aria-labelledby="sd-approval-confirm-title" aria-describedby="sd-approval-confirm-message">
            <div class="sd-approval-modal-body">
                <h2 id="sd-approval-confirm-title"></h2>
                <p id="sd-approval-confirm-message"></p>
                <div class="sd-approval-actions">
                    <button type="button" class="sd-approval-btn" id="sd-approval-confirm-cancel">Cancel</button>
                    <button type="button" class="sd-approval-btn sd-approval-btn--primary" id="sd-approval-confirm-ok">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="sd-approval-toasts" id="sd-approval-toasts" aria-live="polite"></div>
</div>