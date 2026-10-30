<?php
/**
 * Get Event Templates API
 * Returns rows from cc_event_templates
 */
require_once dirname(dirname(dirname(__DIR__))) . '/database/db.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Ensure table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'cc_event_templates'");
    $stmt->execute();
    $exists = (bool) $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exists) {
        echo json_encode(['success' => true, 'templates' => []]);
        exit;
    }

    $q = "SELECT * FROM cc_event_templates ORDER BY template_name ASC";
    $s = $conn->prepare($q);
    $s->execute();
    $templates = $s->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'templates' => $templates]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>