<aside class="sidebar">
    <div class="school-logo">
        <img src="<?php echo BASE_URL ?>/assets/images/bcp-logo.png" alt="School Logo">


       <div class="sidebar-icons d-flex align-items-center gap-3">
    
                <button type="button" id="notif-btn" class="btn position-relative p-0 border-0 bg-transparent">
                    <i class="fa-regular fa-bell fs-5"></i>
                    <span id="notifCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        
                    </span>
                </button>

        <div class="dropdown">
               <button
                type="button"
                id="user-btn"
                class="btn p-0 border-0 bg-transparent"
                data-bs-toggle="dropdown"
                aria-expanded="false"
               >
               <i class="fa-regular fa-circle-user fs-5"></i>
             </button>

        <ul class="dropdown-menu dropdown-menu-end">
           <li>
             <a class="dropdown-item" href="#">
                Profile
            </a>
           </li>

           <li>
            <a class="dropdown-item" href="#">
                Settings
            </a>
           </li>

           <li><hr class="dropdown-divider"></li>

           <li>
            
             <a class="dropdown-item text-danger" href="sms/auth/logout.php" >
                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </a>
          </li>
         </ul>
        </div>

         </div>
       

        </div>


    </div>
    <div class="sidebar-header">
        <div class="user-avatar"><?= $first_two ?></div>
        <h1><?= $name ?></h1>
        <p class="user-id"><?= $studentSchoolInfo['student_number'] ?></p>
    </div>


    <div class="accordion" id="sidebarMenu">

        <!-- dashboard -->

        <h2>Dashboard</h2>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/home" class="menu-link <?php echo CURRENT_URI  === "home" ? 'active' : '' ?> " id="dashboard">Dashboard</a></li>
        </ul>
         <ul>
            <li><a href="<?php echo BASE_URL ?>/grades" class="menu-link <?php echo CURRENT_URI  === "grades" ? 'active' : '' ?> " id="dashboard">Semestal Grades</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/enrollment" class="menu-link <?php echo CURRENT_URI  === "enrollment" ? 'active' : '' ?> " id="dashboard">Enrollment</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/events" class="menu-link <?php echo CURRENT_URI  === "events" ? 'active' : '' ?> " id="dashboard">Events</a></li>
        </ul>

        <div class="separator"></div>
        <h2>Scholarship</h2>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/scholarship" class="menu-link <?php echo CURRENT_URI  === "scholarship" ? 'active' : '' ?> " id="dashboard">
                Scholarship</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/scholarship-offered" class="menu-link <?php echo CURRENT_URI  === "scholarship-offered" ? 'active' : '' ?> " id="dashboard">
                Scholarship Offerred</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/my-applications" class="menu-link <?php echo CURRENT_URI  === "my-applications" ? 'active' : '' ?> " id="dashboard">
                My Applications</a></li>
        </ul>
 
        
        <div class="separator"></div>
        <!-- settings  -->
        <h2>
            <a href="#schoolSettings" class="menu-toggle d-flex justify-content-between align-items-center text-decoration-none text-white">
                Settings
            </a>
        </h2>
        <div id="schoolSettings"  <?php echo in_array(CURRENT_URI, ['activity']) ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
            <ul>
                <li><a href="<?php echo BASE_URL ?>students" class="menu-link" id="profile">Profile</a></li>
            </ul>
        </div>
        <div class="separator"></div>
 
         <!-- request -->
        <h2>
            <a href="#docuRequest" class="d-flex justify-content-between align-items-center text-decoration-none text-white">
                Request Documents
            </a>
        </h2>
        <div id="docuRequest"<?php echo in_array(CURRENT_URI, ['document-request','document-status']) ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
 
         <!-- request -->
        <div id="docuRequest" <?php echo in_array(CURRENT_URI, ['document-request','document-status']) ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
            <ul>
                <li><a href="<?php echo BASE_URL ?>/document-request" class="menu-link <?php echo CURRENT_URI  === "document-request" ? 'active' : '' ?> " id="request">Document Request</a></li>
                <li><a href="<?php echo BASE_URL ?>/document-status" class="menu-link <?php echo CURRENT_URI  === "document-status" ? 'active' : '' ?>" id="status">Document Status</a></li>
            </ul>
        </div>
        <div class="separator"></div>


        <div class="mt-3">
            <a href="<?= BASE_URL ?>/logout"
                class="d-flex justify-content-between align-items-center text-decoration-none text-white"
                style="margin-left: 8px;">
                <span>Logout</span>
            </a>
        </div>
    </div>

</aside>