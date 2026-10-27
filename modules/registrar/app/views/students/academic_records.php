<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>


<main class="main-content">
    <div class="container">

    
            <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Academic Records</h1>
             <p class="text-muted small">Manage and organize student records for the registrar.</p>
        </div>

    </div>
        
  
    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Students List</h3>

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

                <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" id="status">
                            <option value="all">All Status</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="on_leave">On Leave</option>
                            <option value="graeduated">Graduated</option>
                            <option value="dropped">dropped</option>
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

              

        </div>


        <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr></tr>
                            <th>Student No.</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year</th>
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
    </div>        
    </div>
</main>


<!-- filtering -->

<div class="modal fade" id="filterModal">
  <div class="modal-dialog">
    <div class="modal-content p-3">

      <h5>Filter</h5>


        <div class="form-floating mb-2">
      <select id="filter_course" class="form-select" aria-label="Floating label select example">
        <option value="" selected>All Courses</option>
         <?php foreach($course as $courses) { ?>
        
        <option 
            value="<?= $courses['id']; ?>" 
        >
        <?= $courses['name']; ?>
        </option>

        <?php  } ?> 
      </select>
     

      <label for="floatingSelect">Course</label>

      </div>



    <div class="form-floating mb-2" id="year-level-container">
    <select id="filter_year_level" class="form-select" aria-label="Year Level">
        <option value="" selected>All Year Levels</option>
        <option value="1">1st Year</option>
        <option value="2">2nd Year</option>
        <option value="3">3rd Year</option>
        <option value="4">4th Year</option>
    </select>

    <label for="filter_year_level">Year Level</label>
</div>
      


      <button class="btn btn-primary w-100 mb-2" id="applyFilter">Apply Filter</button>
      <button class="btn btn-warning w-100" id="resetFilter">Reset Filter</button>

    </div>
  </div>
 </div>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script>const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?php echo BASE_URL ?>/js/academic_records.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>