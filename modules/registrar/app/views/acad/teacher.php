<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Teacher Management</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>
        

        <button class="btn btn-primary btn-sm" id="addTeacher">
            <i class="bi bi-plus-lg"></i> Add Teacher
        </button>

    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Teacher List</h3>

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
                        placeholder="Search subjects..."
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
                            <th>Employee No.</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
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

<div class="modal fade" id="addTeacherModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    Add Teacher
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <form class="row g-3" id="teacherForm" action="<?php echo BASE_URL ?>/teacher/store" method="POST">

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

                    <!-- Subject Code -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Employee Number
                        </label>

                        <input
                            type="text"
                            class="form-control ai-clean"
                            name="teacher_number"
                            id="teacher_number"
                            placeholder="e.g. 0001">

                        <div class="invalid-feedback" id="error-teacher_number"></div>
                    </div>

                    <!-- Subject Name -->
                    <div class="col-7">
                        <label class="form-label fw-semibold">
                            First Name
                        </label>

                        <input
                            type="text"
                            class="form-control ai-clean"
                            name="first_name"
                            id="first_name"
                            >

                        <div class="invalid-feedback" id="error-first_name"></div>
                    </div>

                    <!-- subject unit -->

                    <div class="col-4">
                        <label class="form-label fw-semibold">
                            Last Name
                        </label>

                        <input
                            type="text"
                            class="form-control ai-clean"
                            name="last_name"
                            id="last_name"
                            >

                        <div class="invalid-feedback" id="error-last_name"></div>
                    </div>
                     <!-- subject lecture hours -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control ai-clean"
                            name="teacher_email"
                            id="teacher_email"
                             >

                        <div class="invalid-feedback" id="error-teacher_email"></div>
                    </div>

                    <!-- subject lab hours -->

                 

                </form>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-primary px-4"
                        id="addTeacherSubmit">
                    Submit
                </button>

            </div>

        </div>
    </div>
</div>

 <!-- edit course modal -->



 <!-- show course modal -->

<!-- <div class="modal fade" id="showCourseModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="showModalTitle">Add Course</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeBtn"></button>
        </div>
        <div class="modal-body">
            
     <form class="row g-3" id="courseForm">

        <div class="col-md-6">
            <label for="inputEmail4" class="form-label">Strand Code</label>
            <input type="text" class="form-control"  id="show_strand_code" readonly>
        </div>

         <div class="col-md-12">
            <label for="inputPassword4" class="form-label">Course Name</label>
            <textarea type="text" class="form-control" id="show_strand_name" readonly></textarea>
        </div>
        
        </form>

        </div>
        </div>
    </div>
</div> -->

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?= BASE_URL ?>/js/teacher.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>