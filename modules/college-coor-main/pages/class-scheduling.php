<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__, 3) . '/database/db.php';
require_once dirname(__DIR__) . '/classes/Schedule.php';
require_once dirname(__DIR__) . '/classes/Faculty.php';
require_once dirname(__DIR__) . '/classes/SectionManager.php';

$database = new Database();
$db = $database->getConnection();

// Check database connection
if (!$db) {
    die("Database connection failed");
}

$schedule = new Schedule($db);
$faculty = new Faculty($db);
$section = new SectionManager($db);

// Load active academic periods for the View Schedules filters.
$school_years = $db->query("SELECT id, name FROM rgr_school_years WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$requestedSchoolYear = isset($_GET['school_year_id']) ? (int)$_GET['school_year_id'] : 0;
$school_year = 0;
foreach ($school_years as $schoolYearOption) {
    if ((int)$schoolYearOption['id'] === $requestedSchoolYear) {
        $school_year = $requestedSchoolYear;
        break;
    }
}
$school_year = $school_year ?: (!empty($school_years) ? (int)$school_years[0]['id'] : 0);

$semesterStmt = $db->prepare("SELECT id, name, school_year_id, is_active
    FROM rgr_semesters
    WHERE school_year_id = :school_year_id
      AND is_active = 1
    ORDER BY id");
$semesterStmt->bindValue(':school_year_id', $school_year, PDO::PARAM_INT);
$semesterStmt->execute();
$semesters = $semesterStmt->fetchAll(PDO::FETCH_ASSOC);

$requestedSemester = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : 0;
$semester = 0;
foreach ($semesters as $semesterOption) {
    if ((int)$semesterOption['id'] === $requestedSemester) {
        $semester = $requestedSemester;
        break;
    }
}
$semester = $semester ?: (!empty($semesters) ? (int)$semesters[0]['id'] : 0);
$subjects = $db->query("SELECT id, code, name FROM rgr_subjects ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);
$faculty_loads = $db->query("SELECT
    fl.id,
    fl.faculty_id,
    fl.subject_id,
    fl.section_id,
    fl.semester_id,
    fl.school_year_id,
    CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
    sec.section_code
    FROM cc_faculty_load fl
    INNER JOIN cc_faculty f ON f.id = fl.faculty_id
    INNER JOIN cc_sections sec ON sec.id = fl.section_id
    INNER JOIN rgr_school_years sy ON sy.id = fl.school_year_id AND sy.is_active = 1
    INNER JOIN rgr_semesters sem ON sem.id = fl.semester_id
        AND sem.school_year_id = fl.school_year_id
        AND sem.is_active = 1
    WHERE fl.school_year_id = {$school_year}
      AND fl.semester_id = {$semester}
      AND NOT EXISTS (
          SELECT 1
          FROM cc_schedule cs
          WHERE cs.faculty_load_id = fl.id
            AND cs.schedule_type = 'Class'
            AND cs.status = 'Scheduled'
      )
    ORDER BY f.last_name, f.first_name, sec.section_code, fl.id")->fetchAll(PDO::FETCH_ASSOC);

// Existing Class schedules may still need their Faculty Load displayed in Edit.
$edit_faculty_loads_stmt = $db->prepare("SELECT
    fl.id, fl.faculty_id, fl.subject_id, fl.section_id, fl.semester_id, fl.school_year_id,
    CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
    sec.section_code
    FROM cc_faculty_load fl
    INNER JOIN cc_faculty f ON f.id = fl.faculty_id
    INNER JOIN cc_sections sec ON sec.id = fl.section_id
    INNER JOIN rgr_school_years sy ON sy.id = fl.school_year_id AND sy.is_active = 1
    INNER JOIN rgr_semesters sem ON sem.id = fl.semester_id
        AND sem.school_year_id = fl.school_year_id
        AND sem.is_active = 1
    ORDER BY f.last_name, f.first_name, sec.section_code, fl.id");
$edit_faculty_loads_stmt->execute();
$edit_faculty_loads = $edit_faculty_loads_stmt->fetchAll(PDO::FETCH_ASSOC);

$getActiveFacultyLoad = static function (PDO $db, int $facultyLoadId, int $schoolYearId, int $semesterId): ?array {
    $stmt = $db->prepare("SELECT fl.faculty_id, fl.subject_id, fl.section_id, fl.semester_id, fl.school_year_id
        FROM cc_faculty_load fl
        INNER JOIN rgr_school_years sy ON sy.id = fl.school_year_id AND sy.is_active = 1
        INNER JOIN rgr_semesters sem ON sem.id = fl.semester_id
            AND sem.school_year_id = fl.school_year_id
            AND sem.is_active = 1
        WHERE fl.id = :faculty_load_id
          AND fl.school_year_id = :school_year_id
          AND fl.semester_id = :semester_id
        LIMIT 1");
    $stmt->bindValue(':faculty_load_id', $facultyLoadId, PDO::PARAM_INT);
    $stmt->bindValue(':school_year_id', $schoolYearId, PDO::PARAM_INT);
    $stmt->bindValue(':semester_id', $semesterId, PDO::PARAM_INT);
    $stmt->execute();
    $load = $stmt->fetch(PDO::FETCH_ASSOC);
    return $load ?: null;
};

// Remaining filter values use IDs from the selected academic period.
$day_of_week = isset($_GET['day_of_week']) ? $_GET['day_of_week'] : '';
$room = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

// Handle form submissions - FIXED HERE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schedule'])) {
        try {
            // Convert time format from HH:MM to HH:MM:SS
            $start_time = $_POST['start_time'] . ':00';
            $end_time = $_POST['end_time'] . ':00';

            // Add new schedule using Schedule class
            $schedule->room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
            $schedule->start_time = $start_time;
            $schedule->end_time = $end_time;
            $schedule->day_of_week = trim($_POST['day_of_week']);
            $schedule->subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
            $schedule->grade_section_id = isset($_POST['grade_section_id']) ? (int)$_POST['grade_section_id'] : 0;
            $schedule->faculty_id = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : 0;
            $schedule->semester_id = isset($_POST['semester_id']) ? (int)$_POST['semester_id'] : 0;
            $schedule->school_year_id = isset($_POST['school_year_id']) ? (int)$_POST['school_year_id'] : 0;
            $schedule->schedule_type = isset($_POST['schedule_type']) ? trim($_POST['schedule_type']) : 'Class';
            $schedule->faculty_load_id = isset($_POST['faculty_load_id']) ? (int)$_POST['faculty_load_id'] : 0;

            $submittedClassFields = [
                'faculty_load_id' => 'Faculty Load',
                'subject_id' => 'Subject',
                'grade_section_id' => 'Section',
                'room_id' => 'Room'
            ];

            if (empty($schedule->day_of_week) || empty($schedule->start_time) || empty($schedule->end_time)) {
                throw new Exception('Day, start time, and end time are required.');
            }

            if ($schedule->schedule_type === 'Break Time') {
                foreach ($submittedClassFields as $field => $label) {
                    if ($field !== 'room_id' && array_key_exists($field, $_POST) && trim((string)$_POST[$field]) !== '') {
                        throw new Exception("Break Time cannot include a {$label}.");
                    }
                }

                // Break Time has no teaching-load relationships, but it may still
                // use a selected room and must retain the directly selected faculty.
                $schedule->faculty_load_id = 0;
                $schedule->subject_id = 0;
                $schedule->grade_section_id = 0;
                $schedule->semester_id = (int)$semester;
                $schedule->school_year_id = (int)$school_year;

                if (empty($schedule->faculty_id)) {
                    throw new Exception('Break Time requires an Assigned Faculty.');
                }

                $facultyStmt = $db->prepare('SELECT id FROM cc_faculty WHERE id = :faculty_id LIMIT 1');
                $facultyStmt->bindValue(':faculty_id', (int)$schedule->faculty_id, PDO::PARAM_INT);
                $facultyStmt->execute();
                if (!$facultyStmt->fetchColumn()) {
                    throw new Exception('The selected Assigned Faculty does not exist.');
                }
            } elseif (empty($schedule->room_id)) {
                throw new Exception('Class schedules require a Room.');
            }

            // If a faculty load was selected, auto-fill faculty, subject, section
            if ($schedule->schedule_type === 'Class') {
                if (empty($schedule->faculty_load_id)) {
                    throw new Exception('Class scheduling requires a Faculty Load.');
                }

                $fld = $getActiveFacultyLoad($db, $schedule->faculty_load_id, $school_year, $semester);
                if (!$fld) {
                    throw new Exception('Class scheduling is only available for the active school year and semester.');
                }

                $schedule->faculty_id = (int)$fld['faculty_id'];
                $schedule->subject_id = (int)$fld['subject_id'];
                $schedule->grade_section_id = (int)$fld['section_id'];
                $schedule->semester_id = (int)$fld['semester_id'];
                $schedule->school_year_id = (int)$fld['school_year_id'];
            }

            if ($schedule->create()) {
                $persistedFacultyId = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : 0;
                echo "<script>
                    sessionStorage.setItem('refreshFacultyLoad', 'true');
                    if ({$persistedFacultyId}) {
                        sessionStorage.setItem('schedulePrintFacultyId', '{$persistedFacultyId}');
                    }
                    window.location.href=window.location.href;
                </script>";
                exit;
            } else {
                $errorInfo = $db->errorInfo();
                throw new Exception("Failed to add schedule. Error: " . ($errorInfo[2] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    } elseif (isset($_POST['update_schedule'])) {
        try {
            // Convert time format from HH:MM to HH:MM:SS
            $start_time = $_POST['start_time'] . ':00';
            $end_time = $_POST['end_time'] . ':00';

            // Update schedule using Schedule class
            $schedule->id = (int)$_POST['schedule_id'];
            $schedule->room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
            $schedule->start_time = $start_time;
            $schedule->end_time = $end_time;
            $schedule->day_of_week = trim($_POST['day_of_week']);
            $schedule->subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
            $schedule->grade_section_id = isset($_POST['grade_section_id']) ? (int)$_POST['grade_section_id'] : 0;
            $schedule->faculty_id = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : 0;
            $schedule->semester_id = isset($_POST['semester_id']) ? (int)$_POST['semester_id'] : 0;
            $schedule->school_year_id = isset($_POST['school_year_id']) ? (int)$_POST['school_year_id'] : 0;
            $schedule->schedule_type = isset($_POST['schedule_type']) ? trim($_POST['schedule_type']) : 'Class';
            $schedule->faculty_load_id = isset($_POST['faculty_load_id']) ? (int)$_POST['faculty_load_id'] : 0;

            if ($schedule->schedule_type === 'Break Time') {
                $schedule->faculty_load_id = 0;
                $schedule->subject_id = 0;
                $schedule->grade_section_id = 0;
            }

            // If a faculty load was selected, auto-fill faculty, subject, section
            if ($schedule->schedule_type === 'Class') {
                if (empty($schedule->faculty_load_id)) {
                    throw new Exception('Class scheduling requires a Faculty Load.');
                }

                $fld = $getActiveFacultyLoad($db, $schedule->faculty_load_id, $school_year, $semester);
                if (!$fld) {
                    throw new Exception('Class scheduling is only available for the active school year and semester.');
                }

                $schedule->faculty_id = (int)$fld['faculty_id'];
                $schedule->subject_id = (int)$fld['subject_id'];
                $schedule->grade_section_id = (int)$fld['section_id'];
                $schedule->semester_id = (int)$fld['semester_id'];
                $schedule->school_year_id = (int)$fld['school_year_id'];
            }

            if ($schedule->update()) {
                $persistedFacultyId = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : 0;
                echo "<script>
                    sessionStorage.setItem('refreshFacultyLoad', 'true');
                    if ({$persistedFacultyId}) {
                        sessionStorage.setItem('schedulePrintFacultyId', '{$persistedFacultyId}');
                    }
                    window.location.href=window.location.href;
                </script>";
                exit;
            } else {
                $errorInfo = $db->errorInfo();
                throw new Exception("Failed to update schedule. Error: " . ($errorInfo[2] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    } elseif (isset($_POST['delete_schedule'])) {
        try {
            $schedule->id = (int)$_POST['schedule_id'];
            if ($schedule->delete()) {
                echo "<script>alert('Schedule deleted successfully!'); sessionStorage.setItem('refreshFacultyLoad', 'true'); window.location.href=window.location.href;</script>";
                exit;
            } else {
                throw new Exception("Failed to delete schedule.");
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Get all schedules using the new method
$schedules_result = $schedule->getAllWithFilters($semester, $school_year, $day_of_week, $room);
$schedules = $schedules_result ? $schedules_result->fetchAll(PDO::FETCH_ASSOC) : [];

// Get distinct values for filters using the new methods
$distinct_days_result = $schedule->getDistinctDays($semester, $school_year);
$distinct_days = $distinct_days_result ? $distinct_days_result->fetchAll() : [];

$distinct_rooms_result = $schedule->getDistinctRooms($semester, $school_year);
$distinct_rooms = $distinct_rooms_result ? $distinct_rooms_result->fetchAll() : [];
$all_rooms = $db->query("SELECT id AS room_id, room_name AS room FROM cc_room WHERE status = 'Available' ORDER BY room_name")->fetchAll(PDO::FETCH_ASSOC);

// Get data for dropdowns
$all_faculty = $db->query("SELECT id, faculty_code, first_name, last_name FROM cc_faculty ORDER BY last_name")->fetchAll();
$all_courses = $db->query("SELECT id, code, name FROM rgr_courses ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);
$all_sections = $db->query("SELECT cs.id, cs.section_code, cs.grade_level, c.code AS program FROM cc_sections cs LEFT JOIN rgr_courses c ON cs.program_id = c.id ORDER BY cs.section_code")->fetchAll();

// Calculate statistics using the new method
$stats = $schedule->getStatistics($semester, $school_year);
$total_schedules = $stats['total_schedules'] ?? 0;
$rooms_count = $stats['total_rooms'] ?? 0;
$faculty_count = $stats['total_faculty'] ?? 0;
$sections_count = $stats['total_sections'] ?? 0;

// Get weekly summary
$weekly_summary_result = $schedule->getWeeklySummary($semester, $school_year);
$weekly_summary = $weekly_summary_result ? $weekly_summary_result->fetchAll(PDO::FETCH_ASSOC) : [];

// Create daily summary from weekly_summary
$daily_summary = [];
foreach ($weekly_summary as $item) {
    $daily_summary[$item['day_of_week']] = $item['total_classes'];
}

// Helper maps for display
$semester_map = array_column($semesters, 'name', 'id');
$school_year_map = array_column($school_years, 'name', 'id');
?>
        <!-- Header -->
        <div class="module-header">
            <h1>Schedule Management System</h1>
            <p>Office of the Safety and Security | Class Schedule Management</p>
        </div>

        <div class="module-content">
            <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-check fa-2x" style="color: #4361ee;"></i>
                <div class="stat-value"><?= $total_schedules ?></div>
                <div class="stat-label">Total Classes</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-door-open fa-2x" style="color: #3a0ca3;"></i>
                <div class="stat-value"><?= $rooms_count ?></div>
                <div class="stat-label">Rooms Used</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chalkboard-teacher fa-2x" style="color: #4cc9f0;"></i>
                <div class="stat-value"><?= $faculty_count ?></div>
                <div class="stat-label">Faculty Scheduled</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users fa-2x" style="color: #f72585;"></i>
                <div class="stat-value"><?= $sections_count ?></div>
                <div class="stat-label">Sections</div>
            </div>
        </div>

        <!-- Daily Summary -->
        <?php if (!empty($daily_summary)): ?>
        <div class="form-card">
            <h3><i class="fas fa-chart-bar"></i> Weekly Schedule Distribution</h3>
            <div class="summary-grid">
                <?php 
                $days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($days_order as $day): 
                    $count = $daily_summary[$day] ?? 0;
                ?>
                <div class="summary-item">
                    <div class="summary-day"><?= $day ?></div>
                    <div class="summary-count"><?= $count ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <button type="button" class="tab <?= !isset($_GET['action']) || $_GET['action'] == 'add' ? 'active' : '' ?>" data-tab="add">
                <i class="fas fa-plus-circle"></i> Add Schedule
            </button>
            <button type="button" class="tab <?= isset($_GET['action']) && $_GET['action'] == 'proctoring' ? 'active' : '' ?>" data-tab="proctoring">
                <i class="fas fa-user-shield"></i> Exam Proctoring
            </button>
        </div>

        <!-- Filters -->
        <div id="classScheduleFilters" class="filters">
            <form method="GET" class="filter-group">
                <div class="filter-item">
                    <label><i class="fas fa-graduation-cap"></i> Semester</label>
                    <select name="semester_id">
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label><i class="fas fa-calendar-alt"></i> School Year</label>
                    <select name="school_year_id" onchange="this.form.submit()">
                        <?php foreach ($school_years as $sy): ?>
                            <option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label><i class="fas fa-calendar-day"></i> Day of Week</label>
                    <select name="day_of_week">
                        <option value="">All Days</option>
                        <?php foreach ($distinct_days as $day): ?>
                            <option value="<?= htmlspecialchars($day['day_of_week']) ?>" <?= $day_of_week == $day['day_of_week'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($day['day_of_week']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label><i class="fas fa-door-open"></i> Room</label>
                    <select name="room_id">
                        <option value="">All Rooms</option>
                        <?php foreach ($distinct_rooms as $room_item): ?>
                            <option value="<?= (int)$room_item['room_id'] ?>" <?= $room == $room_item['room_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($room_item['room']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="schedule_manager.php" class="btn btn-warning">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
            <?php if ($school_year && empty($semesters)): ?>
                <div class="alert alert-warning" style="margin-top: 12px;">The selected school year has no active semester. No schedules are available for this academic period.</div>
            <?php endif; ?>
        </div>

        <!-- Add Schedule Tab -->
        <div id="add-tab" class="tab-content <?= isset($_GET['action']) && $_GET['action'] == 'add' ? 'active' : '' ?>">
            <h2><i class="fas fa-plus-circle"></i> Add New Schedule</h2>
            
            <form id="addForm" method="POST" class="form-grid" data-custom-submit onsubmit="return validateForm()">
                <div class="form-card">
                    <h3>Basic Information</h3>
                    <div class="form-group schedule-type-field" id="add-room-field">
                        <label for="room_id">Room</label>
                        <select id="room_id" name="room_id" required>
                            <option value="">Select Room</option>
                            <?php foreach ($all_rooms as $room_item): ?>
                                <option value="<?= (int)$room_item['room_id'] ?>"><?= htmlspecialchars($room_item['room']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="schedule_type">Schedule Type</label>
                        <select id="schedule_type" name="schedule_type">
                            <option value="Class">Class</option>
                            <option value="Break Time">Break Time</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Course Information</h3>
                    <div class="form-group">
                        <label for="day_of_week">Day of Week</label>
                        <select id="day_of_week" name="day_of_week" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-subject-field">
                        <label for="subject_id">Subject</label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= (int)$sub['id'] ?>"><?= htmlspecialchars($sub['code']) ?> - <?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-section-field">
                        <label for="section_id">Section</label>
                        <select id="section_id" name="grade_section_id" required>
                            <option value="">Select Section</option>
                            <?php foreach ($all_sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>">
                                    <?= htmlspecialchars($sec['section_code']) ?> (<?= htmlspecialchars($sec['grade_level']) ?> - <?= htmlspecialchars($sec['program']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Faculty & Semester</h3>
                    <div class="form-group schedule-type-field" id="add-faculty-load-field">
                        <label for="faculty_load_id">Faculty Load</label>
                        <select id="faculty_load_id" name="faculty_load_id" required>
                            <option value="">-- Select Faculty Load --</option>
                            <?php foreach ($faculty_loads as $fl): ?>
                                <option value="<?= (int)$fl['id'] ?>" data-faculty="<?= (int)$fl['faculty_id'] ?>" data-subject="<?= (int)$fl['subject_id'] ?>" data-section="<?= (int)$fl['section_id'] ?>" data-semester="<?= (int)$fl['semester_id'] ?>" data-school-year="<?= (int)$fl['school_year_id'] ?>">
                                    <?= htmlspecialchars($fl['faculty_name']) ?> | <?= htmlspecialchars($fl['section_code']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="add-faculty-field">
                        <label for="faculty_id">Assigned Faculty</label>
                        <select id="faculty_id" name="faculty_id" required>
                            <option value="">Select Faculty</option>
                            <?php foreach ($all_faculty as $fac): ?>
                                <option value="<?= (int)$fac['id'] ?>">
                                    <?= htmlspecialchars($fac['last_name']) ?>, <?= htmlspecialchars($fac['first_name']) ?> (<?= htmlspecialchars($fac['faculty_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-semester-field">
                        <label for="semester_id">Semester</label>
                        <select id="semester_id" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-school-year-field">
                        <label for="school_year_id">School Year</label>
                        <select id="school_year_id" name="school_year_id" required>
                            <option value="">Select School Year</option>
                            <?php foreach ($school_years as $sy): ?>
                                <option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="grid-column: 1 / -1; text-align: center;">
                    <input type="hidden" name="semester_filter" value="<?= htmlspecialchars($semester) ?>">
                    <input type="hidden" name="school_year_filter" value="<?= htmlspecialchars($school_year) ?>">
                    <button type="submit" name="add_schedule" value="1" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Schedule
                    </button>
                    <button type="reset" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
                <?php if (!empty($all_faculty)): ?>
                    <script id="preload-scheduleAllFaculty" type="application/json">
                        <?= json_encode(array_map(function($f){ return [
                            'id' => (int)($f['id'] ?? $f['faculty_id'] ?? 0),
                            'first_name' => $f['first_name'] ?? '',
                            'last_name' => $f['last_name'] ?? '',
                            'faculty_code' => $f['faculty_code'] ?? ''
                        ]; }, $all_faculty), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
                    </script>
                <?php endif; ?>

            <!-- View Schedules Section -->
            <div style="margin-top: 40px; border-top: 2px solid #dee2e6; padding-top: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2><i class="fas fa-list"></i> All Schedules (<?= $total_schedules ?>)</h2>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <label for="facultySelector" style="margin: 0; font-weight: 600;">Select Faculty to Print:</label>
                            <select id="facultySelector" class="form-control" style="width: 250px; flex: 0;">
                                <option value="">-- Select Faculty --</option>
                                <?php 
                                    $faculty_list = [];
                                    foreach ($schedules as $schedule) {
                                        $faculty_key = (int)($schedule['faculty_id'] ?? 0);
                                        if ($faculty_key && !isset($faculty_list[$faculty_key])) {
                                            $first_name = $schedule['first_name'] ?? '';
                                            $last_name = $schedule['last_name'] ?? '';
                                            $faculty_name = trim($first_name . ' ' . $last_name);
                                            $faculty_list[$faculty_key] = $faculty_name ?: 'Unknown';
                                        }
                                    }
                                    foreach ($faculty_list as $fac_id => $fac_name) {
                                        echo '<option value="' . $fac_id . '">' . htmlspecialchars($fac_name) . '</option>';
                                    }
                                ?>
                            </select>
                            <button id="printScheduleBtn" onclick="printSchedule()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                
                <?php if ($total_schedules > 0): ?>
                    <div class="table-container" id="viewScheduleTableContainer">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time Slot</th>
                                    <th>Room</th>
                                    <th>Subject</th>
                                    <th>Section</th>
                                    <th>Faculty</th>
                                    <th>Semester</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $row): ?>
                                <tr data-faculty-id="<?= (int)($row['faculty_id'] ?? 0) ?>">
                                    <td>
                                        <span class="badge badge-primary"><?= htmlspecialchars($row['day_of_week'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <small><?= date('h:i A', strtotime($row['start_time'])) ?> - <?= date('h:i A', strtotime($row['end_time'])) ?></small>
                                    </td>
                                    <td><span class="badge badge-warning"><?= htmlspecialchars($row['room'] ?? '') ?></span></td>
                                    <td><strong><?= htmlspecialchars($row['subject_code'] ?? '') ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($row['section_code'] ?? '') ?><br>
                                        <small><?= htmlspecialchars($row['grade_level'] ?? '') ?> - <?= htmlspecialchars($row['program'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['last_name'] ?? '') ?>, <?= htmlspecialchars($row['first_name'] ?? '') ?><br>
                                        <small><?= htmlspecialchars($row['faculty_code'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($semester_map[$row['semester_id']] ?? ($row['semester'] ?? '')) ?><br>
                                        <small><?= htmlspecialchars($school_year_map[$row['school_year_id']] ?? ($row['school_year'] ?? '')) ?></small>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick='editSchedule(<?= json_encode($row) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-calendar-times fa-3x" style="margin-bottom: 20px; color: #ddd;"></i>
                        <h3>No schedules found</h3>
                        <p>Try different filters or add new schedules</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Exam Proctoring Tab -->
        <div id="proctoring-tab" class="tab-content <?= isset($_GET['action']) && $_GET['action'] == 'proctoring' ? 'active' : '' ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;">
                <h2><i class="fas fa-user-shield"></i> Exam Proctoring Assignment</h2>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="button" class="btn btn-success" onclick="openExamModal()"><i class="fas fa-file-medical"></i> Create Exam</button>
                    <button type="button" class="btn btn-primary" onclick="openExamScheduleModal()"><i class="fas fa-calendar-plus"></i> Add Exam Schedule</button>
                    <button type="button" class="btn btn-primary" onclick="openProctorModal()"><i class="fas fa-plus"></i> Assign Proctor</button>
                    <button type="button" class="btn btn-secondary" onclick="printProctorSchedule()"><i class="fas fa-print"></i> Print Schedule</button>
                </div>
            </div>
            <div class="filters" style="margin-bottom: 20px;">
                <div class="filter-group">
                    <div class="filter-item"><label for="proctorSemesterFilter">Semester</label><select id="proctorSemesterFilter">
                        <option value="">All Semesters</option>
                        <?php foreach ($semesters as $sem): ?><option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="filter-item"><label for="proctorSchoolYearFilter">School Year</label><select id="proctorSchoolYearFilter">
                        <option value="">All School Years</option>
                        <?php foreach ($school_years as $sy): ?><option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="filter-item"><label for="proctorExamFilter">Exam</label><select id="proctorExamFilter"><option value="">All Exams</option></select></div>
                    <div class="filter-item"><label for="proctorStatusFilter">Status</label><select id="proctorStatusFilter"><option value="Assigned" selected>Assigned</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option><option value="">All Statuses</option></select></div>
                    <div class="filter-item"><button type="button" class="btn btn-primary" onclick="loadProctorAssignments()"><i class="fas fa-filter"></i> Apply Filters</button> <button type="button" class="btn btn-warning" onclick="clearProctorFilters()"><i class="fas fa-times"></i> Clear</button></div>
                </div>
            </div>
            <div class="table-container"><table class="data-table"><thead><tr>
                <th>Exam</th><th>Subject</th><th>Section</th><th>Room</th><th>Exam Date</th><th>Time</th><th>Proctor</th><th>Role</th><th>Status</th><th>Actions</th>
            </tr></thead><tbody id="proctorAssignmentsBody"><tr><td colspan="10" style="text-align:center;">Loading assignments...</td></tr></tbody></table></div>
            <h3 style="margin: 25px 0 12px;"><i class="fas fa-calendar-alt"></i> Exam Schedule List</h3>
            <div class="table-container"><table class="data-table"><thead><tr>
                <th>Type</th><th>Exam</th><th>Subject</th><th>Section</th><th>Room</th><th>Date</th><th>Time</th><th>Status</th>
            </tr></thead><tbody id="examSchedulesBody"><tr><td colspan="8" style="text-align:center;">Loading schedules...</td></tr></tbody></table></div>
        </div>
    </div>

    <!-- Create Examination Modal -->
    <div id="examModal" class="modal">
        <div class="modal-content" style="max-width: 760px;">
            <div class="modal-header"><h2><i class="fas fa-file-medical"></i> Create Examination</h2><button class="close-modal" type="button" onclick="closeExamModal()">&times;</button></div>
            <form id="examForm" onsubmit="submitExam(event)">
                <div class="form-grid">
                    <div class="form-group"><label for="examName">Exam Name</label><input type="text" name="exam_name" id="examName" required></div>
                    <div class="form-group"><label for="examType">Exam Type</label><select name="exam_type" id="examType" required><option value="">Select Exam Type</option><option value="Preliminary">Prelim</option><option value="Midterm">Midterm</option><option value="Final">Final</option><option value="Special">Special</option></select></div>
                    <div class="form-group"><label for="examSchoolYear">School Year</label><select name="school_year_id" id="examSchoolYear" required><?php foreach ($school_years as $sy): ?><option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label for="examSemester">Semester</label><select name="semester_id" id="examSemester" required><?php foreach ($semesters as $sem): ?><option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label for="examStartDate">Start Date</label><input type="date" name="start_date" id="examStartDate" required></div>
                    <div class="form-group"><label for="examEndDate">End Date</label><input type="date" name="end_date" id="examEndDate" required></div>
                    <div class="form-group"><label for="examStatus">Status</label><select name="status" id="examStatus" required><option value="Scheduled">Scheduled</option><option value="Draft">Draft</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                </div>
                <div style="text-align:center; margin-top:15px;"><button type="button" class="btn btn-warning" onclick="closeExamModal()">Cancel</button> <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Examination</button></div>
            </form>
        </div>
    </div>

    <!-- Add Exam Schedule Modal -->
    <div id="examScheduleModal" class="modal">
        <div class="modal-content" style="max-width: 760px;">
            <div class="modal-header"><h2><i class="fas fa-calendar-plus"></i> Add Exam Schedule</h2><button class="close-modal" type="button" onclick="closeExamScheduleModal()">&times;</button></div>
            <form id="examScheduleForm" onsubmit="submitExamSchedule(event)">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1;"><label for="scheduleExamId">Exam</label><select name="exam_id" id="scheduleExamId" required></select><small id="scheduleExamContext" style="display:block;margin-top:5px;color:#666;"></small></div>
                    <div class="form-group"><label for="scheduleType">Schedule Type</label><select name="schedule_type" id="scheduleType" required><option value="Exam">Exam</option><option value="Break Time">Break Time</option></select></div>
                    <div id="examScheduleClassFields" style="display: contents;">
                        <div class="form-group"><label for="scheduleCourseId">Course</label><select id="scheduleCourseId"><option value="">Select Course</option>
<?php foreach ($all_courses as $course): ?>
<option value="<?= (int)$course['id'] ?>"><?= htmlspecialchars($course['code']) ?> - <?= htmlspecialchars($course['name']) ?></option>
<?php endforeach; ?>
</select></div>
                        <div class="form-group"><label for="scheduleYearLevel">Year Level</label><select id="scheduleYearLevel"><option value="">Select Year Level</option><option value="1st Year">1st Year</option><option value="2nd Year">2nd Year</option><option value="3rd Year">3rd Year</option><option value="4th Year">4th Year</option></select></div>
                        <div class="form-group"><label for="scheduleSubjectId">Subject</label><select name="subject_id" id="scheduleSubjectId" required></select></div>
                        <div class="form-group"><label for="scheduleSectionId">Section</label><select name="section_id" id="scheduleSectionId" required></select></div>
                        <div class="form-group"><label for="scheduleRoomId">Room</label><select name="room_id" id="scheduleRoomId" required></select></div>
                    </div>
                    <div class="form-group"><label for="scheduleExamDate">Exam Date</label><input type="date" name="exam_date" id="scheduleExamDate" required></div>
                    <div class="form-group"><label for="scheduleStartTime">Start Time</label><input type="time" name="start_time" id="scheduleStartTime" required></div>
                    <div class="form-group"><label for="scheduleEndTime">End Time</label><input type="time" name="end_time" id="scheduleEndTime" required></div>
                    <div class="form-group"><label for="scheduleStatus">Status</label><select name="status" id="scheduleStatus" required><option value="Scheduled">Scheduled</option><option value="Draft">Draft</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                </div>
                <div style="text-align:center; margin-top:15px;"><button type="button" class="btn btn-warning" onclick="closeExamScheduleModal()">Cancel</button> <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Schedule</button></div>
            </form>
        </div>
    </div>

    <!-- Exam Proctor Modal -->
    <div id="proctorModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header"><h2 id="proctorModalTitle"><i class="fas fa-user-shield"></i> Assign Exam Proctor</h2><button class="close-modal" type="button" onclick="closeProctorModal()">&times;</button></div>
            <form id="proctorForm" onsubmit="submitProctor(event)">
                <input type="hidden" name="id" id="proctorAssignmentId">
                <div class="form-group"><label for="proctorScheduleId">Exam Schedule</label><select name="exam_schedule_id" id="proctorScheduleId" required><option value="">Select Exam Schedule</option></select></div>
                <div id="proctorScheduleInfo" class="form-card" style="margin-bottom: 15px; min-height: 20px;"></div>
                <div class="form-grid" style="margin-bottom: 10px;">
                    <div class="form-group"><label for="proctorFacultyId">Faculty / Proctor</label><select name="faculty_id" id="proctorFacultyId" required><option value="">Select Faculty</option></select></div>
                    <div class="form-group"><label for="proctorRole">Role</label><select name="role" id="proctorRole" required><option value="Proctor">Proctor</option><option value="Lead Proctor">Lead Proctor</option><option value="Reliever">Reliever</option></select></div>
                    <div class="form-group"><label for="proctorStatus">Status</label><select name="status" id="proctorStatus" required><option value="Assigned">Assigned</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                </div>
                <div style="text-align: center; margin-top: 15px;"><button type="button" class="btn btn-warning" onclick="closeProctorModal()">Cancel</button> <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Assignment</button></div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Schedule</h2>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" id="editForm" class="form-grid" data-custom-submit onsubmit="return validateEditForm()">
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                
                <div class="form-card">
                    <h3>Basic Information</h3>
                    
                    <div class="form-group">
                        <label>Room</label>
                        <select name="room_id" id="edit_room" required>
                            <option value="">Select Room</option>
                            <?php foreach ($all_rooms as $room_item): ?>
                                <option value="<?= (int)$room_item['room_id'] ?>"><?= htmlspecialchars($room_item['room']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Schedule Type</label>
                        <select name="schedule_type" id="edit_schedule_type">
                            <option value="Class">Class</option>
                            <option value="Break Time">Break Time</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" id="edit_end_time" required>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Course Information</h3>
                    <div class="form-group">
                        <label>Day of Week</label>
                        <select name="day_of_week" id="edit_day_of_week" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject_id" id="edit_subject_id">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= (int)$sub['id'] ?>"><?= htmlspecialchars($sub['code']) ?> - <?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Section</label>
                        <select name="grade_section_id" id="edit_grade_section_id">
                            <?php foreach ($all_sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>">
                                    <?= htmlspecialchars($sec['section_code']) ?> (<?= htmlspecialchars($sec['grade_level']) ?> - <?= htmlspecialchars($sec['program']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Faculty & Semester</h3>
                    <div class="form-group">
                        <label>Faculty Load (optional)</label>
                        <select name="faculty_load_id" id="edit_faculty_load_id">
                            <option value="">-- Select Faculty Load --</option>
                            <?php foreach ($edit_faculty_loads as $fl): ?>
                                <option value="<?= (int)$fl['id'] ?>" data-faculty="<?= (int)$fl['faculty_id'] ?>" data-subject="<?= (int)$fl['subject_id'] ?>" data-section="<?= (int)$fl['section_id'] ?>" data-semester="<?= (int)$fl['semester_id'] ?>" data-school-year="<?= (int)$fl['school_year_id'] ?>"><?= htmlspecialchars($fl['faculty_name']) ?> | <?= htmlspecialchars($fl['section_code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assigned Faculty</label>
                        <select name="faculty_id" id="edit_faculty_id" required>
                            <?php foreach ($all_faculty as $fac): ?>
                                <option value="<?= (int)$fac['id'] ?>"><?= htmlspecialchars($fac['last_name']) ?>, <?= htmlspecialchars($fac['first_name']) ?> (<?= htmlspecialchars($fac['faculty_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester_id" id="edit_semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= (int)$sem['id'] ?>"><?= htmlspecialchars($sem['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>School Year</label>
                        <select name="school_year_id" id="edit_school_year_id" required>
                            <option value="">Select School Year</option>
                            <?php foreach ($school_years as $sy): ?>
                                <option value="<?= (int)$sy['id'] ?>"><?= htmlspecialchars($sy['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="grid-column: 1 / -1; text-align: center; margin-top: 20px;">
                    <button type="submit" name="update_schedule" value="1" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Schedule
                    </button>
                    <button type="button" class="btn btn-warning" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    </body>
</html>