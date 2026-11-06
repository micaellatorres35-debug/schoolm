<?php
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['section_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing section_id parameter']);
        exit;
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $sectionId = $_GET['section_id'];
    
    $sectionQuery = "SELECT id FROM gc_sections WHERE id = :section_id";
    $sectionStmt = $conn->prepare($sectionQuery);
    $sectionStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
    $sectionStmt->execute();
    $section = $sectionStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$section) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Section not found']);
        exit;
    }
    
    // Include every active GenEd subject, not only subjects
    // mapped to the selected section's curriculum, year, and semester.
    $sql = "SELECT
        s.id,
        s.code,
        s.name,
        s.units,
        s.lecture_hours,
        s.lab_hours
    FROM rgr_subjects s
    WHERE s.archived_at IS NULL
      AND UPPER(s.code) REGEXP '^(GE.*|FIL [1-3]|NSTP [1-2]|PE [1-2])$'
    ORDER BY s.code";

    $stmt = $conn->query($sql);

    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$subjects || count($subjects) === 0) {
        // No subjects found for the active curriculum — return success with message
        echo json_encode([
            'success' => true,
            'subjects' => [],
            'message' => 'No subjects are available for the active curriculum.'
        ]);
        exit;
    }

    echo json_encode(['success' => true, 'subjects' => $subjects]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
