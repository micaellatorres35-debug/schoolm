<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Facility.php';
include_once __DIR__ . '/../classes/User.php';

$facility = new Facility();
$userClass = new User();
$userInfo = $userClass->userSession();

// Add new issue - FIXED HERE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if this is a facility form submission
    if (isset($_POST['submit_issue']) || isset($_POST['room'])) {
        $room = trim($_POST['room'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = trim($_POST['priority'] ?? 'Medium');
        $reported_by = $_SESSION['employee_id'] ?? 'Admin';
        
        if ($room && $type && $description) {
            $facility->insert($room, $type, $description, $priority, $reported_by);
            echo "<script>alert('Issue reported successfully!'); window.location.href='index.php?page=facilities_monitor';</script>";
            exit;
        } else {
            echo "<script>alert('Please fill in all required fields.');</script>";
        }
    }
}

// Mark fixed
if (isset($_GET['fix'])) {
    $id = intval($_GET['fix']);
    $facility->markFixed($id);
    echo "<script>alert('Issue marked as fixed!'); window.location.href='index.php?page=facilities_monitor';</script>";
    exit;
}

$issues = $facility->getAll();

// Calculate statistics
$totalIssues = count($issues);
$pendingIssues = array_filter($issues, fn($row) => $row['status'] == 'Pending');
$highPriorityIssues = array_filter($issues, fn($row) => $row['priority'] == 'High' && $row['status'] == 'Pending');
$rooms = array_unique(array_column($issues, 'room'));
$todayIssues = array_filter($issues, fn($row) => 
    date('Y-m-d', strtotime($row['date_reported'])) == date('Y-m-d')
);
?>

<style>
/* ====================================
   MINIMALIST BLUE & WHITE FACILITIES MONITOR
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

/* Module Header - Matching attendance_form */
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

.system-status {
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

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success);
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
    border: 1px solid var(--color7);
    transition: all 0.3s ease;
    cursor: pointer;
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

.stat-change {
    color: var(--color3);
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 4px;
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

.form-body {
    padding: 1.5rem;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.form-group {
    margin-bottom: 0;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color2);
    font-size: 0.85rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--color7);
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
    color: var(--color2);
}

.form-control:focus {
    outline: none;
    border-color: var(--color5);
}

.form-control.select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* Buttons - Matching attendance_form */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    font-size: 0.9rem;
}

.btn-primary {
    background: var(--color7);
    color: var(--color2);
}

.btn-primary:hover {
    background: var(--color4);
    color: white;
}

.btn-success {
    background: var(--color5);
    color: white;
    width: 100%;
    justify-content: center;
    margin-top: 1rem;
}

.btn-success:hover {
    background: var(--color6);
}

.btn-fix {
    background: var(--white);
    color: var(--success-text);
    border: 1px solid var(--success);
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    border-radius: 6px;
}

.btn-fix:hover {
    background: var(--success-bg);
}

/* Priority Badges */
.priority-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.priority-low { 
    background: var(--priority-low-bg); 
    color: var(--success-text);
}
.priority-medium { 
    background: var(--priority-medium-bg); 
    color: var(--warning-text);
}
.priority-high { 
    background: var(--priority-high-bg); 
    color: var(--danger-text);
}

/* Status Badges - Matching attendance_form */
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-pending {
    background: var(--warning-bg);
    color: var(--warning-text);
}

