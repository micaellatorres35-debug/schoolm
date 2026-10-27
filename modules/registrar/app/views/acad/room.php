<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Room Management</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>
        

        <button class="btn btn-primary btn-sm" id="addRooms">
            <i class="bi bi-plus-lg"></i> Add Rooms
        </button>

    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Room List</h3>

            <div class="d-flex gap-2">

                <button type="button" class="btn btn-outline-danger btn-sm" id="pdf">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </button>

                <button type="button" class="btn btn-outline-success btn-sm" id="excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </button>

                <button type="button" class="btn btn-outline-primary btn-sm" id="csv">
                    <i class="bi bi-filetype-csv"></i> CSV
                </button>

            </div>
        </div>


        <div class="card-body">

            <div class="row g-2 mb-3">

                <div class="col-md-2">
                    <label class="form-label small">Per Page</label>
                    <select class="form-select form-select-sm" id="limit">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="40">40</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Order</label>
                    <select class="form-select form-select-sm" id="order">
                        <option value="desc">Descending (Z-A)</option>
                        <option value="asc">Ascending (A-Z)</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label small">Search</label>
                    <input type="text"
                        class="form-control form-control-sm"
                        placeholder="Search course..."
                        id="search">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-danger btn-sm d-none w-100" id="delete-btn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>

            </div>


            <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Building</th>
                            <th>Capacity</th>
                            <th>type</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody">
                    </tbody>

                </table>

            </div>


            
            <div class="d-flex align-items-center justify-content-between mt-3">

                <div class="fw-semibold small" id="pageInfo"></div>

                <div id="pagination" class="d-flex gap-1"></div>

            </div>

        </div>

    </div>

</div>
</main>

 <!-- add course modal -->

<div class="modal fade" id="addRoomsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    Add Rooms
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <form class="row g-3" id="roomForm" action="<?php echo BASE_URL ?>/room/store" method="POST">

                    <!-- AI Auto Correct -->
                    <div class="col-12 d-flex justify-content-between align-items-center border rounded p-2 px-3 bg-light">

                        <div>
                            <span class="fw-semibold">AI Auto-Correct</span>
                            <i class="bi bi-info-circle text-muted"
                               data-bs-toggle="tooltip"
                               data-bs-placement="right"
                               title="When enabled, AI automatically corrects grammar, spelling, and capitalization.">
                            </i>
                            <div class="small text-muted">
                                Improve text automatically before saving.
                            </div>
                        </div>

                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="ai_auto_correct" value="0">

                            <input class="form-check-input"
                                   type="checkbox"
                                   id="aiAutoCorrect"
                                   name="ai_auto_correct"
                                   value="1">
                        </div>

                    </div>

                    <!-- Course Code -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                           Name
                        </label>

                        <input
                            type="text"
                            class="form-control ai-clean"
                            name="room_name"
                            id="room_name"
                            placeholder="e.g. Room 101, Lab 3">

                        <div class="invalid-feedback" id="error-room_name"></div>
                    </div>

                    <!-- Course Years -->

                     <div class="col-md-6">
                        <label class="form-label fw-semibold">
                           Building
                        </label>

                        <input
                            type="text"
                            class="form-control ai-clean"
                            name="bldg_name"
                            id="bldg_name"
                            >

                        <div class="invalid-feedback" id="error-bldg_name"></div>
                    </div>

                    <!-- Course Name -->
                    <div class="col-6">
                        <label class="form-label fw-semibold">
                            Capacity
                        </label>

                        <input
                            type="number"
                            class="form-control ai-clean"
                            name="room_capacity"
                            id="room_capacity"
                            placeholder="e.g. 40 students">

                        <div class="invalid-feedback" id="error-room_capacity"></div>
                    </div>

                     <div class="col-6">
                        <label class="form-label fw-semibold">
                            Type
                        </label>

                        <input
                            type="text"
                            class="form-control ai-clean"
                            name="room_type"
                            id="room_type"
                            placeholder="e.g. lecture, laboratory">

                        <div class="invalid-feedback" id="error-room_type"></div>
                    </div>
                    


                </form>

            </div>

            <!-- Footer -->
            <div class="modal-footer">

                <button type="button"
                        class="btn btn-primary px-4"
                        id="addRoomsSubmit">
                    Submit
                </button>

            </div>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>   const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?php echo BASE_URL ?>/js/room.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>