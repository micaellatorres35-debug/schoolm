<?php
ini_set('display_errors', 0);
error_reporting(0);

include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Attendance.php';

header('Content-Type: application/json');

$userClass = new User(); // Assuming you have a User class
$userInfo  = $userClass->userSession();

if (!$userInfo) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$attendance = new Attendance();

switch ($action) {

    // ── MARK ATTENDANCE ───────────────────────────────────────────────────
    case 'mark':
        // Validate required fields
        $schedule_id = $_POST['schedule_id'] ?? null;
        $attendance_date = $_POST['attendance_date'] ?? null;
        $faculty_id = $_POST['faculty_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $recorded_by = $_SESSION['employee_id'] ?? null;

        if (!$schedule_id || !$attendance_date || !$faculty_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Schedule ID, date, faculty ID, and status are required.']);
            exit;
        }
        if (!$recorded_by) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
            exit;
        }

        // Prepare data array
        $data = [
            'schedule_id' => $schedule_id,
            'attendance_date' => $attendance_date,
            'faculty_id' => $faculty_id,
            'status' => $status,
            'remarks' => $_POST['remarks'] ?? null,
            'student_count' => intval($_POST['student_count'] ?? 0),
            'recorded_by' => $recorded_by,
            'class_type' => $_POST['class_type'] ?? 'onsite',
            'online_platform' => $_POST['online_platform'] ?? null,
            'meeting_link' => $_POST['meeting_link'] ?? null,
            'meeting_id' => $_POST['meeting_id'] ?? null,
            'meeting_password' => $_POST['meeting_password'] ?? null,
            'online_attendance_file' => null, // Handle file upload separately if needed
            'internet_status' => $_POST['internet_status'] ?? null,
            'connectivity_issues' => $_POST['connectivity_issues'] ?? null
        ];

        // Check if attendance already exists
        $existing = $attendance->checkExisting($schedule_id, $attendance_date, $faculty_id);

        if ($existing) {
            // Update existing record
            $result = $attendance->updateAttendance($existing['id'], $data);
            $message = $result ? 'Attendance updated successfully.' : 'Failed to update attendance.';
        } else {
            // Insert new record
            $result = $attendance->insertAttendance($data);
            $message = $result ? 'Attendance marked successfully.' : 'Failed to mark attendance.';
        }

        echo json_encode([
            'success' => (bool) $result,
            'message' => $message
        ]);
        break;

    // ── GET BY DATE ────────────────────────────────────────────────────────
    case 'getByDate':
        $date = $_GET['date'] ?? null;
        
        if (!$date) {
            echo json_encode(['success' => false, 'message' => 'Date is required.']);
            exit;
        }

        $records = $attendance->getByDate($date);
        echo json_encode([
            'success' => true,
            'data' => $records
        ]);
        break;

    // ── GET REPORT ─────────────────────────────────────────────────────────
    case 'getReport':
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        $faculty_id = $_GET['faculty_id'] ?? null;

        if (!$start_date || !$end_date) {
            echo json_encode(['success' => false, 'message' => 'Start date and end date are required.']);
            exit;
        }

        $report = $attendance->getReport($start_date, $end_date, $faculty_id);
        echo json_encode([
            'success' => true,
            'data' => $report
        ]);
        break;

    // ── GET STUDENT COUNT STATS ────────────────────────────────────────────
    case 'getStudentCountStats':
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        if (!$start_date || !$end_date) {
            echo json_encode(['success' => false, 'message' => 'Start date and end date are required.']);
            exit;
        }

        $stats = $attendance->getStudentCountStats($start_date, $end_date);
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        break;

    // ── GET ONLINE CLASS SUMMARY ───────────────────────────────────────────
    case 'getOnlineSummary':
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        if (!$start_date || !$end_date) {
            echo json_encode(['success' => false, 'message' => 'Start date and end date are required.']);
            exit;
        }

        $summary = $attendance->getOnlineClassSummary($start_date, $end_date);
        echo json_encode([
            'success' => true,
            'data' => $summary
        ]);
        break;

    // ── GET FACULTY HISTORY ────────────────────────────────────────────────
    case 'getFacultyHistory':
        $faculty_id = $_GET['faculty_id'] ?? null;
        $limit = intval($_GET['limit'] ?? 30);

        if (!$faculty_id) {
            echo json_encode(['success' => false, 'message' => 'Faculty ID is required.']);
            exit;
        }

        $history = $attendance->getFacultyHistory($faculty_id, $limit);
        echo json_encode([
            'success' => true,
            'data' => $history
        ]);
        break;

    // ── GET SINGLE RECORD ──────────────────────────────────────────────────
    case 'getOne':
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID is required.']);
            exit;
        }

        $record = $attendance->getOne($id);
        
        if ($record) {
            echo json_encode([
                'success' => true,
                'data' => $record
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Record not found.'
            ]);
        }
        break;

    // ── DELETE RECORD ──────────────────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID is required.']);
            exit;
        }

        // Optional: Check permissions before deleting
        $result = $attendance->delete($id);
        
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Attendance record deleted successfully.' : 'Failed to delete attendance record.'
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
?>