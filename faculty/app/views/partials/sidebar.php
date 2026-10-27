<aside class="sidebar">
    <div class="school-logo">
        <img src="<?php echo BASE_URL ?>/assets/images/bcp-logo.png" alt="School Logo">


        <div class="sidebar-icons d-flex align-items-center gap-3">

            <button type="button" id="notif-btn" class="btn position-relative p-0 border-0 bg-transparent">
                <i class="fa-regular fa-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    5
                </span>
            </button>

            <button type="button" class="btn p-0 border-0 bg-transparent">
                <i class="fa-regular fa-circle-user fs-5"></i>
            </button>

        </div>


    </div>
    <div class="sidebar-header">
        <div class="user-avatar"><?= $first_two ?></div>
        <h1><?= $name ?></h1>
        <p class="user-id">SAMPLEID123456789</p>
    </div>


    <div class="accordion" id="sidebarMenu">

        <!-- dashboard -->

        <h2>Dashboard</h2>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/home" class="menu-link <?php echo CURRENT_URI  === "home" ? 'active' : '' ?> " id="dashboard">Dashboard</a></li>
        </ul>
       
    

        <!-- student management -->

        <hr>
        <h2>
            <a data-bs-toggle="collapse" href="#studentMenu" class="menu-toggle d-flex justify-content-between align-items-center text-decoration-none text-white">
                Class Management
                <i class="fa-solid fa-chevron-down"></i>
            </a>
        </h2>

        <div id="studentMenu" class="collapse <?php echo in_array(CURRENT_URI, ['class', 'class-schedule']) ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
            <ul>
                <li><a href="<?php echo BASE_URL ?>/class" class="menu-link <?php echo CURRENT_URI  === "class" ? 'active' : '' ?>" id="student">Classes</a></li>
                  <li><a href="<?php echo BASE_URL ?>/class-schedule" class="menu-link <?php echo CURRENT_URI  === "class-schedule" ? 'active' : '' ?>" id="student">Class Schedule</a></li>

            </ul>
        </div>
    

        <!-- settings  -->
        <hr>
        <h2>
            <a data-bs-toggle="collapse" href="#schoolSettings" class="menu-toggle d-flex justify-content-between align-items-center text-decoration-none text-white">
                Settings
                <i class="fa-solid fa-chevron-down"></i>
            </a>
        </h2>
        <div id="schoolSettings" class="collapse <?php echo in_array(CURRENT_URI, ['activity']) ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
            <ul>
                <li><a href="<?php echo BASE_URL ?>students" class="menu-link" id="profile">Profile</a></li>
            </ul>
        </div>
        <hr>
        <div class="mt-3">
            <a href="<?= BASE_URL ?>/logout"
                class="d-flex justify-content-between align-items-center text-decoration-none text-white text-uppercase"
                style="margin-left: 8px;">
                <span>Logout</span>
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

</aside>