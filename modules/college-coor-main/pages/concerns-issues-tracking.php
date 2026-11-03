<?php
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Issues.php';
    include_once __DIR__ . '/../classes/User.php';

    /*User Class*/
    $userClass = new User();
    $userInfo = $userClass->userSession();
    $isDirectress = Issues::isDirectress($userInfo);

    // The Create button is shown only when the server would actually accept a new concern
    // (validateConcernInput requires a department on the user's account).
    $canCreate = !empty($userInfo['department_id']);

    /*Issues Class*/
    $issuesClass = new Issues();
    $departments = $isDirectress ? $issuesClass->getDepartments() : [];

    $statusLabels = [
        'draft'     => 'Draft',
        'submitted' => 'Submitted',
        'reviewed'  => 'Reviewed',
        'resolved'  => 'Resolved',
        'dismissed' => 'Dismissed',
    ];
?>

<div class="module-header sd-issue-header">
    <div class="sd-issue-header-text">
        <h1>Concerns &amp; Issue Tracking</h1>
        <p>Monitor departmental concerns, coordinate follow-ups, and track resolutions.</p>
    </div>
</div>

<div class="module-content sd-issue-page" data-is-directress="<?= $isDirectress ? '1' : '0' ?>">

    <!-- Summary cards -->
    <section class="sd-issue-summary" aria-label="Concern totals">
        <div class="sd-issue-card sd-issue-card-total">
            <strong class="sd-issue-card-value" id="sd-issue-count-total">–</strong>
            <span class="sd-issue-card-label">Total Concerns</span>
            <span class="sd-issue-card-note" id="sd-issue-note-total">Submitted concerns</span>
        </div>
        <div class="sd-issue-card sd-issue-card-submitted">
            <strong class="sd-issue-card-value" id="sd-issue-count-submitted">–</strong>
            <span class="sd-issue-card-label">Open Concerns</span>
            <span class="sd-issue-card-note">Submitted, awaiting review</span>
        </div>
        <div class="sd-issue-card sd-issue-card-reviewed">
            <strong class="sd-issue-card-value" id="sd-issue-count-reviewed">–</strong>
            <span class="sd-issue-card-label">In Progress</span>
            <span class="sd-issue-card-note">Reviewed, awaiting resolution</span>
        </div>
        <div class="sd-issue-card sd-issue-card-resolved">
            <strong class="sd-issue-card-value" id="sd-issue-count-resolved">–</strong>
            <span class="sd-issue-card-label">Resolved</span>
            <span class="sd-issue-card-note">Closed as resolved</span>
        </div>
    </section>

    <div class="sd-issue-overview">
        <!-- Needs attention -->
        <section class="sd-issue-panel" aria-labelledby="sd-issue-attn-title">
            <div class="sd-issue-panel-head">
                <div>
                    <h2 class="sd-issue-panel-title" id="sd-issue-attn-title">Needs Attention</h2>
                    <p class="sd-issue-panel-sub">Unresolved concerns, longest-waiting first.</p>
                </div>

                <?php if ($canCreate) : ?>
                    <button type="button" id="sd-issue-create" class="sd-issue-btn sd-issue-btn-primary">+ Create Concern</button>
                <?php endif; ?>
            </div>

            <ul class="sd-issue-attn-list" id="sd-issue-attn-list">
                <li class="sd-issue-state">Loading…</li>
            </ul>

            <p class="sd-issue-panel-foot" id="sd-issue-attn-foot"></p>
        </section>

        <!-- Workflow -->
        <section class="sd-issue-panel" aria-labelledby="sd-issue-flow-title">
            <div class="sd-issue-panel-head">
                <div>
                    <h2 class="sd-issue-panel-title" id="sd-issue-flow-title">Case Workflow</h2>
                    <p class="sd-issue-panel-sub">Select a stage to filter the list.</p>
                </div>
            </div>
            <ol class="sd-issue-flow" id="sd-issue-flow">
                <li><button type="button" class="sd-issue-stage sd-issue-stage-submitted" data-status="submitted">
                    <span class="sd-issue-stage-count" data-count="submitted">–</span>
                    <span class="sd-issue-stage-text"><strong>Submitted</strong><small>Waiting for the Directress to review</small></span>
                </button></li>
                <li><button type="button" class="sd-issue-stage sd-issue-stage-reviewed" data-status="reviewed">
                    <span class="sd-issue-stage-count" data-count="reviewed">–</span>
                    <span class="sd-issue-stage-text"><strong>Reviewed</strong><small>Waiting for an outcome</small></span>
                </button></li>
                <li><button type="button" class="sd-issue-stage sd-issue-stage-resolved" data-status="resolved">
                    <span class="sd-issue-stage-count" data-count="resolved">–</span>
                    <span class="sd-issue-stage-text"><strong>Resolved</strong><small>Closed with a resolution summary</small></span>
                </button></li>
                <li><button type="button" class="sd-issue-stage sd-issue-stage-dismissed" data-status="dismissed">
                    <span class="sd-issue-stage-count" data-count="dismissed">–</span>
                    <span class="sd-issue-stage-text"><strong>Dismissed</strong><small>Closed without a resolution</small></span>
                </button></li>
            </ol>
        </section>
    </div>

    <!-- Issue list -->
    <section class="sd-issue-panel sd-issue-list-panel" aria-labelledby="sd-issue-list-title">
        <div class="sd-issue-panel-head sd-issue-list-head">
            <h2 class="sd-issue-panel-title" id="sd-issue-list-title">All Concerns</h2>

            <div class="sd-issue-toolbar">
                <input class="sd-issue-input sd-issue-search" id="sd-issue-search" type="search"
                       placeholder="Search title, ID or submitter" aria-label="Search concerns">

                <?php if ($isDirectress) : ?>
                    <select class="sd-issue-input" id="sd-issue-dept" aria-label="Filter by department">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept) : ?>
                            <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <select class="sd-issue-input" id="sd-issue-status" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusLabels as $value => $label) : ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="sd-issue-table-wrap">
            <table class="sd-issue-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Concern</th>
                        <th>Department</th>
                        <th>Submitted By</th>
                        <th>Reported</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="sd-issue-tbody">
                    <tr><td colspan="8" class="sd-issue-state">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <?php if ($canCreate) : ?>
    <!-- Create / edit draft modal -->
    <div class="sd-issue-overlay" id="sd-issue-modal-overlay">
        <div class="sd-issue-modal" role="dialog" aria-modal="true" aria-labelledby="sd-issue-modal-title">
            <div class="sd-issue-modal-head">
                <h3 id="sd-issue-modal-title">Create Concern</h3>
                <button type="button" class="sd-issue-close" id="sd-issue-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="sd-issue-modal-body">
                <form id="sd-issue-form" enctype="multipart/form-data" data-skip novalidate>
                    <div class="sd-issue-field-group">
                        <label for="sd-issue-title">Title</label>
                        <input class="sd-issue-input" type="text" id="sd-issue-title" name="title" maxlength="255" required>
                    </div>

                    <div class="sd-issue-field-group">
                        <label for="sd-issue-details">Details</label>
                        <textarea class="sd-issue-input" id="sd-issue-details" name="details" rows="4" placeholder="Describe the concern" required></textarea>
                    </div>

                    <div class="sd-issue-field-group">
                        <label for="sd-issue-desired">Desired Resolution <span class="sd-issue-optional">(optional)</span></label>
                        <textarea class="sd-issue-input" id="sd-issue-desired" name="desired_resolution" rows="3" placeholder="What outcome are you hoping for"></textarea>
                    </div>

                    <div class="sd-issue-field-group">
                        <label for="sd-issue-file">Attachment <span class="sd-issue-optional">(optional)</span></label>
                        <input class="sd-issue-input sd-issue-file" id="sd-issue-file" name="file" type="file"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                        <small class="sd-issue-hint" id="sd-issue-file-hint">PDF, Word, Excel, JPG or PNG &middot; up to 10 MB</small>
                    </div>

                    <input type="hidden" id="sd-issue-id-field" name="issue_id" value="">

                    <div class="sd-issue-form-error" id="sd-issue-form-error" role="alert" hidden></div>

                    <div class="sd-issue-btn-row">
                        <button type="submit" class="sd-issue-btn sd-issue-btn-primary" id="sd-issue-submit">Submit Concern</button>
                        <button type="button" class="sd-issue-btn" id="sd-issue-draft">Save as Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Concern details modal (same layout as the report details modal) -->
    <div class="sd-issue-overlay" id="sd-issue-view-overlay">
        <div class="sd-issue-modal sd-issue-modal-lg" id="sd-issue-view-modal" role="dialog" aria-modal="true" aria-labelledby="sd-issue-view-title">
            <div class="sd-issue-modal-head">
                <h3 id="sd-issue-view-title">Concern Details</h3>
                <button type="button" class="sd-issue-close" id="sd-issue-view-close" aria-label="Close">&times;</button>
            </div>
            <div class="sd-issue-modal-body" id="sd-issue-view-body">
                <!-- populated by JS -->
            </div>
        </div>
    </div>

    <!-- Attachment viewer (Full screen) -->
    <div class="sd-issue-overlay sd-issue-overlay-top" id="sd-issue-viewer-overlay">
        <div class="sd-issue-modal sd-issue-modal-lg" role="dialog" aria-modal="true" aria-labelledby="sd-issue-viewer-title">
            <div class="sd-issue-modal-head">
                <h3 id="sd-issue-viewer-title">Attachment</h3>
                <button type="button" class="sd-issue-close" id="sd-issue-viewer-close" aria-label="Close">&times;</button>
            </div>
            <div class="sd-issue-viewer-body">
                <iframe id="sd-issue-viewer-frame" class="sd-issue-frame" src="" title="Attachment"></iframe>
            </div>
        </div>
    </div>
</div>