<?php
require_once(__DIR__ . '/../../../database/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $section_code = trim((string)($_POST['section_code'] ?? ''));
    $grade_level = trim((string)($_POST['grade_level'] ?? ''));
    $program_id = isset($_POST['program_id']) ? (int)$_POST['program_id'] : 0;
    $school_year_id = isset($_POST['school_year_id']) ? (int)$_POST['school_year_id'] : 0;
    $semester_id = isset($_POST['semester_id']) ? (int)$_POST['semester_id'] : 0;

    if ($section_code === '' || $grade_level === '' || $program_id <= 0 || $school_year_id <= 0 || $semester_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $programStmt = $conn->prepare('SELECT id FROM rgr_courses WHERE id = :program_id LIMIT 1');
    $programStmt->execute([':program_id' => $program_id]);
    $programRow = $programStmt->fetch(PDO::FETCH_ASSOC);

    if (!$programRow) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid program']);
        exit;
    }

    $semesterStmt = $conn->prepare('SELECT id FROM rgr_semesters WHERE id = :semester_id AND school_year_id = :school_year_id LIMIT 1');
    $semesterStmt->execute([
        ':semester_id' => $semester_id,
        ':school_year_id' => $school_year_id,
    ]);
    $semesterRow = $semesterStmt->fetch(PDO::FETCH_ASSOC);

    if (!$semesterRow) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Selected semester does not belong to the selected school year']);
        exit;
    }

    $duplicateStmt = $conn->prepare('SELECT id FROM cc_sections WHERE section_code = :section_code AND school_year_id = :school_year_id AND semester_id = :semester_id LIMIT 1');
    $duplicateStmt->execute([
        ':section_code' => $section_code,
        ':school_year_id' => $school_year_id,
        ':semester_id' => $semester_id,
    ]);

    if ($duplicateStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Section code already exists for the selected school year and semester']);
        exit;
    }

    $insertStmt = $conn->prepare('INSERT INTO cc_sections (section_code, grade_level, program_id, school_year_id, semester_id, adviser_id, created_at) VALUES (:section_code, :grade_level, :program_id, :school_year_id, :semester_id, NULL, NOW())');
    $insertStmt->execute([
        ':section_code' => $section_code,
        ':grade_level' => $grade_level,
        ':program_id' => $program_id,
        ':school_year_id' => $school_year_id,
        ':semester_id' => $semester_id,
    ]);

    echo json_encode(['success' => true, 'message' => 'Section added successfully']);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the section']);
}
?>
