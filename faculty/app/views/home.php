<?php include __DIR__ .'/partials/sidebar.php'; ?>

<?php include __DIR__ .'/partials/header.php'; ?>

<main class="main-content bg-light pb-5">
    <div class="container-fluid px-4">

    <!-- Dashboard Header -->
    <div class="py-4">
        <div id="dashboard-title">
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Dashboard</h1>
            <p class="text-muted small mb-0">
                Welcome back! Here's what's happening with your classes today.
            </p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">

        <!-- Classes -->
        <div class="col-xl-3 col-md-6" id="card-1">
            <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center no-gutters">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Class Assignments
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                4
                            </div>
                        </div>

                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students -->
        <div class="col-xl-3 col-md-6" id="card-2">
            <div class="card border-0 border-start border-success border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center no-gutters">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                My Students
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                128
                            </div>
                        </div>

                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects -->
        <div class="col-xl-3 col-md-6" id="card-3">
            <div class="card border-0 border-start border-info border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center no-gutters">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Subjects
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                3
                            </div>
                        </div>

                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Grades -->
        <div class="col-xl-3 col-md-6" id="card-4">
            <div class="card border-0 border-start border-warning border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center no-gutters">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Pending Grades
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                12
                            </div>
                        </div>

                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- Today's Schedule + Activity -->
    <div class="row d-flex align-items-stretch">

        <!-- Today's Schedule -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">
                        Today's Schedule
                    </h6>

                    <a href="<?= BASE_URL ?>/schedule"
                       class="small text-decoration-none">
                        View Schedule
                    </a>
                </div>

                <div class="card-body">

                    <!-- Class 1 -->
                    <div class="d-flex align-items-center border-bottom py-3">

                        <div class="me-3 text-center" style="min-width: 80px;">
                            <div class="fw-bold">08:00 AM</div>
                            <small class="text-muted">09:30 AM</small>
                        </div>

                        <div class="border-start ps-3 flex-grow-1">
                            <h6 class="fw-bold mb-1">
                                IT101 - Introduction to Computing
                            </h6>

                            <div class="small text-muted">
                                <i class="fas fa-users me-1"></i>
                                BSIS-1001
                                <span class="mx-2">•</span>
                                <i class="fas fa-door-open me-1"></i>
                                Room 201
                            </div>
                        </div>

                        <a href="<?= BASE_URL ?>/classes/1"
                           class="btn btn-sm btn-outline-primary">
                            View Class
                        </a>

                    </div>


                    <!-- Class 2 -->
                    <div class="d-flex align-items-center border-bottom py-3">

                        <div class="me-3 text-center" style="min-width: 80px;">
                            <div class="fw-bold">10:00 AM</div>
                            <small class="text-muted">11:30 AM</small>
                        </div>

                        <div class="border-start ps-3 flex-grow-1">
                            <h6 class="fw-bold mb-1">
                                IT104 - Computer Hardware Fundamentals
                            </h6>

                            <div class="small text-muted">
                                <i class="fas fa-users me-1"></i>
                                BSIS-1002
                                <span class="mx-2">•</span>
                                <i class="fas fa-door-open me-1"></i>
                                Room 305
                            </div>
                        </div>

                        <a href="<?= BASE_URL ?>/classes/2"
                           class="btn btn-sm btn-outline-primary">
                            View Class
                        </a>

                    </div>


                    <!-- No Classes Example -->
                    <!--
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <h6 class="fw-bold">No classes today</h6>
                        <p class="text-muted small mb-0">
                            You don't have any scheduled classes today.
                        </p>
                    </div>
                    -->

                </div>
            </div>
        </div>


        <!-- Pending Tasks -->
        <div class="col-lg-4 mb-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        Pending Tasks
                    </h6>
                </div>

                <div class="card-body">

                    <!-- Pending Grade -->
                    <div class="d-flex align-items-start mb-4">

                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 me-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">
                                Pending Grades
                            </h6>

                            <p class="text-muted small mb-2">
                                12 students have missing grades.
                            </p>

                            <a href="<?= BASE_URL ?>/grades"
                               class="small text-decoration-none">
                                Enter Grades →
                            </a>
                        </div>

                    </div>


                    <!-- Attendance -->
                    <div class="d-flex align-items-start mb-4">

                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">
                                Attendance
                            </h6>

                            <p class="text-muted small mb-2">
                                Attendance has not been submitted for one class.
                            </p>

                            <a href="<?= BASE_URL ?>/attendance"
                               class="small text-decoration-none">
                                Take Attendance →
                            </a>
                        </div>

                    </div>


                    <!-- Students -->
                    <div class="d-flex align-items-start">

                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 me-3">
                            <i class="fas fa-users"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">
                                My Students
                            </h6>

                            <p class="text-muted small mb-2">
                                View your assigned students.
                            </p>

                            <a href="<?= BASE_URL ?>/students"
                               class="small text-decoration-none">
                                View Students →
                            </a>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>


    <!-- Activity / Calendar -->
    <div class="row d-flex align-items-stretch">

        <!-- Calendar -->
        <div class="col-lg-8 mb-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">

                    <h6 class="m-0 fw-bold text-primary">
                        Academic Calendar
                    </h6>

                </div>

                <div class="card-body">
                    <div id="homeCalendar"
                         style="height: 100%; width: 100%;">
                    </div>
                </div>

            </div>

        </div>


        <!-- Activity Chart -->
        <div class="col-lg-4 mb-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

                    <h6 class="m-0 fw-bold text-primary">
                        My Activity
                    </h6>

                    <select id="period"
                            class="form-select form-select-sm w-auto">

                        <option value="7days" selected>
                            7 Days
                        </option>

                        <option value="this_week">
                            This Week
                        </option>

                        <option value="this_month">
                            This Month
                        </option>

                        <option value="last_month">
                            Last Month
                        </option>

                    </select>

                </div>

                <div class="card-body p-2">

                    <canvas id="activityChart"
                            style="height: 100%; width: 100%;">
                    </canvas>

                </div>

            </div>

        </div>

    </div>


    <!-- Quick Actions -->
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h6 class="m-0 fw-bold text-primary">
                Quick Actions
            </h6>

        </div>

        <div class="card-body">

            <div class="row g-3">

                <!-- Take Attendance -->
                <div class="col-6 col-md-4 col-lg-3">

                    <a href="<?= BASE_URL ?>/attendance"
                       class="text-decoration-none">

                        <div class="card h-100 text-center shadow-sm border-0">

                            <div class="card-body py-3">

                                <div class="bg-light text-primary mb-2 mx-auto d-inline-block p-3 rounded-circle">
                                    <i class="fas fa-calendar-check fa-lg"></i>
                                </div>

                                <h6 class="fw-bold mb-1 text-dark">
                                    Take Attendance
                                </h6>

                                <p class="text-muted small mb-0">
                                    Record class attendance
                                </p>

                            </div>

                        </div>

                    </a>

                </div>


                <!-- Enter Grades -->
                <div class="col-6 col-md-4 col-lg-3">

                    <a href="<?= BASE_URL ?>/grades"
                       class="text-decoration-none">

                        <div class="card h-100 text-center shadow-sm border-0">

                            <div class="card-body py-3">

                                <div class="bg-light text-success mb-2 mx-auto d-inline-block p-3 rounded-circle">
                                    <i class="fas fa-clipboard-list fa-lg"></i>
                                </div>

                                <h6 class="fw-bold mb-1 text-dark">
                                    Enter Grades
                                </h6>

                                <p class="text-muted small mb-0">
                                    Record student grades
                                </p>

                            </div>

                        </div>

                    </a>

                </div>


                <!-- My Students -->
                <div class="col-6 col-md-4 col-lg-3">

                    <a href="<?= BASE_URL ?>/students"
                       class="text-decoration-none">

                        <div class="card h-100 text-center shadow-sm border-0">

                            <div class="card-body py-3">

                                <div class="bg-light text-warning mb-2 mx-auto d-inline-block p-3 rounded-circle">
                                    <i class="fas fa-users fa-lg"></i>
                                </div>

                                <h6 class="fw-bold mb-1 text-dark">
                                    My Students
                                </h6>

                                <p class="text-muted small mb-0">
                                    View assigned students
                                </p>

                            </div>

                        </div>

                    </a>

                </div>


                <!-- My Schedule -->
                <div class="col-6 col-md-4 col-lg-3">

                    <a href="<?= BASE_URL ?>/schedule"
                       class="text-decoration-none">

                        <div class="card h-100 text-center shadow-sm border-0">

                            <div class="card-body py-3">

                                <div class="bg-light text-info mb-2 mx-auto d-inline-block p-3 rounded-circle">
                                    <i class="fas fa-calendar-alt fa-lg"></i>
                                </div>

                                <h6 class="fw-bold mb-1 text-dark">
                                    My Schedule
                                </h6>

                                <p class="text-muted small mb-0">
                                    View teaching schedule
                                </p>

                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>
```

</main>

<!-- Libraries -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>

<script>

    // Dashboard animation
    const tl = gsap.timeline();

    tl.from(
        ["#card-1", "#card-2", "#card-3", "#card-4"],
        {
            y: -10,
            opacity: 0,
            duration: 0.8,
            stagger: 0.2,
            ease: "power2.out"
        }
    );

</script>

<!-- Driver.js -->

<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<script src="<?= BASE_URL ?>/js/driver.js"></script>

<!-- FullCalendar -->

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>

<script src="<?= BASE_URL ?>/js/home.js"></script>

<?php include __DIR__ .'/partials/footer.php'; ?>
