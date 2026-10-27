<?php
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/classes/DashboardOverview.php';
require_once dirname(dirname(__DIR__)) . '/database/db.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    $dashboard = new DashboardOverview($conn);

    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => $dashboard->getSummary(),
            'alerts' => $dashboard->getAlertItems(),
            'upcoming_activities' => $dashboard->getUpcomingActivities(),
            'recent_activities' => $dashboard->getRecentActivities(8),
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load dashboard alerts.',
    ]);
}
