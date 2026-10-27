<?php
/**
 * scholarships.php
 * Module: Scholarship (Guidance & Counseling)
 *
 * Renders server-side directly from the Scholarship model, same pattern
 * as cases.php. ScholarshipController.php (AJAX) is only used afterward
 * — for filtering, opening the detail drawer, creating applications, and
 * moving them through the review workflow.
 */

include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Scholarship.php';

$scholarshipClass = new Scholarship();

$filters = [
    'search'            => trim($_GET['search'] ?? ''),
    'status'            => $_GET['status'] ?? '',
    'eligibility_basis' => $_GET['eligibility_basis'] ?? '',
    'coverage_type'     => $_GET['coverage_type'] ?? '',
    'counselor_id'      => $_GET['counselor_id'] ?? '',
];
$page     = max(1, (int) ($_GET['page'] ?? 1));
$pageSize = 10;

$result             = $scholarshipClass->getList($filters, $page, $pageSize);
$scholarships       = $result['rows'];
$totalScholarships  = $result['total'];
$totalPages         = (int) ceil($totalScholarships / $pageSize);

$counselors = $scholarshipClass->getCounselors();

function schol_status_badge_class($status) {
    return match ($status) {
        'Applied' => 'schol-badge--status-applied',
        'Under Review' => 'schol-badge--status-review',
        'Approved' => 'schol-badge--status-approved',
        'Rejected' => 'schol-badge--status-rejected',
        'Active' => 'schol-badge--status-active',
        'Completed' => 'schol-badge--status-completed',
        default => 'schol-badge--status-terminated', // Terminated
    };
}
?>

<div class="module-header">
    <h1>Scholarship</h1>
</div>

