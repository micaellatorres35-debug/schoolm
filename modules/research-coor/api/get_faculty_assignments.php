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
    
    $manager = new FacultyManager($conn);
    $assignments = $manager->getFacultyAssignments($_GET['faculty_id']);
    
    echo json_encode(['success' => true, 'assignments' => $assignments]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
