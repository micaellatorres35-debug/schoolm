<?php
require_once('../classes/FacultyManager.php');
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['faculty_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faculty ID is required']);
        exit;
    }

    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $facultyId = (int)$_GET['faculty_id'];
    $sql = "SELECT 
            sf.section_id,
            s.section_code,
            s.program_id,
            s.grade_level,
            s.school_year_id,
            s.semester_id,
            sy.name AS school_year,
            sem.name AS semester,
            c.code AS program_code
        FROM cc_section_faculty sf
        JOIN cc_sections s ON sf.section_id = s.id
        JOIN rgr_semesters sem ON s.semester_id = sem.id
        JOIN rgr_school_years sy ON s.school_year_id = sy.id
        LEFT JOIN rgr_courses c ON s.program_id = c.id
        WHERE sf.faculty_id = :faculty_id
          AND sf.role = 'Instructor'
          AND (sf.status = 'Active' OR sf.status IS NULL)
        ORDER BY s.section_code";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':faculty_id', $facultyId, PDO::PARAM_INT);
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'sections' => $sections]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>