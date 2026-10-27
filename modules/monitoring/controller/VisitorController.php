<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // OFF — we return JSON errors instead

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Always respond with JSON
header('Content-Type: application/json');

// ── Include required files ────────────────────────────────────────────────────
$sessionFile = __DIR__ . '/../../../auth/session.php';
$visitorFile = __DIR__ . '/../classes/Visitor.php';
$userFile    = __DIR__ . '/../classes/User.php';

if (!file_exists($sessionFile)) {
    echo json_encode(['success' => false, 'message' => 'Session file not found']);
    exit;
}
if (!file_exists($visitorFile)) {
    echo json_encode(['success' => false, 'message' => 'Visitor class file not found']);
    exit;
}
if (!file_exists($userFile)) {
    echo json_encode(['success' => false, 'message' => 'User class file not found']);
    exit;
}

include_once $sessionFile;
include_once $visitorFile;
include_once $userFile;

// ── Bootstrap ─────────────────────────────────────────────────────────────────
try {
    $userClass = new User();
    $userInfo  = $userClass->userSession();

    // Resolve recorded_by
    $recorded_by = $_SESSION['employee_id'] ?? 'SYSTEM';
    if ($recorded_by === 'SYSTEM' && $userInfo) {
        $recorded_by = $userInfo['employee_id'] ?? ($userInfo['id'] ?? 'SYSTEM');
    }

    $action = trim($_POST['action'] ?? $_GET['action'] ?? '');

    if (!$action) {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit;
    }

    // FIXED: renamed $visitor → $visitorObj to avoid any future collision
    $visitorObj = new Visitor();

    switch ($action) {

        // ── TIME IN ───────────────────────────────────────────────────────────
        case 'timeIn':
            $name         = trim($_POST['name']         ?? '');
            $purpose      = trim($_POST['purpose']      ?? '');
            $person       = trim($_POST['person']       ?? '');
            $department   = trim($_POST['department']   ?? '');
            $contact      = trim($_POST['contact']      ?? '');
            $id_presented = trim($_POST['id_presented'] ?? '');

            $errors = [];
            if (!$name)         $errors[] = 'Visitor name is required.';
            if (!$purpose)      $errors[] = 'Purpose of visit is required.';
            if (!$person)       $errors[] = 'Person to visit is required.';
            if (!$department)   $errors[] = 'Department is required.';
            if (!$contact)      $errors[] = 'Contact number is required.';
            if (!$id_presented) $errors[] = 'ID presented is required.';

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                exit;
            }

            if (!preg_match('/^[0-9+\-\s]+$/', $contact)) {
                echo json_encode(['success' => false, 'message' => 'Invalid contact number format.']);
                exit;
            }

            try {
                $visitorObj->insert($name, $purpose, $person, $department, $contact, $id_presented, $recorded_by);
                // FIXED: getLastInsertId() now exists in the class
                echo json_encode([
                    'success' => true,
                    'message' => 'Visitor time-in recorded successfully.',
                    'data'    => ['id' => $visitorObj->getLastInsertId()]
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to record time-in: ' . $e->getMessage()]);
            }
            break;

        // ── TIME OUT ──────────────────────────────────────────────────────────
        case 'timeOut':
            $id = intval($_POST['id'] ?? 0);

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Visitor ID is required.']);
                exit;
            }

            try {
                if (!$visitorObj->exists($id)) {
                    echo json_encode(['success' => false, 'message' => 'Visitor not found.']);
                    exit;
                }
                if (!$visitorObj->isActive($id)) {
                    echo json_encode(['success' => false, 'message' => 'Visitor already timed out.']);
                    exit;
                }

                $visitorObj->timeout($id);
                echo json_encode(['success' => true, 'message' => 'Visitor timed out successfully.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to record time-out: ' . $e->getMessage()]);
            }
            break;

        // ── GET ALL VISITORS ──────────────────────────────────────────────────
        case 'getAll':
            try {
                $visitors = $visitorObj->getAll();
                echo json_encode(['success' => true, 'data' => $visitors, 'count' => count($visitors)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error loading visitors: ' . $e->getMessage()]);
            }
            break;

        // ── GET BY ID ─────────────────────────────────────────────────────────
        case 'getById':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Visitor ID is required.']);
                exit;
            }
            try {
                $row = $visitorObj->getById($id);
                if ($row) {
                    echo json_encode(['success' => true, 'data' => $row]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Visitor not found.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error loading visitor: ' . $e->getMessage()]);
            }
            break;

        // ── GET ACTIVE VISITORS ───────────────────────────────────────────────
        case 'getActive':
            try {
                $visitors = $visitorObj->getActiveVisitors();
                echo json_encode(['success' => true, 'data' => $visitors, 'count' => count($visitors)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error loading active visitors: ' . $e->getMessage()]);
            }
            break;

        // ── GET TODAY'S VISITORS ──────────────────────────────────────────────
        case 'getToday':
            try {
                $visitors = $visitorObj->getTodayVisitors();
                echo json_encode(['success' => true, 'data' => $visitors, 'count' => count($visitors)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => "Error loading today's visitors: " . $e->getMessage()]);
            }
            break;

        // ── GET BY DATE RANGE ─────────────────────────────────────────────────
        case 'getByDateRange':
            $start_date = trim($_GET['start_date'] ?? '');
            $end_date   = trim($_GET['end_date']   ?? '');

            if (!$start_date || !$end_date) {
                echo json_encode(['success' => false, 'message' => 'Start date and end date are required.']);
                exit;
            }
            try {
                $visitors = $visitorObj->getByDateRange($start_date, $end_date);
                echo json_encode(['success' => true, 'data' => $visitors, 'count' => count($visitors)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error loading visitors by date range: ' . $e->getMessage()]);
            }
            break;

        // ── GET BY DEPARTMENT ─────────────────────────────────────────────────
        case 'getByDepartment':
            $department = trim($_GET['department'] ?? '');
            if (!$department) {
                echo json_encode(['success' => false, 'message' => 'Department is required.']);
                exit;
            }
            try {
                $visitors = $visitorObj->getByDepartment($department);
                echo json_encode(['success' => true, 'data' => $visitors, 'count' => count($visitors)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error loading visitors by department: ' . $e->getMessage()]);
            }
            break;

        // ── SEARCH VISITORS ───────────────────────────────────────────────────
        case 'search':
            $keyword = trim($_GET['keyword'] ?? '');
            if (!$keyword) {
                echo json_encode(['success' => false, 'message' => 'Search keyword is required.']);
                exit;
            }
            try {
                $results = $visitorObj->search($keyword);
                echo json_encode(['success' => true, 'data' => $results, 'count' => count($results)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error searching visitors: ' . $e->getMessage()]);
            }
            break;

        // ── GET STATISTICS ────────────────────────────────────────────────────
        case 'getStats':
            $start_date = trim($_GET['start_date'] ?? '');
            $end_date   = trim($_GET['end_date']   ?? '');
            try {
                $stats = $visitorObj->getStats($start_date ?: null, $end_date ?: null);
                echo json_encode(['success' => true, 'data' => $stats]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error getting statistics: ' . $e->getMessage()]);
            }
            break;

        // ── UPDATE VISITOR ────────────────────────────────────────────────────
        case 'update':
            $id           = intval($_POST['id']           ?? 0);
            $name         = trim($_POST['name']           ?? '');
            $purpose      = trim($_POST['purpose']        ?? '');
            $person       = trim($_POST['person']         ?? '');
            $department   = trim($_POST['department']     ?? '');
            $contact      = trim($_POST['contact']        ?? '');
            $id_presented = trim($_POST['id_presented']   ?? '');

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Visitor ID is required.']);
                exit;
            }

            $errors = [];
            if (!$name)         $errors[] = 'Visitor name is required.';
            if (!$purpose)      $errors[] = 'Purpose of visit is required.';
            if (!$person)       $errors[] = 'Person to visit is required.';
            if (!$department)   $errors[] = 'Department is required.';
            if (!$contact)      $errors[] = 'Contact number is required.';
            if (!$id_presented) $errors[] = 'ID presented is required.';

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                exit;
            }

            try {
                $visitorObj->update($id, $name, $purpose, $person, $department, $contact, $id_presented);
                echo json_encode(['success' => true, 'message' => 'Visitor information updated successfully.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to update visitor: ' . $e->getMessage()]);
            }
            break;

        // ── DELETE VISITOR ────────────────────────────────────────────────────
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Visitor ID is required.']);
                exit;
            }
            try {
                $visitorObj->delete($id);
                echo json_encode(['success' => true, 'message' => 'Visitor record deleted successfully.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to delete visitor: ' . $e->getMessage()]);
            }
            break;

        // ── BULK TIMEOUT ──────────────────────────────────────────────────────
        case 'bulkTimeout':
            $department = trim($_POST['department'] ?? '') ?: null;
            try {
                $count = $visitorObj->bulkTimeout($department);
                echo json_encode([
                    'success' => true,
                    'message' => "Bulk time-out completed. {$count} visitor(s) updated."
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to perform bulk timeout: ' . $e->getMessage()]);
            }
            break;

        // ── TEST CONNECTION ───────────────────────────────────────────────────
        case 'test':
            try {
                $visitorObj->testConnection();
                echo json_encode(['success' => true, 'message' => 'Database connection successful.', 'data' => ['connection' => 'OK']]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            }
            break;

        // ── UNKNOWN ACTION ────────────────────────────────────────────────────
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action: ' . htmlspecialchars($action)]);
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>