<?php
// Enable error reporting - but don't output anything
error_reporting(E_ALL);
ini_set('display_errors', 0); // Change to 0 to prevent output

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$success_message = '';
$error_message = '';

// Handle messages from timeout handler
if (isset($_GET['success']) && $_GET['success'] == 'timedout') {
    $success_message = "Visitor " . htmlspecialchars($_GET['name'] ?? '') . " timed out successfully!";}
if (isset($_GET['error'])) {
    $error_messages = [
        'not_found' => 'Visitor not found!',
        'already_timedout' => 'Visitor already timed out!',
        'timeout_failed' => 'Failed to timeout visitor. Please try again.',
        'missing_id' => 'No visitor ID provided!'
    ];
    $error_key = $_GET['error'];
    $error_message = $error_messages[$error_key] ?? 'Error: ' . htmlspecialchars(urldecode($_GET['error']));
}

// Include the Visitor class
require_once __DIR__ . '/../classes/Visitor.php';

// Initialize Visitor class
try {
    $visitor = new Visitor();
} catch (Exception $e) {
    die("Failed to initialize Visitor system: " . $e->getMessage());
}

// Handle form submission - Using Visitor class
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a visitor form submission
    if (isset($_POST['submit_visitor']) || isset($_POST['name'])) {
        
        $name = trim($_POST['name'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        $person = trim($_POST['person'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $id_presented = trim($_POST['id_presented'] ?? '');
        $recorded_by = $_SESSION['employee_id'] ?? 'SYSTEM';
        
        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($purpose)) $errors[] = "Purpose is required";
        if (empty($person)) $errors[] = "Person to visit is required";
        if (empty($department)) $errors[] = "Department is required";
        if (empty($contact)) $errors[] = "Contact is required";
        if (empty($id_presented)) $errors[] = "ID presented is required";
        
        if (empty($errors)) {
            try {
                // Use Visitor class to insert
                $result = $visitor->insert($name, $purpose, $person, $department, $contact, $id_presented, $recorded_by);
                
                if ($result) {
                    $lastId = $visitor->getLastInsertId();
                    echo "<script>
                        alert('Visitor registered successfully! ID: " . $lastId . "');
                        window.location.href = 'visitor_log.php?success=1';
                    </script>";
                    exit;
                } else {
                    $error_message = "Failed to register visitor. " . implode(', ', $visitor->getErrors());
                }
                
            } catch (Exception $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        } else {
            $error_message = implode(', ', $errors);
        }
    }
}

// Load records using Visitor class
$records = [];
try {
    $records = $visitor->getAll();
} catch (Exception $e) {
    $error_message = "Error loading records: " . $e->getMessage();
}

// Statistics
$totalVisitors = count($records);
$activeVisitors = array_filter($records, fn($r) => empty($r['time_out']));
$departments = array_unique(array_filter(array_column($records, 'department')));
$todayVisitors = array_filter($records, fn($r) => 
    !empty($r['time_in']) && date('Y-m-d', strtotime($r['time_in'])) === date('Y-m-d')
);

// Add status indicators to records
foreach ($records as &$record) {
    if (!empty($record['time_out'])) {
        $record['status_class'] = 'completed';
        $record['status_text'] = 'Timed Out';
        $record['status_icon'] = '<i class="fas fa-check-circle"></i>';
    } else {
        $record['status_class'] = 'active';
        $record['status_text'] = 'Active';
        $record['status_icon'] = '<i class="fas fa-clock"></i>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    --color1: #2c3e50;
    --color2: #34495e;
    --color3: #7f8c8d;
    --color4: #95a5a6;
    --color5: #3498db;
    --color6: #2980b9;
    --color7: #ecf0f1;

    --success:      #10b981;
    --success-bg:   #d4edda;
    --success-text: #155724;
    --warning:      #f59e0b;
    --warning-bg:   #fff3cd;
    --warning-text: #856404;
    --danger:       #ef4444;
    --danger-bg:    #f8d7da;
    --danger-text:  #721c24;

    --white:    #ffffff;
    --gray-50:  #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', -apple-system, sans-serif;
    background: var(--gray-50);
    color: var(--color2);
    line-height: 1.5;
}

.container {
    max-width: 1440px;
    margin: 0 auto;
    padding: 1.5rem;
}

/* ── Header ── */
.module-header {
    background: linear-gradient(135deg, var(--color5), var(--color6));
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,.1);
    position: relative;
}
.module-header h1 {
    font-size: 1.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: .35rem;
}
.module-header p { font-size: .9rem; opacity: .9; }

.current-time {
    position: absolute;
    top: 1.5rem;
    right: 2rem;
    background: rgba(255,255,255,.2);
    padding: .4rem 1rem;
    border-radius: 30px;
    font-size: .85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: .5rem;
}

/* ── Stats ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid var(--color7);
    transition: all .25s;
}
.stat-card:hover {
    border-color: var(--color5);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52,152,219,.2);
}
.stat-icon {
    width: 54px; height: 54px;
    background: var(--color7);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    color: var(--color5);
    flex-shrink: 0;
}
.stat-info h3 { font-size: .85rem; font-weight: 500; color: var(--color3); margin-bottom: .2rem; }
.stat-value   { font-size: 2rem;   font-weight: 700; color: var(--color1); line-height: 1.1; }
.stat-change  { font-size: .75rem; color: var(--color4); display: flex; align-items: center; gap: 4px; margin-top: .2rem; }

/* ── Alert ── */
.alert {
    padding: .9rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: .9rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.alert-danger  { background: var(--danger-bg);  color: var(--danger-text);  border: 1px solid var(--danger); }
.alert-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success); }

/* ── Form Section ── */
.form-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,.08);
    margin-bottom: 1.75rem;
    overflow: hidden;
    border: 1px solid var(--color7);
}
.form-section-header {
    background: linear-gradient(135deg, var(--color5), var(--color6));
    padding: .9rem 1.5rem;
}
.form-section-header h3 {
    margin: 0;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.form-body { padding: 1.5rem; }

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}
.form-group { margin-bottom: 0; }
.form-group.full-width { grid-column: 1 / -1; }

