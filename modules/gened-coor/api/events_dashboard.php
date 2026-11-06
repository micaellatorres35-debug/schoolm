<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['employee_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to access GenEd events.']);
    exit;
}

require_once(__DIR__ . '/../../../database/db.php');

const GC_EVENT_TYPES = [
    'Academic',
    'Meeting',
    'Seminar',
    'Institutional Event',
    'Cultural Event',
    'Sports Event',
    'Orientation',
    'Other',
];
const GC_EVENT_CATEGORIES = [
    'Language & Literature',
    'History & Culture',
    'Science & Math',
    'Values & Ethics',
    'Sports',
    'Community',
    'Academic Support',
];
const GC_EVENT_APPROVER_ROLES = ['System Administrator', 'Admin College Coordinator'];

function gcEventsRespond(int $statusCode, array $response): void
{
    http_response_code($statusCode);
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function gcEventsCanApprove(): bool
{
    return in_array((string)($_SESSION['role_name'] ?? ''), GC_EVENT_APPROVER_ROLES, true);
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $periodStmt = $conn->query("
            SELECT sy.id AS school_year_id, sy.name AS school_year,
                   sem.id AS semester_id, sem.name AS semester
            FROM rgr_school_years sy
            INNER JOIN rgr_semesters sem
                ON sem.school_year_id = sy.id AND sem.is_active = 1
            WHERE sy.is_active = 1
            ORDER BY sy.id DESC, sem.id DESC
            LIMIT 1
        ");
        $period = $periodStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $subjectCount = (int)$conn->query("
            SELECT COUNT(*)
            FROM rgr_subjects
            WHERE archived_at IS NULL
              AND UPPER(code) REGEXP '^(GE.*|FIL [1-3]|NSTP [1-2]|PE [1-2])$'
        ")->fetchColumn();
        $facultyCount = (int)$conn->query("SELECT COUNT(*) FROM gc_faculty")->fetchColumn();

        $activitySummary = ['pending' => 0, 'pending_book_reports' => 0];
        $itemAnalysisSummary = ['total' => 0, 'pending' => 0, 'approved' => 0, 'completed' => 0, 'rejected' => 0];
        $activities = [];
        $nextExam = null;
        if ($period) {
            $periodParams = [
                ':school_year_id' => (int)$period['school_year_id'],
                ':semester_id' => (int)$period['semester_id'],
            ];
            $summaryStmt = $conn->prepare("
                SELECT
                    SUM(status = 'Pending Approval') AS pending,
                    SUM(category = 'Book Reports' AND status = 'Pending Approval') AS pending_book_reports
                FROM gc_academic_activities
                WHERE school_year_id = :school_year_id AND semester_id = :semester_id
            ");
            $summaryStmt->execute($periodParams);
            $activitySummary = array_merge($activitySummary, $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: []);

            $itemAnalysisStmt = $conn->prepare("
                SELECT COUNT(*) AS total,
                       COALESCE(SUM(status = 'Pending Approval'), 0) AS pending,
                       COALESCE(SUM(status = 'Approved'), 0) AS approved,
                       COALESCE(SUM(status = 'Completed'), 0) AS completed,
                       COALESCE(SUM(status = 'Rejected'), 0) AS rejected
                FROM gc_academic_activities
                WHERE school_year_id = :school_year_id
                  AND semester_id = :semester_id
                  AND category = 'Common Exams'
                  AND exam_period = 'Item Analysis'
            ");
            $itemAnalysisStmt->execute($periodParams);
            $itemAnalysisSummary = $itemAnalysisStmt->fetch(PDO::FETCH_ASSOC) ?: $itemAnalysisSummary;

            $activityStmt = $conn->prepare("
                SELECT id, title, description, category, exam_period, activity_date, status
                FROM gc_academic_activities
                WHERE school_year_id = :school_year_id AND semester_id = :semester_id
                  AND activity_date >= CURDATE()
                  AND status IN ('Pending Approval', 'Approved', 'Completed')
                ORDER BY activity_date ASC, created_at DESC
                LIMIT 5
            ");
            $activityStmt->execute($periodParams);
            $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

            $examStmt = $conn->prepare("
                SELECT title, exam_period, activity_date
                FROM gc_academic_activities
                WHERE school_year_id = :school_year_id AND semester_id = :semester_id
                  AND category = 'Common Exams' AND status = 'Approved'
                  AND activity_date >= CURDATE()
                ORDER BY activity_date ASC
                LIMIT 1
            ");
            $examStmt->execute($periodParams);
            $nextExam = $examStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $eventSummaryStmt = $conn->query("
            SELECT COUNT(*) AS total,
                   SUM(approval_status = 'Pending Approval') AS pending,
                   SUM(event_date >= CURDATE()
                       AND status IN ('upcoming', 'ongoing')
                       AND approval_status = 'Approved') AS upcoming,
                   SUM(approval_status = 'Completed' OR status = 'completed') AS completed
            FROM gc_events
            WHERE archived_at IS NULL
        ");
        $eventSummary = $eventSummaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $pendingEventCount = (int)($eventSummary['pending'] ?? 0);
        $eventStmt = $conn->query("
            SELECT event_id AS id, event_title AS title, event_type, event_date,
                   start_time, end_time, location, target_audience, event_scope,
                   approval_status AS status, submitted_by
            FROM gc_events
            WHERE event_date >= CURDATE()
              AND approval_status IN ('Pending Approval', 'Approved', 'Completed')
              AND archived_at IS NULL
            ORDER BY event_date ASC, start_time ASC
            LIMIT 5
        ");
        $eventRecordsStmt = $conn->query("
            SELECT ev.event_id AS id, ev.event_title AS title, ev.event_type, ev.event_date,
                   ev.start_time, ev.end_time, ev.description, ev.location, ev.target_audience,
                   ev.event_scope, ev.event_category, ev.decision_note,
                   ev.status AS event_status, ev.approval_status AS status,
                   ev.submitted_by,
                   NULLIF(TRIM(CONCAT(COALESCE(emp.first_name, ''), ' ', COALESCE(emp.last_name, ''))), '') AS submitted_by_name
            FROM gc_events ev
            LEFT JOIN em_employees emp ON emp.employee_id = ev.submitted_by
            WHERE ev.archived_at IS NULL
            ORDER BY ev.event_date ASC, ev.start_time ASC
        ");

        $employeeDepartmentStmt = $conn->prepare("
            SELECT department_id FROM em_employees WHERE employee_id = :employee_id LIMIT 1
        ");
        $employeeDepartmentStmt->execute([':employee_id' => (int)$_SESSION['employee_id']]);
        $departmentId = $employeeDepartmentStmt->fetchColumn();
        $openConcerns = null;
        if ($departmentId !== false && $departmentId !== null) {
            $concernStmt = $conn->prepare("
                SELECT COUNT(*) FROM cc_issues
                WHERE department = :department_id AND status IN ('submitted', 'reviewed')
            ");
            $concernStmt->execute([':department_id' => (int)$departmentId]);
            $openConcerns = (int)$concernStmt->fetchColumn();
        }

        $canCreate = in_array(
            (string)($_SESSION['role_name'] ?? ''),
            ['GenEd Coordinator', ...GC_EVENT_APPROVER_ROLES],
            true
        );
        gcEventsRespond(200, [
            'success' => true,
            'period' => $period,
            'metrics' => [
                'subjects' => $subjectCount,
                'faculty' => $facultyCount,
                'pending_approvals' => (int)($activitySummary['pending'] ?? 0) + $pendingEventCount,
                'open_concerns' => $openConcerns,
                'pending_book_reports' => (int)($activitySummary['pending_book_reports'] ?? 0),
                'next_exam' => $nextExam,
                'item_analysis' => [
                    'total' => (int)($itemAnalysisSummary['total'] ?? 0),
                    'pending' => (int)($itemAnalysisSummary['pending'] ?? 0),
                    'approved' => (int)($itemAnalysisSummary['approved'] ?? 0),
                    'completed' => (int)($itemAnalysisSummary['completed'] ?? 0),
                    'rejected' => (int)($itemAnalysisSummary['rejected'] ?? 0),
                ],
            ],
            'event_summary' => [
                'total' => (int)($eventSummary['total'] ?? 0),
                'pending' => $pendingEventCount,
                'upcoming' => (int)($eventSummary['upcoming'] ?? 0),
                'completed' => (int)($eventSummary['completed'] ?? 0),
            ],
            'activities' => $activities,
            'events' => $eventStmt->fetchAll(PDO::FETCH_ASSOC),
            'event_records' => $eventRecordsStmt->fetchAll(PDO::FETCH_ASSOC),
            'current_employee_id' => (int)$_SESSION['employee_id'],
            'can_create' => $canCreate,
            'can_approve' => gcEventsCanApprove(),
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        gcEventsRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        gcEventsRespond(400, ['success' => false, 'message' => 'Invalid request data.']);
    }
    $action = (string)($input['action'] ?? '');
    $employeeId = (int)$_SESSION['employee_id'];
    $roleName = (string)($_SESSION['role_name'] ?? '');

    if ($action === 'create') {
        if (!in_array($roleName, ['GenEd Coordinator', ...GC_EVENT_APPROVER_ROLES], true)) {
            gcEventsRespond(403, ['success' => false, 'message' => 'You are not allowed to submit GenEd events.']);
        }

        $title = trim((string)($input['event_title'] ?? ''));
        $eventType = trim((string)($input['event_type'] ?? ''));
        $eventCategory = trim((string)($input['event_category'] ?? ''));
        $eventDate = trim((string)($input['event_date'] ?? ''));
        $startTime = trim((string)($input['start_time'] ?? ''));
        $endTime = trim((string)($input['end_time'] ?? ''));
        $location = trim((string)($input['location'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $targetAudience = trim((string)($input['target_audience'] ?? ''));
        $scope = trim((string)($input['event_scope'] ?? ''));
        $date = DateTime::createFromFormat('!Y-m-d', $eventDate);
        $start = DateTime::createFromFormat('!H:i', $startTime);
        $end = DateTime::createFromFormat('!H:i', $endTime);

        if ($title === '' || strlen($title) > 255 || !in_array($eventType, GC_EVENT_TYPES, true)
            || !in_array($eventCategory, GC_EVENT_CATEGORIES, true)
            || !$date || $date->format('Y-m-d') !== $eventDate
            || !$start || $start->format('H:i') !== $startTime
            || !$end || $end->format('H:i') !== $endTime
            || $end <= $start || $location === '' || strlen($location) > 255
            || strlen($targetAudience) > 100
            || !in_array($scope, ['School-wide', 'Department-level'], true)) {
            gcEventsRespond(422, ['success' => false, 'message' => 'Complete all required fields with valid event details.']);
        }

        $insert = $conn->prepare("
            INSERT INTO gc_events
                (event_title, event_type, event_date, start_time, end_time, description,
                 location, target_audience, event_scope, event_category, status, approval_status, submitted_by)
            VALUES
                (:event_title, :event_type, :event_date, :start_time, :end_time, :description,
                 :location, :target_audience, :event_scope, :event_category,
                 'upcoming', 'Pending Approval', :submitted_by)
        ");
        $insert->execute([
            ':event_title' => $title,
            ':event_type' => $eventType,
            ':event_date' => $eventDate,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
            ':description' => $description !== '' ? $description : null,
            ':location' => $location,
            ':target_audience' => $targetAudience !== '' ? $targetAudience : null,
            ':event_scope' => $scope,
            ':event_category' => $eventCategory,
            ':submitted_by' => $employeeId,
        ]);
        gcEventsRespond(201, ['success' => true, 'message' => 'Event submitted for approval.']);
    }

    $eventId = (int)($input['id'] ?? 0);
    if ($eventId <= 0) {
        gcEventsRespond(422, ['success' => false, 'message' => 'A valid event ID is required.']);
    }

    if ($action === 'submit') {
        $resubmit = $conn->prepare("
            UPDATE gc_events
            SET approval_status = 'Pending Approval', decided_by = NULL, decision_note = NULL
            WHERE event_id = :id AND archived_at IS NULL
              AND approval_status IN ('Draft', 'Rejected')
              AND (submitted_by = :employee_id OR :can_manage = 1)
        ");
        $resubmit->execute([
            ':id' => $eventId,
            ':employee_id' => $employeeId,
            ':can_manage' => in_array($roleName, ['GenEd Coordinator', ...GC_EVENT_APPROVER_ROLES], true) ? 1 : 0,
        ]);
        if ($resubmit->rowCount() !== 1) {
            gcEventsRespond(409, ['success' => false, 'message' => 'Only the submitter or a GenEd manager can submit a draft or returned event.']);
        }
        gcEventsRespond(200, ['success' => true, 'message' => 'Event submitted for approval.']);
    }

    if ($action === 'archive') {
        $archive = $conn->prepare("
            UPDATE gc_events
            SET archived_at = CURRENT_TIMESTAMP
            WHERE event_id = :id AND archived_at IS NULL
              AND (submitted_by = :employee_id OR :can_manage = 1)
        ");
        $archive->execute([
            ':id' => $eventId,
            ':employee_id' => $employeeId,
            ':can_manage' => in_array($roleName, ['GenEd Coordinator', ...GC_EVENT_APPROVER_ROLES], true) ? 1 : 0,
        ]);
        if ($archive->rowCount() !== 1) {
            gcEventsRespond(409, ['success' => false, 'message' => 'This event cannot be archived or is already archived.']);
        }
        gcEventsRespond(200, ['success' => true, 'message' => 'Event archived.']);
    }

    if (in_array($action, ['approve', 'reject'], true)) {
        if (!gcEventsCanApprove()) {
            gcEventsRespond(403, ['success' => false, 'message' => 'Only an administrator or college coordinator can review events.']);
        }
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        $note = trim((string)($input['decision_note'] ?? ''));
        if ($action === 'reject' && $note === '') {
            gcEventsRespond(422, ['success' => false, 'message' => 'Please provide a reason for rejecting this event.']);
        }
        $update = $conn->prepare("
            UPDATE gc_events
            SET approval_status = :status, decided_by = :decided_by, decision_note = :decision_note
            WHERE event_id = :id AND approval_status = 'Pending Approval'
        ");
        $update->execute([
            ':status' => $status,
            ':decided_by' => $employeeId,
            ':decision_note' => $note !== '' ? $note : null,
            ':id' => $eventId,
        ]);
        if ($update->rowCount() !== 1) {
            gcEventsRespond(409, ['success' => false, 'message' => 'This event is no longer waiting for approval.']);
        }
        gcEventsRespond(200, ['success' => true, 'message' => 'Event ' . strtolower($status) . '.']);
    }

    if ($action === 'complete') {
        $complete = $conn->prepare("
            UPDATE gc_events
            SET approval_status = 'Completed', status = 'completed'
            WHERE event_id = :id AND approval_status = 'Approved'
              AND (submitted_by = :employee_id OR :can_approve = 1)
        ");
        $complete->execute([
            ':id' => $eventId,
            ':employee_id' => $employeeId,
            ':can_approve' => gcEventsCanApprove() ? 1 : 0,
        ]);
        if ($complete->rowCount() !== 1) {
            gcEventsRespond(409, ['success' => false, 'message' => 'Only the submitter or an approver can complete an approved event.']);
        }
        gcEventsRespond(200, ['success' => true, 'message' => 'Event marked completed.']);
    }

    gcEventsRespond(400, ['success' => false, 'message' => 'Unknown action.']);
} catch (Throwable $e) {
    error_log('GenEd events dashboard API error: ' . $e->getMessage());
    gcEventsRespond(500, ['success' => false, 'message' => 'Unable to process the GenEd events request.']);
}
