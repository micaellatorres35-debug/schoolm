<?php
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Issues.php';
    include_once __DIR__ . '/../classes/User.php';

    /*User Class*/
    $userClass = new User();
    $userInfo = $userClass->userSession();
    $isDirectress = ($userInfo['role'] === 'School Directress');

    /*Issues Class*/
    $issuesClass = new Issues();
    $myDepartmentId = $isDirectress ? null : ($userInfo['department_id'] ?? null);
    $concerns = $issuesClass->getConcerns($myDepartmentId);
    $departments = $issuesClass->getDepartments();

    $statusLabels = [
        'draft'     => 'Draft',
        'submitted' => 'Submitted',
        'reviewed'  => 'Reviewed',
        'resolved'  => 'Resolved',
        'dismissed' => 'Dismissed',
    ];
?>

<div class="module-header">
    <h1>Concerns & Issue Tracking</h1>
    <p>Log, review, and resolve concerns directly within the system.</p>
</div>

<div class="module-content" data-is-directress="<?= $isDirectress ? '1' : '0' ?>">
    <div class="concerns-list-head">
        <h3>Issue List</h3>

        <div class="concerns-header-actions">
            <div class="concerns-filters">
                <?php if ($isDirectress) : ?>
                    <select id="concern-department-filter">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept) : ?>
                            <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <select id="concern-filter">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusLabels as $value => $label) : ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>

                <input
                    class="concern-search"
                    id="concern-search"
                    type="search"
                    placeholder="Search by title or submitter"
                >
            </div>

            <button type="button" id="concern-open-modal" class="concern-add-btn">+ Log Concern</button>
        </div>
    </div>

    <div class="concerns-list">
        <div class="concern-table-wrapper">
            <table class="concern-table">
                <thead>
                    <tr>
                        <th>Concern ID</th>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th>Submitted On</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($concerns)) : ?>
                        <?php foreach ($concerns as $concern) : ?>
                            <tr data-issue-id="<?= htmlspecialchars($concern['issue_id']) ?>">
                                <td><?= htmlspecialchars($concern['issue_id']) ?></td>
                                <td><?= htmlspecialchars($concern['title'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($concern['department_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($concern['submitted_by'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($concern['status']) ?>">
                                        <?= htmlspecialchars($statusLabels[$concern['status']] ?? $concern['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($concern['submitted_on'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="actions">
                                        <button type="button" class="btn-view concern-view-btn" data-issue-id="<?= htmlspecialchars($concern['issue_id']) ?>">View</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="muted">No concerns found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Log Concern Modal -->
    <div class="concern-modal-overlay" id="concern-modal-overlay">
        <div class="concern-modal" role="dialog" aria-modal="true" aria-labelledby="concern-modal-title">
            <div class="concern-modal-header">
                <h3 id="concern-modal-title">Log Concern</h3>
                <button type="button" class="concern-modal-close" id="concern-modal-close" aria-label="Close">&times;</button>
            </div>

            <div class="concern-modal-body">
                <form id="concern-log-form" enctype="multipart/form-data" data-skip>

                    <div class="form-group">
                        <label for="concern-title">Title</label>
                        <input type="text" id="concern-title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="concern-details">Details</label>
                        <textarea id="concern-details" name="details" rows="4" placeholder="Describe the concern" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="concern-desired-resolution">Desired Resolution <span style="font-weight:400; color:var(--color5);">(optional)</span></label>
                        <textarea id="concern-desired-resolution" name="desired_resolution" rows="3" placeholder="What outcome are you hoping for"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="concern-file">
                            Attachment
                            <span style="font-weight:400; color:var(--color5);">(optional)</span>
                        </label>

                        <input
                            class="file-btn"
                            id="concern-file"
                            name="file"
                            type="file"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                        >
                        <small class="muted">PDF, Word, Excel, JPG or PNG &middot; up to 10 MB</small>
                    </div>

                    <input type="hidden" id="concern-issue-id" name="issue_id" value="">

                    <div id="concern-form-error" style="display:none; color:red; font-size:0.85rem; margin-bottom:0.6rem;"></div>

                    <div class="form-actions">
                        <button type="submit" class="btn-log" id="concern-submit-btn">Log Concern</button>
                        <button type="button" class="btn-cancel" id="concern-draft-btn">Save as Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail / Review Modal -->
    <div class="concern-modal-overlay" id="concern-view-modal-overlay">
        <div class="concern-modal concern-modal-lg" role="dialog" aria-modal="true" aria-labelledby="concern-view-modal-title">
            <div class="concern-modal-header">
                <h3 id="concern-view-modal-title">Concern Details</h3>
                <button type="button" class="concern-modal-close" id="concern-view-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="concern-modal-body" id="concern-view-modal-body">
                <!-- populated by JS -->
            </div>
        </div>
    </div>

    <!-- File Viewer Modal (generated PDF and attachments) -->
    <div class="concern-modal-overlay" id="concern-pdf-modal-overlay">
        <div class="concern-modal concern-modal-lg" role="dialog" aria-modal="true" aria-labelledby="concern-pdf-modal-title">
            <div class="concern-modal-header">
                <h3 id="concern-pdf-modal-title">Concern PDF</h3>
                <button type="button" class="concern-modal-close" id="concern-pdf-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="concern-modal-body concern-pdf-body">
                <iframe id="concern-pdf-frame" class="concern-pdf-frame" src="" title="Concern file"></iframe>
            </div>
        </div>
    </div>
</div>