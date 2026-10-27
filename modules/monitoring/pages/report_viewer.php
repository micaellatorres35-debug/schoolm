<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Attendance.php';
include_once __DIR__ . '/../classes/Visitor.php';
include_once __DIR__ . '/../classes/Facility.php';
include_once __DIR__ . '/../classes/User.php';

// Create database connection if not available
if (!isset($db)) {
    require_once __DIR__ . '/../../../database/db.php';
    $database = new Database();
    $db = $database->getConnection();
}

$attendance = new Attendance($db);
$visitor = new Visitor($db);
$facility = new Facility($db);
$userClass = new User();
$userInfo = $userClass->userSession();

// Get report parameters
$report_type = $_GET['type'] ?? 'attendance';
$period = $_GET['period'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Calculate date ranges based on period
if ($period == '15days') {
    $start_date = date('Y-m-d', strtotime('-15 days'));
    $end_date = date('Y-m-d');
} elseif ($period == 'monthly') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
} elseif ($period == 'weekly') {
    $start_date = date('Y-m-d', strtotime('monday this week'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));
} elseif ($period == 'yearly') {
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
}

// Get data based on report type
$report_data = [];
$summary_stats = [];

switch($report_type) {
    case 'attendance':  // Changed from 'mon_attendance' to match form
        $report_data = $attendance->getReport($start_date, $end_date);
        
        // Calculate summary stats
        $total_present = 0;
        $total_absent = 0;
        $total_late = 0;
        $total_online = 0;
        $total_onsite = 0;
        
        foreach ($report_data as $row) {
            $total_present += ($row['present_days'] ?? 0);
            $total_absent += ($row['absent_days'] ?? 0);
            $total_late += ($row['late_days'] ?? 0);
            $total_online += ($row['online_classes'] ?? 0);
            $total_onsite += ($row['onsite_classes'] ?? 0);
        }
        
        $summary_stats = [
            'total_classes' => count($report_data),
            'present' => $total_present,
            'absent' => $total_absent,
            'late' => $total_late,
            'online' => $total_online,
            'onsite' => $total_onsite
        ];
        break;
        
    case 'visitor':
        $all_visitors = $visitor->getAll();
        $report_data = array_filter($all_visitors, function($v) use ($start_date, $end_date) {
            $visit_date = date('Y-m-d', strtotime($v['time_in']));
            return $visit_date >= $start_date && $visit_date <= $end_date;
        });
        
        $active_visits = array_filter($report_data, fn($v) => empty($v['time_out']));
        $departments = array_unique(array_column($report_data, 'department'));
        
        $summary_stats = [
            'total_visitors' => count($report_data),
            'active_now' => count($active_visits),
            'departments' => count($departments),
            'avg_visit_time' => calculateAvgVisitTime($report_data) . ' min'
        ];
        break;
        
    case 'facility':
        $all_facilities = $facility->getAll();
        $report_data = array_filter($all_facilities, function($f) use ($start_date, $end_date) {
            return $f['date_reported'] >= $start_date && $f['date_reported'] <= $end_date;
        });
        
        $pending = array_filter($report_data, fn($f) => $f['status'] == 'Pending');
        $fixed = array_filter($report_data, fn($f) => $f['status'] == 'Fixed');
        $high_priority = array_filter($report_data, fn($f) => $f['priority'] == 'High');
        
        $summary_stats = [
            'total_issues' => count($report_data),
            'pending' => count($pending),
            'fixed' => count($fixed),
            'high_priority' => count($high_priority)
        ];
        break;
        
    case 'consolidated':
        $attendance_data = $attendance->getReport($start_date, $end_date);  // Changed from getAttendanceReport
        $all_visitors = $visitor->getAll();
        $visitor_data = array_filter($all_visitors, function($v) use ($start_date, $end_date) {
            $visit_date = date('Y-m-d', strtotime($v['time_in']));
            return $visit_date >= $start_date && $visit_date <= $end_date;
        });
        
        $all_facilities = $facility->getAll();
        $facility_data = array_filter($all_facilities, function($f) use ($start_date, $end_date) {
            return $f['date_reported'] >= $start_date && $f['date_reported'] <= $end_date;
        });
        
        $summary_stats = [
            'total_attendance' => count($attendance_data),
            'total_visitors' => count($visitor_data),
            'total_issues' => count($facility_data),
            'active_visitors' => count(array_filter($visitor_data, fn($v) => empty($v['time_out']))),
            'pending_issues' => count(array_filter($facility_data, fn($f) => $f['status'] == 'Pending')),
            'attendance_rate' => calculateAttendanceRate($attendance_data)
        ];
        
        $report_data = [
            'attendance' => $attendance_data,
            'visitor' => $visitor_data,
            'facility' => $facility_data
        ];
        break;
}

