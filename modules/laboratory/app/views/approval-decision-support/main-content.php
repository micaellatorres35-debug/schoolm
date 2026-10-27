<main class="main-content">
    <div class="container-fluid mt-4">

        <div class="card mb-4 card shadow-sm border-0 border-top border-4 border-secondary shadow-lg p-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Approval & Decision Support</h4>
                    <small class="text-muted">
                        Simple approval queue for submitted requests and reports
                    </small>
                </div>

                <div style="width:250px;">
                    <select id="department-filter" class="form-select">
                        <option value="">All Departments</option>

                        <?php foreach ($departments as $dept) : ?>

                            <option value="<?= $dept['department_id'] ?>"
                                <?= (($_GET['department'] ?? '') == $dept['department_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-end w-100 mb-2">
                    <button
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createApprovalDSModal">
                        <i class="bi bi-plus-lg me-1"></i>
                        Create
                    </button>
                </div>
                <div class="table-responsive">
                    <?php require __DIR__ . '/table.php'; ?>
                </div>
            </div>

        </div>

    </div>
</main>

<?php require __DIR__ . '/create-approvalDS-modal.php'; ?>
<?php require __DIR__ . '/ads-modal.php'; ?>