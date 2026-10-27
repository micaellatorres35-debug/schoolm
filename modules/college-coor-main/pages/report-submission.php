<?php
    include_once __DIR__ . '/../../../auth/session.php';
    include __DIR__ . '/../classes/Report.php';
    include __DIR__ . '/../classes/Department.php';
    include __DIR__ . '/../classes/User.php';

    /*User Class*/
    $userClass = new User();
    $userInfo = $userClass->userSession();
    $isDirectress = ($userInfo['role'] === 'School Directress');

    /*Report Class*/
    $reportClass = new Report();
    $myDepartmentId = (int) ($_SESSION['department_id'] ?? 0);
    $reportTypes = $reportClass->getReportTypesByDepartment($myDepartmentId);

    $listDepartmentId = $isDirectress ? null : $myDepartmentId;
    $reports = $reportClass->getReports($listDepartmentId);

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
    <h1>Report Submissions & Management</h1>
    <p>Create, submit, and manage reports directly within the system.</p>
</div>

<div class="module-content">
    <div class="rsm-page" data-is-directress="<?= $isDirectress ? '1' : '0' ?>">

        <div class="rsm-list-header">
            <h3>Report List</h3>
            <div class="rsm-header-actions">
                <div class="rsm-filters">
                    <?php if ($isDirectress) : ?>
                        <select id="rsm-filter" class="rsm-select">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept) : ?>
                                <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <select id="rsm-status-filter" class="rsm-select">
                        <option value="">All Statuses</option>
                        <?php foreach ($statusLabels as $value => $label) : ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="rsm-search" id="rsm-search" type="search" placeholder="Search by title or submitter">
                </div>
                <button type="button" id="rsm-open-modal" class="rsm-add-btn">+ Create Report</button>
            </div>
        </div>

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
                        <?php if (!empty($reports)) : ?>
                            <?php foreach ($reports as $report) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($report['report_id']) ?></td>
                                    <td><?= htmlspecialchars($report['title']) ?></td>
                                    <td><?= htmlspecialchars($report['report_type'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($report['department_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($report['submitted_by'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="rsm-status rsm-status-<?= htmlspecialchars($report['status']) ?>">
                                            <?= htmlspecialchars($statusLabels[$report['status']] ?? $report['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($report['submitted_at'] ?? 'N/A') ?></td>
                                    <td class="rsm-actions-cell">
                                        <button type="button" class="rsm-btn-view rsm-view-report" data-report-id="<?= htmlspecialchars($report['report_id']) ?>">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr class="rsm-no-data">
                                <td colspan="8">No reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

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
                            <input type="text" id="report-title" name="title" placeholder="Enter report title" required>
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
                            <label for="report-summary">Summary</label>
                            <textarea id="report-summary" name="summary" rows="3" placeholder="Give a brief overview of the report" required></textarea>
                        </div>

                        <div class="rsm-form-group">
                            <label for="report-findings">Findings</label>
                            <textarea id="report-findings" name="findings" rows="4" placeholder="What was observed or discovered"></textarea>
                        </div>

                        <div class="rsm-form-group">
                            <label for="report-recommendations">Recommendations</label>
                            <textarea id="report-recommendations" name="recommendations" rows="4" placeholder="What actions are being recommended"></textarea>
                        </div>

                        <!-- Optional attachment -->
                        <div class="rsm-form-group">
                            <span class="rsm-field-label">Attachment <span class="rsm-optional">(optional)</span></span>
                            <div class="rsm-file-picker">
                                <input type="file" id="report-attachment" name="attachment" class="rsm-file-input"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <label for="report-attachment" class="rsm-file-btn">Choose file</label>
                                <span id="rsm-file-name" class="rsm-file-name">No file chosen</span>
                                <button type="button" id="rsm-file-clear" class="rsm-file-clear" aria-label="Remove selected file" hidden>&times;</button>
                            </div>
                            <small class="rsm-file-hint">PDF, Word, Excel, JPG or PNG &middot; up to 10 MB</small>
                        </div>

                        <input type="hidden" id="report-id" name="report_id" value="">

                        <div id="report-form-error" class="rsm-file-error" style="display:none;"></div>

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

        <!-- File Viewer Modal (generated PDF and attachments) -->
        <div class="rsm-modal-overlay" id="rsm-pdf-modal-overlay">
            <div class="rsm-modal rsm-modal-lg" role="dialog" aria-modal="true" aria-labelledby="rsm-pdf-modal-title">
                <div class="rsm-modal-header">
                    <h3 id="rsm-pdf-modal-title">Report PDF</h3>
                    <button type="button" class="rsm-modal-close" id="rsm-pdf-modal-close" aria-label="Close">&times;</button>
                </div>
                <div class="rsm-modal-body rsm-pdf-body">
                    <iframe id="rsm-pdf-frame" class="rsm-pdf-frame" src="" title="Report file"></iframe>
                </div>
            </div>
        </div>

    </div>
</div>