function calculateAvgVisitTime($visitors) {
    $total_time = 0;
    $count = 0;
    foreach ($visitors as $v) {
        if (!empty($v['time_out'])) {
            $time_in = strtotime($v['time_in']);
            $time_out = strtotime($v['time_out']);
            $total_time += ($time_out - $time_in) / 60;
            $count++;
        }
    }
    return $count > 0 ? round($total_time / $count) : 0;
}

function calculateAttendanceRate($data) {
    $total = count($data);
    if ($total == 0) return '0%';
    
    $present = array_sum(array_column($data, 'present_days'));
    $total_days = $present + array_sum(array_column($data, 'absent_days')) + array_sum(array_column($data, 'late_days'));
    
    return $total_days > 0 ? round(($present / $total_days) * 100) . '%' : '0%';
}
?>

<style>
/* ====================================
   MINIMALIST BLUE & WHITE REPORT VIEWER
   ==================================== */

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    /* Blue Palette - Matching other pages */
    --color1: #2c3e50;
    --color2: #34495e;
    --color3: #7f8c8d;
    --color4: #95a5a6;
    --color5: #3498db;
    --color6: #2980b9;
    --color7: #ecf0f1;
    
    /* Status Colors */
    --success: #10b981;
    --success-bg: #d4edda;
    --success-text: #155724;
    --warning: #f59e0b;
    --warning-bg: #fff3cd;
    --warning-text: #856404;
    --danger: #ef4444;
    --danger-bg: #f8d7da;
    --danger-text: #721c24;
    
    /* Priority Colors */
    --priority-low: #10b981;
    --priority-low-bg: #d4edda;
    --priority-medium: #f59e0b;
    --priority-medium-bg: #fff3cd;
    --priority-high: #ef4444;
    --priority-high-bg: #f8d7da;
    
    /* Neutrals */
    --white: #ffffff;
    --off-white: #fafafa;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    
    /* Shadows */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    
    /* Border Radius */
    --radius-sm: 4px;
    --radius-md: 6px;
    --radius-lg: 8px;
    --radius-xl: 12px;
    
    /* Spacing */
    --space-1: 0.25rem;
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-5: 1.25rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
    --space-10: 2.5rem;
}

/* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, sans-serif;
    background: var(--gray-50);
    color: var(--gray-700);
    line-height: 1.5;
}

.container {
    max-width: 1440px;
    margin: 0 auto;
    padding: var(--space-6);
}

/* Module Header - Matching other pages */
.module-header {
    background: linear-gradient(135deg, var(--color5), var(--color6));
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    position: relative;
}

.module-header h1 {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.module-header h1 i {
    font-size: 1.6rem;
}

.module-header p {
    font-size: 0.9rem;
    font-weight: 400;
    opacity: 0.9;
}

.report-period {
    position: absolute;
    top: var(--space-6);
    right: var(--space-8);
    background: rgba(255,255,255,0.2);
    padding: var(--space-2) var(--space-4);
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.report-period i {
    font-size: 0.8rem;
}

.module-content {
    padding: 0 1rem;
}

/* Filter Form - Matching other pages */
.filters {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--color7);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-item {
    flex: 1;
    min-width: 180px;
}

.filter-item label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color2);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-item select,
.filter-item input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--color7);
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
    color: var(--color2);
}

.filter-item select:focus,
.filter-item input:focus {
    outline: none;
    border-color: var(--color5);
}

.filter-item .btn {
    background: var(--color5);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-item .btn:hover {
    background: var(--color6);
}

/* Statistics Grid - Matching dashboard */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid var(--color7);
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: var(--color5);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(52, 152, 219, 0.2);
}

