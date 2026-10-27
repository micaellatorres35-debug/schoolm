<?php
include_once __DIR__ . '/../../../auth/session.php';
include __DIR__ . '/../classes/Employee.php';
$employeeClass = new Employee();

// Get current page for active state
$current_page = $_GET['page'] ?? 'dashboard-overview';
?>

<!-- Link to external sidebar CSS -->
 

<aside class="sidebar">
    <div class="school-logo">
        <img src="assets/bcp-logo.png" alt="School Logo">
        <div class="sidebar-icons">

            <!-- Bell Icon + Notification Dropdown -->
            <div class="icon-wrapper" id="bellWrapper">
                <i class="fa-regular fa-bell" id="bellBtn"></i>
                <div class="icon-dropdown" id="bellDropdown">
                    <div class="dropdown-header">
                        <span>Notifications</span>
                        <button class="mark-all-read">Mark all as read</button>
                    </div>
                    <ul class="notif-list">
                        <li class="notif-item">
                            <!-- Notification items will go here -->
                        </li>
                    </ul>
                    <div class="dropdown-footer">
                        <a href="#">View all notifications</a>
                    </div>
                </div>
            </div>

            <!-- User Icon + Profile Dropdown -->
            <div class="icon-wrapper" id="userWrapper">
                <i class="fa-regular fa-circle-user" id="userBtn"></i>
                <div class="icon-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-user-info">
                            <div class="dropdown-avatar">
                                <?= substr(htmlspecialchars($employeeClass->getEmployeeName() ?: 'U'), 0, 1) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($employeeClass->getEmployeeName() ?: 'User') ?></strong>
                                <span><?= htmlspecialchars($employeeClass->getEmployeePosition() ?: 'Staff') ?></span>
                            </div>
                        </div>
                    </div>
                    <ul class="user-menu">
                        <li>
                            <a href="#"><i class="fa-regular fa-user"></i> Profile Settings</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa-solid fa-lock"></i> Change Password</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="/sms/auth/logout.php" class="signout-link">
                                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
    
    <div class="sidebar-header">
        <div class="user_avatar">
            <?= substr(htmlspecialchars($employeeClass->getEmployeeName() ?: 'U'), 0, 1) ?>
        </div>
        <h1 class="employee_name"><?= htmlspecialchars($employeeClass->getEmployeeName() ?: 'User') ?></h1>
        <p class="employee_position"><?= htmlspecialchars($employeeClass->getEmployeePosition() ?: 'Staff') ?></p>
    </div>
    
    <h2>Directress Dashboard</h2>
    
    <ul>
    <li>
        <a href="?page=dashboard-overview" class="menu-link <?= $current_page == 'dashboard-overview' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard Overview
        </a>
    </li>
    
    <li class="section-header">
        <div class="separator"></div>
        <h3>Requests & Reports</h3>
    </li>
 
    
    <li class="section-header">
        <div class="separator"></div>
        <h3>Monitoring</h3>
    </li>
    <li>
        <a href="?page=attendance_form" class="menu-link <?= $current_page == 'attendance_form' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> Attendance Form
        </a>
    </li>
    <li>
        <a href="?page=visitor_log" class="menu-link <?= $current_page == 'visitor_log' ? 'active' : '' ?>">
            <i class="fas fa-user-clock"></i> Visitor Log
        </a>
    </li>
    <li>
        <a href="?page=facilities_monitor" class="menu-link <?= $current_page == 'facilities_monitor' ? 'active' : '' ?>">
            <i class="fas fa-tools"></i> Facilities Monitor
        </a>
    </li>
    
    <li class="section-header">
        <div class="separator"></div>
        <h3>Analytics</h3>
    </li>
    <li>
        <a href="?page=report_viewer" class="menu-link <?= $current_page == 'report_viewer' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
    </li>

    <li>
        <a href="?page=report-submission" class="menu-link <?= $current_page == 'report-submission' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Report Submission
        </a>
    </li>
    <li>
        <a href="?page=concern-submission" class="menu-link <?= $current_page == 'concern-submission' ? 'active' : '' ?>">
            <i class="fas fa-exclamation-triangle"></i> Concern Submission
        </a>
    </li>
    <li>
        <a href="?page=approval-submission" class="menu-link <?= $current_page == 'approval-submission' ? 'active' : '' ?>">
            <i class="fas fa-check-circle"></i> Approval Submission
        </a>
    </li>
    
</ul>
</aside>