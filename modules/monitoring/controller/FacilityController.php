<?php
ini_set('display_errors', 0);
error_reporting(0);

include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Facility.php';
include_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

$userClass = new User();
$userInfo  = $userClass->userSession();

if (!$userInfo) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$facility = new Facility();

switch ($action) {

    // ── REPORT ISSUE ───────────────────────────────────────────────────
    case 'report':
        $room = trim($_POST['room'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = trim($_POST['priority'] ?? 'Medium');
        $reported_by = $_SESSION['employee_id'] ?? null;

        // Validation
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Room is required.']);
            exit;
        }
        if (!$type) {
            echo json_encode(['success' => false, 'message' => 'Issue type is required.']);
            exit;
        }
        if (!$description) {
            echo json_encode(['success' => false, 'message' => 'Description is required.']);
            exit;
        }
        if (!in_array($priority, ['High', 'Medium', 'Low'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid priority level.']);
            exit;
        }
        if (!$reported_by) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
            exit;
        }

        $result = $facility->insert($room, $type, $description, $priority, $reported_by);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Issue reported successfully.' : 'Failed to report issue. Please try again.'
        ]);
        break;

    // ── MARK AS FIXED ───────────────────────────────────────────────────
    case 'markFixed':
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid issue ID.']);
            exit;
        }

        // Optional: Check if user has permission to mark as fixed
        // You can add role-based checking here

        $result = $facility->markFixed($id);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Issue marked as fixed.' : 'Failed to update issue status.'
        ]);
        break;

    // ── GET ALL ISSUES ───────────────────────────────────────────────────
    case 'getAll':
        $issues = $facility->getAll();
        
        echo json_encode([
            'success' => true,
            'data' => $issues
        ]);
        break;

    // ── GET SINGLE ISSUE ─────────────────────────────────────────────────
    case 'getOne':
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Issue ID is required.']);
            exit;
        }

        $issue = $facility->getOne($id);
        
        if ($issue) {
            echo json_encode([
                'success' => true,
                'data' => $issue
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Issue not found.'
            ]);
        }
        break;

    // ── UPDATE ISSUE ─────────────────────────────────────────────────────
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $room = trim($_POST['room'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = trim($_POST['priority'] ?? 'Medium');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Issue ID is required.']);
            exit;
        }
        if (!$room || !$type || !$description) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }
        if (!in_array($priority, ['High', 'Medium', 'Low'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid priority level.']);
            exit;
        }

        $result = $facility->update($id, $room, $type, $description, $priority);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Issue updated successfully.' : 'Failed to update issue.'
        ]);
        break;

    // ── DELETE ISSUE ─────────────────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Issue ID is required.']);
            exit;
        }

        // Optional: Add permission check here
        // Only admins or the original reporter might delete

        $result = $facility->delete($id);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Issue deleted successfully.' : 'Failed to delete issue.'
        ]);
        break;

    // ── GET BY STATUS ────────────────────────────────────────────────────
    case 'getByStatus':
        $status = $_GET['status'] ?? 'Pending';
        
        if (!in_array($status, ['Pending', 'Fixed'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit;
        }

        $issues = $facility->getByStatus($status);
        
        echo json_encode([
            'success' => true,
            'data' => $issues
        ]);
        break;

    // ── GET BY PRIORITY ──────────────────────────────────────────────────
    case 'getByPriority':
        $priority = $_GET['priority'] ?? 'High';
        
        if (!in_array($priority, ['High', 'Medium', 'Low'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid priority.']);
            exit;
        }

        $issues = $facility->getByPriority($priority);
        
        echo json_encode([
            'success' => true,
            'data' => $issues
        ]);
        break;

    // ── GET BY ROOM ──────────────────────────────────────────────────────
    case 'getByRoom':
        $room = $_GET['room'] ?? '';

        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Room is required.']);
            exit;
        }

        $issues = $facility->getByRoom($room);
        
        echo json_encode([
            'success' => true,
            'data' => $issues
        ]);
        break;

    // ── GET STATISTICS ───────────────────────────────────────────────────
    case 'getStats':
        $stats = $facility->getStats();
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
?>