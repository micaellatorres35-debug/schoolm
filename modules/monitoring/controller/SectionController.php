<?php
ini_set('display_errors', 0);
error_reporting(0);

include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Section.php';
include_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

$userClass = new User();
$userInfo  = $userClass->userSession();

if (!$userInfo) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$section = new Section();

switch ($action) {

    // ── CREATE SECTION ────────────────────────────────────────────────────
    case 'create':
        // Check permissions (optional)
        // Only admins or authorized personnel can create sections
        
        $section_code = trim($_POST['section_code'] ?? '');
        $grade_level = trim($_POST['grade_level'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $school_year = trim($_POST['school_year'] ?? '');

        // Validation
        $errors = [];
        if (!$section_code) $errors[] = 'Section code is required.';
        if (!$grade_level) $errors[] = 'Grade level is required.';
        if (!$program) $errors[] = 'Program is required.';
        if (!$semester) $errors[] = 'Semester is required.';
        if (!$school_year) $errors[] = 'School year is required.';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Check if section already exists
        if ($section->sectionExists($section_code, $semester, $school_year)) {
            echo json_encode(['success' => false, 'message' => 'Section code already exists for this semester and school year.']);
            exit;
        }

        $result = $section->create($section_code, $grade_level, $program, $semester, $school_year);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Section created successfully.' : 'Failed to create section.'
        ]);
        break;

    // ── UPDATE SECTION ────────────────────────────────────────────────────
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $section_code = trim($_POST['section_code'] ?? '');
        $grade_level = trim($_POST['grade_level'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $school_year = trim($_POST['school_year'] ?? '');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Section ID is required.']);
            exit;
        }

        // Validation
        $errors = [];
        if (!$section_code) $errors[] = 'Section code is required.';
        if (!$grade_level) $errors[] = 'Grade level is required.';
        if (!$program) $errors[] = 'Program is required.';
        if (!$semester) $errors[] = 'Semester is required.';
        if (!$school_year) $errors[] = 'School year is required.';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Check if section exists (excluding current)
        if ($section->sectionExists($section_code, $semester, $school_year, $id)) {
            echo json_encode(['success' => false, 'message' => 'Section code already exists for this semester and school year.']);
            exit;
        }

        $result = $section->update($id, $section_code, $grade_level, $program, $semester, $school_year);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Section updated successfully.' : 'Failed to update section.'
        ]);
        break;

    // ── DELETE SECTION ────────────────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Section ID is required.']);
            exit;
        }

        // Check if section exists
        $existing = $section->getById($id);
        if (!$existing) {
            echo json_encode(['success' => false, 'message' => 'Section not found.']);
            exit;
        }

        $result = $section->delete($id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Section deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot delete section because it is being used in schedules.']);
        }
        break;

    // ── GET ALL SECTIONS ──────────────────────────────────────────────────
    case 'getAll':
        $sections = $section->getAll();
        
        echo json_encode([
            'success' => true,
            'data' => $sections
        ]);
        break;

    // ── GET BY ID ─────────────────────────────────────────────────────────
    case 'getById':
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Section ID is required.']);
            exit;
        }

        $sectionData = $section->getById($id);
        
        if ($sectionData) {
            echo json_encode([
                'success' => true,
                'data' => $sectionData
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Section not found.'
            ]);
        }
        break;

    // ── GET BY SEMESTER ───────────────────────────────────────────────────
    case 'getBySemester':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $sections = $section->getBySemester($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $sections
        ]);
        break;

    // ── GET BY GRADE LEVEL ────────────────────────────────────────────────
    case 'getByGradeLevel':
        $grade_level = $_GET['grade_level'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$grade_level || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Grade level, semester, and school year are required.']);
            exit;
        }

        $sections = $section->getByGradeLevel($grade_level, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $sections
        ]);
        break;

    // ── GET BY PROGRAM ────────────────────────────────────────────────────
    case 'getByProgram':
        $program = $_GET['program'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$program || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Program, semester, and school year are required.']);
            exit;
        }

        $sections = $section->getByProgram($program, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $sections
        ]);
        break;

    // ── GET DISTINCT GRADE LEVELS ─────────────────────────────────────────
    case 'getDistinctGradeLevels':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $gradeLevels = $section->getDistinctGradeLevels($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $gradeLevels
        ]);
        break;

    // ── GET DISTINCT PROGRAMS ─────────────────────────────────────────────
    case 'getDistinctPrograms':
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $programs = $section->getDistinctPrograms($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $programs
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

        $stats = $section->getStatistics($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        break;

    // ── SEARCH SECTIONS ───────────────────────────────────────────────────
    case 'search':
        $keyword = $_GET['keyword'] ?? '';
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;

        if (!$keyword) {
            echo json_encode(['success' => false, 'message' => 'Search keyword is required.']);
            exit;
        }

        if (!$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Semester and school year are required.']);
            exit;
        }

        $results = $section->search($keyword, $semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $results
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

        $sections = $section->getWithScheduleCount($semester, $school_year);
        
        echo json_encode([
            'success' => true,
            'data' => $sections
        ]);
        break;

    // ── CHECK SECTION EXISTS ──────────────────────────────────────────────
    case 'checkExists':
        $section_code = $_GET['section_code'] ?? null;
        $semester = $_GET['semester'] ?? null;
        $school_year = $_GET['school_year'] ?? null;
        $exclude_id = $_GET['exclude_id'] ?? null;

        if (!$section_code || !$semester || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Section code, semester, and school year are required.']);
            exit;
        }

        $exists = $section->sectionExists($section_code, $semester, $school_year, $exclude_id);
        
        echo json_encode([
            'success' => true,
            'exists' => $exists
        ]);
        break;

    // ── BULK INSERT ───────────────────────────────────────────────────────
    case 'bulkInsert':
        // This would typically receive JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        $sections = $input['sections'] ?? [];

        if (empty($sections)) {
            echo json_encode(['success' => false, 'message' => 'No sections data provided.']);
            exit;
        }

        $result = $section->bulkInsert($sections);
        
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
?>