<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Section Schedule</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>


    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Section Schedule List</h3>

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

                <div class="col-md-3">
                    <label class="form-label small">Section</label>
                    <select class="form-select form-select-sm" id="section">
                        <?php foreach ($sections as $section) {?>
                        <option value=<?= $section['id'] ?>><?= $section['name'] ?></option>
                        <?php  } ?>
                    </select>
                </div>

        </div>


        <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            
                        <th style="width:100px;">Time</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                           
                        </tr>
                    </thead>

                    <tbody id="grid-body">
                        
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


 <!-- show course modal -->
<div class="modal fade" id="showScheduleDetailModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-3">

           
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0" id="showModalTitle">
                        Class Details
                    </h5>
                    <small class="opacity-75">View class and schedule information</small>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-4">


            <!-- COURSE SECTION -->
                <div class="mb-4">
                    <h6 class="text-muted text-uppercase small mb-2">Course Information</h6>

                    <div class="p-3 bg-light rounded">
                        
                        <div class="row mb-2">
                           
                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">Course Code</small>
                                <div class="fw-semibold" id="show_course_code">—</div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Course Name</small>
                                <div class="fw-semibold" id="show_course_name">—</div>
                            </div>
                        </div>

                          <div class="row mb-1">
                           
                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">Section</small>
                                <div class="fw-semibold" id="show_section_name">—</div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Year Level</small>
                                <div class="fw-semibold" id="show_year_level">—</div>
                            </div>
                        </div>

                    </div>
                </div>


                <!-- CURRICULUM SECTION -->
                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Subject Information</h6>

                    <div class="p-3 border rounded">

                        <div class="row mb-2">
                           
                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">Subject Code</small>
                                <div class="fw-semibold" id="show_subject_code">—</div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Subject Name</small>
                                <div class="fw-semibold" id="show_subject_name">—</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                           
                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">Semester</small>
                                <div class="fw-semibold" id="show_semester">—</div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Teacher</small>
                                <div class="fw-semibold" id="show_teacher_name">—</div>
                            </div>
                        </div>

                         <div class="row mb-2">
                           
                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">Units</small>
                                <div class="fw-semibold" id="show_units">—</div>
                            </div>

                              <div class="col-md-4">
                                <small class="text-muted">Lecture Hours</small>
                                <div class="fw-semibold" id="show_lecture_hours">—</div>
                            </div>

                              <div class="col-md-4">
                                <small class="text-muted">Laboratory Hours</small>
                                <div class="fw-semibold" id="show_laboratory_hours">—</div>
                            </div>
                        </div>

                    </div>
                </div>



                 <!-- CURRICULUM SECTION -->
                <div>
                    <h6 class="text-muted text-uppercase small mt-4">Class and Schedule Information</h6>

                    <div class="p-3 border rounded">


                         <div class="row mb-2">
                           
                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">Room</small>
                                <div class="fw-semibold" id="show_room">—</div>
                            </div>

                              <div class="col-md-4">
                                <small class="text-muted">Room Type</small>
                                <div class="fw-semibold" id="show_room_type">—</div>
                            </div>

                              <div class="col-md-4">
                                <small class="text-muted">Building</small>
                                <div class="fw-semibold" id="show_building">—</div>
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                           
                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">Day</small>
                                <div class="fw-semibold" id="show_day">—</div>
                            </div>                          

                            <div class="col-md-4">
                                <small class="text-muted ">Start Time</small>
                                <div class="fw-semibold" id="show_start_time">—</div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">End Time</small>
                                <div class="fw-semibold" id="show_end_time">—</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-between">
                  
                  <a href='#' id="class_offer_card" class="btn btn-primary" style="cursor:pointer;">
                     Go to Class Offering
                </a>

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
<script src="<?= BASE_URL ?>/js/section_schedule.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?> 