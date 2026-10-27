<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
if (!isset($db)) {
    require_once __DIR__ . '/../../../database/db.php';
    $database = new Database();
    $db = $database->getConnection();
}

require_once __DIR__ . '/../classes/Schedule.php';
require_once __DIR__ . '/../classes/Attendance.php';

$schedule = new Schedule($db);
$attendance = new Attendance($db);

// Get parameters
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$time_breaker = isset($_GET['time_breaker']) ? $_GET['time_breaker'] : '12:30-3:00';
$class_type = isset($_GET['class_type']) ? $_GET['class_type'] : 'all';
$current_index = isset($_GET['index']) ? (int)$_GET['index'] : 0;

$semester = "2nd Sem";
$school_year = "2025-2026";

// Get schedules
$schedules = $schedule->getByDateTime($date, $time_breaker, $semester, $school_year);

// Get already recorded attendance
$already_recorded = [];
if (!empty($schedules)) {
    $schedule_ids = array_column($schedules, 'id');
    $placeholders = implode(',', array_fill(0, count($schedule_ids), '?'));
    $query = "SELECT schedule_id, faculty_id, status, class_type, student_count, remarks, 
                     online_platform, meeting_link, meeting_id, meeting_password, 
                     online_attendance_file, internet_status, connectivity_issues 
              FROM mon_attendance 
              WHERE attendance_date = ? AND schedule_id IN ($placeholders)";
    
    $stmt = $db->prepare($query);
    $params = array_merge([$date], $schedule_ids);
    $stmt->execute($params);
    
    $recorded = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recorded as $record) {
        $already_recorded[$record['schedule_id']] = $record;
    }
}

// Filter out already recorded schedules if needed
if (!empty($schedules) && isset($_GET['hide_recorded']) && $_GET['hide_recorded'] == 1) {
    $schedules = array_filter($schedules, function($schedule) use ($already_recorded) {
        return !isset($already_recorded[$schedule['id']]);
    });
    // Reindex array
    $schedules = array_values($schedules);
    // Reset index to 0 when filtering
    $current_index = 0;
}

// Handle file upload
function handleFileUpload($file, $schedule_id, $date) {
    $target_dir = "uploads/attendance/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $filename = "attendance_{$schedule_id}_{$date}." . $file_extension;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $filename;
    }
    return null;
}

// Process attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $success_count = 0;
    $errors = [];
    
    foreach ($_POST['attendance'] as $schedule_id => $status) {
        try {
            if (!isset($_POST['faculty_id'][$schedule_id])) {
                throw new Exception("Faculty ID missing for schedule $schedule_id");
            }
            
            $data = [
                'schedule_id' => $schedule_id,
                'attendance_date' => $_POST['attendance_date'],
                'faculty_id' => $_POST['faculty_id'][$schedule_id],
                'status' => $status,
                'remarks' => $_POST['remarks'][$schedule_id] ?? '',
                'student_count' => isset($_POST['student_count'][$schedule_id]) ? (int)$_POST['student_count'][$schedule_id] : 0,
                'recorded_by' => "Admin",
                'class_type' => $_POST['class_type'][$schedule_id] ?? 'onsite',
                'online_platform' => $_POST['online_platform'][$schedule_id] ?? null,
                'meeting_link' => $_POST['meeting_link'][$schedule_id] ?? null,
                'meeting_id' => $_POST['meeting_id'][$schedule_id] ?? null,
                'meeting_password' => $_POST['meeting_password'][$schedule_id] ?? null,
                'online_attendance_file' => null,
                'internet_status' => $_POST['internet_status'][$schedule_id] ?? null,
                'connectivity_issues' => $_POST['connectivity_issues'][$schedule_id] ?? null
            ];
            
            // Handle file upload
            if (isset($_FILES['attendance_file']['name'][$schedule_id]) && 
                $_FILES['attendance_file']['error'][$schedule_id] == 0) {
                $uploaded_file = [
                    'name' => $_FILES['attendance_file']['name'][$schedule_id],
                    'tmp_name' => $_FILES['attendance_file']['tmp_name'][$schedule_id],
                    'error' => $_FILES['attendance_file']['error'][$schedule_id]
                ];
                $data['online_attendance_file'] = handleFileUpload($uploaded_file, $schedule_id, $_POST['attendance_date']);
            }
            
            $existing = $attendance->checkExisting(
                $schedule_id, 
                $_POST['attendance_date'], 
                $_POST['faculty_id'][$schedule_id]
            );
            
            if ($existing) {
                if ($attendance->updateAttendance($existing['id'], $data)) {
                    $success_count++;
                } else {
                    $errors[] = "Failed to update schedule ID: $schedule_id";
                }
            } else {
                if ($attendance->insertAttendance($data)) {
                    $success_count++;
                } else {
                    $errors[] = "Failed to insert schedule ID: $schedule_id";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Error for schedule $schedule_id: " . $e->getMessage();
        }
    }
    
    $total_records = count($_POST['attendance']);
    $message = "Attendance recorded! $success_count out of $total_records records saved.";
    
    if (!empty($errors)) {
        $message .= " Errors: " . implode(", ", $errors);
    }
    
    // Determine next index after submission
    $next_index = $current_index + 1;
    if ($next_index >= count($schedules)) {
        $next_index = 0; // Go back to start or show completion message
    }
    
    echo "<script>
        alert('" . addslashes($message) . "');
        window.location.href = 'index.php?page=attendance_form&date=" . $_POST['attendance_date'] . "&time_breaker=" . $_POST['time_breaker'] . "&index=" . $next_index . "';
    </script>";
    exit;
}
?>

<style>
:root {
    --color1: #2c3e50;
    --color2: #34495e;
    --color3: #7f8c8d;
    --color4: #95a5a6;
    --color5: #3498db;
    --color6: #2980b9;
    --color7: #ecf0f1;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f5f5;
    color: var(--color1);
}

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

.module-content {
    padding: 0 1rem;
}

/* Filters Section */
.filters {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
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
    min-width: 200px;
}

.filter-item label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--color2);
    font-size: 0.9rem;
}

