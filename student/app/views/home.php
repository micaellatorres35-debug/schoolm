<?php include __DIR__ . '/partials/header.php'; ?>
<?php include __DIR__ . '/partials/sidebar.php'; ?>


<main class="main-content bg-light pb-5">


<div class="container-fluid px-4 py-4">

   
    <div class="mb-4">

        <small class="text-primary fw-semibold">
            Dashboard
        </small>

        <h2 class="fw-bold mt-1">
            Announcements
        </h2>

    </div>


    <div class="row g-4 mb-4">

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center mb-4">

                        <i class="bi bi-person fs-4 text-secondary me-2"></i>

                        <h4 class="fw-bold mb-0">
                            <?= htmlspecialchars($student['first_name'] ?? 'Student Name') ?>
                            <?= htmlspecialchars($student['surname'] ?? 'Student Name') ?>
                        </h4>

                    </div>


                    <div class="row align-items-center">

                        <div class="col-md-8">

                            <p class="mb-1">

                                <strong>Course:</strong>

                                <?= $course['name'] ?>

                            </p>

                            <p class="mb-1">

                                <strong>Admission Type:</strong>

                                 <?= htmlspecialchars(ucfirst($student['admission_type']) ??  'Continuing') ?> 

                            </p>

                            <p class="mb-1">

                                <strong>Student ID:</strong>

                                <?= $studentSchoolInfo['student_number'] ?>

                            </p>

                            <p class="mb-0"> 
                                <strong>Year Level:</strong>
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

                          

                             </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Adviser Information -->
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center mb-4">

                        <i class="bi bi-person fs-4 text-secondary me-2"></i>

                        <h4 class="fw-bold mb-0">
                            ADVISER
                        </h4>

                    </div>


                    <p class="mb-1">

                        <strong>Name:</strong>

                        <?= ucfirst($adviser['first_name'])  ?>  <?= ucfirst($adviser['last_name'])  ?>
                        

                    </p>

                    <p class="mb-3">

                        <strong>My Section:</strong>

                        Bulacan  <?= $section['section_code'] ?>

                    </p>

                </div>

            </div>

        </div>

    </div>


    <!-- Announcements -->
    <div class="card border-0 shadow-sm rounded-4">

        <!-- Announcement Header -->
        <div class="card-header bg-white border-bottom py-4 px-4 rounded-top-4">

            <div class="d-flex align-items-center">

                <i class="bi bi-megaphone fs-4 text-secondary me-2"></i>

                <h4 class="fw-bold mb-0">
                    Announcements
                </h4>

            </div>

        </div>


        <div class="card-body p-4">

    <div id="announcementList">

      

    
    </div>

     <nav class="d-flex justify-content-center">
    <ul class="pagination" id="pagination" >
        
    </ul>
   </nav>


</div>
       
    </div>

</div>

</main>




<!-- Existing Scripts -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
<script src="<?= BASE_URL ?>/js/driver.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script src="<?= BASE_URL ?>/js/home.js"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<?php include __DIR__ . '/partials/footer.php'; ?>