.stat-icon {
    width: 48px;
    height: 48px;
    background: var(--color7);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--color5);
    flex-shrink: 0;
}

.stat-info h3 {
    color: var(--color2);
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
}

.stat-info .stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color1);
    line-height: 1.2;
}

/* Charts Container */
.charts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    border: 1px solid var(--color7);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-card h3 {
    color: var(--color2);
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--color7);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-card h3 i {
    color: var(--color5);
}

/* Navigation Links - Matching footer */
.nav-links {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 1rem;
    border: 1px solid var(--color7);
    background: white;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.85rem;
    color: var(--color2);
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn:hover {
    border-color: var(--color5);
    color: var(--color5);
}

.btn-primary {
    background: var(--color5);
    color: white;
    border-color: var(--color5);
}

.btn-primary:hover {
    background: var(--color6);
    border-color: var(--color6);
    color: white;
}

/* Export Buttons */
.export-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.btn-export {
    background: white;
    border: 1px solid var(--color7);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--color2);
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-export:hover {
    border-color: var(--color5);
    color: var(--color5);
}

.btn-export i {
    color: var(--color5);
}

/* Form Section - Matching other pages */
.form-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
    border: 1px solid var(--color7);
}

.form-section-header {
    background: linear-gradient(135deg, var(--color5), var(--color6));
    padding: 1rem 1.5rem;
}

.form-section-header h2,
.form-section-header h3 {
    margin: 0;
    color: white;
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-header h2 i,
.form-section-header h3 i {
    font-size: 1rem;
}

.form-body {
    padding: 1.5rem;
}

/* Table Container - Matching attendance_form */
.table-container {
    background: white;
    border-radius: 8px;
    border: 1px solid var(--color7);
    overflow: auto;
    margin-top: 0.5rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
    font-size: 0.9rem;
}

.data-table th {
    background: var(--color7);
    color: var(--color2);
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    border-bottom: 2px solid var(--color4);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--color7);
    color: var(--color2);
    vertical-align: middle;
}

.data-table tbody tr:hover {
    background: var(--gray-50);
}

.data-table tr:last-child td {
    border-bottom: none;
}

/* Badge Styles - Matching other pages */
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-present { background: var(--success-bg); color: var(--success-text); }
.badge-absent { background: var(--danger-bg); color: var(--danger-text); }
.badge-late { background: var(--warning-bg); color: var(--warning-text); }
.badge-pending { background: var(--warning-bg); color: var(--warning-text); }
.badge-fixed { background: var(--success-bg); color: var(--success-text); }
.badge-high { background: var(--danger-bg); color: var(--danger-text); }
.badge-medium { background: var(--warning-bg); color: var(--warning-text); }
.badge-low { background: var(--success-bg); color: var(--success-text); }
.badge-active { background: var(--success-bg); color: var(--success-text); }
.badge-completed { background: var(--gray-100); color: var(--gray-600); }

/* Section Title */
.section-title {
    color: var(--color2);
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-left: 0.5rem;
}

.section-title i {
    color: var(--color5);
}

/* Consolidated Sections */
.consolidated-section {
    margin-bottom: 2rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--color3);
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--color4);
}

.empty-state h3 {
    font-weight: 500;
    color: var(--color2);
    margin-bottom: 0.25rem;
}

.empty-state p {
    color: var(--color3);
}

