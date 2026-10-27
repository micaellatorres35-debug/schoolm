<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">

<main class="main-content bg-light pb-5">

    <div class="container-fluid px-4 py-4">

        <div class="d-flex justify-content-between align-items-center mb-1">

            <div>
                <h1 class="fw-bold mb-1 dashboard-title">
                    Dashboard
                </h1>

                <p class="text-muted mb-0">
                    Welcome back! Here's what's happening today.
                </p>
            </div>

            <a href="#" class="btn btn-primary shadow-sm">
                <i class="fas fa-chart-line me-2"></i>
                Generate Report
            </a>

        </div>

        <!-- summary card -->
        <div class="row g-3 mt-3 mb-3">

            <!-- Today's Schedule -->
            <div class="col-xl-3 col-md-6">
                <div class="summary-card schedule-card">

                    <div class="summary-icon blue">
                        <i class="fas fa-calendar"></i>
                    </div>

                    <div class="summary-content">
                        <div class="summary-label">
                            TODAY'S SCHEDULE
                        </div>

                        <div class="summary-value">
                            <span class="count" data-count="3">0</span> Schedules
                        </div>
                    </div>

                    <i class="fas fa-chevron-right summary-arrow"></i>

                </div>
            </div>


            <!-- Total Laboratories -->
            <div class="col-xl-3 col-md-6">
                <div class="summary-card laboratory-card">

                    <div class="summary-icon green">
                        <i class="fas fa-building"></i>
                    </div>

                    <div class="summary-content">
                        <div class="summary-label">
                            TOTAL LABORATORIES
                        </div>

                        <div class="summary-value">
                            <span class="count" data-count="11">0</span> Labs
                        </div>
                    </div>

                    <i class="fas fa-chevron-right summary-arrow"></i>

                </div>
            </div>


            <!-- Damaged Equipment -->
            <div class="col-xl-3 col-md-6">
                <div class="summary-card damage-card">

                    <div class="summary-icon red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>

                    <div class="summary-content">
                        <div class="summary-label">
                            DAMAGED EQUIPMENT
                        </div>

                        <div class="summary-value">
                            <span class="count" data-count="8">0</span> Items
                        </div>
                    </div>

                    <i class="fas fa-chevron-right summary-arrow"></i>

                </div>
            </div>



            <!-- Active Users -->
            <div class="col-xl-3 col-md-6">
                <div class="summary-card users-card">

                    <div class="summary-icon cyan">
                        <i class="fas fa-users"></i>
                    </div>

                    <div class="summary-content">
                        <div class="summary-label">
                            ACTIVE USERS
                        </div>

                        <div class="summary-value">
                            <span class="count" data-count="10">0</span> Users
                        </div>
                    </div>

                    <i class="fas fa-chevron-right summary-arrow"></i>

                </div>
            </div>

        </div>


        <!-- status and chart -->
        <div class="row g-3 mb-3">

            <!-- Equipment Status -->
            <div class="col-xl-8">

                <div class="dashboard-panel h-100">

                    <div class="panel-header">
                        <h5>
                            <i class="fas fa-cog me-2"></i>
                            Equipment Status
                        </h5>
                    </div>

                    <div class="panel-body">

                        <?php

                        $statusCounts = [
                            'available' => 0,
                            'working' => 0,
                            'damage' => 0,
                            'under inspection' => 0,
                            'unavailable' => 0
                        ];

                        foreach ($equipmentStatus as $status) {

                            $statusName = strtolower(trim($status['status']));

                            if (isset($statusCounts[$statusName])) {
                                $statusCounts[$statusName] = $status['total'];
                            }
                        }

                        ?>

                        <div class="row g-3">

                            <!-- Available -->
                            <div class="col">

                                <div class="status-box available">

                                    <div class="status-icon">
                                        <i class="fas fa-check"></i>
                                    </div>

                                    <div class="status-name">
                                        Available
                                    </div>

                                    <div class="status-number">
                                        <?= $statusCounts['available'] ?>
                                    </div>
                                    

                                </div>

                            </div>


                            <!-- Working -->
                            <div class="col">

                                <div class="status-box working">

                                    <div class="status-icon">
                                        <i class="fas fa-cog"></i>
                                    </div>

                                    <div class="status-name">
                                        Working
                                    </div>

                                    <div class="status-number">
                                        <?= $statusCounts['working'] ?>
                                    </div>

                                </div>

                            </div>


                            <!-- Damage -->
                            <div class="col">

                                <div class="status-box damage">

                                    <div class="status-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>

                                    <div class="status-name">
                                        Damage
                                    </div>

                                    <div class="status-number">
                                        <?= $statusCounts['damage'] ?>
                                    </div>

                                </div>

                            </div>


                            <!-- Under Inspection -->
                            <div class="col">

                                <div class="status-box inspection">

                                    <div class="status-icon">
                                        <i class="fas fa-search"></i>
                                    </div>

                                    <div class="status-name">
                                        Under Inspection
                                    </div>

                                    <div class="status-number">
                                        <?= $statusCounts['under inspection'] ?>
                                    </div>

                                </div>

                            </div>


                            <!-- Unavailable -->
                            <div class="col">

                                <div class="status-box unavailable">

                                    <div class="status-icon">
                                        <i class="fas fa-times"></i>
                                    </div>

                                    <div class="status-name">
                                        Unavailable
                                    </div>

                                    <div class="status-number">
                                        <?= $statusCounts['unavailable'] ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Equipment Distribution -->
            <div class="col-xl-4">

                <div class="dashboard-panel h-100">

                    <div class="panel-header">
                        <h5>
                            <i class="fas fa-chart-pie me-2"></i>
                            Equipment Distribution
                        </h5>
                    </div>

                    <div class="panel-body">

                        <div class="chart-container">

                            <canvas id="equipmentChart"></canvas>

                            <div id="equipmentLegend" class="equipment-legend"></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- schedule + damage -->
        <div class="row g-3 mb-3">

            <!-- Today's Schedule -->
            <div class="col-xl-7">

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <h5>
                            <i class="fas fa-calendar-alt me-2"></i>
                            Today's Laboratory Schedule
                        </h5>

                        <a href="<?= BASE_URL ?>/schedule">
                            View All
                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="table dashboard-table mb-0">

                            <thead>
                                <tr>
                                    <th>Laboratory</th>
                                    <th>Subject</th>
                                    <th>Instructor</th>
                                    <th>Section</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>IT Lab 1</td>
                                    <td>Programming</td>
                                    <td>Ms. Santos</td>
                                    <td>BSIT-4A</td>
                                    <td>8:00 - 10:00</td>
                                    <td>
                                        <span class="badge schedule-badge">
                                            Scheduled
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Physics Lab</td>
                                    <td>General Physics</td>
                                    <td>Mr. Reyes</td>
                                    <td>BSCS-2A</td>
                                    <td>10:00 - 12:00</td>
                                    <td>
                                        <span class="badge schedule-badge">
                                            Scheduled
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Chemistry Lab</td>
                                    <td>Organic Chemistry</td>
                                    <td>Dr. Cruz</td>
                                    <td>BSCHM-1A</td>
                                    <td>1:00 - 3:00</td>
                                    <td>
                                        <span class="badge schedule-badge">
                                            Scheduled
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>IT Lab 1</td>
                                    <td>Capstone</td>
                                    <td>Dr. Cruz</td>
                                    <td>BSIS-1A</td>
                                    <td>3:00 - 4:00</td>
                                    <td>
                                        <span class="badge schedule-badge">
                                            Scheduled
                                        </span>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!-- Recent Damage Reports -->
            <div class="col-xl-5">

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <h5>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Recent Damage Reports
                        </h5>

                        <a href="<?= BASE_URL ?>/damages">
                            View All
                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="table dashboard-table mb-0">

                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Laboratory</th>
                                    <th>Status</th>
                                    <th>Date Reported</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>Microscope</td>
                                    <td>Physics Lab</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            Damage
                                        </span>
                                    </td>
                                    <td>Apr 25, 2025</td>
                                </tr>

                                <tr>
                                    <td>Computer</td>
                                    <td>IT Lab 1</td>
                                    <td>
                                        <span class="badge badge-warning">
                                            Under Inspection
                                        </span>
                                    </td>
                                    <td>Apr 24, 2025</td>
                                </tr>

                                <tr>
                                    <td>Projector</td>
                                    <td>Chemistry Lab</td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            Unavailable
                                        </span>
                                    </td>
                                    <td>Apr 22, 2025</td>
                                </tr>

                                <tr>
                                    <td>Balance</td>
                                    <td>Biology Lab</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            Damage
                                        </span>
                                    </td>
                                    <td>Apr 21, 2025</td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <!-- ================= RECENT BORROWINGS ================= -->
        <div class="row">

            <div class="col-12">

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <h5>
                            <i class="fas fa-hand-holding me-2"></i>
                            Recent Borrowings
                        </h5>

                        <a href="<?= BASE_URL ?>/borrow">
                            View All
                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="table dashboard-table mb-0">

                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Borrower</th>
                                    <th>Laboratory</th>
                                    <th>Purpose</th>
                                    <th>Date Borrowed</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>Laptop</td>
                                    <td>Juan Dela Cruz</td>
                                    <td>IT Lab 1</td>
                                    <td>Research</td>
                                    <td>Apr 25, 2025</td>
                                    <td>Apr 28, 2025</td>
                                    <td>
                                        <span class="badge badge-success">
                                            Borrowed
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Camera</td>
                                    <td>Maria Santos</td>
                                    <td>Media Lab</td>
                                    <td>Documentation</td>
                                    <td>Apr 24, 2025</td>
                                    <td>Apr 27, 2025</td>
                                    <td>
                                        <span class="badge badge-success">
                                            Borrowed
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Test Tube Set</td>
                                    <td>Ramon Garcia</td>
                                    <td>Chemistry Lab</td>
                                    <td>Laboratory Activity</td>
                                    <td>Apr 22, 2025</td>
                                    <td>Apr 25, 2025</td>
                                    <td>
                                        <span class="badge schedule-badge">
                                            Returned
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Microphone</td>
                                    <td>Ana Reyes</td>
                                    <td>Audio Lab</td>
                                    <td>Project</td>
                                    <td>Apr 20, 2025</td>
                                    <td>Apr 23, 2025</td>
                                    <td>
                                        <span class="badge schedule-badge">
                                            Returned
                                        </span>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

<script src="js/dashboard.js"></script>

<?php include 'includes/footer.php'; ?>