<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Class List</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>
        

    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Class Lists</h3>

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
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Schedule</th>
                            <th>Room</th>
                            <th>Instructor</th>
                            <th>Students</th>
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



<!-- view modal  -->

<div class="modal fade" id="showClassList"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-scrollable modal-lg">

        <div class="modal-content shadow-lg border-0 rounded-3">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0" id="showModalTitle">
                        Class Details
                    </h5>

                    <small class="opacity-75">
                        View class and schedule information
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-4">

                <!-- =========================
                     COURSE INFORMATION
                ========================== -->
                <div class="mb-4">

                    <h6 class="text-muted text-uppercase small mb-2">
                        Course Information
                    </h6>

                    <div class="p-3 bg-light rounded">

                        <div class="row mb-2">

                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Course Code
                                </small>

                                <div class="fw-semibold"
                                     id="show_course_code">
                                    —
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Course Name
                                </small>

                                <div class="fw-semibold"
                                     id="show_course_name">
                                    —
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Section
                                </small>

                                <div class="fw-semibold"
                                     id="show_section_name">
                                    —
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Year Level
                                </small>

                                <div class="fw-semibold"
                                     id="show_year_level">
                                    —
                                </div>
                            </div>

                        </div>

                    </div>
                </div>


                <!-- =========================
                     SUBJECT INFORMATION
                ========================== -->
                <div class="mb-4">

                    <h6 class="text-muted text-uppercase small mb-2">
                        Subject Information
                    </h6>

                    <div class="p-3 border rounded">

                        <div class="row mb-2">

                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Subject Code
                                </small>

                                <div class="fw-semibold"
                                     id="show_subject_code">
                                    —
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Subject Name
                                </small>

                                <div class="fw-semibold"
                                     id="show_subject_name">
                                    —
                                </div>
                            </div>

                        </div>

                        <div class="row mb-3">

                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Semester
                                </small>

                                <div class="fw-semibold"
                                     id="show_semester">
                                    —
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">
                                    Teacher
                                </small>

                                <div class="fw-semibold"
                                     id="show_teacher_name">
                                    —
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Units
                                </small>

                                <div class="fw-semibold"
                                     id="show_units">
                                    —
                                </div>
                            </div>

                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Lecture Hours
                                </small>

                                <div class="fw-semibold"
                                     id="show_lecture_hours">
                                    —
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">
                                    Laboratory Hours
                                </small>

                                <div class="fw-semibold"
                                     id="show_laboratory_hours">
                                    —
                                </div>
                            </div>

                        </div>

                    </div>
                </div>


                <!-- =========================
                     CLASS / SCHEDULE
                ========================== -->
                <div class="mb-4">

                    <h6 class="text-muted text-uppercase small mb-2">
                        Class and Schedule Information
                    </h6>

                    <div class="p-3 border rounded">

                        <div class="row mb-2">

                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Room
                                </small>

                                <div class="fw-semibold"
                                     id="show_room">
                                    —
                                </div>
                            </div>

                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Room Type
                                </small>

                                <div class="fw-semibold"
                                     id="show_room_type">
                                    —
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">
                                    Building
                                </small>

                                <div class="fw-semibold"
                                     id="show_building">
                                    —
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Day
                                </small>

                                <div class="fw-semibold"
                                     id="show_day">
                                    —
                                </div>
                            </div>

                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted">
                                    Start Time
                                </small>

                                <div class="fw-semibold"
                                     id="show_start_time">
                                    —
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">
                                    End Time
                                </small>

                                <div class="fw-semibold"
                                     id="show_end_time">
                                    —
                                </div>
                            </div>

                        </div>

                    </div>
                </div>


        
                <!-- =========================
                     STUDENT LIST
                ========================== -->
                <div>

                    <div class="d-flex justify-content-between align-items-center mb-2">

                        <h6 class="text-muted text-uppercase small mb-0">
                            Students
                        </h6>

                        <span class="badge bg-primary"
                              id="show_student_count">
                            0 Students
                        </span>

                    </div>

                    <div class="border rounded overflow-hidden">

                        <div class="table-responsive">

                            <table class="table table-hover mb-0">

                                <thead class="table-light">

                                    <tr>
                                        <th width="60">#</th>
                                        <th>Student No.</th>
                                        <th>Student Name</th>
                                        <th width="120">Status</th>
                                    </tr>

                                </thead>

                                <tbody id="show_student_list">

                                    <tr>
                                        <td colspan="4"
                                            class="text-center text-muted py-4">
                                            No students found.
                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- FOOTER -->
            <div class="modal-footer d-flex justify-content-between">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">

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

          <div class="form-floating mb-2 " id="semester-container" >
      <select id="filter_semester" class="form-select" aria-label="Floating label select example">
        <option value="">All Semester</option>
         
        <option 
            value=""
        >
        
        </option>
    
      </select>
     

      <label for="floatingSelect">Semester</label>

      </div>



    <div class="form-floating mb-2 " id="course-container" >
      <select id="filter_section" class="form-select" aria-label="Floating label select example">
        <option value="">All Sections</option>
         
        <option 
            value=""
        >
        
        </option>
    
      </select>
     

      <label for="floatingSelect">Section</label>

     </div>


      
    <div class="form-floating mb-2 " id="course-container" >
      <select id="filter_subject" class="form-select" aria-label="Floating label select example">
        <option value="">All Subjects</option>
            <?php foreach($subject as $subjects) { ?>

        <option 
            value="<?= $subjects['id']; ?>"
        >

         <?= $subjects['name']; ?>
        
        </option>

        <?php  } ?> 
    
      </select>
     

      <label for="floatingSelect">Subject</label>

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
<script src="<?= BASE_URL ?>/js/class_list.js"></script>


<?php include  __DIR__ .'/../partials/footer.php'; ?>