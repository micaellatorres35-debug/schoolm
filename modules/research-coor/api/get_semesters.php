<?php
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['school_year_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing school_year_id parameter']);
        exit;
    }

    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $schoolYearId = $_GET['school_year_id'];

    $sql = "SELECT id, name FROM rgr_semesters WHERE school_year_id = :school_year_id AND is_active = 1 ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
    $stmt->execute();
    $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'semesters' => $semesters]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>