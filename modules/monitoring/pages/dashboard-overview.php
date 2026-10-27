<?php
// Include required classes
require_once __DIR__ . '/../classes/Attendance.php';
require_once __DIR__ . '/../classes/Faculty.php';
require_once __DIR__ . '/../classes/Schedule.php';
require_once __DIR__ . '/../../../database/db.php';

// Create database connection if not available
if (!isset($db)) {
    $database = new Database();
    $db = $database->getConnection();
}

// Initialize classes
$attendance = new Attendance($db);
$faculty = new Faculty($db);
$schedule = new Schedule($db);

// Get current date information
$today = date('Y-m-d');
$day_of_week = date('l');
$current_time = date('H:i:s');

// Get statistics with error handling
try {
    $total_faculty = $db->query("SELECT COUNT(*) as count FROM cc_faculty")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $total_faculty = 0;
}

try {
    $today_attendance = $db->query("
        SELECT COUNT(DISTINCT a.faculty_id) as count 
        FROM mon_attendance a 
        WHERE a.attendance_date = '$today' 
        AND a.status = 'Present'
    ")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $today_attendance = 0;
}

try {
    $today_classes = $db->query("
        SELECT COUNT(*) as count 
        FROM cc_schedule s 
        WHERE s.day_of_week = '$day_of_week' 
        AND s.semester = '2nd Sem' 
        AND s.school_year = '2025-2026'
    ")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $today_classes = 0;
}

try {
    $late_today = $db->query("
        SELECT COUNT(*) as count 
        FROM mon_attendance a 
        WHERE a.attendance_date = '$today' 
        AND a.status = 'Late'
    ")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $late_today = 0;
}

try {
    $recent_attendance = $db->query("
        SELECT a.*, f.first_name, f.last_name, s.room, s.subject_code
        FROM mon_attendance a
        JOIN cc_faculty f ON a.faculty_id = f.id
        JOIN cc_schedule s ON a.schedule_id = s.id
        ORDER BY a.recorded_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_attendance = [];
}

// Get upcoming classes for today
$current_hour = date('H');
$time_slot = '';
if ($current_hour >= 7 && $current_hour < 10) {
    $time_slot = '7:30-10:00';
} elseif ($current_hour >= 10 && $current_hour < 12) {
    $time_slot = '10:00-12:30';
} elseif ($current_hour >= 12 && $current_hour < 15) {
    $time_slot = '12:30-3:00';
} elseif ($current_hour >= 15 && $current_hour < 18) {
    $time_slot = '3:00-5:30';
}

if ($time_slot) {
    try {
        $upcoming_classes = $db->query("
            SELECT s.*, f.first_name, f.last_name, sec.section_code
            FROM cc_schedule s
            LEFT JOIN cc_faculty f ON s.faculty_id = f.id
            LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
            WHERE s.day_of_week = '$day_of_week'
            AND s.official_time = '$time_slot'
            AND s.semester = '2nd Sem'
            AND s.school_year = '2025-2026'
            ORDER BY s.room
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $upcoming_classes = [];
    }
} else {
    $upcoming_classes = [];
}

// Get attendance summary for the week
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));

try {
    $weekly_summary = $db->query("
        SELECT 
            a.status,
            COUNT(*) as count
        FROM mon_attendance a
        WHERE a.attendance_date BETWEEN '$week_start' AND '$week_end'
        GROUP BY a.status
        ORDER BY COUNT(*) DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $weekly_summary = [];
}
?>

<style>
/* ====================================
   MINIMALIST BLUE & WHITE DASHBOARD
   ==================================== */

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    /* Blue Palette - Matching attendance_form */
    --color1: #2c3e50;
    --color2: #34495e;
    --color3: #7f8c8d;
    --color4: #95a5a6;
    --color5: #3498db;
    --color6: #2980b9;
    --color7: #ecf0f1;
    
    /* Status Colors */
    --present: #10b981;
    --present-bg: #d4edda;
    --present-text: #155724;
    --absent: #ef4444;
    --absent-bg: #f8d7da;
    --absent-text: #721c24;
    --late: #f59e0b;
    --late-bg: #fff3cd;
    --late-text: #856404;
    
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

/* Module Header - Matching attendance_form */
.module-header {
    background: linear-gradient(135deg, var(--color5), var(--color6));
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.module-header h1 {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.module-header p {
    font-size: 0.9rem;
    font-weight: 400;
    opacity: 0.9;
}

.current-time {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    display: inline-block;
    background: rgba(255,255,255,0.2);
    padding: 0.25rem 1rem;
    border-radius: 30px;
}

.module-content {
    padding: 0 1rem;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid var(--color7);
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    border-color: var(--color5);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(52, 152, 219, 0.2);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: var(--color7);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--color5);
    flex-shrink: 0;
}

.stat-info h3 {
    color: var(--color2);
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 600;
    color: var(--color1);
    line-height: 1.2;
    margin-bottom: 0.25rem;
}

.stat-info p {
    color: var(--color3);
    font-size: 0.75rem;
}

.stat-loading {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.loading-spinner {
    width: 30px;
    height: 30px;
    border: 2px solid var(--color7);
    border-top-color: var(--color5);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Form Section - Matching attendance_form */
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

.form-section-header h3 {
    margin: 0;
    color: white;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-header h3 i {
    font-size: 1rem;
}

/* Table Container */
.table-container {
    padding: 1.5rem;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.data-table th {
    background: var(--color7);
    color: var(--color2);
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    border-bottom: 2px solid var(--color4);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--color7);
    color: var(--color2);
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.data-table tr:last-child td {
    border-bottom: none;
}

/* Status Badges - Matching attendance_form */
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-present {
    background: var(--present-bg);
    color: var(--present-text);
}

.badge-absent {
    background: var(--absent-bg);
    color: var(--absent-text);
}

.badge-late {
    background: var(--late-bg);
    color: var(--late-text);
}

/* Room Badge - Matching attendance_form */
.room-badge {
    background: var(--color5);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

/* Empty State - Matching attendance_form */
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

/* Weekly Summary Progress Bars */
.progress-container {
    width: 160px;
    background: var(--color7);
    border-radius: 20px;
    overflow: hidden;
    height: 8px;
}

.progress-bar {
    height: 8px;
    border-radius: 20px;
    transition: width 0.3s ease;
}

/* Global Loading Overlay */
.global-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    transition: opacity 0.3s ease;
}

.loading-content {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid var(--color7);
}

.loading-spinner-large {
    width: 40px;
    height: 40px;
    border: 2px solid var(--color7);
    border-top-color: var(--color5);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 1rem;
}

.loading-content h3 {
    color: var(--color1);
    margin-bottom: 0.25rem;
}

.loading-content p {
    color: var(--color3);
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .container { padding: var(--space-4); }
    
    .module-header { padding: 1rem; }
    .module-header h1 { font-size: 1.3rem; }
    
    .stats-grid { gap: 1rem; }
    
    .stat-card { padding: 1rem; }
    .stat-icon { width: 50px; height: 50px; font-size: 1.2rem; }
    .stat-value { font-size: 1.5rem; }
    
    .table-container { padding: 1rem; }
    .data-table { min-width: 600px; }
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
    <!-- Module Header -->
    <div class="module-header">
        <h1>Faculty Attendance Dashboard</h1>
        <p>Office of Safety & Security</p>
        <div class="current-time">
            <i class="far fa-clock"></i> <?= date('l, F j, Y') ?> • <?= date('h:i A') ?>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card" onclick="handleNavigation('index.php?page=faculty_list', this)">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="stat-info">
                <h3>Total Faculty</h3>
                <div class="stat-value"><?= $total_faculty ?></div>
                <p>Registered members</p>
            </div>
            <div class="stat-loading" style="display: none;">
                <div class="loading-spinner"></div>
            </div>
        </div>

        <div class="stat-card" onclick="handleNavigation('index.php?page=attendance_form&date=<?= date('Y-m-d') ?>', this)">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-info">
                <h3>Present Today</h3>
                <div class="stat-value"><?= $today_attendance ?></div>
                <p>On time</p>
            </div>
            <div class="stat-loading" style="display: none;">
                <div class="loading-spinner"></div>
            </div>
        </div>

        <div class="stat-card" onclick="handleNavigation('index.php?page=schedule_manager', this)">
            <div class="stat-icon">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-info">
                <h3>Today's Classes</h3>
                <div class="stat-value"><?= $today_classes ?></div>
                <p>Scheduled</p>
            </div>
            <div class="stat-loading" style="display: none;">
                <div class="loading-spinner"></div>
            </div>
        </div>

        <div class="stat-card" onclick="handleNavigation('index.php?page=attendance_form&date=<?= date('Y-m-d') ?>&status=late', this)">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>Late Today</h3>
                <div class="stat-value"><?= $late_today ?></div>
                <p>Arrived late</p>
            </div>
            <div class="stat-loading" style="display: none;">
                <div class="loading-spinner"></div>
            </div>
        </div>
    </div>

    <!-- Global Loading Overlay -->
    <div class="global-loading-overlay" id="globalLoadingOverlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner-large"></div>
            <h3>Loading</h3>
            <p>Please wait...</p>
        </div>
    </div>

    <!-- Tables Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 1.5rem;">
        <!-- Recent Attendance -->
        <div class="form-section">
            <div class="form-section-header">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Subject</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_attendance)): ?>
                            <?php foreach ($recent_attendance as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['last_name'] . ', ' . $record['first_name']) ?></td>
                                <td><?= htmlspecialchars($record['subject_code'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="room-badge">
                                        <i class="fas fa-door-open"></i>
                                        <?= htmlspecialchars($record['room'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower($record['status']) ?>">
                                        <?= htmlspecialchars($record['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('h:i A', strtotime($record['recorded_at'] ?? 'now')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="far fa-clock"></i>
                                    <h3>No Recent Records</h3>
                                    <p>Attendance records will appear here</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Current Classes -->
        <div class="form-section">
            <div class="form-section-header">
                <h3><i class="fas fa-chalkboard"></i> Current Classes</h3>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($upcoming_classes)): ?>
                            <?php foreach ($upcoming_classes as $class): ?>
                            <tr>
                                <td>
                                    <span class="room-badge">
                                        <i class="fas fa-door-open"></i>
                                        <?= htmlspecialchars($class['room'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><strong><?= htmlspecialchars($class['subject_code'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($class['section_code'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($class['last_name'] ?? '—') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php elseif ($time_slot): ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="far fa-calendar-times"></i>
                                    <h3>No Classes Scheduled</h3>
                                    <p>No classes for this time slot</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="far fa-moon"></i>
                                    <h3>Outside Class Hours</h3>
                                    <p>No active classes at this time</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Weekly Summary -->
    <?php if (!empty($weekly_summary)): ?>
    <div class="form-section" style="margin-top: 1.5rem;">
        <div class="form-section-header">
            <h3><i class="fas fa-chart-simple"></i> Weekly Overview</h3>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                        <th>Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_week = array_sum(array_column($weekly_summary, 'count'));
                    foreach ($weekly_summary as $summary): 
                        $percentage = $total_week > 0 ? round(($summary['count'] / $total_week) * 100, 1) : 0;
                        $bar_width = min($percentage, 100);
                        $bar_color = '';
                        switch($summary['status']) {
                            case 'Present': $bar_color = '#10b981'; break;
                            case 'Absent': $bar_color = '#ef4444'; break;
                            case 'Late': $bar_color = '#f59e0b'; break;
                            default: $bar_color = '#95a5a6';
                        }
                    ?>
                    <tr>
                        <td>
                            <span class="badge badge-<?= strtolower($summary['status']) ?>">
                                <?= htmlspecialchars($summary['status']) ?>
                            </span>
                        </td>
                        <td><strong><?= $summary['count'] ?></strong></td>
                        <td><?= $percentage ?>%</td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= $bar_width ?>%; background: <?= $bar_color ?>;"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Update time
function updateTime() {
    const now = new Date();
    const timeElement = document.querySelector('.current-time');
    if (timeElement) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateStr = now.toLocaleDateString('en-US', options);
        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        timeElement.innerHTML = `<i class="far fa-clock"></i> ${dateStr} • ${timeStr}`;
    }
}

updateTime();
setInterval(updateTime, 60000);

// Navigation
let isNavigating = false;

function handleNavigation(url, element) {
    if (isNavigating) return;
    
    isNavigating = true;
    
    if (element) {
        const loadingDiv = element.querySelector('.stat-loading');
        if (loadingDiv) loadingDiv.style.display = 'flex';
    }
    
    const globalLoading = document.getElementById('globalLoadingOverlay');
    if (globalLoading) {
        globalLoading.style.display = 'flex';
        setTimeout(() => globalLoading.style.opacity = '1', 10);
    }
    
    setTimeout(() => {
        window.location.href = url;
    }, 400);
}

// Page load
window.addEventListener('load', () => {
    isNavigating = false;
    
    const globalLoading = document.getElementById('globalLoadingOverlay');
    if (globalLoading) {
        globalLoading.style.opacity = '0';
        setTimeout(() => globalLoading.style.display = 'none', 200);
    }
    
    document.querySelectorAll('.stat-loading').forEach(el => {
        el.style.display = 'none';
    });
});
</script>