<?php
ini_set('display_errors', 0);
error_reporting(0);

include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Schedule.php';
include_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

$userClass = new User();
$userInfo  = $userClass->userSession();

if (!$userInfo) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$schedule = new Schedule();

switch ($action) {

    // ── CREATE SCHEDULE ───────────────────────────────────────────────────
    case 'create':
        // Validate required fields
        $room = trim($_POST['room'] ?? '');
        $official_time = trim($_POST['official_time'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $day_of_week = trim($_POST['day_of_week'] ?? '');
        $subject_code = trim($_POST['subject_code'] ?? '');
        $grade_section_id = trim($_POST['grade_section_id'] ?? '');
        $faculty_id = trim($_POST['faculty_id'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $school_year = trim($_POST['school_year'] ?? '');

        // Validation
        $errors = [];
        if (!$room) $errors[] = 'Room is required.';
        if (!$official_time) $errors[] = 'Official time is required.';
        if (!$start_time) $errors[] = 'Start time is required.';
        if (!$end_time) $errors[] = 'End time is required.';
        if (!$day_of_week) $errors[] = 'Day of week is required.';
        if (!$subject_code) $errors[] = 'Subject code is required.';
        if (!$grade_section_id) $errors[] = 'Grade section is required.';
        if (!$faculty_id) $errors[] = 'Faculty is required.';
        if (!$semester) $errors[] = 'Semester is required.';
        if (!$school_year) $errors[] = 'School year is required.';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Check for conflicts
        $conflict = $schedule->checkConflict($room, $day_of_week, $start_time, $end_time, $semester, $school_year);
        if ($conflict) {
            echo json_encode(['success' => false, 'message' => 'Schedule conflict detected for this room and time.']);
            exit;
        }

        $data = [
            'room' => $room,
            'official_time' => $official_time,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'day_of_week' => $day_of_week,
            'subject_code' => $subject_code,
            'grade_section_id' => $grade_section_id,
            'faculty_id' => $faculty_id,
            'semester' => $semester,
            'school_year' => $school_year
        ];

        $result = $schedule->create($data);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Schedule created successfully.' : 'Failed to create schedule.'
        ]);
        break;

    // ── UPDATE SCHEDULE ───────────────────────────────────────────────────
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Schedule ID is required.']);
            exit;
        }

        // Validate required fields
        $room = trim($_POST['room'] ?? '');
        $official_time = trim($_POST['official_time'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $day_of_week = trim($_POST['day_of_week'] ?? '');
        $subject_code = trim($_POST['subject_code'] ?? '');
        $grade_section_id = trim($_POST['grade_section_id'] ?? '');
        $faculty_id = trim($_POST['faculty_id'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $school_year = trim($_POST['school_year'] ?? '');

        // Validation
        $errors = [];
        if (!$room) $errors[] = 'Room is required.';
        if (!$official_time) $errors[] = 'Official time is required.';
        if (!$start_time) $errors[] = 'Start time is required.';
        if (!$end_time) $errors[] = 'End time is required.';
        if (!$day_of_week) $errors[] = 'Day of week is required.';
        if (!$subject_code) $errors[] = 'Subject code is required.';
        if (!$grade_section_id) $errors[] = 'Grade section is required.';
        if (!$faculty_id) $errors[] = 'Faculty is required.';
        if (!$semester) $errors[] = 'Semester is required.';
        if (!$school_year) $errors[] = 'School year is required.';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Check for conflicts (excluding current schedule)
        $conflict = $schedule->checkConflict($room, $day_of_week, $start_time, $end_time, $semester, $school_year, $id);
        if ($conflict) {
            echo json_encode(['success' => false, 'message' => 'Schedule conflict detected for this room and time.']);
            exit;
        }

        $data = [
            'room' => $room,
            'official_time' => $official_time,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'day_of_week' => $day_of_week,
            'subject_code' => $subject_code,
            'grade_section_id' => $grade_section_id,
            'faculty_id' => $faculty_id,
            'semester' => $semester,
            'school_year' => $school_year
        ];

        $result = $schedule->update($id, $data);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Schedule updated successfully.' : 'Failed to update schedule.'
        ]);
        break;

    // ── DELETE SCHEDULE ───────────────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Schedule ID is required.']);
            exit;
        }

        // Optional: Check permissions
        // Only admins might delete schedules

        $result = $schedule->delete($id);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Schedule deleted successfully.' : 'Failed to delete schedule.'
        ]);
        break;

    // ── GET BY DATE AND TIME ──────────────────────────────────────────────
    case 'getByDateTime':
        $date = $_GET['date'] ?? null;
        $time_breaker = $_GET['time_breaker'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$date || !$time_breaker || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Date, time, semester, and school year are required.']);
            exit;
        }

        $schedules = $schedule->getByDateTime($date, $time_breaker, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $schedules
        ]);
        break;

    // ── GET BY ID ─────────────────────────────────────────────────────────
    case 'getById':
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Schedule ID is required.']);
            exit;
        }

        $scheduleData = $schedule->getById($id);
        
        if ($scheduleData) {
            echo json_encode([
                'success' => true,
                'data' => $scheduleData
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Schedule not found.'
            ]);
        }
        break;

    // ── GET ALL WITH FILTERS ──────────────────────────────────────────────
    case 'getAll':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;
        $day_of_week = $_GET['day_of_week'] ?? null;
        $room = $_GET['room'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $schedules = $schedule->getAllWithFilters($semester, $school_year, $day_of_week, $room);
        
        echo json_encode([
            'success' => true,
            'data' => $schedules
        ]);
        break;

    // ── GET DISTINCT DAYS ─────────────────────────────────────────────────
    case 'getDistinctDays':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $days = $schedule->getDistinctDays($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $days
        ]);
        break;

    // ── GET DISTINCT ROOMS ────────────────────────────────────────────────
    case 'getDistinctRooms':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $rooms = $schedule->getDistinctRooms($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $rooms
        ]);
        break;

    // ── GET BY FACULTY ────────────────────────────────────────────────────
    case 'getByFaculty':
        $faculty_id = $_GET['faculty_id'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$faculty_id || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Faculty ID, semester, and school year are required.']);
            exit;
        }

        $schedules = $schedule->getByFaculty($faculty_id, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $schedules
        ]);
        break;

    // ── GET BY SECTION ────────────────────────────────────────────────────
    case 'getBySection':
        $section_id = $_GET['section_id'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$section_id || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Section ID, semester, and school year are required.']);
            exit;
        }

        $schedules = $schedule->getBySection($section_id, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $schedules
        ]);
        break;

    // ── GET BY ROOM ───────────────────────────────────────────────────────
    case 'getByRoom':
        $room = $_GET['room'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$room || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Room, semester, and school year are required.']);
            exit;
        }

        $schedules = $schedule->getByRoom($room, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $schedules
        ]);
        break;

    // ── GET STATISTICS ────────────────────────────────────────────────────
    case 'getStatistics':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $stats = $schedule->getStatistics($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        break;

    // ── CHECK CONFLICT ────────────────────────────────────────────────────
    case 'checkConflict':
        $room = $_GET['room'] ?? null;
        $day_of_week = $_GET['day_of_week'] ?? null;
        $start_time = $_GET['start_time'] ?? null;
        $end_time = $_GET['end_time'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;
        $exclude_id = $_GET['exclude_id'] ?? null;

        if (!$room || !$day_of_week || !$start_time || !$end_time || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'All fields are required for conflict check.']);
            exit;
        }

        $conflict = $schedule->checkConflict($room, $day_of_week, $start_time, $end_time, $semester, $school_year, $exclude_id);
        
        echo json_encode([
            'success' => true,
            'has_conflict' => $conflict
        ]);
        break;

    // ── GET WEEKLY SUMMARY ────────────────────────────────────────────────
    case 'getWeeklySummary':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $summary = $schedule->getWeeklySummary($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $summary
        ]);
        break;

    // ── SEARCH ────────────────────────────────────────────────────────────
    case 'search':
        $keyword = $_GET['keyword'] ?? '';
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$keyword || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Keyword, semester, and school year are required.']);
            exit;
        }

        $results = $schedule->search($keyword, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $results
        ]);
        break;

    // ── CHECK EXISTS ──────────────────────────────────────────────────────
    case 'checkExists':
        $room = $_GET['room'] ?? null;
        $day_of_week = $_GET['day_of_week'] ?? null;
        $start_time = $_GET['start_time'] ?? null;
        $subject_code = $_GET['subject_code'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$room || !$day_of_week || !$start_time || !$subject_code || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        $exists = $schedule->scheduleExists($room, $day_of_week, $start_time, $subject_code, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'exists' => $exists
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
?>