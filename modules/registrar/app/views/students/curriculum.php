<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Curriculums</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>
        

        <button class="btn btn-primary btn-sm" id="curriculumBtn">
            <i class="bi bi-plus-lg"></i> Add Curriculums
        </button>

    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Curriculum List</h3>

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
                            <th>Curriculum Name</th>   
                            <th>Course Name</th>
                            <th>Effective Year</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
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

<div class="modal fade" id="curriculumModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Add Curriculum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>

         
            <!-- Body -->
            <div class="modal-body">

                <form class="row g-4" id="curriculumForm" action="<?php echo BASE_URL ?>/curriculum/store" method="POST">

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

                    <!-- Course -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Course</label>
                        <select name="course_id" id="course_id" class="form-select shadow-sm" required>
                            <option value="" disabled selected>Select a course</option>
                            <?php foreach($courses as $course) { ?>
                                <option value="<?= $course['id'] ?>" data-course="<?= $course['code'] ?>" >
                                    <?= $course['code'] ?> - <?= $course['name'] ?>
                                </option>   
                            <?php } ?>
                        </select>
                    
                    </div>

                      <!-- Effective Year -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Effective Year</label>
                     <select name="effective_year" id="effective_year" class="form-select">
                        <option value="" disabled selected>Select Year</option>
                        <?php
                        $currentYear = date("Y");
                        $futureYear = $currentYear + 10;

                        for ($year = $currentYear; $year <= $futureYear; $year++) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                    </div>

                    <!-- Curriculum Name -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Curriculum Name</label>
                        <input 
                            type="text" 
                            class="form-control shadow-sm ai-clean" 
                            name="curriculum_name" 
                            id="curriculum_name"
                            placeholder="e.g. BSIT Curriculum 2024"
                        required
                        >
                        <div class="invalid-feedback" id="error-curriculum_name"></div>
                    </div>

                </form>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-success px-4" id="curriculumSubmitBtn">
                    Save Curriculum
                </button>
            </div>

        </div>
    </div>
</div>







 <!-- show course modal -->

<div class="modal fade" id="showCourseModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

    <div class="modal-dialog ">
        <div class="modal-content shadow-lg border-0 rounded-3">

           
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0" id="showModalTitle">
                        Curriculum Details
                    </h5>
                    <small class="opacity-75">View course and curriculum information</small>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-4">


            <!-- COURSE SECTION -->
                <div class="mb-4">
                    <h6 class="text-muted text-uppercase small mb-2">Course Information</h6>

                    <div class="p-3 bg-light rounded">
                        <div class="mb-2">
                            <small class="text-muted">Course Code</small>
                            <div class="fw-semibold" id="show_course_code">—</div>
                        </div>

                        <div>
                            <small class="text-muted">Course Name</small>
                            <div class="fw-semibold" id="show_strand_name">—</div>
                        </div>
                    </div>
                </div>

                <!-- CURRICULUM SECTION -->
                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Curriculum Information</h6>

                    <div class="p-3 border rounded">

                        <div class="mb-2">
                            <small class="text-muted">Curriculum Name</small>
                            <div class="fw-semibold" id="curr_curriculum_name">—</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">Effective Year</small>
                                <div>
                                    <span class="badge bg-success fs-6" id="curr_effective_year">—</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?= BASE_URL ?>/js/curriculum.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>