/* Footer */
.footer {
    text-align: center;
    margin-top: var(--space-8);
    padding: var(--space-6);
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    color: var(--gray-500);
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .container { padding: var(--space-4); }
    
    .module-header { padding: 1rem; }
    .module-header h1 { font-size: 1.3rem; }
    
    .report-period {
        position: static;
        margin-top: var(--space-4);
        display: inline-flex;
    }
    
    .filter-group {
        flex-direction: column;
    }
    
    .filter-item {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .charts-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .form-body {
        padding: 1rem;
    }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: var(--color7);
}

::-webkit-scrollbar-thumb {
    background: var(--color4);
    border-radius: 20px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--color3);
}
</style>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="container">
    <!-- Header -->
    <div class="module-header">
        <h1>
            <i class="fas fa-chart-bar"></i>
            <?= ucfirst($report_type) ?> Reports
        </h1>
        <p>Generate and analyze system reports</p>
        <div class="report-period">
            <i class="fas fa-calendar-alt"></i>
            <?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filters">
        <form method="GET" class="filter-group">
            <input type="hidden" name="page" value="report_viewer">
            
            <div class="filter-item">
                <label>Report Type</label>
                <select name="type" onchange="this.form.submit()">
                    <option value="attendance" <?= $report_type == 'attendance' ? 'selected' : '' ?>>Attendance Report</option>
                    <option value="visitor" <?= $report_type == 'visitor' ? 'selected' : '' ?>>Visitor Log Report</option>
                    <option value="facility" <?= $report_type == 'facility' ? 'selected' : '' ?>>Facility Issues Report</option>
                    <option value="consolidated" <?= $report_type == 'consolidated' ? 'selected' : '' ?>>Consolidated Report</option>
                </select>
            </div>

            <div class="filter-item">
                <label>Period</label>
                <select name="period" onchange="this.form.submit()">
                    <option value="weekly" <?= $period == 'weekly' ? 'selected' : '' ?>>This Week</option>
                    <option value="15days" <?= $period == '15days' ? 'selected' : '' ?>>Last 15 Days</option>
                    <option value="monthly" <?= $period == 'monthly' ? 'selected' : '' ?>>This Month</option>
                    <option value="yearly" <?= $period == 'yearly' ? 'selected' : '' ?>>This Year</option>
                    <option value="custom" <?= $period == 'custom' ? 'selected' : '' ?>>Custom Range</option>
                </select>
            </div>

            <?php if ($period == 'custom'): ?>
            <div class="filter-item">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" required>
            </div>

            <div class="filter-item">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" required>
            </div>

            <div class="filter-item">
                <button type="submit" class="btn">
                    <i class="fas fa-search"></i> Generate
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Statistics Cards -->
    <?php if (!empty($summary_stats)): ?>
    <div class="stats-grid">
        <?php foreach ($summary_stats as $key => $value): ?>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-<?php 
                    $icons = [
                        'total_classes' => 'calendar-check',
                        'present' => 'user-check',
                        'absent' => 'user-times',
                        'late' => 'clock',
                        'online' => 'wifi',
                        'onsite' => 'building',
                        'total_visitors' => 'users',
                        'active_now' => 'user-clock',
                        'departments' => 'building',
                        'avg_visit_time' => 'hourglass-half',
                        'total_issues' => 'exclamation-triangle',
                        'pending' => 'hourglass-half',
                        'fixed' => 'check-circle',
                        'high_priority' => 'exclamation-circle',
                        'total_attendance' => 'calendar-check',
                        'active_visitors' => 'user-clock',
                        'pending_issues' => 'tools',
                        'attendance_rate' => 'percentage'
                    ];
                    echo $icons[$key] ?? 'chart-line';
                ?>"></i>
            </div>
            <div class="stat-info">
                <h3><?= ucwords(str_replace('_', ' ', $key)) ?></h3>
                <div class="stat-value"><?= $value ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Charts Container -->
    <?php if (!empty($summary_stats)): ?>
    <div class="charts-container">
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Distribution</h3>
            <canvas id="distributionChart" style="max-height: 250px;"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Trend Analysis</h3>
            <canvas id="trendChart" style="max-height: 250px;"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation Links -->
    <div class="nav-links">
        <a href="index.php" class="btn">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="?page=attendance_form" class="btn">
            <i class="fas fa-clipboard-check"></i> Attendance
        </a>
        <a href="?page=visitor_log" class="btn">
            <i class="fas fa-user-clock"></i> Visitor Log
        </a>
        <a href="?page=facilities_monitor" class="btn">
            <i class="fas fa-tools"></i> Facilities
        </a>
    </div>

    <!-- Export Buttons -->
    <?php if (!empty($report_data)): ?>
    <div class="export-buttons">
        <button class="btn-export" onclick="exportToPDF()">
            <i class="fas fa-file-pdf"></i> Export PDF
        </button>
        <button class="btn-export" onclick="exportToExcel()">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
        <button class="btn-export" onclick="printReport()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
    <?php endif; ?>

    <!-- Report Tables -->
    <?php if ($report_type == 'consolidated'): ?>
        <!-- Consolidated Report -->
        <div class="consolidated-section">
            <h3 class="section-title"><i class="fas fa-calendar-check"></i> Attendance Records</h3>
            <div class="form-section">
                <div class="form-section-header">
                    <h3>Attendance Summary</h3>
                </div>
                <div class="form-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Faculty</th>
                                    <th>Subject</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Online</th>
                                    <th>Onsite</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report_data['attendance'])): ?>
                                    <?php foreach ($report_data['attendance'] as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['last_name'] ?? '') ?>, <?= htmlspecialchars($row['first_name'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['subject_code'] ?? '') ?></td>
                                        <td><?= $row['present_days'] ?? 0 ?></td>
                                        <td><?= $row['absent_days'] ?? 0 ?></td>
                                        <td><?= $row['late_days'] ?? 0 ?></td>
                                        <td><?= $row['online_classes'] ?? 0 ?></td>
                                        <td><?= $row['onsite_classes'] ?? 0 ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            <i class="fas fa-calendar-times"></i>
                                            <p>No attendance records found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="consolidated-section">
            <h3 class="section-title"><i class="fas fa-users"></i> Visitor Logs</h3>
            <div class="form-section">
                <div class="form-section-header">
                    <h3>Visitor Records</h3>
                </div>
                <div class="form-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Visitor Name</th>
                                    <th>Purpose</th>
                                    <th>Department</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report_data['visitor'])): ?>
                                    <?php foreach ($report_data['visitor'] as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['visitor_name'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['purpose'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['department'] ?? '') ?></td>
                                        <td><?= date('M d, H:i', strtotime($row['time_in'])) ?></td>
                                        <td><?= !empty($row['time_out']) ? date('H:i', strtotime($row['time_out'])) : '--' ?></td>
                                        <td>
                                            <span class="badge <?= empty($row['time_out']) ? 'badge-active' : 'badge-completed' ?>">
                                                <?= empty($row['time_out']) ? 'Active' : 'Completed' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <i class="fas fa-user-clock"></i>
                                            <p>No visitor records found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="consolidated-section">
            <h3 class="section-title"><i class="fas fa-tools"></i> Facility Issues</h3>
            <div class="form-section">
                <div class="form-section-header">
                    <h3>Issues Report</h3>
                </div>
                <div class="form-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Issue Type</th>
                                    <th>Description</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report_data['facility'])): ?>
                                    <?php foreach ($report_data['facility'] as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['room'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['issue_type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($row['priority'] ?? '') ?>">
                                                <?= $row['priority'] ?? '' ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($row['date_reported'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($row['status'] ?? '') ?>">
                                                <?= $row['status'] ?? '' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <i class="fas fa-tools"></i>
                                            <p>No facility issues found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Regular Report -->
        <div class="form-section">
            <div class="form-section-header">
                <h2>
                    <i class="fas fa-list"></i>
                    Detailed <?= ucfirst($report_type) ?> Records
                </h2>
            </div>
            <div class="form-body">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php if ($report_type == 'attendance'): ?>
                                <th>Faculty</th>
                                <th>Subject</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Online</th>
                                <th>Onsite</th>
                                <?php elseif ($report_type == 'visitor'): ?>
                                <th>Visitor</th>
                                <th>Purpose</th>
                                <th>Person to Visit</th>
                                <th>Department</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                                <?php elseif ($report_type == 'facility'): ?>
                                <th>Room</th>
                                <th>Issue Type</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Status</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php if ($report_type == 'attendance'): ?>
                                    <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['last_name'] ?? '') ?>, <?= htmlspecialchars($row['first_name'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['subject_code'] ?? '') ?></td>
                                        <td><?= $row['present_days'] ?? 0 ?></td>
                                        <td><?= $row['absent_days'] ?? 0 ?></td>
                                        <td><?= $row['late_days'] ?? 0 ?></td>
                                        <td><?= $row['online_classes'] ?? 0 ?></td>
                                        <td><?= $row['onsite_classes'] ?? 0 ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php elseif ($report_type == 'visitor'): ?>
                                    <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['visitor_name'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['purpose'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['person_to_visit'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['department'] ?? '') ?></td>
                                        <td><?= date('M d, H:i', strtotime($row['time_in'])) ?></td>
                                        <td><?= !empty($row['time_out']) ? date('H:i', strtotime($row['time_out'])) : '--' ?></td>
                                        <td>
                                            <span class="badge <?= empty($row['time_out']) ? 'badge-active' : 'badge-completed' ?>">
                                                <?= empty($row['time_out']) ? 'Active' : 'Completed' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php elseif ($report_type == 'facility'): ?>
                                    <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['room'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['issue_type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($row['priority'] ?? '') ?>">
                                                <?= $row['priority'] ?? '' ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($row['date_reported'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($row['status'] ?? '') ?>">
                                                <?= $row['status'] ?? '' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-chart-bar"></i>
                                        <h3>No Data Found</h3>
                                        <p>Try adjusting your filters or selecting a different date range</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p>Report Viewer © <?= date('Y') ?> | Office of Safety and Security</p>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Initialize charts
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($summary_stats)): ?>
        // Distribution Chart
        const ctx1 = document.getElementById('distributionChart').getContext('2d');
        const chartLabels = <?= json_encode(array_map(function($label) {
            return ucwords(str_replace('_', ' ', $label));
        }, array_keys($summary_stats))) ?>;
        const chartData = <?= json_encode(array_values($summary_stats)) ?>;
        
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: [
                        '#3498db', '#10b981', '#ef4444', '#f59e0b',
                        '#8b5cf6', '#ec4899', '#6366f1', '#14b8a6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            font: { size: 10, family: 'Inter' }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Trend Chart
        const ctx2 = document.getElementById('trendChart').getContext('2d');
        let trendLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        let trendData = [];
        
        <?php if ($report_type == 'attendance'): ?>
            trendData = [<?= $summary_stats['present'] ?? 0 ?>, 
                        <?= $summary_stats['absent'] ?? 0 ?>, 
                        <?= $summary_stats['late'] ?? 0 ?>, 
                        <?= $summary_stats['online'] ?? 0 ?>];
        <?php elseif ($report_type == 'visitor'): ?>
            trendData = [<?= $summary_stats['total_visitors'] ?? 0 ?>, 
                        <?= $summary_stats['active_now'] ?? 0 ?>, 
                        <?= $summary_stats['departments'] ?? 0 ?>, 
                        <?= (int) str_replace(' min', '', $summary_stats['avg_visit_time'] ?? 0) ?>];
        <?php elseif ($report_type == 'facility'): ?>
            trendData = [<?= $summary_stats['total_issues'] ?? 0 ?>, 
                        <?= $summary_stats['pending'] ?? 0 ?>, 
                        <?= $summary_stats['fixed'] ?? 0 ?>, 
                        <?= $summary_stats['high_priority'] ?? 0 ?>];
        <?php else: ?>
            trendData = [<?= $summary_stats['total_attendance'] ?? 0 ?>, 
                        <?= $summary_stats['total_visitors'] ?? 0 ?>, 
                        <?= $summary_stats['total_issues'] ?? 0 ?>, 
                        <?= (int) str_replace('%', '', $summary_stats['attendance_rate'] ?? 0) ?>];
        <?php endif; ?>
        
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: '<?= ucfirst($report_type) ?>',
                    data: trendData,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.05)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#3498db',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.03)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
        <?php endif; ?>
    });

    // Export functions
    function exportToPDF() {
        window.print();
    }

    function exportToExcel() {
        const tables = document.querySelectorAll('.data-table');
        let csv = [];
        
        tables.forEach((table, index) => {
            if (index > 0) csv.push('');
            
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td, th');
                const rowData = [];
                cells.forEach(cell => {
                    rowData.push('"' + cell.innerText.replace(/"/g, '""') + '"');
                });
                csv.push(rowData.join(','));
            });
        });
        
        if (csv.length > 0) {
            const csvContent = csv.join('\n');
            const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'report_<?= $report_type ?>_<?= date('Y-m-d') ?>.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } else {
            alert('No data to export');
        }
    }

    function printReport() {
        window.print();
    }
</script>