<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="main-content">
    <div class="container-fluid py-4">

        <!-- Back -->
        <div class="mb-3">
            <a href="<?= BASE_URL ?>/faculties/classes"
               class="text-decoration-none text-muted">
                <i class="fa-solid fa-arrow-left me-1"></i>
                Back to My Classes
            </a>
        </div>

        <div class="card border-0 shadow-sm">

    <!-- Class Header -->
    <div class="card-body border-bottom">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h4 class="fw-bold mb-1">
                    <?= $subject['name'] ?? 'Subject Name' ?>
                </h4>

                <div class="text-muted">
                   <?= $section['section_code'] ?? 'section' ?>
                    <span class="mx-2">•</span>
                      <?= $semester['name'] ?>
                    <span class="mx-2">•</span>
                       <?= $school_year['name'] ?>
                </div>
            </div>

            <span class="badge text-bg-primary">
               <?= $numberOfStudent['student_count'] ?? 0 ?> Students
            </span>

        </div>

    </div>


    <!-- Tabs -->
    <div class="card-body pb-0">

        <ul class="nav nav-tabs" id="classTabs" role="tablist">

            <li class="nav-item" role="presentation">

                <button
                    class="nav-link active"
                    id="overview-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#overview"
                    type="button"
                    role="tab">

                    <i class="fa-solid fa-chart-line me-1"></i>
                    Overview

                </button>

            </li>


            <li class="nav-item" role="presentation">

                <button
                    class="nav-link"
                    id="students-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#students"
                    type="button"
                    role="tab">

                    <i class="fa-solid fa-users me-1"></i>
                    Students

                </button>

            </li>


            <li class="nav-item" role="presentation">

                <button
                    class="nav-link"
                    id="grades-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#grades"
                    type="button"
                    role="tab">

                    <i class="fa-solid fa-pen-to-square me-1"></i>
                    Grades

                </button>

            </li>


            <li class="nav-item" role="presentation">

                <button
                    class="nav-link"
                    id="attendance-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#overall"
                    type="button"
                    role="tab">

                    <i class="fa-solid fa-calendar-check me-1"></i>
                    Overall Grade

                </button>

            </li>

        </ul>

    </div>


    <!-- Tab Content -->
    <div class="card-body">

        <div class="tab-content" id="classTabsContent">


  
        <?php include VIEW_PATH . '/class-overview/overview.php'; ?>

        <?php include VIEW_PATH . '/class-overview/student.php'; ?>

        <?php include VIEW_PATH . '/class-overview/grade.php'; ?>
        
        <?php include VIEW_PATH . '/class-overview/overall.php'; ?>


        </div>

    </div>

</div>

      



    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script> const ScheduleId = <?= $schedule['id']  ?></script>
<script src="<?= BASE_URL ?>/js/class.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
