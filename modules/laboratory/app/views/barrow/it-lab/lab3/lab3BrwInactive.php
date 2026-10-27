<?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>

<main class="main-content">

    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h3 class="mb-1">Inactive IT Lab 3 Borrowing</h3>

                <p class="text-muted mb-0">
                    Borrowing records that have been deactivated.
                </p>
            </div>

            <a href="<?= BASE_URL ?>/lab3-borrow"
               class="btn btn-secondary btn-sm">

                <i class="fas fa-arrow-left me-1"></i>
                Back to Borrowing
            </a>

        </div>


        <div class="card shadow-sm border-0 border-top border-4 border-secondary">

            <div class="card-body">

                <div class="table-responsive">

                    <table id="lab3InactiveBorrowTable"
                           class="table table-striped table-bordered"
                           style="width:100%">

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

                            <?php foreach ($rows as $row): ?>

                                <tr>

                                    <td><?= $row['id']; ?></td>
                                    <td><?= $row['laboratory']; ?></td>
                                    <td><?= $row['borrower_name']; ?></td>
                                    <td><?= $row['student_id']; ?></td>
                                    <td><?= $row['section']; ?></td>
                                    <td><?= $row['item_name']; ?></td>
                                    <td><?= $row['quantity']; ?></td>
                                    <td><?= $row['borrowed_date']; ?></td>
                                    <td><?= $row['expected_return']; ?></td>
                                    <td><?= $row['returned_date']; ?></td>

                                    <td>
                                        <?php if ($row['status'] == 'Returned'): ?>

                                            <span class="badge bg-success">
                                                Returned
                                            </span>

                                        <?php elseif ($row['status'] == 'Borrowed'): ?>

                                            <span class="badge bg-warning text-dark">
                                                Borrowed
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                <?= $row['status']; ?>
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                    <td>

                                        <div class="dropdown">

                                            <button class="btn btn-secondary btn-sm dropdown-toggle"
                                                    type="button"
                                                    data-bs-toggle="dropdown">
                                                Action
                                            </button>

                                            <ul class="dropdown-menu">

                                                <!-- View disabled -->
                                                <li>
                                                    <a href="#"
                                                       class="dropdown-item disabled"
                                                       aria-disabled="true"
                                                       tabindex="-1">

                                                        <i class="fas fa-eye me-2"></i>
                                                        View
                                                    </a>
                                                </li>

                                                <!-- Activate -->
                                                <li>
                                                    <a href="<?= BASE_URL ?>/lab3-borrow/activate/<?= $row['id']; ?>"
                                                       class="dropdown-item text-success"
                                                       onclick="return confirm('Are you sure you want to activate this borrowing record?');">

                                                        <i class="fas fa-check-circle me-2"></i>
                                                        Activate
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

        $('#lab3InactiveBorrowTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 30, 40]
        });

    });
</script>


<?php include __DIR__ . '/../../../includes/footer.php'; ?>