.filter-item select,
.filter-item input[type="date"] {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--color7);
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.filter-item select:focus,
.filter-item input[type="date"]:focus {
    outline: none;
    border-color: var(--color5);
}

.filter-item .btn {
    background: var(--color5);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s ease;
}

.filter-item .btn:hover {
    background: var(--color6);
}

/* Progress Bar */
.progress-container {
    margin-top: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    color: var(--color2);
    font-weight: 600;
}

.progress-bar {
    width: 100%;
    height: 10px;
    background: var(--color7);
    border-radius: 5px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--color5), var(--color6));
    transition: width 0.3s ease;
}

/* Info Bar */
.info-bar {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--color7);
    border-radius: 8px;
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.info-bar span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color2);
}

.info-bar i {
    color: var(--color5);
}

/* Form Section */
.form-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.form-section-header {
    background: linear-gradient(135deg, var(--color5), var(--color6));
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.form-section-header h3 {
    margin: 0;
    color: white;
    font-size: 1.25rem;
    font-weight: 600;
}

.form-section-header .badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 0.25rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Single Schedule Card */
.schedule-card {
    padding: 2rem;
    background: white;
}

.schedule-detail {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--color7);
    border-radius: 10px;
}

.detail-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-label {
    font-size: 0.8rem;
    color: var(--color3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--color2);
}

.detail-value small {
    font-size: 0.9rem;
    font-weight: normal;
    color: var(--color3);
}

.room-badge-large {
    background: var(--color5);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 30px;
    font-size: 1.1rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

/* Form Fields */
.form-fields {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.field-group label {
    font-weight: 600;
    color: var(--color2);
    font-size: 0.9rem;
}

.field-group select,
.field-group input,
.field-group textarea {
    padding: 0.75rem;
    border: 2px solid var(--color7);
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.field-group select:focus,
.field-group input:focus,
.field-group textarea:focus {
    outline: none;
    border-color: var(--color5);
}

.field-group input[type="number"] {
    width: 120px;
}

/* Online Details */
.online-details {
    background: var(--color7);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.online-details select,
.online-details input,
.online-details textarea {
    width: 100%;
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    border: 1px solid var(--color4);
    border-radius: 4px;
}

/* Action Buttons */
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--color7);
}

.nav-buttons {
    display: flex;
    gap: 1rem;
}

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
}

.btn-success:hover {
    background: var(--color6);
}

.btn-secondary {
    background: var(--color3);
    color: white;
}

.btn-secondary:hover {
    background: var(--color2);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Recorded Badge */
.recorded-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #27ae60;
    color: white;
    font-size: 0.9rem;
    border-radius: 20px;
    margin-left: 1rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--color3);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--color4);
}

