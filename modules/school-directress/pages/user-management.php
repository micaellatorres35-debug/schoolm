<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../../../auth/guard.php';
require_once __DIR__ . '/../classes/User.php';

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

if (($_SESSION['role_name'] ?? '') !== 'School Directress') {
    ?>
    <div class="module-header">
        <h1>User &amp; Access Management</h1>
        <p>You do not have permission to view this page.</p>
    </div>
    <?php
    return;
}

// CSRF token for the write actions (checked in UserController.php)
if (empty($_SESSION['um_csrf'])) {
    $_SESSION['um_csrf'] = bin2hex(random_bytes(32));
}

$umModel = new User();
$umOpts  = $umModel->getFormOptions();

/* Option renderers (closures so repeated includes never redeclare a function) */
$umRoleOptions = function (string $placeholder) use ($umOpts, $h) {
    $out = '<option value="">' . $h($placeholder) . '</option>';
    foreach ($umOpts['roles'] as $r) {
        $out .= '<option value="' . (int) $r['role_id'] . '">' . $h($r['role_name']) . '</option>';
    }
    return $out;
};
$umDeptOptions = function (string $placeholder) use ($umOpts, $h) {
    $out = '<option value="">' . $h($placeholder) . '</option>';
    foreach ($umOpts['departments'] as $d) {
        $out .= '<option value="' . (int) $d['department_id'] . '">' . $h($d['department_name']) . '</option>';
    }
    return $out;
};
$umUnitOptions = function (string $placeholder) use ($umOpts, $h) {
    $out = '<option value="">' . $h($placeholder) . '</option>';
    foreach ($umOpts['units'] as $u) {
        $out .= '<option value="' . (int) $u['unit_id'] . '" data-department="' . (int) $u['department_id'] . '">'
              . $h($u['unit_name']) . '</option>';
    }
    return $out;
};
?>

<div class="module-header">
    <h1>User &amp; Access Management</h1>
    <p>Manage employee accounts, roles, and account activity.</p>
</div>

