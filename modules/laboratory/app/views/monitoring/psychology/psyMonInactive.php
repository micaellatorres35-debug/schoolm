<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<main class="main-content">
    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Inactive Psychology Monitoring</h3>
                <p class="text-muted mb-0">
                    Monitoring records that have been deactivated.
                </p>
            </div>

            <a href="<?= BASE_URL ?>/psy_monitoring"
                class="btn btn-secondary">

                <i class="fas fa-arrow-left me-2"></i>
                Back to Monitoring
            </a>
        </div>

        <div class="card mb-4 shadow-sm border-0 border-top border-4 border-secondary">

            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-box-archive me-2"></i>
                    Inactive Monitoring
                </h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table id="psyInactiveMonitoringTable"
                        class="table table-striped table-bordered"
                        style="width:100%">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Laboratory</th>
                                <th>Equipment Condition</th>
                                <th>Last Checked</th>
                                <th>Checked By</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($rows)): ?>

                                <?php foreach ($rows as $row): ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($row['id']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['item_name']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['laboratory']); ?>
                                        </td>

                                        <td>
                                            <?php
                                            $condition = strtolower($row['equipment_condition']);

                                            if ($condition === 'good') {

                                                $conditionClass = 'bg-success';
                                                $conditionText = 'Good';

                                            } elseif ($condition === 'working') {

                                                $conditionClass = 'bg-primary';
                                                $conditionText = 'Working';

                                            } elseif (
                                                $condition === 'damage' ||
                                                $condition === 'damaged'
                                            ) {

                                                $conditionClass = 'bg-danger';
                                                $conditionText = 'Damaged';

                                            } elseif ($condition === 'under inspection') {

                                                $conditionClass = 'bg-warning text-dark';
                                                $conditionText = 'Under Inspection';

                                            } elseif ($condition === 'unavailable') {

                                                $conditionClass = 'bg-secondary';
                                                $conditionText = 'Unavailable';

                                            } else {

                                                $conditionClass = 'bg-secondary';
                                                $conditionText = htmlspecialchars(
                                                    $row['equipment_condition']
                                                );
                                            }
                                            ?>

                                            <span class="badge <?= $conditionClass ?>">
                                                <?= $conditionText ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['last_checked']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['checked_by']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['remarks']); ?>
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

                                                    <!-- VIEW - DISABLED -->
                                                    <li>
                                                        <a
                                                            class="dropdown-item disabled"
                                                            href="#"
                                                            aria-disabled="true"
                                                            tabindex="-1">

                                                            <i class="fas fa-eye me-2"></i>
                                                            View

                                                        </a>
                                                    </li>

                                                    <!-- ACTIVATE -->
                                                    <li>
                                                        <a
                                                            class="dropdown-item text-success"
                                                            href="<?= BASE_URL ?>/psy_monitoring/activate/<?= $row['id']; ?>"
                                                            onclick="return confirm('Are you sure you want to activate this monitoring record?');">

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

                                        No inactive monitoring records found.

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

        $('#psyInactiveMonitoringTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 30, 40]
        });

    });
</script>


<?php include __DIR__ . '/../../includes/footer.php'; ?>