.form-label {
    display: block;
    margin-bottom: .4rem;
    font-weight: 500;
    color: var(--color2);
    font-size: .85rem;
}
.form-control {
    width: 100%;
    padding: .7rem 1rem;
    border: 2px solid var(--color7);
    border-radius: 8px;
    font-size: .9rem;
    background: white;
    color: var(--color2);
    transition: border-color .25s;
}
.form-control:focus {
    outline: none;
    border-color: var(--color5);
}

/* ── Buttons ── */
.btn {
    padding: .65rem 1.25rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all .25s;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    text-decoration: none;
    font-size: .88rem;
}
.btn-primary { background: var(--color7); color: var(--color2); }
.btn-primary:hover { background: var(--color4); color: white; }

.btn-success {
    background: var(--color5);
    color: white;
    width: 100%;
    justify-content: center;
    margin-top: .75rem;
    font-size: .95rem;
    padding: .85rem;
}
.btn-success:hover { background: var(--color6); }
.btn-success:disabled { opacity: .6; cursor: not-allowed; }

.btn-danger {
    background: var(--danger-bg);
    color: var(--danger-text);
    border: 1px solid #f5c6cb;
    padding: .4rem .85rem;
    font-size: .8rem;
}
.btn-danger:hover { background: var(--danger); color: white; border-color: var(--danger); }

/* ── Table ── */
.table-container {
    overflow-x: auto;
    border-radius: 0 0 10px 10px;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 960px;
    font-size: .88rem;
}
.data-table th {
    background: var(--color7);
    color: var(--color2);
    font-weight: 600;
    padding: .85rem 1rem;
    text-align: left;
    border-bottom: 2px solid var(--color4);
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    white-space: nowrap;
}
.data-table td {
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--color7);
    color: var(--color2);
    vertical-align: middle;
}
.data-table tbody tr:hover { background: var(--gray-50); }
.data-table tr:last-child td { border-bottom: none; }

