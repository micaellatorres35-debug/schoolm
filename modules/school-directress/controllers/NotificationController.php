
<?php
ob_start();

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

function notificationRespond(array $data, int $status = 200): void
{
    http_response_code($status);

    if (ob_get_level() > 0) {
        ob_clean();
    }

    echo json_encode($data);
    exit;
}

try {
    // Require the existing authenticated session.
    require_once __DIR__ . '/../../../auth/session.php';

    // Project root: modules/school-directress/controllers/../../../
    require_once __DIR__ . '/../../../database/db.php';

    if (session_status() === PHP_SESSION_ACTIVE) {
        // The existing session.php already validates the login.
    }

    $db = new Database();
    $conn = $db->getConnection();

    // Resolve the account ID using the authenticated employee session.
    $stmt = $conn->prepare(
        'SELECT user_id
         FROM user_account
         WHERE employee_id = ?
         LIMIT 1'
    );
    $stmt->execute([(int) $_SESSION['employee_id']]);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        notificationRespond([
            'success' => false,
            'message' => 'No user account is associated with this employee.'
        ], 403);
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $action = $_GET['action'] ?? '';

        if ($action !== 'list') {
            notificationRespond([
                'success' => false,
                'message' => 'Invalid action.'
            ], 400);
        }

        $stmt = $conn->prepare(
            'SELECT notification_id, title, message, notification_type,
                    module, record_id, is_read, created_at
             FROM sd_notifications
             WHERE user_id = ?
             ORDER BY created_at DESC, notification_id DESC
             LIMIT 20'
        );
        $stmt->execute([(int) $userId]);
        $notifications = $stmt->fetchAll();

        $stmt = $conn->prepare(
            'SELECT COUNT(*)
             FROM sd_notifications
             WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([(int) $userId]);
        $unreadCount = (int) $stmt->fetchColumn();

        notificationRespond([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            notificationRespond([
                'success' => false,
                'message' => 'Invalid JSON request.'
            ], 400);
        }

        $action = $input['action'] ?? '';

        if ($action === 'mark_all_read') {
            $stmt = $conn->prepare(
                'UPDATE sd_notifications
                 SET is_read = 1, read_at = NOW()
                 WHERE user_id = ? AND is_read = 0'
            );
            $stmt->execute([(int) $userId]);

            notificationRespond([
                'success' => true,
                'message' => 'Notifications marked as read.'
            ]);
        }

        notificationRespond([
            'success' => false,
            'message' => 'Invalid action.'
        ], 400);
    }

    header('Allow: GET, POST');

    notificationRespond([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log('NotificationController error: ' . $e->getMessage());

    notificationRespond([
        'success' => false,
        'message' => 'An internal error occurred while processing notifications.'
    ], 500);
}
