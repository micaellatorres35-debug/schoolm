<div class="container-fluid">

    <!-- Page Header -->
    <div class="mb-4">
        <h3 class="mb-1">Report Submissions & Management</h3>
        <p class="text-muted mb-0">
            View and manage submitted reports from teachers and staff.
        </p>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0 border-top border-4 border-secondary">

        <div class="card-body">

            <!-- Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                <!-- Left Side -->
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <!-- Department Filter -->
                    <form method="GET" class="mb-0">
                        <select
                            name="department"
                            id="rsm-filter"
                            class="form-select form-select-sm"
                            style="width: 170px;"
                            onchange="this.form.submit()"
                        >
                            <option value="">All Departments</option>

                            <?php foreach ($departments as $dept) : ?>
                                <option
                                    value="<?= $dept['department_id'] ?>"
                                    <?= (
                                        isset($_GET['department']) &&
                                        $_GET['department'] == $dept['department_id']
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </form>

                    <!-- Search -->
                    <input
                        class="form-control form-control-sm"
                        id="rsm-search"
                        type="search"
                        placeholder="Search by title or submitter"
                        style="width: 220px;"
                    >

                </div>

                <!-- Right Side -->
                <div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createconcernsITModal"
                    >
                        <i class="fas fa-plus me-1"></i>
                        Create
                    </button>
                </div>

            </div>

            <!-- Existing Table -->
            <div class="table-responsive">
                <?php require __DIR__ . '/table.php'; ?>
            </div>

        </div>

    </div>

</div>