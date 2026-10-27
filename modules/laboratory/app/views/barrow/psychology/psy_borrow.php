<?php include  __DIR__ . '/../../includes/sidebar.php'; ?>
<?php include  __DIR__ . '/../../includes/header.php'; ?>


<main class="main-content">
    <div class="container-fluid px-4">
        <h1 class="h3 mb-2 text-gray-800">Borrowing</h1>
        <p class="mb-4">Psychology Laboratory</p>

        <div class="card mb-4 card shadow-sm border-0 border-top border-4 border-secondary shadow-lg p-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Borrowing
                </div>

                <button
                    class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#psyAddBorrowModal">
                    <i class="fas fa-plus me-1"></i> Create New
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="psyBorrowTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Laboratory</th>
                                <th>Borrower Name</th>
                                <th>Student ID</th>
                                <th>Section</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Borrowed Date</th>
                                <th>Expected Return</th>
                                <th>Returned Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrows as $row): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['laboratory'] ?></td>
                                    <td><?= $row['borrower_name'] ?></td>
                                    <td><?= $row['student_id'] ?></td>
                                    <td><?= $row['section'] ?></td>
                                    <td><?= $row['item_name'] ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= $row['borrowed_date'] ?></td>
                                    <td><?= $row['expected_return'] ?></td>
                                    <td><?= $row['returned_date'] ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'Returned'): ?>
                                            <span class="badge bg-success">Returned</span>
                                        <?php elseif ($row['status'] == 'Borrowed'): ?>
                                            <span class="badge bg-warning text-dark">Borrowed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= $row['status'] ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Action
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="#"
                                                        class="dropdown-item viewBtn"
                                                        data-id="<?= $row['id']; ?>"
                                                        data-bs-toggle="modal">
                                                        <i class="fas fa-eye me-2"></i> View
                                                    </a>
                                                </li>

                                                <li>
                                                    <a href="#"
                                                        class="dropdown-item editBtn"
                                                        data-id="<?= $row['id']; ?>">
                                                        <i class="fas fa-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a href="#"
                                                        class="dropdown-item text-danger deleteBtn"
                                                        data-id="<?= $row['id']; ?>">
                                                        <i class="fas fa-trash me-2"></i>
                                                        Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>



<script>
    $(document).ready(function() {
        $('#psyBorrowTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 30, 40],
        });
    });
</script>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>

<script src="<?= BASE_URL ?>/js/psyBorrow.js"></script>

<?php require __DIR__ . '/psyAddBorrowModal.php'; ?>
<?php require __DIR__ . '/psyViewBorrowModal.php'; ?>
<?php require __DIR__ . '/psyEditBorrowModal.php'; ?>


<?php include  __DIR__ . '/../../includes/footer.php'; ?>