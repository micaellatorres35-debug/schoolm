    <div class="tab-pane fade" id="students" role="tabpanel">

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

            <div>
                <h5 class="fw-bold mb-1">Students</h5>
                <div class="text-muted small">
                    Students enrolled in this class
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">

                <!-- Student Count -->
                <span class="badge text-bg-primary px-3 py-2">
                    35 Students
                </span>

            </div>

        </div>


        <!-- Summary Cards -->
        <div class="row g-3 mb-4">

            <div class="col-md-4">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <div class="text-muted small">
                                    Total Students
                                </div>

                                <h4 class="fw-bold mb-0">
                                    35
                                </h4>
                            </div>

                            <i class="fa-solid fa-users fs-3 text-primary"></i>

                        </div>

                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <div class="text-muted small">
                                    Enrolled
                                </div>

                                <h4 class="fw-bold mb-0">
                                    33
                                </h4>
                            </div>

                            <i class="fa-solid fa-user-check fs-3 text-success"></i>

                        </div>

                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <div class="text-muted small">
                                    Other Status
                                </div>

                                <h4 class="fw-bold mb-0">
                                    2
                                </h4>
                            </div>

                            <i class="fa-solid fa-user-clock fs-3 text-warning"></i>

                        </div>

                    </div>
                </div>
            </div>

        </div>


        <!-- Student List -->
        <div class="card border-0 shadow-sm">

            <!-- Toolbar -->
            <div class="card-body border-bottom">

                <div class="row g-2">

                    <!-- Search -->
                    <div class="col-md-7">

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>

                            <input
                                type="search"
                                class="form-control"
                                placeholder="Search student number or name..."
                            >

                        </div>

                    </div>


                    <!-- Status Filter -->
                    <div class="col-md-3">

                        <select class="form-select">

                            <option value="">
                                All Status
                            </option>

                            <option value="enrolled">
                                Enrolled
                            </option>

                            <option value="dropped">
                                Dropped
                            </option>

                            <option value="on_leave">
                                On Leave
                            </option>

                        </select>

                    </div>


                    <!-- Export -->
                    <div class="col-md-2">

                        <button class="btn btn-outline-secondary w-100">

                            <i class="fa-solid fa-download me-1"></i>

                            Export

                        </button>

                    </div>

                </div>

            </div>


            <!-- Table -->
            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th style="width: 60px;">
                                #
                            </th>

                            <th>
                                Student No.
                            </th>

                            <th>
                                Student Name
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-end">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <td class="text-muted">
                                1
                            </td>

                            <td>
                                <span class="fw-semibold">
                                    2026-001
                                </span>
                            </td>

                            <td>
                                <div class="d-flex align-items-center">

                                    <div
                                        class="rounded-circle bg-primary-subtle text-primary
                                            d-flex align-items-center justify-content-center
                                            me-2"
                                        style="width:38px;height:38px;"
                                    >
                                        JD
                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            Juan Dela Cruz
                                        </div>

                                        <small class="text-muted">
                                            BSIS-1001
                                        </small>

                                    </div>

                                </div>
                            </td>

                            <td>

                                <span class="badge text-bg-success">
                                    Enrolled
                                </span>

                            </td>

                            <td class="text-end">

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="fa-solid fa-eye me-1"></i>
                                    View
                                </button>

                            </td>

                        </tr>


                        <tr>

                            <td class="text-muted">
                                2
                            </td>

                            <td>
                                <span class="fw-semibold">
                                    2026-002
                                </span>
                            </td>

                            <td>

                                <div class="d-flex align-items-center">

                                    <div
                                        class="rounded-circle bg-primary-subtle text-primary
                                            d-flex align-items-center justify-content-center
                                            me-2"
                                        style="width:38px;height:38px;"
                                    >
                                        MS
                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            Maria Santos
                                        </div>

                                        <small class="text-muted">
                                            BSIS-1001
                                        </small>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <span class="badge text-bg-success">
                                    Enrolled
                                </span>

                            </td>

                            <td class="text-end">

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="fa-solid fa-eye me-1"></i>
                                    View
                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- Footer / Pagination -->
            <div class="card-body border-top">

                <div class="d-flex flex-column flex-md-row
                            justify-content-between align-items-center gap-3">

                    <div class="text-muted small">
                        Showing <strong>1–10</strong> of <strong>35</strong> students
                    </div>


                    <nav>

                        <ul class="pagination pagination-sm mb-0">

                            <li class="page-item disabled">
                                <button class="page-link">
                                    Previous
                                </button>
                            </li>

                            <li class="page-item active">
                                <button class="page-link">
                                    1
                                </button>
                            </li>

                            <li class="page-item">
                                <button class="page-link">
                                    2
                                </button>
                            </li>

                            <li class="page-item">
                                <button class="page-link">
                                    3
                                </button>
                            </li>

                            <li class="page-item">
                                <button class="page-link">
                                    Next
                                </button>
                            </li>

                        </ul>

                    </nav>

                </div>

            </div>

        </div>

    </div>