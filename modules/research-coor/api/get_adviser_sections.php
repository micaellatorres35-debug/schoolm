<?php
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['school_year_id']) || !isset($_GET['semester_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $schoolYearId = (int)$_GET['school_year_id'];
    $semesterId = (int)$_GET['semester_id'];
    $facultyId = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : null;
    
    $sql = "SELECT 
        sec.id,
        sec.section_code,
        sec.grade_level,
        sec.program_id,
        c.code AS program_code,
        c.name AS program_name
    FROM cc_sections sec
    LEFT JOIN rgr_courses c ON sec.program_id = c.id
    WHERE sec.school_year_id = :school_year_id
    AND sec.semester_id = :semester_id";

    if ($facultyId) {
        $sql .= " AND NOT EXISTS (
            SELECT 1 FROM cc_section_faculty sf
            WHERE sf.section_id = sec.id
            AND sf.faculty_id = :faculty_id
            AND sf.role = 'Instructor'
            AND (sf.status = 'Active' OR sf.status IS NULL)
            AND sf.school_year_id = :school_year_id
            AND sf.semester_id = :semester_id
        )";
    }

    $sql .= " ORDER BY sec.section_code";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
    $stmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
    if ($facultyId) {
        $stmt->bindParam(':faculty_id', $facultyId, PDO::PARAM_INT);
    }
    $stmt->execute();
    
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'sections' => $sections]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
