<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
        
            <div id="dashboard-title">
                <h1 class="h3 fw-bold mb-0">Section Management</h1>
                <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
            </div>
            

            <button class="btn btn-primary btn-sm" id="addSectionBtn">
                <i class="bi bi-plus-lg"></i> Add Section
            </button>

        </div>

        
    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="fs-6 mb-0 text-primary">Section Lists</h3>

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
                        placeholder="Search semesters..."
                        id="search">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-danger btn-sm d-none w-100" id="delete-btn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>

            </div>

      
        <table class="table table-hover table-striped">
            <button class="btn btn-danger d-none"  id="delete-btn" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .65rem;">Delete</button>
            <thead>
                <tr>    
                <th><input type="checkbox" id="select-all"></th>
                <th scope="col">No.</th>
                <th scope="col">Name</th>
                <th scope="col">Year Level</th>
                <th scope="col">Course</th>
                <th scope="col">Capacity</th>
                <th scope="col">Adviser</th>
                </tr>
            </thead>
            <tbody id="studentsTableBody">

        
             
            </tbody>
         </table>

         

       <div class="d-flex align-items-center justify-content-between">
            <div class="fw-bold" id="pageInfo"></div>
            <div id="pagination" class="d-flex gap-2"></div>
        </div>

          


         </div>
        
       
    </div>
    </div>

      
 
</main>

 <!-- add semester modal -->

<div class="modal fade" id="addSectionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Section</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeBtn"></button>
        </div>
        <div class="modal-body">
            
        <form class="row g-3" id="sectionForm" action="<?php echo BASE_URL ?>/section/store" method="POST">

            <div class="col-12 d-flex justify-content-between align-items-center border rounded p-2 px-3 bg-light mb-3">

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

            <div class="row mb-3">


             <div class="col-md-6">
             <label class="form-label">Course</label>
             <select name="course_name" class="form-control" id="course_name" required>
                <option value="">Select Course</option>

                 <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>">
                        <?= htmlspecialchars($course['code']) ?>
                    </option>
                <?php endforeach; ?>


             </select>
             </div>

            <div class="col-md-6">
                <label class="form-label">Year Level</label>
                <select name="year_level" class="form-control" id="year_level" required>
                    <option value="">Select Year Level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>

            </div>
        

             </div>

             <div class="row mb-3">

             <div class="col-md-6">
             <label class="form-label">Semester</label>
             <select name="semester_name" id="semester_name" class="form-control" required>
                <option value="">Select Semester</option>

                 <?php foreach ($semesters as $semester): ?>
                <option 
                value="<?= $semester['id']; ?>"
            <?= $semester['is_active'] == 1 ? 'selected' : ''; ?>
            >
            <?= $semester['name']; ?>
                <?php endforeach; ?>


             </select>
             </div>

             <div class="col-md-6">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control" id="section_capacity" name="section_capacity" placeholder="eg.45">
                <div class="invalid-feedback" id="error-section_capacity"></div>
             </div>

             </div>

              <div class="col-md-6">
                <label class="form-label">Section Name</label>
                <input type="text" class="form-control" name="section_name" id="section_name"
                placeholder="eg. BSIS1A"
                >
                <div class="invalid-feedback" id="error-section_name"></div>
            </div>

            <div class="col-md-6">
             <label class="form-label">Adviser</label>
             <select name="teacher_name" id="teacher_name" class="form-control" required>
                <option value="">Select Adviser</option>

                 <?php foreach ($teachers as $teacher): ?>
                <option 
                value="<?= $teacher['id']; ?>"
               >
                  <?= $teacher['first_name']; ?> <?= $teacher['last_name']; ?>
                <?php endforeach; ?>


             </select>
               <div class="invalid-feedback" id="error-teacher_name"></div>
             </div>
            


        </form>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="addSectionSubmit">Submit</button>
        </div>
        </div>
    </div>
</div>


<div class="modal fade" id="filterModal">
  <div class="modal-dialog">
    <div class="modal-content p-3">

        <h5>Filter</h5>

       <!-- school year filter -->
     <div class="form-floating mt-2 mb-2">
            
       <select id="filter_school_year"  class="form-select mb-2">
             <?php foreach($school_year as $sy) { ?>
        <option 
            value="<?= $sy['id']; ?>"
            <?= $sy['is_active'] == 1 ? 'selected' : ''; ?>
        >
            <?= $sy['name']; ?>
        </option>
    
        <?php  } ?> 
      </select>

      <label for="floatingSelect">School Year</label>

      </div>

      <div class="form-floating mb-3">
            
       <select id="filter_semester"  class="form-select mb-2">
        <option value="">All Semester</option>
             <?php foreach($semesters as $sem) { ?>
        <option 
            value="<?= $sem['id']; ?>"
            <?= $sem['is_active'] == 1 ? 'selected' : ''; ?>
        >
            <?= $sem['name']; ?>
        </option>
    
        <?php  } ?> 
      </select>

      <label for="floatingSelect">Semester</label>

      </div>
  
    
      <button class="btn btn-primary w-100 mb-2" id="applyFilter">Apply Filter</button>
      <button class="btn btn-warning w-100" id="resetFilter">Reset Filter</button>

    </div>
  </div>
</div>


 

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>   const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?php echo BASE_URL ?>/js/section.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>