/* Status row highlighting */
.data-table tbody tr.active-row td:first-child {
    border-left: 4px solid var(--warning);
    background: linear-gradient(to right, rgba(245, 158, 11, 0.02), transparent);
}

.data-table tbody tr.completed-row td:first-child {
    border-left: 4px solid var(--success);
    background: linear-gradient(to right, rgba(16, 185, 129, 0.02), transparent);
}

.data-table tbody tr.active-row:hover {
    background: rgba(245, 158, 11, 0.05);
}

.data-table tbody tr.completed-row:hover {
    background: rgba(16, 185, 129, 0.05);
}

/* Enhanced badges */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.badge-active { 
    background: var(--warning-bg); 
    color: var(--warning-text);
    border: 1px solid var(--warning);
}

.badge-completed { 
    background: var(--success-bg); 
    color: var(--success-text);
    border: 1px solid var(--success);
}

/* Enhanced check mark */
.check-mark {
    color: var(--success-text);
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--success-bg);
    padding: 6px 12px;
    border-radius: 20px;
    border: 1px solid var(--success);
    white-space: nowrap;
}

.check-mark i {
    font-size: 0.9rem;
}

.check-mark small {
    font-size: 0.65rem;
    color: var(--success-text);
    opacity: 0.8;
}

/* Room badge */
.room-badge {
    background: var(--color5);
    color: white;
    padding: .2rem .7rem;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: .25rem;
}

/* Recent Visitors Cards */
.recent-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}
.recent-card {
    background: var(--gray-50);
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid var(--color7);
}
.recent-card.active-card {
    border-left: 4px solid var(--warning);
}
.recent-card.completed-card {
    border-left: 4px solid var(--success);
}
.recent-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: .4rem;
}
.recent-card-name { font-weight: 600; color: var(--color2); }
.recent-card-meta { font-size: .83rem; color: var(--color3); margin-bottom: .2rem; }
.recent-card-time { font-size: .78rem; color: var(--color4); }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--color3);
}
.empty-state i    { font-size: 2.5rem; margin-bottom: .75rem; color: var(--color4); display: block; }
.empty-state h3   { font-weight: 500; color: var(--color2); margin-bottom: .25rem; }

/* Footer */
.footer {
    text-align: center;
    margin-top: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: 10px;
    border: 1px solid var(--gray-200);
    color: var(--gray-500);
    font-size: .88rem;
}
.nav-links {
    display: flex;
    justify-content: center;
    gap: .75rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}
.nav-links .btn {
    background: white;
    border: 1px solid var(--gray-200);
    color: var(--gray-600);
}
.nav-links .btn:hover { background: var(--gray-50); border-color: var(--gray-400); }

/* Responsive */
@media (max-width: 768px) {
    .current-time { position: static; margin-top: .75rem; display: inline-flex; }
    .form-grid    { grid-template-columns: 1fr; }
    .module-header h1 { font-size: 1.3rem; }
    .badge { padding: 4px 8px; font-size: 0.65rem; }
}

/* Scrollbar */
::-webkit-scrollbar       { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--color7); }
::-webkit-scrollbar-thumb { background: var(--color4); border-radius: 20px; }
::-webkit-scrollbar-thumb:hover { background: var(--color3); }
</style>
</head>
<body>

