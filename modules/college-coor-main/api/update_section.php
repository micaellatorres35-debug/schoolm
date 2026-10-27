<?php
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $section_id = $_POST['section_id'] ?? 0;
    $section_code = $_POST['section_code'] ?? '';
    $program = $_POST['program'] ?? '';
    $year_level = $_POST['year_level'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $school_year_id = $_POST['school_year_id'] ?? 0;
    $semester_id = $_POST['semester_id'] ?? 0;

    if (!$section_id || !$section_code || !$program || !$year_level || !$capacity || !$school_year_id || !$semester_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Get program ID from program code
    $programStmt = $conn->prepare("SELECT id FROM rgr_courses WHERE code = ?");
    $programStmt->execute([$program]);
    $programRow = $programStmt->fetch(PDO::FETCH_ASSOC);

    if (!$programRow) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid program code']);
        exit;
    }

    $program_id = $programRow['id'];

    $sql = "UPDATE cc_sections 
            SET section_code = :section_code, 
                program_id = :program_id, 
                grade_level = :grade_level, 
                capacity = :capacity, 
                school_year_id = :school_year_id, 
                semester_id = :semester_id, 
                updated_at = NOW()
            WHERE id = :section_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
    $stmt->bindParam(':section_code', $section_code);
    $stmt->bindParam(':program_id', $program_id, PDO::PARAM_INT);
    $stmt->bindParam(':grade_level', $year_level);
    $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
    $stmt->bindParam(':school_year_id', $school_year_id, PDO::PARAM_INT);
    $stmt->bindParam(':semester_id', $semester_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Section updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
