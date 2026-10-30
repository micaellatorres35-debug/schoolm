<?php
require_once('../classes/FacultyProfileManager.php');
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

    $manager = new FacultyProfileManager($conn);
    $facultyId = (int) $_GET['faculty_id'];
    $profile = $manager->getFacultyProfileWithDocuments($facultyId);

    if (!$profile) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Faculty not found']);
        exit;
    }

    echo json_encode(['success' => true, 'profile' => $profile]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
