<?php include __DIR__ .'/partials/sidebar.php'; ?>
<?php include  __DIR__ .'/partials/header.php'; ?>

<main class="main-content bg-light pb-5">
    <div class="container-fluid px-4">
        
        <div class="py-4" >
            <div id="dashboard-title">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">Dashboard</h1>
                <p class="text-muted small mb-0">Welcome back! Here's what's happening today.</p>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6" id="card-1">
                <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Enrolled Students</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="enrolled">1,240</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6" id="card-2">
                <div class="card border-0 border-start border-success border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1" >Subjects Offered</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="subject-offered">21</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-book fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6" id="card-3">
                <div class="card border-0 border-start border-info border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">COR Requests</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="corRequests">12</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6" id="card-4">
                <div class="card border-0 border-start border-warning border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">TOR Requests</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="torRequests">5</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6" id="card-5">
                <div class="card border-0 border-start border-warning border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Reports</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="pendingReports">5</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

              <div class="col-xl-3 col-md-6" id="card-6">
                <div class="card border-0 border-start border-danger border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Total Students</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="totalStudents">12</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


             <div class="col-xl-3 col-md-6" id="card-6">
                <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Curriculums</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="curriculums">12</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <div class="col-xl-3 col-md-6" id="card-6">
                <div class="card border-0 border-start border-danger border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center no-gutters">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Offered Programs</div>
                                <div class="h5 mb-0 fw-bold text-gray-800" id="offeredPrograms">12</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

     <div class="row d-flex align-items-stretch">

     <!-- RECENT ENROLLEES -->

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">

        <!-- Header -->
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">
                Recent Enrollees
            </h6>

            <a href="<?= BASE_URL ?>/students"
               class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>

        <!-- Body -->
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Student No.</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                            <th>Date Enrolled</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="recentEnrollees">

                         <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Loading enrollees...
                                </td>
                            </tr>

                    </tbody>

                </table>
            </div>

        </div>

    </div>
</div>


</div>

    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary">Activity Calendar</h6>
            </div>
            <div class="card-body">
                <div id='homeCalendar' style="height: 100%; width: 100%;"></div>
            </div>
        </div>
     </div>

    <div class="col-lg-4 mb-4 d-flex flex-column">
        
      
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">System Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3 small">
                    Maintenance Mode: <span class="badge bg-success float-end">Off</span>
                </div>
                <div class="mb-0 small">
                        CPU Usage:  <?= $cpu ?>%      
                    <div class="progress mt-2" style="height: 8px;">
                        <div id="cpuBar" class="progress-bar bg-info" style="width:  <?= $cpu ?>%"></div>
                    </div>

                </div>
            </div>
        </div>

     
        <div class="card shadow-sm border-0 flex-grow-1"> 
            <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h6 class="m-0 fw-bold text-primary">Activity</h6>

                 <select id="period" class="form-select form-select-sm w-auto">
                    <option value="7days" selected>7 Days</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                </select>
            </div>

            
            <div class="card-body p-2">
                <canvas id="activityChart" style="height: 100%; width: 100%;"></canvas>
            </div>
        </div>

    </div>
</div>





  

    </div> 

  

</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script>

  const tl = gsap.timeline();

  tl.from(["#card-1", "#card-2", "#card-3",'#card-4','#card-5','#card-6'], { 
            y: -10, 
            opacity: 0, 
            duration: 1, 
            stagger: 0.4, 
            ease: "power2.out" 
        });

</script>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
<script src="<?php echo BASE_URL ?>/js/driver.js"></script>
<script> 
    const BASE_URL = "<?php echo BASE_URL ?>"
    const cpu = "<?= $cpu ?>"
</script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js'></script>
<script src="<?php echo BASE_URL ?>/js/home.js"></script>


<?php include  __DIR__ .'/partials/footer.php'; ?>