.empty-state h3 {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

/* Legend */
.legend {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-top: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.legend h4 {
    color: var(--color2);
    margin-bottom: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend h4 i {
    color: var(--color5);
}

.legend-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.5rem;
    list-style: none;
}

.legend-grid li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: var(--color7);
    border-radius: 6px;
    font-size: 0.9rem;
    color: var(--color2);
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-present {
    background: #d4edda;
    color: #155724;
}

.badge-absent {
    background: #f8d7da;
    color: #721c24;
}

.badge-late {
    background: #fff3cd;
    color: #856404;
}

.type-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.type-onsite {
    background: var(--color5);
    color: white;
}

.type-online {
    background: var(--color6);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .module-header { padding: 1rem; }
    .module-header h1 { font-size: 1.3rem; }
    
    .filter-group { flex-direction: column; }
    .filter-item { width: 100%; }
    
    .schedule-detail {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .action-bar {
        flex-direction: column;
        gap: 1rem;
    }
    
    .nav-buttons {
        width: 100%;
        justify-content: center;
    }
    
    .legend-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="module-header">
    <h1>Faculty Attendance Monitoring</h1>
</div>

<div class="module-content">
    <!-- Filters -->
    <div class="filters">
        <form method="GET" class="filter-group">
            <input type="hidden" name="page" value="attendance_form">
            <div class="filter-item">
                <label>Date</label>
                <input type="date" name="date" value="<?= $date ?>" required>
            </div>
            <div class="filter-item">
                <label>Time Slot</label>
                <select name="time_breaker">
                    <option value="7:30-10:00" <?= $time_breaker == '7:30-10:00' ? 'selected' : '' ?>>7:30 - 10:00 AM</option>
                    <option value="10:00-12:30" <?= $time_breaker == '10:00-12:30' ? 'selected' : '' ?>>10:00 - 12:30 PM</option>
                    <option value="12:30-3:00" <?= $time_breaker == '12:30-3:00' ? 'selected' : '' ?>>12:30 - 3:00 PM</option>
                    <option value="3:00-5:30" <?= $time_breaker == '3:00-5:30' ? 'selected' : '' ?>>3:00 - 5:30 PM</option>
                </select>
            </div>
            <div class="filter-item">
                <label>Class Type</label>
                <select name="class_type">
                    <option value="all" <?= $class_type == 'all' ? 'selected' : '' ?>>All Classes</option>
                    <option value="onsite" <?= $class_type == 'onsite' ? 'selected' : '' ?>>On-site Only</option>
                    <option value="online" <?= $class_type == 'online' ? 'selected' : '' ?>>Online Only</option>
                </select>
            </div>
            <div class="filter-item">
                <label>&nbsp;</label>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Load
                    </button>
                    
                </div>
            </div>
        </form>
        
        <div class="info-bar">
            <span><i class="fas fa-calendar"></i> <?= date('l, F j, Y', strtotime($date)) ?></span>
            <span><i class="fas fa-clock"></i> Slot: <?= htmlspecialchars($time_breaker) ?></span>
            <span><i class="fas fa-layer-group"></i> Type: <?= ucfirst($class_type) ?></span>
            <span><i class="fas fa-list"></i> Total: <?= count($schedules) ?> Schedules</span>
        </div>
    </div>

    <?php if (!empty($schedules)): ?>
        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-info">
                <span><i class="fas fa-tasks"></i> Progress</span>
                <span><?= $current_index + 1 ?> of <?= count($schedules) ?> Schedules</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= (($current_index + 1) / count($schedules)) * 100 ?>%"></div>
            </div>
        </div>

        <?php if ($current_index < count($schedules)): 
            $row = $schedules[$current_index];
            $isRecorded = isset($already_recorded[$row['id']]);
            $record = $isRecorded ? $already_recorded[$row['id']] : null;
        ?>
        
        <!-- Attendance Form for Single Schedule -->
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="attendance_date" value="<?= $date ?>">
            <input type="hidden" name="time_breaker" value="<?= $time_breaker ?>">
            
            <div class="form-section">
                <div class="form-section-header">
                    <h3>
                        Schedule #<?= $current_index + 1 ?>
                        <?php if ($isRecorded): ?>
                            <span class="recorded-badge">
                                <i class="fas fa-check-circle"></i> Recorded
                            </span>
                        <?php endif; ?>
                    </h3>
                    <span class="badge"><?= $row['room'] ?? 'N/A' ?></span>
                </div>

                <div class="schedule-card">
                    <!-- Schedule Details -->
                    <div class="schedule-detail">
                        <div class="detail-group">
                            <span class="detail-label">Room</span>
                            <span class="room-badge-large">
                                <i class="fas fa-door-open"></i>
                                <?= htmlspecialchars($row['room'] ?? 'N/A') ?>
                            </span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">Time</span>
                            <span class="detail-value">
                                <i class="far fa-clock"></i>
                                <?= htmlspecialchars($row['official_time'] ?? 'N/A') ?>
                            </span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">Subject / Section</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($row['subject_code'] ?? 'N/A') ?>
                                <small><?= htmlspecialchars($row['section_code'] ?? '') ?></small>
                            </span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">Faculty</span>
                            <span class="detail-value">
                                <?= htmlspecialchars(($row['last_name'] ?? '') . ', ' . ($row['first_name'] ?? '')) ?>
                            </span>
                        </div>
                    </div>

                    <input type="hidden" name="faculty_id[<?= $row['id'] ?>]" value="<?= $row['faculty_id'] ?? '' ?>">

                    <!-- Form Fields -->
                    <div class="form-fields">
                        <div class="field-group">
                            <label><i class="fas fa-chalkboard-teacher"></i> Class Type</label>
                            <select name="class_type[<?= $row['id'] ?>]" 
                                    class="class-type-select" 
                                    onchange="toggleOnlineFields(this, <?= $row['id'] ?>)"
                                    <?= $isRecorded ? 'disabled' : '' ?>>
                                <option value="onsite" <?= ($isRecorded && $record['class_type'] == 'onsite') ? 'selected' : '' ?>>On-site</option>
                                <option value="online" <?= ($isRecorded && $record['class_type'] == 'online') ? 'selected' : '' ?>>Online</option>
                            </select>
                        </div>

                        <div class="field-group">
                            <label><i class="fas fa-user-check"></i> Attendance Status</label>
                            <select name="attendance[<?= $row['id'] ?>]" 
                                    class="attendance-select" 
                                    onchange="toggleStudentCount(this, <?= $row['id'] ?>)"
                                    <?= $isRecorded ? 'disabled' : '' ?>>
                                <option value="Present" <?= ($isRecorded && $record['status'] == 'Present') ? 'selected' : '' ?>>Present</option>
                                <option value="Absent" <?= ($isRecorded && $record['status'] == 'Absent') ? 'selected' : '' ?>>Absent</option>
                                <option value="Late" <?= ($isRecorded && $record['status'] == 'Late') ? 'selected' : '' ?>>Late</option>
                                <option value="Official Business" <?= ($isRecorded && $record['status'] == 'Official Business') ? 'selected' : '' ?>>Official Business</option>
                                <option value="No Teacher" <?= ($isRecorded && $record['status'] == 'No Teacher') ? 'selected' : '' ?>>No Teacher</option>
                                <option value="Early Break" <?= ($isRecorded && $record['status'] == 'Early Break') ? 'selected' : '' ?>>Early Break</option>
                                <option value="Early Dismissal" <?= ($isRecorded && $record['status'] == 'Early Dismissal') ? 'selected' : '' ?>>Early Dismissal</option>
                                <option value="Academic Tour" <?= ($isRecorded && $record['status'] == 'Academic Tour') ? 'selected' : '' ?>>Academic Tour</option>
                            </select>
                        </div>

                        <div class="field-group">
                            <label><i class="fas fa-users"></i> Number of Students</label>
                            <input type="number" 
                                   name="student_count[<?= $row['id'] ?>]" 
                                   id="student-count-<?= $row['id'] ?>"
                                   class="student-count-field" 
                                   placeholder="Count" 
                                   min="0" 
                                   value="<?= $isRecorded ? htmlspecialchars($record['student_count'] ?? 0) : 0 ?>"
                                   <?= $isRecorded ? 'disabled' : '' ?>>
                        </div>

                        <div class="field-group">
                            <label><i class="fas fa-comment"></i> Remarks</label>
                            <input type="text" name="remarks[<?= $row['id'] ?>]" 
                                   class="remarks-input" 
                                   placeholder="Add remarks"
                                   value="<?= $isRecorded ? htmlspecialchars($record['remarks'] ?? '') : '' ?>"
                                   <?= $isRecorded ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <!-- Online Fields -->
                    <div id="online-fields-<?= $row['id'] ?>" style="display: <?= ($isRecorded && $record['class_type'] == 'online') ? 'block' : 'none'; ?>;">
                        <div class="online-details">
                            <h4 style="margin-bottom: 1rem; color: var(--color2);">
                                <i class="fas fa-globe"></i> Online Class Details
                            </h4>
                            
                            <select name="online_platform[<?= $row['id'] ?>]" <?= $isRecorded ? 'disabled' : '' ?>>
                                <option value="">Select platform</option>
                                <option value="zoom" <?= ($isRecorded && $record['online_platform'] == 'zoom') ? 'selected' : '' ?>>Zoom</option>
                                <option value="google_meet" <?= ($isRecorded && $record['online_platform'] == 'google_meet') ? 'selected' : '' ?>>Google Meet</option>
                                <option value="ms_teams" <?= ($isRecorded && $record['online_platform'] == 'ms_teams') ? 'selected' : '' ?>>MS Teams</option>
                                <option value="skype" <?= ($isRecorded && $record['online_platform'] == 'skype') ? 'selected' : '' ?>>Skype</option>
                                <option value="discord" <?= ($isRecorded && $record['online_platform'] == 'discord') ? 'selected' : '' ?>>Discord</option>
                                <option value="other" <?= ($isRecorded && $record['online_platform'] == 'other') ? 'selected' : '' ?>>Other</option>
                            </select>
                            
                            <input type="url" name="meeting_link[<?= $row['id'] ?>]" 
                                   placeholder="Meeting link"
                                   value="<?= $isRecorded ? htmlspecialchars($record['meeting_link'] ?? '') : '' ?>"
                                   <?= $isRecorded ? 'disabled' : '' ?>>
                            
                            <div style="display: flex; gap: 4px;">
                                <input type="text" name="meeting_id[<?= $row['id'] ?>]" 
                                       placeholder="Meeting ID"
                                       value="<?= $isRecorded ? htmlspecialchars($record['meeting_id'] ?? '') : '' ?>"
                                       <?= $isRecorded ? 'disabled' : '' ?>>
                                <input type="text" name="meeting_password[<?= $row['id'] ?>]" 
                                       placeholder="Password"
                                       value="<?= $isRecorded ? htmlspecialchars($record['meeting_password'] ?? '') : '' ?>"
                                       <?= $isRecorded ? 'disabled' : '' ?>>
                            </div>
                            
                            <select name="internet_status[<?= $row['id'] ?>]" <?= $isRecorded ? 'disabled' : '' ?>>
                                <option value="">Internet status</option>
                                <option value="stable" <?= ($isRecorded && $record['internet_status'] == 'stable') ? 'selected' : '' ?>>Stable</option>
                                <option value="unstable" <?= ($isRecorded && $record['internet_status'] == 'unstable') ? 'selected' : '' ?>>Unstable</option>
                                <option value="intermittent" <?= ($isRecorded && $record['internet_status'] == 'intermittent') ? 'selected' : '' ?>>Intermittent</option>
                            </select>
                            
                            <textarea name="connectivity_issues[<?= $row['id'] ?>]" 
                                      placeholder="Connectivity issues..." 
                                      rows="2"
                                      <?= $isRecorded ? 'disabled' : '' ?>><?= $isRecorded ? htmlspecialchars($record['connectivity_issues'] ?? '') : '' ?></textarea>
                            
                            <div>
                                <label><i class="fas fa-camera"></i> Screenshot</label>
                                <input type="file" name="attendance_file[<?= $row['id'] ?>]" 
                                       accept="image/*,.pdf"
                                       <?= $isRecorded ? 'disabled' : '' ?>>
                                <?php if ($isRecorded && !empty($record['online_attendance_file'])): ?>
                                    <small style="display: block; margin-top: 4px; color: var(--color3);">
                                        Current file: <?= $record['online_attendance_file'] ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-bar">
                        <div class="nav-buttons">
                            <?php if ($current_index > 0): ?>
                                <a href="?page=attendance_form&date=<?= $date ?>&time_breaker=<?= $time_breaker ?>&index=<?= $current_index - 1 ?>" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($current_index < count($schedules) - 1): ?>
                                <a href="?page=attendance_form&date=<?= $date ?>&time_breaker=<?= $time_breaker ?>&index=<?= $current_index + 1 ?>" class="btn btn-primary">
                                    Skip <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($current_index == count($schedules) - 1): ?>
                                <a href="?page=attendance_form&date=<?= $date ?>&time_breaker=<?= $time_breaker ?>&index=0" class="btn btn-primary">
                                    <i class="fas fa-redo"></i> Start Over
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if (!$isRecorded): ?>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save & Continue
                            </button>
                        <?php else: ?>
                            <a href="?page=attendance_form&date=<?= $date ?>&time_breaker=<?= $time_breaker ?>&index=<?= $current_index + 1 ?>" class="btn btn-success">
                                Next Schedule <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>

        <?php else: ?>
            <!-- All schedules completed -->
            <div class="form-section">
                <div class="form-section-header">
                    <h3>All Done!</h3>
                </div>
                <div class="schedule-card">
                    <div class="empty-state">
                        <i class="fas fa-check-circle" style="color: #27ae60; font-size: 4rem;"></i>
                        <h3>All schedules have been processed</h3>
                        <p>You've completed all attendance records for this time slot.</p>
                        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                            <a href="?page=attendance_form&date=<?= $date ?>&time_breaker=<?= $time_breaker ?>&index=0" class="btn btn-primary">
                                <i class="fas fa-redo"></i> Start Over
                            </a>
                            <a href="?page=attendance_form" class="btn btn-success">
                                <i class="fas fa-calendar"></i> New Date
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No schedules found -->
        <div class="form-section">
            <div class="form-section-header">
                <h3>No Schedules Found</h3>
            </div>
            <div class="schedule-card">
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>No schedules found for this date and time</h3>
                    <p>Try selecting a different date or time slot</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Legend -->
    <div class="legend">
        <h4><i class="fas fa-info-circle"></i> Quick Reference</h4>
        <ul class="legend-grid">
            <li><span class="type-badge type-onsite">On-site</span> - Physical class</li>
            <li><span class="type-badge type-online">Online</span> - Virtual class</li>
            <li><span class="badge badge-present">Present</span> - Faculty present</li>
            <li><span class="badge badge-absent">Absent</span> - Faculty absent</li>
            <li><span class="badge badge-late">Late</span> - Faculty late</li>
            <li>📋 Official Business</li>
            <li>❌ No Teacher</li>
            <li>⏰ Early Break</li>
            <li>🚪 Early Dismissal</li>
            <li>🎓 Academic Tour</li>
        </ul>
    </div>
</div>

<script>
function toggleOnlineFields(select, rowId) {
    const onlineFields = document.getElementById('online-fields-' + rowId);
    if (onlineFields) {
        onlineFields.style.display = select.value === 'online' ? 'block' : 'none';
    }
}

function toggleStudentCount(select, rowId) {
    const studentCountField = document.getElementById('student-count-' + rowId);
    const isCountable = select.value === 'Present' || select.value === 'Late';
    
    if (studentCountField && !studentCountField.disabled) {
        studentCountField.disabled = !isCountable;
        if (!isCountable) {
            studentCountField.value = '0';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.class-type-select:not([disabled])').forEach(select => {
        const match = select.name.match(/\d+/);
        if (match) {
            const rowId = match[0];
            toggleOnlineFields(select, rowId);
        }
    });

    document.querySelectorAll('.attendance-select:not([disabled])').forEach(select => {
        const match = select.name.match(/\d+/);
        if (match) {
            const rowId = match[0];
            toggleStudentCount(select, rowId);
        }
    });
});
</script>