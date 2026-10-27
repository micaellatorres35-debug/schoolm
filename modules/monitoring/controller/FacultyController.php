<?php
ini_set('display_errors', 0);
error_reporting(0);

include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Faculty.php';
include_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

$userClass = new User();
$userInfo  = $userClass->userSession();

if (!$userInfo) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$faculty = new Faculty();

switch ($action) {

    // ── CREATE FACULTY ────────────────────────────────────────────────────
    case 'create':
        // Check permissions (optional)
        // Only admins or authorized personnel can create faculty
        
        $faculty_code = trim($_POST['faculty_code'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');

        // Validation
        $errors = [];
        if (!$faculty_code) $errors[] = 'Faculty code is required.';
        if (!$first_name) $errors[] = 'First name is required.';
        if (!$last_name) $errors[] = 'Last name is required.';
        if (!$email) $errors[] = 'Email is required.';
        
        // Email format validation
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Check if faculty code already exists
        if ($faculty->facultyCodeExists($faculty_code)) {
            echo json_encode(['success' => false, 'message' => 'Faculty code already exists.']);
            exit;
        }

        // Check if email already exists
        if ($faculty->emailExists($email)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists.']);
            exit;
        }

        $result = $faculty->create($faculty_code, $first_name, $last_name, $email, $department);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Faculty member created successfully.' : 'Failed to create faculty member.'
        ]);
        break;

    // ── UPDATE FACULTY ────────────────────────────────────────────────────
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $faculty_code = trim($_POST['faculty_code'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Faculty ID is required.']);
            exit;
        }

        // Validation
        $errors = [];
        if (!$faculty_code) $errors[] = 'Faculty code is required.';
        if (!$first_name) $errors[] = 'First name is required.';
        if (!$last_name) $errors[] = 'Last name is required.';
        if (!$email) $errors[] = 'Email is required.';
        
        // Email format validation
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Check if faculty exists
        $existing = $faculty->getById($id);
        if (!$existing) {
            echo json_encode(['success' => false, 'message' => 'Faculty member not found.']);
            exit;
        }

        // Check if faculty code already exists (excluding current)
        if ($faculty->facultyCodeExists($faculty_code, $id)) {
            echo json_encode(['success' => false, 'message' => 'Faculty code already exists.']);
            exit;
        }

        // Check if email already exists (excluding current)
        if ($faculty->emailExists($email, $id)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists.']);
            exit;
        }

        $result = $faculty->update($id, $faculty_code, $first_name, $last_name, $email, $department);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Faculty member updated successfully.' : 'Failed to update faculty member.'
        ]);
        break;

    // ── DELETE FACULTY ────────────────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Faculty ID is required.']);
            exit;
        }

        // Check if faculty exists
        $existing = $faculty->getById($id);
        if (!$existing) {
            echo json_encode(['success' => false, 'message' => 'Faculty member not found.']);
            exit;
        }

        $result = $faculty->delete($id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Faculty member deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot delete faculty member because they have existing schedule or attendance records.']);
        }
        break;

    // ── GET ALL FACULTY ───────────────────────────────────────────────────
    case 'getAll':
        $facultyList = $faculty->getAll();
        
        echo json_encode([
            'success' => true,
            'data' => $facultyList
        ]);
        break;

    // ── GET BY ID ─────────────────────────────────────────────────────────
    case 'getById':
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Faculty ID is required.']);
            exit;
        }

        $facultyData = $faculty->getById($id);
        
        if ($facultyData) {
            echo json_encode([
                'success' => true,
                'data' => $facultyData
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Faculty member not found.'
            ]);
        }
        break;

    // ── GET BY CODE ───────────────────────────────────────────────────────
    case 'getByCode':
        $code = $_GET['code'] ?? '';

        if (!$code) {
            echo json_encode(['success' => false, 'message' => 'Faculty code is required.']);
            exit;
        }

        $facultyData = $faculty->getByCode($code);
        
        if ($facultyData) {
            echo json_encode([
                'success' => true,
                'data' => $facultyData
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Faculty member not found.'
            ]);
        }
        break;

    // ── GET BY DEPARTMENT ─────────────────────────────────────────────────
    case 'getByDepartment':
        $department = $_GET['department'] ?? '';

        if (!$department) {
            echo json_encode(['success' => false, 'message' => 'Department is required.']);
            exit;
        }

        $facultyList = $faculty->getByDepartment($department);
        
        echo json_encode([
            'success' => true,
            'data' => $facultyList
        ]);
        break;

    // ── SEARCH FACULTY ────────────────────────────────────────────────────
    case 'search':
        $keyword = $_GET['keyword'] ?? '';

        if (!$keyword) {
            echo json_encode(['success' => false, 'message' => 'Search keyword is required.']);
            exit;
        }

        $results = $faculty->search($keyword);
        
        echo json_encode([
            'success' => true,
            'data' => $results
        ]);
        break;

    // ── GET STATISTICS ────────────────────────────────────────────────────
    case 'getStatistics':
        $stats = $faculty->getStatistics();
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        break;

    // ── GET WITH SCHEDULE COUNT ───────────────────────────────────────────
    case 'getWithScheduleCount':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $facultyList = $faculty->getWithScheduleCount($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $facultyList
        ]);
        break;

    // ── GET WITH ATTENDANCE SUMMARY ───────────────────────────────────────
    case 'getWithAttendanceSummary':
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        if (!$start_date || !$end_date) {
            echo json_encode(['success' => false, 'message' => 'Start date and end date are required.']);
            exit;
        }

        $facultyList = $faculty->getWithAttendanceSummary($start_date, $end_date);
        
        echo json_encode([
            'success' => true,
            'data' => $facultyList
        ]);
        break;

    // ── GET BY NAME ───────────────────────────────────────────────────────
    case 'getByName':
        $name = $_GET['name'] ?? '';

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Name is required.']);
            exit;
        }

        $facultyList = $faculty->getByName($name);
        
        echo json_encode([
            'success' => true,
            'data' => $facultyList
        ]);
        break;

    // ── GET UNASSIGNED FACULTY ────────────────────────────────────────────
    case 'getUnassigned':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $facultyList = $faculty->getUnassignedFaculty($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $facultyList
        ]);
        break;

    // ── CHECK FACULTY CODE EXISTS ─────────────────────────────────────────
    case 'checkCodeExists':
        $faculty_code = $_GET['faculty_code'] ?? '';
        $exclude_id = $_GET['exclude_id'] ?? null;

        if (!$faculty_code) {
            echo json_encode(['success' => false, 'message' => 'Faculty code is required.']);
            exit;
        }

        $exists = $faculty->facultyCodeExists($faculty_code, $exclude_id);
        
        echo json_encode([
            'success' => true,
            'exists' => $exists
        ]);
        break;

    // ── CHECK EMAIL EXISTS ────────────────────────────────────────────────
    case 'checkEmailExists':
        $email = $_GET['email'] ?? '';
        $exclude_id = $_GET['exclude_id'] ?? null;

        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Email is required.']);
            exit;
        }

        $exists = $faculty->emailExists($email, $exclude_id);
        
        echo json_encode([
            'success' => true,
            'exists' => $exists
        ]);
        break;

    // ── BULK INSERT ───────────────────────────────────────────────────────
    case 'bulkInsert':
        // This would typically receive JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        $faculty_list = $input['faculty'] ?? [];

        if (empty($faculty_list)) {
            echo json_encode(['success' => false, 'message' => 'No faculty data provided.']);
            exit;
        }

        $result = $faculty->bulkInsert($faculty_list);
        
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
?>