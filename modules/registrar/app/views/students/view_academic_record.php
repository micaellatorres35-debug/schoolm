<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>


<main class="main-content">
<div class="container">

<div class="container-fluid py-4">

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-start">

                <div>
                    <h4 class="mb-1"><?= $applicant_first_name ?? ''?> <?= $applicant_surname ?? '' ?> <?= $applicant_suffix ?? ''?></h4>

                    <p class="text-muted mb-1">
                        <?= $applicant_number ?? '' ?>
                    </p>

                    <span class="badge bg-primary">
                        <?= $applicant_course_code ?? 'None' ?>
                    </span>

                    <span class="badge bg-info">
                        <?= $admission_type ?>
                    </span>

                   
                </div>


                 <div>
                    
                    <div class="text-muted mb-1">
                        <?= $applicant_course_name ?>
                    </div>

                </div>


            </div>

        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow-sm">

        <div class="card-header p-0">

            <ul class="nav nav-tabs card-header-tabs" role="tablist">

                <li class="nav-item">
                    <button
                        class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#overview">
                        Overview
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#history">
                        Academic History
                    </button>
                </li>
               
            </ul>

        </div>

        <div class="card-body">

            <div class="tab-content">

                <!-- Overview -->
                 
                 <?php include VIEW_PATH . '/academic-overviews/overview.php'; ?>

                <!-- History -->
                 
                 <?php include VIEW_PATH . '/academic-overviews/academic_history.php'; ?>
              

            </div>

        </div>

    </div>


</div>

</div>
</main>





</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.5/js/lightbox.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script>
     const student_id = <?= $student_id ?>;
</script>
<script src="<?= BASE_URL ?>/js/.js"></script>


<?php include  __DIR__ .'/../partials/footer.php'; ?>