<div class="container">

    <!-- ── Header ── -->
    <div class="module-header">
        <h1><i class="fas fa-user-clock"></i> Visitor Management System</h1>
        <p>Track and manage visitor entries in real time</p>
        <div class="current-time">
            <i class="fas fa-clock"></i>
            <span id="timeDisplay"><?= date('h:i A') ?> • <?= date('M d, Y') ?></span>
        </div>
    </div>

    <!-- ── Error / Success ── -->
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error_message) ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($success_message) ?>
    </div>
    <?php endif; ?>

    <!-- ── Statistics ── -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3>Total Visitors</h3>
                <div class="stat-value"><?= $totalVisitors ?></div>
                <div class="stat-change"><i class="fas fa-history"></i> All time</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
                <h3>Active Now</h3>
                <div class="stat-value"><?= count($activeVisitors) ?></div>
                <div class="stat-change"><i class="fas fa-building"></i> In premises</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <div class="stat-info">
                <h3>Departments</h3>
                <div class="stat-value"><?= count($departments) ?></div>
                <div class="stat-change"><i class="fas fa-exchange-alt"></i> Visited</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-info">
                <h3>Today's Visitors</h3>
                <div class="stat-value"><?= count($todayVisitors) ?></div>
                <div class="stat-change"><i class="fas fa-calendar"></i> <?= date('M d') ?></div>
            </div>
        </div>
    </div>

    <!-- ── Register Form ── -->
    <div class="form-section">
        <div class="form-section-header">
            <h3><i class="fas fa-user-plus"></i> Register New Visitor</h3>
        </div>
        <div class="form-body">
            <form method="POST" id="visitorForm" onsubmit="return validateForm()">
                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label">Visitor Name *</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Full name" required
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose of Visit *</label>
                        <input type="text" name="purpose" class="form-control"
                               placeholder="Meeting, Delivery, Interview…" required
                               value="<?= htmlspecialchars($_POST['purpose'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Person to Visit *</label>
                        <input type="text" name="person" class="form-control"
                               placeholder="Staff name" required
                               value="<?= htmlspecialchars($_POST['person'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Department *</label>
                        <input type="text" name="department" class="form-control"
                               placeholder="e.g. HR, IT, Admin" required
                               value="<?= htmlspecialchars($_POST['department'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" name="contact" class="form-control"
                               placeholder="Phone number" required
                               value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">ID Presented *</label>
                        <input type="text" name="id_presented" class="form-control"
                               placeholder="Driver's License, Passport, etc." required
                               value="<?= htmlspecialchars($_POST['id_presented'] ?? '') ?>">
                    </div>

                    <div class="form-group full-width">
                        <button type="submit" name="submit_visitor" value="1" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-sign-in-alt"></i> Register Visitor &amp; Time In
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- ── Recent Visitors Cards ── -->
    <?php if (!empty($records)): ?>
    <div class="form-section">
        <div class="form-section-header">
            <h3><i class="fas fa-history"></i> Recent Visitors</h3>
        </div>
        <div class="form-body">
            <div class="recent-grid">
                <?php
                $recentSlice = array_slice($records, 0, 4);
                foreach ($recentSlice as $recentRow):
                ?>
                <div class="recent-card <?= empty($recentRow['time_out']) ? 'active-card' : 'completed-card' ?>">
                    <div class="recent-card-top">
                        <span class="recent-card-name">
                            <?= htmlspecialchars($recentRow['visitor_name'] ?? '') ?>
                        </span>
                        <span class="badge <?= empty($recentRow['time_out']) ? 'badge-active' : 'badge-completed' ?>">
                            <?= empty($recentRow['time_out']) ? '<i class="fas fa-clock"></i> ACTIVE' : '<i class="fas fa-check-circle"></i> DONE' ?>
                        </span>
                    </div>
                    <div class="recent-card-meta">
                        <i class="fas fa-briefcase"></i>
                        <?= htmlspecialchars($recentRow['purpose'] ?? '') ?>
                    </div>
                    <div class="recent-card-time">
                        <i class="fas fa-clock"></i>
                        <?= !empty($recentRow['time_in']) ? date('h:i A', strtotime($recentRow['time_in'])) : '' ?>
                        <?php if (!empty($recentRow['time_out'])): ?>
                            &rarr; <?= date('h:i A', strtotime($recentRow['time_out'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── All Visitor Records Table ── -->
    <div class="form-section">
        <div class="form-section-header">
            <h3><i class="fas fa-list-alt"></i> All Visitor Records</h3>
        </div>
        <div class="form-body" style="padding: 0;">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Name / ID</th>
                            <th>Purpose</th>
                            <th>Visiting</th>
                            <th>Department</th>
                            <th>Contact</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $tableRow): ?>
                        <tr class="<?= empty($tableRow['time_out']) ? 'active-row' : 'completed-row' ?>">
                            <td>
                                <?php if (empty($tableRow['time_out'])): ?>
                                    <span class="badge badge-active">
                                        <i class="fas fa-clock"></i> ACTIVE
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-completed">
                                        <i class="fas fa-check-circle"></i> DONE
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($tableRow['visitor_name'] ?? '') ?></strong><br>
                                <small style="color: var(--color4);">
                                    <?= htmlspecialchars($tableRow['id_presented'] ?? '') ?>
                                </small>
                            </td>
                            <td><?= htmlspecialchars($tableRow['purpose'] ?? '') ?></td>
                            <td><?= htmlspecialchars($tableRow['person_to_visit'] ?? '') ?></td>
                            <td>
                                <span class="room-badge">
                                    <i class="fas fa-building"></i>
                                    <?= htmlspecialchars($tableRow['department'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($tableRow['contact_number'] ?? '') ?></td>
                            <td>
                                <?php if (!empty($tableRow['time_in'])): ?>
                                    <span style="color: var(--success-text); font-weight: 500;">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <?= date('h:i A', strtotime($tableRow['time_in'])) ?>
                                    </span><br>
                                    <small style="color: var(--color4);">
                                        <?= date('M d, Y', strtotime($tableRow['time_in'])) ?>
                                    </small>
                                <?php else: ?>
                                    <em style="color:var(--color4);">—</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($tableRow['time_out'])): ?>
                                    <span style="color: var(--color3); font-weight: 500;">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <?= date('h:i A', strtotime($tableRow['time_out'])) ?>
                                    </span><br>
                                    <small style="color: var(--color4);">
                                        <?= date('M d, Y', strtotime($tableRow['time_out'])) ?>
                                    </small>
                                <?php else: ?>
                                    <em style="color: var(--color4); font-style: italic;">Not yet timed out</em>
                                <?php endif; ?>
                            <td>
    <?php if (empty($tableRow['time_out'])): ?>
        <a href="timeout_handler.php?id=<?= (int)$tableRow['id'] ?>"
           class="btn btn-danger"
           onclick="return confirm('Mark this visitor as timed out?')">
            <i class="fas fa-sign-out-alt"></i> Time Out
        </a>
    <?php else: ?>
        <span class="check-mark">
            <i class="fas fa-check-circle"></i> Completed<br>
            <small><?= date('h:i A', strtotime($tableRow['time_out'])) ?></small>
        </span>
    <?php endif; ?>
</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-user-clock"></i>
                                    <h3>No Visitors Yet</h3>
                                    <p>Register a visitor above to get started.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Footer ── -->
    <div class="footer">
        <div class="nav-links">
            <a href="index.php" class="btn"><i class="fas fa-home"></i> Dashboard</a>
            <a href="?page=attendance_form" class="btn"><i class="fas fa-clipboard-check"></i> Attendance</a>
            <a href="?page=reports" class="btn"><i class="fas fa-chart-bar"></i> Reports</a>
        </div>
        <p>Visitor Management System &copy; <?= date('Y') ?> | Office of Safety and Security</p>
    </div>

</div><!-- /container -->

<script>
// Real-time clock
function updateClock() {
    const now  = new Date();
    const time = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit' });
    const date = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('timeDisplay').textContent = `${time} • ${date}`;
}
setInterval(updateClock, 1000);
updateClock();

// Form validation + loading state
function validateForm() {
    const fields  = ['name', 'purpose', 'person', 'department', 'contact', 'id_presented'];
    const missing = [];

    fields.forEach(f => {
        const el = document.querySelector(`[name="${f}"]`);
        if (!el || !el.value.trim()) {
            missing.push(f.replace('_', ' '));
            if (el) el.style.borderColor = '#ef4444';
        } else {
            if (el) el.style.borderColor = '';
        }
    });

    if (missing.length) {
        alert('Please fill in all required fields:\n• ' + missing.join('\n• '));
        return false;
    }

    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';
    btn.disabled  = true;
    return true;
}

// Reset field highlight on input
document.querySelectorAll('.form-control').forEach(el => {
    el.addEventListener('input', () => { el.style.borderColor = ''; });
});
</script>

</body>
</html>