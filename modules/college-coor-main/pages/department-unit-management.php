<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../../../auth/guard.php';

// Keep these two lists in step with DM_READ_ROLES / DM_WRITE_ROLES in DepartmentController.php.
// The controller is the real gate; this only decides what the page shows.
$dmReadRoles  = ['School Directress', 'Admin College Coordinator'];
$dmWriteRoles = ['School Directress', 'Admin College Coordinator'];

$dmRole     = (string) ($_SESSION['role_name'] ?? '');
$dmCanWrite = in_array($dmRole, $dmWriteRoles, true);

if (!in_array($dmRole, $dmReadRoles, true)) {
    ?>
    <div class="module-header">
        <h1>Department &amp; Unit Management</h1>
        <p>You do not have permission to view this page.</p>
    </div>
    <?php
    return;
}

if (empty($_SESSION['dm_csrf'])) {
    $_SESSION['dm_csrf'] = bin2hex(random_bytes(32));
}

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
?>

<div class="module-header">
    <h1>Department &amp; Unit Management</h1>
    <p>Manage the school's organizational structure.</p>
</div>

<div class="module-content dm-page" id="dmRoot"
     data-csrf="<?= $h($_SESSION['dm_csrf']) ?>"
     data-can-write="<?= $dmCanWrite ? '1' : '0' ?>">

    <!-- ══ Overview ══ -->
    <div id="dmOverview">

        <section class="dm-stat-grid" aria-label="Organization summary">
            <article class="dm-stat-card">
                <div>
                    <strong id="dmStatDepts">—</strong>
                    <span class="dm-stat-label">Total departments</span>
                    <span class="dm-stat-note" id="dmStatDeptsNote"></span>
                </div>
            </article>
            <article class="dm-stat-card">
                <div>
                    <strong id="dmStatUnits">—</strong>
                    <span class="dm-stat-label">Total units</span>
                    <span class="dm-stat-note" id="dmStatUnitsNote"></span>
                </div>
            </article>
            <article class="dm-stat-card">
                <div>
                    <strong id="dmStatEmployees">—</strong>
                    <span class="dm-stat-label">Total employees</span>
                    <span class="dm-stat-note">Active and probationary</span>
                </div>
            </article>
        </section>

        <!-- ── Actions + filters (one panel) ── -->
        <section class="dm-panel dm-control-panel" aria-label="Department directory controls">
            <div class="dm-toolbar">
                <div>
                    <h2>Department directory</h2>
                    <p>Search and manage departments and their units.</p>
                </div>
                <div class="dm-toolbar-actions">
                    <button type="button" class="dm-btn dm-btn-outline" id="dmExportBtn" data-dm-action="export">Export CSV</button>
                    <?php if ($dmCanWrite): ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dm-filters">
                <label class="dm-search">
                    <span class="dm-sr-only">Search departments and units</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="search" id="dmSearch" placeholder="Search departments or units…" autocomplete="off">
                </label>
                <label class="dm-select">
                    <span class="dm-sr-only">Filter by status</span>
                    <select id="dmStatus">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </label>
                <button type="button" class="dm-btn dm-btn-outline" data-dm-action="clear-filters">Clear filters</button>
            </div>
        </section>

        <!-- ── Results: department cards (separate from the controls) ── -->
        <section class="dm-results" aria-label="Departments">
            <p class="dm-state" id="dmListState" role="status" aria-live="polite" hidden></p>
            <div class="dm-grid" id="dmGrid" aria-live="polite"></div>
        </section>
    </div>

    <!-- ══ Department detail ══ -->
    <div id="dmDetail" hidden>
        <button type="button" class="dm-btn dm-btn-outline dm-back" id="dmBackBtn" data-dm-action="back">
            <span aria-hidden="true">←</span> Back to departments
        </button>

        <p class="dm-state" id="dmDetailState" role="status" aria-live="polite" hidden></p>

        <div id="dmDetailContent" hidden>
            <section class="dm-panel dm-detail-head">
                <div class="dm-detail-title">
                    <div class="dm-avatar dm-avatar-large" id="dmDetailAvatar" aria-hidden="true">?</div>
                    <div>
                        <h2 id="dmDetailName" tabindex="-1"></h2>
                        <span class="dm-status" id="dmDetailStatus"></span>
                        <p class="dm-detail-desc" id="dmDetailDesc"></p>
                        <p class="dm-detail-head-name" id="dmDetailHead"></p>
                    </div>
                </div>
                <div class="dm-detail-actions">
                    <?php if ($dmCanWrite): ?>
                    <button type="button" class="dm-btn dm-btn-outline" id="dmDetailEditBtn" data-dm-action="edit-dept">Edit department</button>
                    <button type="button" class="dm-btn dm-btn-primary" id="dmDetailAddUnitBtn" data-dm-action="add-unit-here">
                        <span aria-hidden="true">＋</span> Add unit
                    </button>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dm-detail-stats" aria-label="Department totals">
                <article class="dm-mini-stat"><span>Total units</span><strong id="dmDetailUnits">0</strong></article>
                <article class="dm-mini-stat"><span>Total employees</span><strong id="dmDetailEmployees">0</strong></article>
            </section>

            <section class="dm-panel">
                <h3 class="dm-section-title">Units</h3>
                <p class="dm-state" id="dmUnitsState" role="status" aria-live="polite" hidden></p>
                <div class="dm-unit-grid" id="dmUnitGrid"></div>
            </section>
        </div>
    </div>

    <!-- ══ Department form ══ -->
    <div class="dm-modal-backdrop" id="dmDeptModal" hidden>
        <section class="dm-modal" role="dialog" aria-modal="true" aria-labelledby="dmDeptTitle" tabindex="-1">
            <header class="dm-modal-header">
                <div>
                    <p class="dm-eyebrow">Organization</p>
                    <h2 id="dmDeptTitle">Add department</h2>
                </div>
                <button type="button" class="dm-icon-btn" data-dm-action="close-modal" aria-label="Close department form">×</button>
            </header>
            <form id="dmDeptForm" data-skip novalidate autocomplete="off">
                <input type="hidden" name="id" id="dmDeptId" value="">
                <div class="dm-modal-body">
                    <div class="dm-field">
                        <label for="dmDeptName">Department name <span class="dm-req" aria-hidden="true">*</span></label>
                        <input type="text" id="dmDeptName" name="name" maxlength="100" required aria-describedby="dmDeptNameErr">
                        <p class="dm-field-error" id="dmDeptNameErr" data-error-for="name" hidden></p>
                    </div>
                    <div class="dm-field">
                        <label for="dmDeptDesc">Description</label>
                        <textarea id="dmDeptDesc" name="description" rows="4" maxlength="1000" aria-describedby="dmDeptDescErr"></textarea>
                        <p class="dm-field-error" id="dmDeptDescErr" data-error-for="description" hidden></p>
                    </div>
                    <div class="dm-field">
                        <label for="dmDeptStatus">Status</label>
                        <select id="dmDeptStatus" name="status" aria-describedby="dmDeptStatusErr">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <p class="dm-field-error" id="dmDeptStatusErr" data-error-for="status" hidden></p>
                    </div>
                    <p class="dm-form-message" id="dmDeptMsg" role="alert"></p>
                </div>
                <footer class="dm-modal-footer">
                    <button type="button" class="dm-btn dm-btn-outline" data-dm-action="close-modal">Cancel</button>
                    <button type="submit" class="dm-btn dm-btn-primary" data-label="Save department">Save department</button>
                </footer>
            </form>
        </section>
    </div>

    <!-- ══ Unit form ══ -->
    <div class="dm-modal-backdrop" id="dmUnitModal" hidden>
        <section class="dm-modal" role="dialog" aria-modal="true" aria-labelledby="dmUnitTitle" tabindex="-1">
            <header class="dm-modal-header">
                <div>
                    <p class="dm-eyebrow">Organization</p>
                    <h2 id="dmUnitTitle">Add unit</h2>
                </div>
                <button type="button" class="dm-icon-btn" data-dm-action="close-modal" aria-label="Close unit form">×</button>
            </header>
            <form id="dmUnitForm" data-skip novalidate autocomplete="off">
                <input type="hidden" name="id" id="dmUnitId" value="">
                <div class="dm-modal-body">
                    <div class="dm-field">
                        <label for="dmUnitDept">Parent department <span class="dm-req" aria-hidden="true">*</span></label>
                        <select id="dmUnitDept" name="department_id" required aria-describedby="dmUnitDeptErr"></select>
                        <p class="dm-field-error" id="dmUnitDeptErr" data-error-for="department_id" hidden></p>
                    </div>
                    <div class="dm-field">
                        <label for="dmUnitName">Unit name <span class="dm-req" aria-hidden="true">*</span></label>
                        <input type="text" id="dmUnitName" name="name" maxlength="100" required aria-describedby="dmUnitNameErr">
                        <p class="dm-field-error" id="dmUnitNameErr" data-error-for="name" hidden></p>
                    </div>
                    <div class="dm-field">
                        <label for="dmUnitDesc">Description</label>
                        <textarea id="dmUnitDesc" name="description" rows="4" maxlength="1000" aria-describedby="dmUnitDescErr"></textarea>
                        <p class="dm-field-error" id="dmUnitDescErr" data-error-for="description" hidden></p>
                    </div>
                    <div class="dm-field">
                        <label for="dmUnitStatus">Status</label>
                        <select id="dmUnitStatus" name="status" aria-describedby="dmUnitStatusErr">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <p class="dm-field-error" id="dmUnitStatusErr" data-error-for="status" hidden></p>
                    </div>
                    <p class="dm-form-message" id="dmUnitMsg" role="alert"></p>
                </div>
                <footer class="dm-modal-footer">
                    <button type="button" class="dm-btn dm-btn-outline" data-dm-action="close-modal">Cancel</button>
                    <button type="submit" class="dm-btn dm-btn-primary" data-label="Save unit">Save unit</button>
                </footer>
            </form>
        </section>
    </div>

    <!-- ══ Unit details ══ -->
    <div class="dm-modal-backdrop" id="dmUnitDetailsModal" hidden>
        <section class="dm-modal" role="dialog" aria-modal="true" aria-labelledby="dmUnitDetailsTitle" tabindex="-1">
            <header class="dm-modal-header">
                <div>
                    <p class="dm-eyebrow">Unit details</p>
                    <h2 id="dmUnitDetailsTitle">Unit</h2>
                    <span class="dm-status" id="dmUnitDetailsStatus"></span>
                </div>
                <button type="button" class="dm-icon-btn" data-dm-action="close-modal" aria-label="Close unit details">×</button>
            </header>
            <div class="dm-modal-body">
                <p class="dm-state" id="dmUnitDetailsState" role="status" aria-live="polite">Loading…</p>
                <div id="dmUnitDetailsContent" hidden>
                    <dl class="dm-info-grid">
                        <div><dt>Department</dt><dd id="dmUdDept"></dd></div>
                        <div><dt>Unit head</dt><dd id="dmUdHead"></dd></div>
                        <div><dt>Assigned employees</dt><dd id="dmUdCount"></dd></div>
                        <div class="dm-span-2"><dt>Description</dt><dd id="dmUdDesc"></dd></div>
                    </dl>
                    <h3 class="dm-section-title">Employees in this unit</h3>
                    <p class="dm-state" id="dmUdEmpState" hidden></p>
                    <ul class="dm-emp-list" id="dmUdEmployees"></ul>
                </div>
            </div>
            <footer class="dm-modal-footer">
                <button type="button" class="dm-btn dm-btn-outline" data-dm-action="close-modal">Close</button>
                <?php if ($dmCanWrite): ?>
                <button type="button" class="dm-btn dm-btn-primary" id="dmUdEditBtn" data-dm-action="edit-unit-from-details">Edit unit</button>
                <?php endif; ?>
            </footer>
        </section>
    </div>

    <!-- ══ Confirm status change ══ -->
    <div class="dm-modal-backdrop" id="dmConfirmModal" hidden>
        <section class="dm-modal dm-modal-small" role="alertdialog" aria-modal="true" aria-labelledby="dmConfirmTitle" aria-describedby="dmConfirmText" tabindex="-1">
            <header class="dm-modal-header">
                <div>
                    <p class="dm-eyebrow">Please confirm</p>
                    <h2 id="dmConfirmTitle">Deactivate</h2>
                </div>
                <button type="button" class="dm-icon-btn" data-dm-action="close-modal" aria-label="Close">×</button>
            </header>
            <div class="dm-modal-body">
                <p id="dmConfirmText"></p>
                <p class="dm-form-message" id="dmConfirmMsg" role="alert"></p>
            </div>
            <footer class="dm-modal-footer">
                <button type="button" class="dm-btn dm-btn-outline" data-dm-action="close-modal">Cancel</button>
                <button type="button" class="dm-btn dm-btn-danger" id="dmConfirmBtn" data-dm-action="confirm-status">Deactivate</button>
            </footer>
        </section>
    </div>

    <div class="dm-toast" id="dmToast" role="status" aria-live="polite" hidden></div>
</div>