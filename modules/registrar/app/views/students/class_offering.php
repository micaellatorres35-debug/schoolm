<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Class Offerings</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>
        

        <button class="btn btn-primary btn-sm" id="classOfferingBtn">
            <i class="bi bi-plus-lg"></i> Class Offering
        </button>

    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Class Offering List</h3>

            <div class="d-flex gap-2">

                <button class="btn btn-outline-secondary btn-sm position-relative" id="filterBtn">
                  <i class="bi bi-funnel"></i> 
                  Filter
                  <span id="filterBadge" 
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                    0
                </span>
            </button>

          

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
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Section</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>Teacher</th>
                            <th>Room</th>
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

<div class="modal fade" id="classOfferingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Add Class Offering</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

            <div id="classStepper" class="bs-stepper">

               <div class="bs-stepper-header">

    <div class="step" data-target="#step1">
        <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">1</span>
            <span class="bs-stepper-label">
                Class Info
            </span>
        </button>
    </div>

    <div class="line"></div>

    <div class="step" data-target="#step2">
        <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">2</span>
            <span class="bs-stepper-label">
                Class Details
            </span>
        </button>
    </div>

    <div class="line"></div>

    <div class="step" data-target="#step3">
        <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">3</span>
            <span class="bs-stepper-label">
                Class Schedule
            </span>
        </button>
    </div>

</div>  

              <div class="bs-stepper-content">

                <form class="row g-3" id="classOfferingForm" action="<?php echo BASE_URL ?>/class-offering/store" method="POST">

                    <!-- ================= CLASS INFO ================= -->

                    <div id="step1" class="content">
                    <h6 class="fw-bold border-bottom pb-2">1. Class Information</h6>

                    <div class="row mb-3">
                    
                    <!-- Subject -->
                    <div class="col-md-6">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach($subjects as $subject) { ?>
                            <option value="<?= $subject['id'] ?>"><?= $subject['code'] ?> - <?= $subject['name'] ?></option>   
                            <?php  } ?>
                        </select>
                    </div>

                     <!-- Course -->
                    <div class="col-md-6">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select" id="course_opt" required>
                            <option value="">Select Course</option>
                            <?php foreach($courses as $course) { ?>
                            <option value="<?= $course['id'] ?>"><?= $course['name'] ?></option>   
                            <?php  } ?>
                        </select>
                    </div>

                    </div>

                    <div class="row mb-3">

                    <!-- Section -->
                    <div class="col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" id="section_opt"  required>
                            <option value="">Select Section</option>
                        </select>
                    </div>

                      <!-- Semester -->
                    <div class="col-md-6">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" id="semester_opt" class="form-select" required>
                            <option value="">Select Semester</option>
                        </select>
                    </div>

                    </div>

                    <button type="button" class="btn btn-primary" onclick="validateStep1()">
                           Next
                    </button>

                     </div>

                    <!-- ================= ASSIGNMENT ================= -->

                    <div id="step2" class="content">
                    <h6 class="fw-bold mt-3 border-bottom pb-2">2. Class Details</h6>


                     <div class="row mb-3">

                    <!-- Teacher -->
                    <div class="col-md-6">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">Select Teacher</option>
                            <?php  foreach( $teachers as $teacher) { ?>
                                <option value="<?= $teacher['id'] ?>"><?= $teacher['first_name'] ?> <?= $teacher['last_name'] ?></option>
                            <?php } ?>
                        </select>   
                    </div>

                    <!-- Room -->
                    <div class="col-md-6">
                        <label class="form-label">Room</label>
                        <select name="room_id" class="form-select" required>
                            <option value="">Select Room</option>
                            <?php foreach($rooms as $room) { ?>
                                <option value="<?= $room['id'] ?>"><?= $room['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    </div>

                    <button type="button"  class="btn btn-secondary" onclick="stepper.previous()">
                     Previous
                    </button>

                    <button type="button" class="btn btn-primary" onclick="validateStep2()">
                           Next
                    </button>

                     </div>

                    <!-- ================= SCHEDULE ================= -->
                     <div id="step3" class="content">
                    <h6 class="fw-bold mt-3 border-bottom pb-2">3.Schedule</h6>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Add one or more schedule entries</span>
                            <button type="button" class="btn btn-primary btn-sm" id="addScheduleBtn">
                                + Add Schedule
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Day</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleBody">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                      <button type="button"  class="btn btn-secondary" onclick="stepper.previous()">
                     Previous
                    </button>

                    </div>

                </form>

            </div>
            </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-success px-4 d-none" id="classOfferingSubmit">
                    Save Class Offering
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">Day</label>
                        <select id="schedule_day" class="form-select">
                            <option value="">Select Day</option>
                            <option>Monday</option>
                            <option>Tuesday</option>
                            <option>Wednesday</option>
                            <option>Thursday</option>
                            <option>Friday</option>
                            <option>Saturday</option>
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" id="schedule_start" class="form-control">
                    </div>

                    <div class="col-6">
                        <label class="form-label">End Time</label>
                        <input type="time" id="schedule_end" class="form-control">
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveScheduleBtn">
                    Add Schedule
                </button>
            </div>

        </div>
    </div>
</div>

 <!-- show course modal -->
<div class="modal fade" id="showClassOfferModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

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

                <a href='#' id="scheduleCard" class="btn btn-primary" style="cursor:pointer;">
                     Go to Schedule
                </a>

                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<!-- filtering -->

<div class="modal fade" id="filterModal">
  <div class="modal-dialog">
    <div class="modal-content p-3">

      <h5>Filter</h5>


      <!-- school year filter -->

     <div class="form-floating mb-1">
            
       <select id="filter_school_year"  class="form-select mb-1" >
             <?php foreach($sy as $school_year) { ?>
        <option 
            value="<?= $school_year['id']; ?>"
            <?= $school_year['is_active'] == 1 ? 'selected' : ''; ?>
        >
            <?= $school_year['name']; ?>
        </option>
    
        <?php  } ?> 
      </select>

      <label for="floatingSelect">School Year</label>

      </div>

      <!-- course filter -->

      <div class="form-floating mb-2 " id="course-container" >
      <select id="filter_course" class="form-select" aria-label="Floating label select example">
        <option value="">All Courses</option>
         <?php foreach($defaultActiveCourse as $defaultActiveCourses) { ?>
        <option 
            value="<?= $defaultActiveCourses['id']; ?>"
        >
            <?= $defaultActiveCourses['name']; ?>
        </option>
    
        <?php  } ?> 
      </select>
     

      <label for="floatingSelect">Course</label>

      </div>

      <!-- section filter -->

     <div class="form-floating mb-2 d-none" id="section-container">
      <select id="filter_section" class="form-select" aria-label="Floating label select example">
        <option value="">All Sections</option>
      </select>

      <label for="floatingSelect">Section</label>

      </div>

      


      <button class="btn btn-primary w-100 mb-2" id="applyFilter">Apply Filter</button>
      <button class="btn btn-warning w-100" id="resetFilter">Reset Filter</button>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?= BASE_URL ?>/js/class_offering.js"></script>
<script src="<?= BASE_URL ?>/js/class_offering_wiz.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>