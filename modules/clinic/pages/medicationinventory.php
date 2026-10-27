<?php
require_once __DIR__ . '/../classes/Medication.php';

$medication = new Medication();
$message = '';
$messageType = '';

/* ---------- Handle form actions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['medication_id'] ?? 0);

    try {
        if ($action === 'save') {
            $errors = $medication->validate($_POST);
            if ($errors) {
                $message = implode(' ', $errors);
                $messageType = 'error';
            } elseif ($id > 0) {
                $medication->update($id, $_POST);
                $message = 'Medicine updated.';
                $messageType = 'success';
            } else {
                $medication->create($_POST);
                $message = 'Medicine added to inventory.';
                $messageType = 'success';
            }
        } elseif ($action === 'archive' && $id > 0) {
            $medication->archive($id);
            $message = 'Medicine removed from inventory.';
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Could not save changes. Please try again.';
        $messageType = 'error';
    }
}

/* ---------- Search, filter, pagination ---------- */
$search  = trim($_GET['q'] ?? '');
$filter  = $_GET['status'] ?? 'all';
$perPage = 10;
$total   = $medication->countAll($search, $filter);
$pages   = max(1, (int) ceil($total / $perPage));
$current = min(max(1, (int) ($_GET['p'] ?? 1)), $pages);
$offset  = ($current - 1) * $perPage;

$items   = $medication->getAll($search, $filter, $perPage, $offset);
$summary = $medication->getSummary();

function pageUrl(int $p): string {
    return '?' . http_build_query(array_merge($_GET, ['p' => $p]));
}

$filters = [
    'all' => 'All medicines', 'low' => 'Low stock', 'out' => 'Out of stock',
    'expiring' => 'Expiring in 30 days', 'expired' => 'Expired',
];
?>

<div class="module-header">
    <h1>Medication Inventory</h1>
</div>

