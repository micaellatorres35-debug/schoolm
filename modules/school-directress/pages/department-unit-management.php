<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/User.php';
include_once __DIR__ . '/../classes/Department.php';
include_once __DIR__ . '/../classes/Unit.php';

$user = new User();
$user_info = $user->userSession();

$department = new Department();
$departmentsWithDetails = $department->getDepartmentsWithDetails();

/* Group units by department (single query, no per-row lookups) */
$unitClass = new Unit();
$unitsByDept = [];
foreach ($unitClass->getAllUnitsWithHeads() as $u) {
    $unitsByDept[$u['department_id']][] = $u;
}

function dmInitials($name)
{
    $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
    $out = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $out .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    return $out !== '' ? $out : '?';
}
?>

<div class="module-header">
    <h1>Department & Unit Management</h1>
    <p>View departments, department heads, and staff counts within the system.</p>
</div>

<div class="module-content">

    <!-- ── Toolbar: search + export ── -->
    <div class="dm-toolbar">
        <h3>Departments</h3>
        <div class="dm-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="dmSearch" placeholder="Search by department or head…" autocomplete="off">
        </div>
        <button type="button" id="dmExportBtn" class="dm-btn-primary">Export CSV</button>
    </div>

    <!-- ── Table ── -->
    <div class="dm-table-wrap">
        <table class="dm-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Department</th>
                    <th>Department Head</th>
                    <th>Members</th>
                </tr>
            </thead>
            <tbody id="dmBody">
                <?php if (!empty($departmentsWithDetails)): ?>
                    <?php foreach ($departmentsWithDetails as $i => $dept):
                        $name   = $dept['department_name'] ?? '';
                        $head   = !empty($dept['department_head_name']) ? $dept['department_head_name'] : '';
                        $count  = (int) ($dept['employee_count'] ?? 0);
                        $rid    = 'dmd-' . $i;
                        $units  = $unitsByDept[$dept['department_id']] ?? [];
                    ?>
                    <tr class="dm-row"
                        data-detail="<?= htmlspecialchars($rid) ?>"
                        data-search="<?= htmlspecialchars(mb_strtolower($name . ' ' . $head)) ?>"
                        data-name="<?= htmlspecialchars($name) ?>"
                        data-head="<?= htmlspecialchars($head) ?>"
                        data-count="<?= $count ?>">
                        <td>
                            <div class="dm-toggle">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </td>
                        <td>
                            <div class="dm-dept">
                                <div class="dm-avatar"><?= htmlspecialchars(dmInitials($name)) ?></div>
                                <div>
                                    <div class="dm-name"><?= htmlspecialchars($name) ?></div>
                                    <div class="dm-meta"><?= $count ?> member<?= $count === 1 ? '' : 's' ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= $head !== '' ? htmlspecialchars($head) : '—' ?></td>
                        <td><span class="dm-badge <?= $count > 0 ? 'dm-badge--active' : 'dm-badge--muted' ?>"><?= $count ?></span></td>
                    </tr>
                    <tr class="dm-detail-row" id="<?= htmlspecialchars($rid) ?>">
                        <td class="dm-detail-cell" colspan="4">
                            <div class="dm-detail-inner">
                                <div>
                                    <div class="dm-fl__k">Department Name</div>
                                    <div class="dm-fl__v"><?= htmlspecialchars($name) ?></div>
                                </div>
                                <div>
                                    <div class="dm-fl__k">Department Head</div>
                                    <div class="dm-fl__v"><?= $head !== '' ? htmlspecialchars($head) : '—' ?></div>
                                </div>
                                <div>
                                    <div class="dm-fl__k">Members</div>
                                    <div class="dm-fl__v"><?= $count ?></div>
                                </div>
                                <div class="dm-fl--wide">
                                    <div class="dm-fl__k">Units (<?= count($units) ?>)</div>
                                    <div class="dm-fl__v">
                                        <?php if ($units): ?>
                                            <div class="dm-chips">
                                                <?php foreach ($units as $u): ?>
                                                    <span class="dm-chip<?= ($u['status'] ?? '') === 'Inactive' ? ' dm-chip--muted' : '' ?>"
                                                          title="<?= !empty($u['unit_head_name']) ? 'Head: ' . htmlspecialchars($u['unit_head_name']) : 'No unit head assigned' ?>">
                                                        <?= htmlspecialchars($u['unit_name']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="dmNoMatch" style="display:none;">
                        <td colspan="4"><div class="dm-empty">No departments match your search.</div></td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="4"><div class="dm-empty">No departments found.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Footer: "Showing X-Y of Z" + numbered pagination ── -->
    <div class="dm-footer">
        <span class="dm-showing" id="dmShowing"></span>
        <div class="dm-page-nav" id="dmPageNav"></div>
    </div>

</div>