.badge-fixed {
    background: var(--success-bg);
    color: var(--success-text);
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

/* Table Container - Matching attendance_form */
.table-container {
    background: white;
    border-radius: 10px;
    border: 1px solid var(--color7);
    overflow: auto;
    margin-top: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1000px;
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

/* Row Highlighting */
.data-table tbody tr.pending td:first-child {
    border-left: 3px solid var(--warning);
}

.data-table tbody tr.fixed td:first-child {
    border-left: 3px solid var(--success);
}

.data-table tbody tr.high-priority td:first-child {
    border-left: 3px solid var(--danger);
}

/* Filter Buttons */
.table-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.filter-btn {
    padding: 0.5rem 1rem;
    border: 1px solid var(--color7);
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.85rem;
    color: var(--color2);
    transition: all 0.2s ease;
}

.filter-btn.active {
    background: var(--color5);
    color: white;
    border-color: var(--color5);
}

.filter-btn:hover:not(.active) {
    border-color: var(--color5);
    color: var(--color5);
}

/* Check mark */
.check-mark {
    color: var(--success-text);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.8rem;
}

.check-mark i {
    font-size: 1rem;
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

/* Footer - Matching attendance_form */
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

.nav-links {
    display: flex;
    justify-content: center;
    gap: var(--space-3);
    margin-bottom: var(--space-4);
    flex-wrap: wrap;
}

.nav-links .btn {
    background: var(--white);
    border: 1px solid var(--gray-200);
    color: var(--gray-600);
}

.nav-links .btn:hover {
    background: var(--gray-50);
    border-color: var(--gray-300);
}

/* Floating Action Button */
.fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--color5);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    z-index: 100;
}

.fab:hover {
    background: var(--color6);
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .container { padding: var(--space-4); }
    
    .module-header { padding: 1rem; }
    .module-header h1 { font-size: 1.3rem; }
    
    .system-status {
        position: static;
        margin-top: var(--space-4);
        display: inline-flex;
    }
    
    .stats-grid { gap: 1rem; }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .table-container { overflow-x: auto; }
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
            <i class="fas fa-tools"></i>
            Facilities Monitoring System
        </h1>
        <p>Track, report, and resolve facility issues</p>
        <div class="system-status">
            <span class="status-indicator" id="statusIndicator"></span>
            <span id="statusText">Active</span>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card" onclick="filterIssues('all')">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3>Total Issues</h3>
                <div class="stat-value"><?= $totalIssues ?></div>
                <div class="stat-change">
                    <i class="fas fa-chart-line"></i> All time
                </div>
            </div>
        </div>
        
        <div class="stat-card" onclick="filterIssues('pending')">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>Pending</h3>
                <div class="stat-value"><?= count($pendingIssues) ?></div>
                <div class="stat-change">
                    <i class="fas fa-hourglass-half"></i> Needs attention
                </div>
            </div>
        </div>
        
        <div class="stat-card" onclick="filterIssues('high')">
            <div class="stat-icon">
                <i class="fas fa-fire"></i>
            </div>
            <div class="stat-info">
                <h3>Critical</h3>
                <div class="stat-value"><?= count($highPriorityIssues) ?></div>
                <div class="stat-change">
                    <i class="fas fa-exclamation"></i> High priority
                </div>
            </div>
        </div>
        
        <div class="stat-card" onclick="filterIssues('today')">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info">
                <h3>Today's Reports</h3>
                <div class="stat-value"><?= count($todayIssues) ?></div>
                <div class="stat-change">
                    <i class="fas fa-calendar-day"></i> <?= date('M d') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Form -->
    <div class="form-section">
        <div class="form-section-header">
            <h3><i class="fas fa-plus-circle"></i> Report New Issue</h3>
        </div>
        <div class="form-body">
            <form method="POST" id="issueForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Room / Location *</label>
                        <input type="text" name="room" class="form-control" 
                               placeholder="e.g., Room 101, Lab A" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Item / Equipment</label>
                        <input type="text" name="type" class="form-control" 
                               placeholder="e.g., Projector, AC, Chair" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Describe the issue in detail..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-control select">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <button type="submit" name="submit_issue" value="1" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Submit Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- All Issues Table -->
    <div class="form-section">
        <div class="form-section-header">
            <h3><i class="fas fa-list-alt"></i> Issues Queue</h3>
        </div>
        <div class="form-body">
            <div class="table-filters">
                <button class="filter-btn active" onclick="filterIssues('all')">All Issues</button>
                <button class="filter-btn" onclick="filterIssues('pending')">Pending</button>
                <button class="filter-btn" onclick="filterIssues('high')">High Priority</button>
                <button class="filter-btn" onclick="filterIssues('today')">Today</button>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Reported</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="issuesTable">
                        <?php if (!empty($issues)): ?>
                            <?php foreach ($issues as $row): 
                                $priorityClass = strtolower($row['priority']);
                                $statusClass = strtolower($row['status']);
                                $rowClass = $statusClass;
                                if ($row['priority'] == 'High' && $row['status'] == 'Pending') {
                                    $rowClass .= ' high-priority';
                                }
                            ?>
                            <tr class="<?= $rowClass ?>" data-id="<?= $row['id'] ?>">
                                <td>
                                    <span class="room-badge">
                                        <i class="fas fa-door-open"></i>
                                        <?= htmlspecialchars($row['room']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['issue_type']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td>
                                    <span class="priority-badge priority-<?= $priorityClass ?>">
                                        <?= $row['priority'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?= date('M d, Y', strtotime($row['date_reported'])) ?></div>
                                    <div style="font-size: 0.7rem; color: var(--color3);"><?= date('h:i A', strtotime($row['date_reported'])) ?></div>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'Pending'): ?>
                                        <a href="?fix=<?= $row['id'] ?>" class="btn btn-fix" 
                                           onclick="return confirm('Mark this issue as fixed?')">
                                            <i class="fas fa-check"></i> Mark Fixed
                                        </a>
                                    <?php else: ?>
                                        <span class="check-mark">
                                            <i class="fas fa-check-circle"></i> Fixed
                                            <?php if (!empty($row['date_fixed'])): ?>
                                                <br><small><?= date('M d', strtotime($row['date_fixed'])) ?></small>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-tools"></i>
                                    <h3>No Issues Reported</h3>
                                    <p>Facilities are all in good condition</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
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
        </div>
        <p>Facilities Monitoring System © <?= date('Y') ?> | Office of Safety and Security</p>
    </div>
</div>

<!-- Floating Action Button -->
<button class="fab" onclick="scrollToForm()">
    <i class="fas fa-plus"></i>
</button>

<script>
// Filter issues
function filterIssues(filter) {
    const rows = document.querySelectorAll('#issuesTable tr');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.toLowerCase().includes(filter) || 
            (filter === 'all' && btn.textContent.includes('All'))) {
            btn.classList.add('active');
        }
    });
    
    rows.forEach(row => {
        if (row.classList.contains('empty-state')) return;
        
        const priority = row.querySelector('.priority-badge')?.textContent.trim() || '';
        const status = row.querySelector('.badge')?.textContent.trim() || '';
        const dateCell = row.cells[5]?.querySelector('div')?.textContent || '';
        const today = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        let show = false;
        
        switch(filter) {
            case 'all':
                show = true;
                break;
            case 'pending':
                show = status === 'Pending';
                break;
            case 'high':
                show = priority === 'High' && status === 'Pending';
                break;
            case 'today':
                show = dateCell.includes(today);
                break;
        }
        
        row.style.display = show ? '' : 'none';
    });
    
    // Update status indicator
    updateStatus();
}

// Update status indicator based on critical issues
function updateStatus() {
    const criticalCount = <?= count($highPriorityIssues) ?>;
    const pendingCount = <?= count($pendingIssues) ?>;
    const indicator = document.getElementById('statusIndicator');
    const statusText = document.getElementById('statusText');
    
    if (criticalCount > 0) {
        indicator.style.background = '#ef4444';
        statusText.textContent = 'Critical Issues';
    } else if (pendingCount > 0) {
        indicator.style.background = '#f59e0b';
        statusText.textContent = 'Issues Pending';
    } else {
        indicator.style.background = '#10b981';
        statusText.textContent = 'All Clear';
    }
}

// Form submission feedback
const form = document.getElementById('issueForm');
if (form) {
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Form will submit normally, this is just visual feedback
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });
}

// Scroll to form
function scrollToForm() {
    document.querySelector('.form-section').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    filterIssues('all');
    updateStatus();
});
</script>