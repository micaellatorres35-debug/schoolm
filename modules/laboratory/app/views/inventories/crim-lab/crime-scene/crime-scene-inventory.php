<?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>

<main class="main-content">
    <div class="container-fluid px-4">
        <h1 class="h3 mb-2 text-gray-800">Inventory</h1>
        <p class="mb-4">Crime Scene Laboratory</p>

        <div class="card mb-4 card shadow-sm border-0 border-top border-4 border-secondary shadow-lg p-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Inventory Management
                </div>

                <div class="d-flex gap-2">
                    <button
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#csAddInventoryModal">
                        <i class="fas fa-plus me-1"></i> Create New
                    </button>

                    <a href="<?= BASE_URL ?>/crime-scene-inventory/inactive"
                        class="btn btn-secondary btn-sm">
                        <i class="fas fa-box-archive me-2"></i>
                        Inactive Items
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="csEquipmentTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Laboratory</th>
                            <th>Total Quantity</th>
                            <th>Available Quantity</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventories as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['item_name'] ?></td>
                                <td><?= $row['category'] ?></td>
                                <td><?= $row['laboratory'] ?></td>
                                <td><?= $row['total_item'] ?></td>
                                <td><?= $row['available_item'] ?></td>
                                <td><?= $row['status'] ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Action
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="#"
                                                    class="dropdown-item viewBtn"
                                                    data-id="<?= $row['id']; ?>">
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
                                                <a href="<?= BASE_URL ?>/crime-scene-inventory/deactivate/<?= $row['id']; ?>"
                                                    class="dropdown-item text-danger"
                                                    onclick="return confirm('Are you sure you want to deactivate this inventory item?');">

                                                    <i class="fas fa-ban me-2"></i>
                                                    Deactivate
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
</main>

<?php include  __DIR__ . '/../../../includes//footer.php'; ?>

<script>
    $(document).ready(function() {
        $('#csEquipmentTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 30, 40],
        });
    });
</script>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>

<script src="<?= BASE_URL ?>/js/csInventory.js"></script>

<?php require __DIR__ . '/csAddInventoryModal.php'; ?>
<?php require __DIR__ . '/csEditInventoryModal.php'; ?>
<?php require __DIR__ . '/csViewInventoryModal.php'; ?>