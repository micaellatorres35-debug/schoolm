<?php
require_once('../classes/FacultyManager.php');
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['faculty_id']) || !isset($data['assignments'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    $manager = new FacultyManager($conn);
    $result = $manager->saveAssignments(
        $data['faculty_id'],
        $data['assignments']
    );
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
