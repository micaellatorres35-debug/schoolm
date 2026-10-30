<?php
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Approval.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    /*User Class*/
    $userClass = new User();
    $userInfo = $userClass->userSession();
    $isDirectress = ($userInfo['role'] === 'School Directress');

    /*Approval Class*/
    $approvalClass = new Approval();
    $myDepartmentId = $isDirectress ? null : ($userInfo['department_id'] ?? null);
    $approvals = $approvalClass->getApprovals($myDepartmentId);

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

<div class="approval-module" data-is-directress="<?= $isDirectress ? '1' : '0' ?>">
    <div class="module-header">
        <h1>Approval & Decision Support</h1>
        <p>Create, submit, and manage approval requests directly within the system.</p>
    </div>

    <div class="module-content">

        <div class="approval-section-header">
            <h3>Approval Queue</h3>

            <div class="approval-header-actions">
                <div class="approval-filter">
                    <?php if ($isDirectress) : ?>
                        <select id="department-filter" class="ads-select">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept) : ?>
                                <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <select id="status-filter" class="ads-select">
                        <option value="">All Statuses</option>
                        <?php foreach ($statusLabels as $value => $label) : ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" id="approval-open-modal" class="approval-add-btn">
                    + Submit for Approval
                </button>
            </div>
        </div>

        <div class="approval-queue-section">
            <div class="ads-queue">
                <div class="table-responsive">
                    <table class="ads-table">
                        <thead>
                            <tr>
                                <th>Approval ID</th>
                                <th>Title</th>
                                <th>Submitted By</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                                <th class="actions-header">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($approvals)) : ?>
                                <?php foreach ($approvals as $approval) : ?>
                                    <tr data-approval-id="<?= htmlspecialchars($approval['approval_id']) ?>">
                                        <td><?= htmlspecialchars($approval['approval_id']) ?></td>
                                        <td><?= htmlspecialchars($approval['title'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($approval['submit_by'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($approval['department_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($approval['status']) ?>">
                                                <?= htmlspecialchars($statusLabels[$approval['status']] ?? $approval['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($approval['submitted_on'] ?? 'N/A') ?></td>
                                        <td class="actions-cell">
                                            <button type="button" class="attachment-link approval-view-btn" data-approval-id="<?= htmlspecialchars($approval['approval_id']) ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr class="no-data">
                                    <td colspan="7" class="muted">No approvals found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create / Submit Modal -->
    <div class="approval-modal-overlay" id="approval-modal-overlay">
        <div class="approval-modal" role="dialog" aria-modal="true" aria-labelledby="approval-modal-title">
            <div class="approval-modal-header">
                <h3 id="approval-modal-title">Submit for Approval</h3>
                <button type="button" class="approval-modal-close" id="approval-modal-close" aria-label="Close">&times;</button>
            </div>

            <div class="approval-modal-body">
                <form id="approval-form" enctype="multipart/form-data" data-skip>

                    <div class="approval-form-group">
                        <label for="approval-title">Title</label>
                        <input type="text" id="approval-title" name="title" placeholder="Enter request title" required>
                    </div>

                    <div class="approval-form-group">
                        <label for="approval-description">Description</label>
                        <textarea id="approval-description" name="description" rows="3" placeholder="What is being requested" required></textarea>
                    </div>

                    <div class="approval-form-group">
                        <label for="approval-justification">Justification</label>
                        <textarea id="approval-justification" name="justification" rows="3" placeholder="Why is this needed"></textarea>
                    </div>

                    <div class="approval-form-group">
                        <label for="approval-attachment">Attachment (optional)</label>
                        <input class="file-btn" type="file" id="approval-attachment" name="attachment"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <small class="muted">PDF, Word, Excel, JPG or PNG &middot; up to 10 MB</small>
                    </div>

                    <input type="hidden" id="approval-id" name="approval_id" value="">

                    <div id="approval-form-error" class="approval-error" style="display:none;"></div>

                    <div class="approval-form-group approval-actions">
                        <button type="submit" class="approval-submit-btn" id="approval-submit-btn">
                            Submit for Approval
                        </button>
                        <button type="button" class="approval-draft-btn" id="approval-draft-btn">
                            Save as Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail / Review Modal -->
    <div class="approval-modal-overlay" id="approval-view-modal-overlay">
        <div class="approval-modal approval-modal-lg" role="dialog" aria-modal="true" aria-labelledby="approval-view-modal-title">
            <div class="approval-modal-header">
                <h3 id="approval-view-modal-title">Approval Request Details</h3>
                <button type="button" class="approval-modal-close" id="approval-view-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="approval-modal-body" id="approval-view-modal-body">
                <!-- populated by JS -->
            </div>
        </div>
    </div>

    <!-- File Viewer Modal (generated PDF and attachments) -->
    <div class="approval-modal-overlay" id="approval-pdf-modal-overlay">
        <div class="approval-modal approval-modal-lg" role="dialog" aria-modal="true" aria-labelledby="approval-pdf-modal-title">
            <div class="approval-modal-header">
                <h3 id="approval-pdf-modal-title">Approval PDF</h3>
                <button type="button" class="approval-modal-close" id="approval-pdf-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="approval-modal-body approval-pdf-body">
                <iframe id="approval-pdf-frame" class="approval-pdf-frame" src="" title="Approval file"></iframe>
            </div>
        </div>
    </div>

</div>