<?php
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $sql = "SELECT id, name FROM rgr_school_years WHERE is_active = 1 ORDER BY name DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $schoolYears = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'school_years' => $schoolYears]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>