<div class="module-content">

    <!-- Summary -->
    <div class="stat-row">
        <div class="stat-card"><strong><?= $summary['total'] ?></strong><span>Medicines</span></div>
        <div class="stat-card warn"><strong><?= $summary['low'] ?></strong><span>Low stock</span></div>
        <div class="stat-card danger"><strong><?= $summary['out_of_stock'] ?></strong><span>Out of stock</span></div>
        <div class="stat-card warn"><strong><?= $summary['expiring'] ?></strong><span>Expiring in 30 days</span></div>
        <div class="stat-card danger"><strong><?= $summary['expired'] ?></strong><span>Expired</span></div>
    </div>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Add / Edit form -->
    <div class="student-info">
        <h2 id="formTitle">Add medicine</h2>
        <form method="post" id="medForm">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="medication_id" id="medication_id" value="">

            <div class="form-row">
                <div class="form-col">
                    <label for="medicine_name">Medicine name<span class="required">*</span></label>
                    <input type="text" name="medicine_name" id="medicine_name" maxlength="100" required>
                </div>
                <div class="form-col">
                    <label for="generic_name">Generic name</label>
                    <input type="text" name="generic_name" id="generic_name" maxlength="100">
                </div>
                <div class="form-col">
                    <label for="category">Category</label>
                    <input type="text" name="category" id="category" maxlength="50" placeholder="e.g. Analgesic">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="dosage_form">Dosage form<span class="required">*</span></label>
                    <select name="dosage_form" id="dosage_form">
                        <?php foreach (Medication::FORMS as $form): ?>
                            <option value="<?= $form ?>"><?= $form ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label for="strength">Strength</label>
                    <input type="text" name="strength" id="strength" maxlength="30" placeholder="e.g. 500 mg">
                </div>
                <div class="form-col">
                    <label for="unit">Unit</label>
                    <input type="text" name="unit" id="unit" maxlength="20" value="pcs">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="quantity">Quantity<span class="required">*</span></label>
                    <input type="number" name="quantity" id="quantity" min="0" value="0" required>
                </div>
                <div class="form-col">
                    <label for="reorder_level">Reorder level<span class="required">*</span></label>
                    <input type="number" name="reorder_level" id="reorder_level" min="0" value="10" required>
                </div>
                <div class="form-col">
                    <label for="expiry_date">Expiry date</label>
                    <input type="date" name="expiry_date" id="expiry_date">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="batch_no">Batch no.</label>
                    <input type="text" name="batch_no" id="batch_no" maxlength="50">
                </div>
                <div class="form-col">
                    <label for="supplier">Supplier</label>
                    <input type="text" name="supplier" id="supplier" maxlength="100">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-full">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="2"></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" id="cancelEdit" style="display:none;">Cancel edit</button>
                <button type="submit" class="btn-primary" id="submitBtn">Add medicine</button>
            </div>
        </form>
    </div>

    <!-- Search and filter -->
    <form method="get" class="form-group">
        <?php foreach ($_GET as $key => $value): if (!in_array($key, ['q', 'status', 'p'], true)): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
        <?php endif; endforeach; ?>
        <label for="q">Find a medicine</label>
        <div class="search-container">
            <input type="text" class="search-input" name="q" id="q"
                   value="<?= htmlspecialchars($search) ?>" placeholder="Name, generic name, or category">
            <select class="search-select" name="status" style="flex:1;">
                <?php foreach ($filters as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $filter === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary">Search</button>
        </div>
    </form>

    <!-- Table -->
    <div class="table-info">
        <span>Showing <?= count($items) ?> of <?= $total ?> medicines</span>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Form</th>
                    <th>Stock</th>
                    <th>Reorder at</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$items): ?>
                <tr><td colspan="7" class="no-data">No medicines found. Add one using the form above.</td></tr>
            <?php else: foreach ($items as $row):
                [$statusLabel, $statusClass] = Medication::getStatus($row);
                $rowClass = $statusClass === 'badge-danger' ? 'row-bad' : ($statusClass === 'badge-warning' ? 'row-low' : '');
            ?>
                <tr class="<?= $rowClass ?>">
                    <td>
                        <strong><?= htmlspecialchars($row['medicine_name']) ?></strong>
                        <?php if ($row['strength']): ?> <?= htmlspecialchars($row['strength']) ?><?php endif; ?>
                        <?php if ($row['generic_name']): ?><br><small><?= htmlspecialchars($row['generic_name']) ?></small><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['dosage_form']) ?></td>
                    <td><?= (int) $row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                    <td><?= (int) $row['reorder_level'] ?></td>
                    <td><?= $row['expiry_date'] ? date('M d, Y', strtotime($row['expiry_date'])) : '-' ?></td>
                    <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                    <td class="action-cell">
                        <button type="button" class="btn-edit"
                                data-item="<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>">Edit</button>
                        <form method="post" onsubmit="return confirm('Remove this medicine from inventory?');">
                            <input type="hidden" name="action" value="archive">
                            <input type="hidden" name="medication_id" value="<?= (int) $row['medication_id'] ?>">
                            <button type="submit" class="btn-delete">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="pagination">
        <div class="pagination-info">Page <?= $current ?> of <?= $pages ?></div>
        <div class="pagination-controls">
            <?php if ($current > 1): ?>
                <a class="page-link" href="<?= pageUrl($current - 1) ?>">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $pages; $i++):
                if ($i === 1 || $i === $pages || abs($i - $current) <= 2):
                    if ($i === $current): ?>
                        <span class="page-current"><?= $i ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= pageUrl($i) ?>"><?= $i ?></a>
                    <?php endif;
                elseif (abs($i - $current) === 3): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif;
            endfor; ?>
            <?php if ($current < $pages): ?>
                <a class="page-link" href="<?= pageUrl($current + 1) ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const fields = ['medicine_name', 'generic_name', 'category', 'dosage_form', 'strength', 'unit',
                    'quantity', 'reorder_level', 'expiry_date', 'batch_no', 'supplier', 'notes'];
    const form = document.getElementById('medForm');
    const cancelBtn = document.getElementById('cancelEdit');

    function resetForm() {
        form.reset();
        document.getElementById('medication_id').value = '';
        document.getElementById('formTitle').textContent = 'Add medicine';
        document.getElementById('submitBtn').textContent = 'Add medicine';
        cancelBtn.style.display = 'none';
    }

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const item = JSON.parse(btn.dataset.item);
            document.getElementById('medication_id').value = item.medication_id;
            fields.forEach(function (f) { document.getElementById(f).value = item[f] || ''; });
            document.getElementById('formTitle').textContent = 'Edit medicine';
            document.getElementById('submitBtn').textContent = 'Save changes';
            cancelBtn.style.display = '';
            form.scrollIntoView({ behavior: 'smooth' });
        });
    });

    cancelBtn.addEventListener('click', resetForm);

    // Prevent the "resubmit form?" prompt on refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
})();
</script>