<div class="module-content">

    <!-- Toolbar -->
    <div class="schol-toolbar">
        <div class="schol-toolbar__filters">
            <div class="schol-search-box">
                <i class="fa fa-search"></i>
                <input type="text" id="scholSearchInput" placeholder="Search by student or scholarship..." value="<?= htmlspecialchars($filters['search']) ?>">
            </div>

            <select class="schol-filter-select" id="scholFilterStatus">
                <option value="" <?= $filters['status'] === '' ? 'selected' : '' ?>>All Status</option>
                <option value="Applied" <?= $filters['status'] === 'Applied' ? 'selected' : '' ?>>Applied</option>
                <option value="Under Review" <?= $filters['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                <option value="Approved" <?= $filters['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $filters['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="Active" <?= $filters['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Completed" <?= $filters['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Terminated" <?= $filters['status'] === 'Terminated' ? 'selected' : '' ?>>Terminated</option>
            </select>

            <select class="schol-filter-select" id="scholFilterEligibility">
                <option value="" <?= $filters['eligibility_basis'] === '' ? 'selected' : '' ?>>All Eligibility</option>
                <option value="Academic" <?= $filters['eligibility_basis'] === 'Academic' ? 'selected' : '' ?>>Academic</option>
                <option value="Financial Need" <?= $filters['eligibility_basis'] === 'Financial Need' ? 'selected' : '' ?>>Financial Need</option>
                <option value="Athletic" <?= $filters['eligibility_basis'] === 'Athletic' ? 'selected' : '' ?>>Athletic</option>
                <option value="Talent" <?= $filters['eligibility_basis'] === 'Talent' ? 'selected' : '' ?>>Talent</option>
                <option value="Other" <?= $filters['eligibility_basis'] === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>

            <select class="schol-filter-select" id="scholFilterCounselor">
                <option value="">All Counselors</option>
                <?php foreach ($counselors as $c): ?>
                    <option value="<?= htmlspecialchars($c['employee_id']) ?>" <?= $filters['counselor_id'] == $c['employee_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="schol-toolbar__actions">
            <button type="button" class="schol-btn" id="scholCreateBtn">+ New Application</button>
        </div>
    </div>

    <!-- Scholarship list -->
    <div class="schol-table-wrapper">
        <table class="schol-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Scholarship</th>
                    <th>Coverage</th>
                    <th>Eligibility</th>
                    <th>Status</th>
                    <th>Counselor</th>
                    <th>Applied</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($scholarships)): ?>
                <tr><td colspan="7" class="schol-table__empty">No scholarship applications found for the selected filters.</td></tr>
                <?php else: ?>
                    <?php foreach ($scholarships as $sc): ?>
                    <tr class="schol-row" data-scholarship-id="<?= htmlspecialchars($sc['scholarship_id']) ?>">
                        <td>
                            <div class="schol-student-name"><?= htmlspecialchars($sc['student_name']) ?></div>
                            <div class="schol-student-sub">#<?= htmlspecialchars($sc['student_number']) ?></div>
                        </td>
                        <td>
                            <div class="schol-name"><?= htmlspecialchars($sc['scholarship_name']) ?></div>
                            <?php if (!empty($sc['sponsor'])): ?>
                                <div class="schol-sponsor"><?= htmlspecialchars($sc['sponsor']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($sc['coverage_type']) ?></td>
                        <td><?= htmlspecialchars($sc['eligibility_basis']) ?></td>
                        <td><span class="schol-badge <?= schol_status_badge_class($sc['status']) ?>"><?= htmlspecialchars($sc['status']) ?></span></td>
                        <td><?= htmlspecialchars($sc['counselor_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($sc['applied_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="schol-pagination">
        <span>
            <?php if ($totalScholarships > 0): ?>
                Showing <?= (($page - 1) * $pageSize) + 1 ?>-<?= min($page * $pageSize, $totalScholarships) ?> of <?= $totalScholarships ?> applications
            <?php else: ?>
                No applications found
            <?php endif; ?>
        </span>
        <div class="schol-pagination__pages">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <button class="schol-pagination__page <?= $i === $page ? 'schol-pagination__page--active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></button>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <button class="schol-pagination__page" data-page="<?= $page + 1 ?>">&rsaquo;</button>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ============================================================
     Scholarship Detail Drawer (populated live via AJAX on row click)
     ============================================================ -->
<div class="schol-overlay" id="scholOverlay"></div>
<div class="schol-drawer" id="scholDrawer">

    <div class="schol-drawer__header">
        <div>
            <div class="schol-drawer__title" id="scholDrawerTitle"></div>
            <div class="schol-drawer__subtitle" id="scholDrawerSubtitle"></div>
            <div class="schol-drawer__badges" id="scholDrawerBadges"></div>
        </div>
        <button type="button" class="schol-drawer__close" id="scholDrawerCloseBtn">&times;</button>
    </div>

    <div class="schol-drawer__body">

        <div class="schol-quick-actions" id="scholQuickActions"></div>

        <div class="schol-field-grid">
            <div><div class="schol-field-label">Sponsor</div><div class="schol-field-value" data-field="sponsor"></div></div>
            <div><div class="schol-field-label">Award Amount</div><div class="schol-field-value" data-field="award_amount_display"></div></div>
            <div><div class="schol-field-label">Coverage Type</div><div class="schol-field-value" data-field="coverage_type"></div></div>
            <div><div class="schol-field-label">Eligibility Basis</div><div class="schol-field-value" data-field="eligibility_basis"></div></div>
            <div><div class="schol-field-label">Applied</div><div class="schol-field-value" data-field="applied_at_display"></div></div>
            <div><div class="schol-field-label">Reviewed</div><div class="schol-field-value" data-field="reviewed_at_display"></div></div>
        </div>

        <div class="schol-summary-label">Justification</div>
        <div class="schol-summary-box" data-field="justification"></div>

        <div class="schol-summary-label" id="scholReviewNotesLabel" style="display:none;">Review Notes</div>
        <div class="schol-summary-box" id="scholReviewNotesBox" data-field="review_notes" style="display:none;"></div>

        <div class="schol-attachment-row" id="scholAttachmentRow" style="display:none;">
            <a href="#" id="scholAttachmentLink" target="_blank" class="schol-attachment-link">
                <i class="fa fa-paperclip"></i> <span id="scholAttachmentName"></span>
            </a>
        </div>

    </div>
</div>

<!-- ============================================================
     New Application Modal
     ============================================================ -->
<div class="schol-modal-overlay" id="scholCreateModal">
    <div class="schol-modal">
        <div class="schol-modal__header">
            <h3>New Scholarship Application</h3>
            <button type="button" class="schol-drawer__close" id="scholCreateCloseBtn">&times;</button>
        </div>
        <!-- data-skip: this form submits via its own multipart fetch()
             (file attachment), not the router's JSON initForms() hijack -->
        <form id="scholCreateForm" data-skip enctype="multipart/form-data">
            <div class="schol-modal__body">
                <div class="schol-form-group">
                    <label>Student Number</label>
                    <input type="text" name="student_number" placeholder="e.g. 2023001" required>
                </div>
                <div class="schol-form-group">
                    <label>Counselor</label>
                    <select name="counselor_id" required>
                        <option value="">Select counselor</option>
                        <?php foreach ($counselors as $c): ?>
                            <option value="<?= htmlspecialchars($c['employee_id']) ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['position_name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="schol-form-group">
                    <label>Scholarship Name</label>
                    <input type="text" name="scholarship_name" placeholder="e.g. DOST Merit Scholarship" required>
                </div>
                <div class="schol-form-group">
                    <label>Sponsor</label>
                    <input type="text" name="sponsor" placeholder="e.g. DOST, LGU Caloocan">
                </div>
                <div class="schol-form-group">
                    <label>Coverage Type</label>
                    <select name="coverage_type" required>
                        <option value="Full">Full</option>
                        <option value="Partial">Partial</option>
                        <option value="Allowance">Allowance</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="schol-form-group">
                    <label>Award Amount</label>
                    <input type="number" name="award_amount" step="0.01" min="0" placeholder="Optional">
                </div>
                <div class="schol-form-group">
                    <label>Eligibility Basis</label>
                    <select name="eligibility_basis" required>
                        <option value="Academic">Academic</option>
                        <option value="Financial Need">Financial Need</option>
                        <option value="Athletic">Athletic</option>
                        <option value="Talent">Talent</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="schol-form-group">
                    <label>Justification</label>
                    <textarea name="justification" placeholder="Reason for applying..."></textarea>
                </div>
                <div class="schol-form-group">
                    <label>Attachment (optional)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                </div>
            </div>
            <div class="schol-modal__footer">
                <button type="button" class="schol-btn schol-btn--ghost schol-btn--sm" id="scholCreateCancelBtn">Cancel</button>
                <button type="submit" class="schol-btn schol-btn--sm">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     Review / Finalize Modal (shared shell — body swapped by action)
     ============================================================ -->
<div class="schol-modal-overlay" id="scholActionModal">
    <div class="schol-modal">
        <div class="schol-modal__header">
            <h3 id="scholActionTitle">Update</h3>
            <button type="button" class="schol-drawer__close" id="scholActionCloseBtn">&times;</button>
        </div>
        <form id="scholActionForm" data-skip>
            <div class="schol-modal__body" id="scholActionBody"></div>
            <div class="schol-modal__footer">
                <button type="button" class="schol-btn schol-btn--ghost schol-btn--sm" id="scholActionCancelBtn">Cancel</button>
                <button type="submit" class="schol-btn schol-btn--sm">Save</button>
            </div>
        </form>
    </div>
</div>