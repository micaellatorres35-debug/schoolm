<?php include __DIR__ . '/partials/header.php'; ?>
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main-content bg-light">
    <div class="schp-module-header">
        <h2>Scholarship</h2>
    </div>

    <div class="module-content">
        <div class="schp-summary-row">
            <div class="schp-card schp-student-card">
                <div class="schp-student-info">
                    <div class="schp-student-id"><?= htmlspecialchars($studentNumber) ?></div>
                    <div class="schp-student-name"><?= htmlspecialchars($fullName) ?></div>
                    <div class="schp-student-meta">
                        <?= htmlspecialchars($yearLevel) ?> | <?= htmlspecialchars($courseName) ?><br>
                        <?= htmlspecialchars($syLabel) ?> <?= htmlspecialchars($semLabel) ?>
                    </div>
                </div>
                <div class="schp-card-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
            </div>

            <div class="schp-card schp-grant-card">
                <div class="schp-grant-label"><?= htmlspecialchars($scholarshipName) ?></div>
                <div class="schp-grant-amount-label">Total Amount</div>
                <div class="schp-grant-amount">
                    ₱<?= number_format((float) $totalAmount, 2) ?>
                </div>
                <div class="schp-card-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>
        </div>

        <div class="schp-card schp-transactions-card">
            <h3 class="schp-transactions-title">SCHOLARSHIP TRANSACTIONS</h3>

            <table class="schp-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="4" class="schp-no-data">No Data Found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['particular'] ?? '') ?></td>
                                <td>₱<?= number_format((float) ($row['amount'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/scholarship.js"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/scholarship.css">
<?php include __DIR__ . '/partials/footer.php'; ?>