<div class="module-content um-page" id="umRoot" data-csrf="<?= $h($_SESSION['um_csrf']) ?>">

    <!-- ── Summary ── -->
    <section class="um-stat-grid" aria-label="Account summary">
        <article class="um-stat-card">
            <span class="um-stat-label">Total users</span>
            <strong id="umStatTotal">—</strong>
            <span class="um-stat-note">All registered accounts</span>
        </article>
        <article class="um-stat-card">
            <span class="um-stat-label">Active accounts</span>
            <strong id="umStatActive">—</strong>
            <span class="um-stat-note">Can access the system</span>
        </article>
        <article class="um-stat-card">
            <span class="um-stat-label">Deactivated accounts</span>
            <strong id="umStatInactive">—</strong>
            <span class="um-stat-note">Access disabled</span>
        </article>
    </section>

    <!-- ── Directory ── -->
     <div class="um-directory-header">
        <div class="um-directory-toolbar">
            <div>
                <h2>Employee directory</h2>
                <p>Search and manage registered user accounts.</p>
            </div>
            <div class="um-toolbar-actions">
                <div class="um-view-switch" role="group" aria-label="Directory view">
                    <button type="button" class="um-view-btn is-active" data-um-action="view-switch" data-view="cards" aria-pressed="true">Cards</button>
                    <button type="button" class="um-view-btn" data-um-action="view-switch" data-view="table" aria-pressed="false">Table</button>
                </div>
                <button type="button" class="um-btn um-btn-primary" id="umAddBtn" data-um-action="open-create">
                    <span aria-hidden="true">＋</span> Add user
                </button>
            </div>
        </div>
        <div class="um-filters">
            <label class="um-search">
                <span class="um-sr-only">Search users</span>
                <span aria-hidden="true">⌕</span>
                <input id="umSearch" type="search" placeholder="Search name, employee ID, email, or role..." autocomplete="off">
            </label>
            <label class="um-filter-field">
                <span class="um-sr-only">Filter by account status</span>
                <select id="umStatus">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="deactivated">Deactivated</option>
                </select>
            </label>
            <label class="um-filter-field">
                <span class="um-sr-only">Filter by role</span>
                <select id="umRole"><?= $umRoleOptions('All roles') ?></select>
            </label>
            <label class="um-filter-field">
                <span class="um-sr-only">Filter by department</span>
                <select id="umDepartment"><?= $umDeptOptions('All departments') ?></select>
            </label>
            <label class="um-filter-field">
                <span class="um-sr-only">Filter by unit</span>
                <select id="umUnit"><?= $umUnitOptions('All units') ?></select>
            </label>
            <button type="button" class="um-btn um-btn-quiet" data-um-action="clear-filters">Clear filters</button>
        </div>
     </div>
        <p class="um-empty-state" id="umEmpty" role="status" aria-live="polite">Loading users…</p>

        <!-- Cards and table rows are rendered by user_management.js -->
        <div class="um-card-grid" id="umCardGrid" hidden></div>

        <div class="um-table-wrap" id="umTableWrap" hidden>
            <table class="um-table">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Role</th>
                        <th scope="col">Department / Unit</th>
                        <th scope="col">Last login</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="um-sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="umTableBody"></tbody>
            </table>
        </div>

        <div class="um-footer">
            <span class="um-showing" id="umShowing"></span>
            <div class="um-page-nav" id="umPageNav"></div>
        </div>

    <!-- ══ Modal 1: Employee details ══ -->
    <div class="um-modal-backdrop" id="umDetailsModal" hidden>
        <section class="um-modal um-modal-large" role="dialog" aria-modal="true" aria-labelledby="umDetailsTitle" tabindex="-1">
            <header class="um-modal-header">
                <div class="um-profile-heading">
                    <div class="um-avatar um-avatar-large" id="umDetailAvatar" aria-hidden="true">?</div>
                    <div>
                        <p class="um-eyebrow">Employee profile</p>
                        <h2 id="umDetailsTitle">Employee details</h2>
                        <p class="um-subtitle" id="umDetailSubline"></p>
                        <span class="um-status" id="umDetailStatus"></span>
                    </div>
                </div>
                <button class="um-icon-btn" type="button" data-um-action="close-modal" aria-label="Close employee details">×</button>
            </header>

            <nav class="um-modal-tabs" aria-label="Employee details sections" role="tablist">
                <button type="button" class="um-tab is-active" role="tab" aria-selected="true" aria-controls="umTabOverview" id="umTabBtnOverview" data-um-action="tab" data-tab="overview">Overview</button>
                <button type="button" class="um-tab" role="tab" aria-selected="false" aria-controls="umTabActivity" id="umTabBtnActivity" data-um-action="tab" data-tab="activity" tabindex="-1">Recent activity</button>
                <button type="button" class="um-tab" role="tab" aria-selected="false" aria-controls="umTabAccess" id="umTabBtnAccess" data-um-action="tab" data-tab="access" tabindex="-1">Access</button>
            </nav>

            <div class="um-modal-body">
                <p class="um-notice-text" id="umDetailsNotice" role="status" aria-live="polite">Loading details…</p>

                <div id="umDetailsContent" hidden>
                    <section class="um-tab-panel" id="umTabOverview" role="tabpanel" aria-labelledby="umTabBtnOverview">
                        <div class="um-modal-summary-grid">
                            <article class="um-summary-tile"><span>Last successful login</span><strong id="umDetailLastLogin"></strong></article>
                            <article class="um-summary-tile"><span>Account created</span><strong id="umDetailCreated"></strong></article>
                            <article class="um-summary-tile"><span>Recorded activities</span><strong id="umDetailActivityCount"></strong></article>
                        </div>
                        <div class="um-info-section">
                            <h3>Employee information</h3>
                            <dl class="um-info-grid">
                                <div><dt>Full name</dt><dd id="umInfoName"></dd></div>
                                <div><dt>Employee ID</dt><dd id="umInfoId"></dd></div>
                                <div><dt>Email address</dt><dd id="umInfoEmail"></dd></div>
                                <div><dt>Contact number</dt><dd id="umInfoContact"></dd></div>
                                <div><dt>Department</dt><dd id="umInfoDepartment"></dd></div>
                                <div><dt>Unit</dt><dd id="umInfoUnit"></dd></div>
                                <div><dt>Position</dt><dd id="umInfoPosition"></dd></div>
                                <div><dt>Last activity</dt><dd id="umInfoLastActivity"></dd></div>
                            </dl>
                        </div>
                    </section>

                    <section class="um-tab-panel" id="umTabActivity" role="tabpanel" aria-labelledby="umTabBtnActivity" hidden>
                        <div class="um-section-heading">
                            <div>
                                <h3>Recent activity</h3>
                                <p>Most recent actions recorded in the audit log for this account.</p>
                            </div>
                        </div>
                        <p class="um-notice-text" id="umActivityNotice" role="status" aria-live="polite" hidden></p>
                        <ol class="um-activity-list" id="umActivityList"></ol>
                        <div class="um-activity-more">
                            <button class="um-btn um-btn-secondary" type="button" id="umActivityMore" data-um-action="activity-more" hidden>Load more</button>
                        </div>
                        <p class="um-footnote">Only actions that were recorded are shown. Passwords and session data are never logged.</p>
                    </section>

                    <section class="um-tab-panel" id="umTabAccess" role="tabpanel" aria-labelledby="umTabBtnAccess" hidden>
                        <div class="um-info-section">
                            <h3>Account access</h3>
                            <dl class="um-info-grid">
                                <div><dt>Assigned role</dt><dd id="umAccessRole"></dd></div>
                                <div><dt>Account status</dt><dd id="umAccessStatus"></dd></div>
                                <div><dt>Department scope</dt><dd id="umAccessDepartment"></dd></div>
                                <div><dt>Unit scope</dt><dd id="umAccessUnit"></dd></div>
                                <div><dt>Last password change</dt><dd id="umAccessPwChanged"></dd></div>
                                <div><dt>Failed sign-in attempts</dt><dd id="umAccessFailed"></dd></div>
                                <div><dt>Lock status</dt><dd id="umAccessLock"></dd></div>
                            </dl>
                        </div>
                        <div class="um-info-section" id="umAccessDeactWrap" hidden>
                            <h3>Deactivation</h3>
                            <dl class="um-info-grid">
                                <div><dt>Deactivated on</dt><dd id="umAccessDeactAt"></dd></div>
                                <div><dt>Deactivated by</dt><dd id="umAccessDeactBy"></dd></div>
                                <div class="um-field-full"><dt>Reason</dt><dd id="umAccessDeactReason"></dd></div>
                            </dl>
                        </div>
                        <div class="um-access-actions">
                            <button class="um-btn um-btn-secondary" type="button" data-um-action="reset-password" id="umDetailResetBtn">Reset password</button>
                            <button class="um-btn um-btn-secondary" type="button" data-um-action="unlock" id="umDetailUnlockBtn" hidden>Unlock account</button>
                        </div>
                        <div class="um-notice">
                            <strong>Access note</strong>
                            <p>Access follows the assigned role and the employee's department and unit. Role changes may only apply from the user's next sign-in.</p>
                        </div>
                    </section>
                </div>
            </div>

            <footer class="um-modal-footer">
                <button class="um-btn um-btn-secondary" type="button" data-um-action="close-modal">Close</button>
                <button class="um-btn um-btn-secondary" type="button" data-um-action="edit-access" id="umManageAccessBtn">Manage access</button>
                <button class="um-btn um-btn-secondary" type="button" data-um-action="toggle-status" id="umDetailStatusBtn">Deactivate</button>
                <button class="um-btn um-btn-primary" type="button" data-um-action="edit-user" id="umEditFromDetailsBtn">Edit user</button>
            </footer>
        </section>
    </div>

    <!-- ══ Modal 2: Create user ══ -->
    <div class="um-modal-backdrop" id="umCreateModal" hidden>
        <section class="um-modal" role="dialog" aria-modal="true" aria-labelledby="umCreateTitle" tabindex="-1">
            <header class="um-modal-header">
                <div>
                    <p class="um-eyebrow">User &amp; access management</p>
                    <h2 id="umCreateTitle">Create user account</h2>
                    <p class="um-subtitle">Find the employee, then set their role and a starting password.</p>
                </div>
                <button class="um-icon-btn" type="button" data-um-action="close-modal" aria-label="Close create user form">×</button>
            </header>
            <form id="umCreateForm" data-skip autocomplete="off">
                <div class="um-modal-body">
                    <div class="um-form-grid">
                        <div class="um-form-field um-field-full">
                            <label for="umLookup">Find employee</label>
                            <input type="search" id="umLookup" placeholder="Type a name, employee ID, or email" autocomplete="off">
                            <select id="umEmployee" name="employee_id" size="5" aria-label="Matching employees" aria-describedby="umLookupHint" class="um-lookup-list"></select>
                            <p class="um-field-hint" id="umLookupHint" role="status" aria-live="polite">Type at least 2 characters to search. Employees who already have an account can't be selected.</p>
                        </div>
                        <label class="um-form-field">Role
                            <select name="role_id" id="umCreateRole" required><?= $umRoleOptions('Select role') ?></select>
                        </label>
                        <label class="um-form-field">Department
                            <select name="department_id" id="umCreateDept" required><?= $umDeptOptions('Select department') ?></select>
                        </label>
                        <label class="um-form-field">Unit
                            <select name="unit_id" id="umCreateUnit"><?= $umUnitOptions('Select unit (optional)') ?></select>
                        </label>
                        <label class="um-form-field">Email address
                            <input type="email" name="email" id="umCreateEmail" autocomplete="off" maxlength="100" required placeholder="employee@example.com">
                        </label>
                        <div class="um-form-field um-field-full">
                            <label for="umCreatePassword">Password</label>
                            <div class="um-pw-row">
                                <input type="password" name="password" id="umCreatePassword" autocomplete="new-password" minlength="12" maxlength="72" required placeholder="At least 12 characters">
                                <button type="button" class="um-btn um-btn-quiet" data-um-action="toggle-password" data-target="umCreatePassword">Show</button>
                                <button type="button" class="um-btn um-btn-quiet" data-um-action="generate-password" data-target="umCreatePassword">Generate</button>
                                <button type="button" class="um-btn um-btn-quiet" data-um-action="copy-password" data-target="umCreatePassword">Copy</button>
                            </div>
                            <p class="um-field-hint">Share the password securely. It can't be viewed again after saving.</p>
                        </div>
                    </div>
                    <p class="um-form-message" id="umCreateMsg" role="alert" aria-live="assertive"></p>
                </div>
                <footer class="um-modal-footer">
                    <button class="um-btn um-btn-secondary" type="button" data-um-action="close-modal">Cancel</button>
                    <button class="um-btn um-btn-primary" type="submit" data-label="Create account">Create account</button>
                </footer>
            </form>
        </section>
    </div>

    <!-- ══ Modal 3: Update user ══ -->
    <div class="um-modal-backdrop" id="umEditModal" hidden>
        <section class="um-modal" role="dialog" aria-modal="true" aria-labelledby="umEditTitle" tabindex="-1">
            <header class="um-modal-header">
                <div>
                    <p class="um-eyebrow">User &amp; access management</p>
                    <h2 id="umEditTitle">Edit user account</h2>
                    <p class="um-subtitle" id="umEditSubline">Loading…</p>
                </div>
                <button class="um-icon-btn" type="button" data-um-action="close-modal" aria-label="Close edit user form">×</button>
            </header>
            <form id="umEditForm" data-skip autocomplete="off">
                <input type="hidden" name="user_id" id="umEditUserId" value="">
                <fieldset class="um-fieldset" id="umEditFields" disabled>
                    <div class="um-modal-body">
                        <div class="um-form-grid">
                            <label class="um-form-field">Role
                                <select name="role_id" id="umEditRole" required><?= $umRoleOptions('Select role') ?></select>
                            </label>
                            <label class="um-form-field">Department
                                <select name="department_id" id="umEditDept" required><?= $umDeptOptions('Select department') ?></select>
                            </label>
                            <label class="um-form-field">Unit
                                <select name="unit_id" id="umEditUnit"><?= $umUnitOptions('Select unit (optional)') ?></select>
                            </label>
                            <label class="um-form-field">Email address
                                <input type="email" name="email" id="umEditEmail" autocomplete="off" maxlength="100" required>
                            </label>
                            <div class="um-form-field um-field-full">
                                <label for="umEditPassword">New password (optional)</label>
                                <div class="um-pw-row">
                                    <input type="password" name="password" id="umEditPassword" autocomplete="new-password" minlength="12" maxlength="72" placeholder="Leave blank to keep the current password">
                                    <button type="button" class="um-btn um-btn-quiet" data-um-action="toggle-password" data-target="umEditPassword">Show</button>
                                    <button type="button" class="um-btn um-btn-quiet" data-um-action="generate-password" data-target="umEditPassword">Generate</button>
                                    <button type="button" class="um-btn um-btn-quiet" data-um-action="copy-password" data-target="umEditPassword">Copy</button>
                                </div>
                                <p class="um-field-hint">Setting a new password also clears any sign-in lock.</p>
                            </div>
                        </div>
                        <p class="um-form-message" id="umEditMsg" role="alert" aria-live="assertive"></p>
                    </div>
                </fieldset>
                <footer class="um-modal-footer">
                    <button class="um-btn um-btn-secondary" type="button" data-um-action="close-modal">Cancel</button>
                    <button class="um-btn um-btn-primary" type="submit" id="umEditSubmit" data-label="Save changes">Save changes</button>
                </footer>
            </form>
        </section>
    </div>

    <!-- ══ Modal 4: Deactivate / reactivate ══ -->
    <div class="um-modal-backdrop" id="umStatusModal" hidden>
        <section class="um-modal um-modal-small" role="alertdialog" aria-modal="true" aria-labelledby="umStatusTitle" aria-describedby="umStatusText" tabindex="-1">
            <header class="um-modal-header">
                <div>
                    <p class="um-eyebrow">User &amp; access management</p>
                    <h2 id="umStatusTitle">Deactivate account</h2>
                </div>
                <button class="um-icon-btn" type="button" data-um-action="close-modal" aria-label="Close">×</button>
            </header>
            <div class="um-modal-body">
                <p id="umStatusText"></p>
                <label class="um-form-field um-status-reason" id="umStatusReasonWrap">Reason (optional)
                    <input type="text" id="umStatusReason" maxlength="255" placeholder="For example: resigned, on extended leave">
                </label>
                <p class="um-form-message" id="umStatusMsg" role="alert" aria-live="assertive"></p>
            </div>
            <footer class="um-modal-footer">
                <button class="um-btn um-btn-secondary" type="button" data-um-action="close-modal">Cancel</button>
                <button class="um-btn um-btn-danger" type="button" id="umStatusConfirm" data-um-action="status-confirm">Deactivate</button>
            </footer>
        </section>
    </div>

    <div class="um-toast" id="umToast" role="status" aria-live="polite" hidden></div>
</div>