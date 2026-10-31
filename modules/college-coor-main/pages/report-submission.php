<?php
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Report.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    /*User Class*/
    $userClass = new User();
    $userInfo = $userClass->userSession();
    $isDirectress = ($userInfo['role'] === 'School Directress');

    /*Report Class*/
    $reportClass = new Report();
    $myDepartmentId = (int) ($_SESSION['department_id'] ?? 0);
    $reportTypes = $reportClass->getReportTypesByDepartment($myDepartmentId);       // create form
    $typeNames   = $reportClass->getReportTypeNames($isDirectress ? null : $myDepartmentId); // type filter

    /*Department Class*/
    $departmentClass = new Department();
    $departments = $departmentClass->getAllDepartments();

    $statusLabels = [
        'draft'     => 'Draft',
        'submitted' => 'Submitted',
        'reviewed'  => 'Reviewed',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
    ];
?>

<div class="module-header">
    <h1>Report Submission & Management</h1>
    <p>Monitor submissions, review reporting activity, and manage departmental reports.</p>
</div>

<div class="module-content">
    <div class="rsm-page" data-is-directress="<?= $isDirectress ? '1' : '0' ?>">

        <!-- ── Summary cards (counts come from the database) ── -->
        <section class="rsm-stat-grid" aria-label="Report summary">
            <article class="rsm-stat-card">
                <div>
                    <strong id="rsm-stat-total">—</strong>
                    <span class="rsm-stat-label">Total reports</span>
                    <span class="rsm-stat-note">Includes drafts</span>
                </div>
            </article>
            <article class="rsm-stat-card">
                <div>
                    <strong id="rsm-stat-pending">—</strong>
                    <span class="rsm-stat-label">Pending review</span>
                    <span class="rsm-stat-note">Submitted, not yet reviewed</span>
                </div>
            </article>
            <article class="rsm-stat-card">
                <div>
                    <strong id="rsm-stat-approved">—</strong>
                    <span class="rsm-stat-label">Approved</span>
                    <span class="rsm-stat-note">Decision: approved</span>
                </div>
            </article>
            <article class="rsm-stat-card">
                <div>
                    <strong id="rsm-stat-rejected">—</strong>
                    <span class="rsm-stat-label">Rejected</span>
                    <span class="rsm-stat-note">Decision: rejected</span>
                </div>
            </article>
        </section>

        <!-- ── Analytics ── -->
        <section class="rsm-analytics" aria-label="Report analytics">
            <article class="rsm-card rsm-card-trend">
                <header class="rsm-card-head">
                    <h3>Monthly submission trends</h3>
                    <p id="rsm-trend-sub">Last six months</p>
                </header>
                <div class="rsm-chart-box" id="rsm-trend-box">
                    <canvas id="rsm-trend-chart" role="img" aria-label="Line chart of reports submitted per month"></canvas>
                </div>
                <p class="rsm-chart-state" id="rsm-trend-state" role="status" aria-live="polite">Loading chart…</p>
            </article>

            <article class="rsm-card rsm-card-status">
                <header class="rsm-card-head">
                    <h3>Status distribution</h3>
                    <p>Reports by current status</p>
                </header>
                <div class="rsm-chart-box rsm-chart-box-donut" id="rsm-status-box">
                    <canvas id="rsm-status-chart" role="img" aria-label="Doughnut chart of reports by status"></canvas>
                </div>
                <p class="rsm-chart-state" id="rsm-status-state" role="status" aria-live="polite">Loading chart…</p>
                <ul class="rsm-legend" id="rsm-status-legend"></ul>
            </article>

            <article class="rsm-card rsm-card-dept">
                <header class="rsm-card-head">
                    <h3>Reports by department</h3>
                    <p>Reports without a department appear as “Unassigned”</p>
                </header>
                <div class="rsm-chart-box" id="rsm-dept-box">
                    <canvas id="rsm-dept-chart" role="img" aria-label="Bar chart of reports per department"></canvas>
                </div>
                <p class="rsm-chart-state" id="rsm-dept-state" role="status" aria-live="polite">Loading chart…</p>
            </article>
        </section>

        <!-- ── Actions + filters (one panel) ── -->
        <section class="rsm-panel rsm-control-panel" aria-label="Report filters">
            <div class="rsm-toolbar">
                <div>
                    <h2>Report list</h2>
                    <p>Filters also update the charts above.</p>
                </div>
                <button type="button" id="rsm-open-modal" class="rsm-add-btn">+ Create report</button>
            </div>

            <div class="rsm-filters">
                <label class="rsm-search-wrap">
                    <span class="rsm-sr-only">Search reports</span>
                    <input class="rsm-search" id="rsm-search" type="search" placeholder="Search title, submitter or report ID" autocomplete="off">
                </label>

                <?php if ($isDirectress) : ?>
                <label class="rsm-field">
                    <span class="rsm-sr-only">Filter by department</span>
                    <select id="rsm-filter" class="rsm-select">
                        <option value="">All departments</option>
                        <?php foreach ($departments as $dept) : ?>
                            <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php endif; ?>

                <label class="rsm-field">
                    <span class="rsm-sr-only">Filter by report type</span>
                    <select id="rsm-type-filter" class="rsm-select">
                        <option value="">All report types</option>
                        <?php foreach ($typeNames as $typeName) : ?>
                            <option value="<?= htmlspecialchars($typeName) ?>"><?= htmlspecialchars($typeName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="rsm-field">
                    <span class="rsm-sr-only">Filter by status</span>
                    <select id="rsm-status-filter" class="rsm-select">
                        <option value="">All statuses</option>
                        <?php foreach ($statusLabels as $value => $label) : ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="rsm-field rsm-date-field">
                    <span class="rsm-date-label">Submitted from</span>
                    <input type="date" id="rsm-date-from" class="rsm-select">
                </label>
                <label class="rsm-field rsm-date-field">
                    <span class="rsm-date-label">Submitted to</span>
                    <input type="date" id="rsm-date-to" class="rsm-select">
                </label>

                <button type="button" id="rsm-clear-filters" class="rsm-btn-cancel">Clear filters</button>
            </div>
        </section>

        <!-- ── Results: report list (separate from the controls) ── -->
        <section class="rsm-results" aria-label="Reports">
            <div class="rsm-list">
                <div class="rsm-table-wrapper">
                    <table class="rsm-table">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Submitted By</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="rsm-muted">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rsm-footer">
                <span class="rsm-showing" id="rsm-showing"></span>
                <div class="rsm-page-nav" id="rsm-page-nav"></div>
            </div>
        </section>

        <!-- Create / Submit Report Modal -->
        <div class="rsm-modal-overlay" id="rsm-modal-overlay">
            <div class="rsm-modal" role="dialog" aria-modal="true" aria-labelledby="rsm-modal-title">
                <div class="rsm-modal-header">
                    <h3 id="rsm-modal-title">Create Report</h3>
                    <button type="button" class="rsm-modal-close" id="rsm-modal-close" aria-label="Close">&times;</button>
                </div>

                <div class="rsm-modal-body">
                    <form id="report-form" class="rsm-form" enctype="multipart/form-data" data-skip>

                        <div class="rsm-form-group">
                            <label for="report-title">Title</label>
                            <input type="text" id="report-title" name="title" maxlength="255" placeholder="Enter report title" required>
                        </div>

                        <div class="rsm-form-group">
                            <label for="report-type">Report Type</label>
                            <select id="report-type" name="report_type" required>
                                <option value="">-- Select Type --</option>
                                <?php foreach ($reportTypes as $type) : ?>
                                    <option value="<?= htmlspecialchars($type['type_id']) ?>">
                                        <?= htmlspecialchars($type['report_type']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="rsm-form-group">
                            <label for="report-description">Description <span class="rsm-optional">(optional)</span></label>
                            <textarea id="report-description" name="description" rows="3" maxlength="5000" placeholder="A short note about this report"></textarea>
                        </div>

                        <!-- The report itself: an uploaded file -->
                        <div class="rsm-form-group">
                            <span class="rsm-field-label">Report file <span class="rsm-req" aria-hidden="true">*</span></span>
                            <div class="rsm-file-picker">
                                <input type="file" id="report-attachment" name="attachment" class="rsm-file-input"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <label for="report-attachment" class="rsm-file-btn">Choose file</label>
                                <span id="rsm-file-name" class="rsm-file-name">No file chosen</span>
                                <button type="button" id="rsm-file-clear" class="rsm-file-clear" aria-label="Remove selected file" hidden>&times;</button>
                            </div>
                            <small class="rsm-file-hint">Required to submit. PDF, Word, Excel, JPG or PNG &middot; up to 10 MB. PDFs and images open inside the system.</small>
                        </div>

                        <input type="hidden" id="report-id" name="report_id" value="">

                        <div id="report-form-error" class="rsm-file-error" role="alert" hidden></div>

                        <div class="rsm-actions">
                            <button type="submit" id="submit-btn" class="rsm-btn-submit">Submit Report</button>
                            <button type="button" id="draft-btn" class="rsm-btn-cancel">Save as Draft</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Report Detail / Review Modal -->
        <div class="rsm-modal-overlay" id="rsm-view-modal-overlay">
            <div class="rsm-modal rsm-modal-lg" role="dialog" aria-modal="true" aria-labelledby="rsm-view-modal-title">
                <div class="rsm-modal-header">
                    <h3 id="rsm-view-modal-title">Report Details</h3>
                    <button type="button" class="rsm-modal-close" id="rsm-view-modal-close" aria-label="Close">&times;</button>
                </div>

                <div class="rsm-modal-body" id="rsm-view-modal-body">
                    <!-- populated by JS -->
                </div>
            </div>
        </div>

        <!-- File Viewer Modal (report files open here) -->
        <div class="rsm-modal-overlay" id="rsm-pdf-modal-overlay">
            <div class="rsm-modal rsm-modal-lg" role="dialog" aria-modal="true" aria-labelledby="rsm-pdf-modal-title">
                <div class="rsm-modal-header">
                    <h3 id="rsm-pdf-modal-title">Report file</h3>
                    <button type="button" class="rsm-modal-close" id="rsm-pdf-modal-close" aria-label="Close">&times;</button>
                </div>
                <div class="rsm-modal-body rsm-pdf-body">
                    <iframe id="rsm-pdf-frame" class="rsm-pdf-frame" src="" title="Report file"></iframe>
                </div>
            </div>
        </div>

    </div>
</div>