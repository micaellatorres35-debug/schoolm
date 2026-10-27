<?php include __DIR__ . '/partials/header.php'; ?>
<?php include __DIR__ . '/partials/sidebar.php'; ?>


<main class="main-content bg-light pb-5">
    <div class="container-fluid px-4 py-4">

        
        <div class="mb-4">
            <small class="text-primary fw-semibold">
                Dashboard <i class="bi bi-chevron-right mx-1 small text-muted"></i> Enrollment
            </small>
            <h2 class="fw-bold mt-1 text-dark">
                Enrollment
            </h2>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-4">
                    Enrollment Information
                </h5>

                <div class="row g-4">
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Category</small>
                        <span class="text-secondary">College</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Admission Type</small>
                        <span class="text-secondary"><?= htmlspecialchars(ucfirst($student['admission_type']) ??  'Continuing') ?></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Modality</small>
                        <span class="text-secondary">Hybrid</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Branch</small>
                        <span class="text-secondary">Bulacan Branch</span>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Program / Strand</small>
                        <span class="text-secondary"><?= $course['name'] ?></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Year Level</small>
                        <span class="text-secondary">
                        
                          <?php 

                            switch($studentSchoolInfo['year_level'])
                            {
                                case '1':
                                    echo '1st Year College';
                                    break;
                                case '2':
                                     echo '2nd Year College';
                                    break;
                                case '3':
                                    echo '3rd Year College';
                                    break;
                                case '4':
                                    echo '4th Year College';
                                    break;

                            }
                            
                            ?>

                        </span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Current Section</small>
                        <span class="text-secondary">Bulacan <?= $section['section_code'] ?></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-semibold mb-1">Academic Year</small>
                        <span class="text-secondary"><?= $semester['name'] ?> <?= $schoolYear['name'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-3">
                    Subject Information
                </h5>

                <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="gradesTable">
                <thead class="table-light text-secondary small text-uppercase fw-semibold">
                    <tr>
                        <th scope="col" class="py-3 ps-3">Subject Code</th>
                        <th scope="col" class="py-3">Subject Title</th>
                        <th scope="col" class="py-3">Section</th>
                        <th scope="col" class="py-3 text-center">Units</th>
                        <th scope="col" class="py-3">Instructor / Adviser</th>
                        <th scope="col" class="py-3 text-center">Remarks</th>
                    </tr>
                </thead>
                <tbody class="text-secondary fs-6">
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No Data Found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
         <div class="p-2 mt-2 d-flex justify-content-end">
            <button class="btn btn-dark" id="printBtn"> Print Enrollment</button>
        </div>
            </div>
            
        </div>

         

    </div>
</main>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
<script src="<?= BASE_URL ?>/js/driver.js"></script>
<script> const Id = "<?php echo $studentSchoolInfo['student_id'] ?>" </script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script src="<?= BASE_URL ?>/js/enrollment.js"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<?php include __DIR__ . '/partials/footer.php'; ?>