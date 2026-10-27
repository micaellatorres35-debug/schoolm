<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<main class="main-content">
    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Inactive HE Damages</h3>
                <p class="text-muted mb-0">
                    Damage records that have been deactivated.
                </p>
            </div>

            
            <a href="<?= BASE_URL ?>/he_damage"
                class="btn btn-secondary">

                <i class="fas fa-arrow-left me-2"></i>
                Back to Damages
            </a>
        </div>

        <div class="card mb-4 shadow-sm border-0 border-top border-4 border-secondary">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-box-archive me-2"></i>
                    Inactive Damages
                </h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="heInactiveDamageTable"
                        class="table table-striped table-bordered"
                        style="width:100%">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Laboratory</th>
                                <th>Issue</th>
                                <th>Damaged By</th>
                                <th>Date Reported</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($rows)): ?>
                                <?php foreach ($rows as $row): ?>

                                    <tr>
                                        <td><?= htmlspecialchars($row['id']); ?></td>
                                        <td><?= htmlspecialchars($row['item_name']); ?></td>
                                        <td><?= htmlspecialchars($row['laboratory']); ?></td>
                                        <td><?= htmlspecialchars($row['issue']); ?></td>
                                        <td><?= htmlspecialchars($row['reported_by']); ?></td>
                                        <td><?= htmlspecialchars($row['date_reported']); ?></td>

                                        <td>
                                            <?php
                                            $status = strtolower($row['status']);

                                            if ($status === 'damage') {
                                                $statusClass = 'bg-danger';
                                                $statusText = 'Damage';
                                            } elseif ($status === 'under inspection') {
                                                $statusClass = 'bg-warning text-dark';
                                                $statusText = 'Under Inspection';
                                            } elseif ($status === 'fixed') {
                                                $statusClass = 'bg-primary';
                                                $statusText = 'Fixed';
                                            } elseif ($status === 'working') {
                                                $statusClass = 'bg-success';
                                                $statusText = 'Working';
                                            } else {
                                                $statusClass = 'bg-secondary';
                                                $statusText = htmlspecialchars($row['status']);
                                            }
                                            ?>

                                            <span class="badge <?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="dropdown">

                                                <button
                                                    class="btn btn-sm btn-secondary dropdown-toggle"
                                                    type="button"
                                                    data-bs-toggle="dropdown">

                                                    Action

                                                </button>

                                                <ul class="dropdown-menu">

                                                    <!-- VIEW -->
                                                    <li>
                                                        <a
                                                            class="dropdown-item disabled"
                                                            href="<?= BASE_URL ?>/he_damage/view/<?= $row['id']; ?>">

                                                            <i class="fas fa-eye me-2"></i>
                                                            View
                                                        </a>
                                                    </li>

                                                    <!-- ACTIVATE -->
                                                    <li>
                                                        <a
                                                            class="dropdown-item text-success"
                                                            href="<?= BASE_URL ?>/he_damage/activate/<?= $row['id']; ?>"
                                                            onclick="return confirm('Are you sure you want to activate this damage record?');">

                                                            <i class="fas fa-check-circle me-2"></i>
                                                            Activate
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>
                                <tr>
                                    <td colspan="8"
                                        class="text-center text-muted py-4">

                                        <i class="fas fa-box-open fa-2x mb-2"></i>

                                        <br>

                                        No inactive damage records found.

                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>


<script>
    $(document).ready(function() {

        $('#heInactiveDamageTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 30, 40]
        });

    });
</script>


<?php include __DIR__ . '/../../../includes/footer.php'; ?>