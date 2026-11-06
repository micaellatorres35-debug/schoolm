<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['employee_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to access academic activities.']);
    exit;
}

require_once(__DIR__ . '/../../../database/db.php');

const GC_ACTIVITY_CATEGORIES = [
    'Common Exams',
    'Book Reports',
    'Quiz Bees & Contests',
    'Research & Paper Presentations',
    'Orientation & Curriculum Review',
    'Meetings & Training',
];

const GC_ACTIVITY_EXAM_PERIODS = ['Prelim', 'Midterm', 'Final', 'Item Analysis'];
const GC_ACTIVITY_APPROVER_ROLES = ['System Administrator', 'Admin College Coordinator'];

function gcActivityRespond(int $statusCode, array $response): void
{
    http_response_code($statusCode);
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function gcActivityIsApprover(): bool
{
    return in_array((string)($_SESSION['role_name'] ?? ''), GC_ACTIVITY_APPROVER_ROLES, true);
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
        ");
        $periods = $periodStmt->fetchAll(PDO::FETCH_ASSOC);
        $requestedSchoolYear = (int)($_GET['school_year_id'] ?? 0);
        $requestedSemester = (int)($_GET['semester_id'] ?? 0);
        $selectedPeriod = null;
        foreach ($periods as $period) {
            if ((int)$period['school_year_id'] === $requestedSchoolYear
                && (int)$period['semester_id'] === $requestedSemester) {
                $selectedPeriod = $period;
                break;
            }
        }
        $selectedPeriod = $selectedPeriod ?? ($periods[0] ?? null);
        if (!$selectedPeriod) {
            gcActivityRespond(200, [
                'success' => true,
                'periods' => [],
                'selected_period' => null,
                'activities' => [],
                'summary' => ['total' => 0, 'pending' => 0, 'upcoming' => 0, 'completed' => 0],
                'current_employee_id' => (int)$_SESSION['employee_id'],
                'can_create' => in_array((string)($_SESSION['role_name'] ?? ''), ['GenEd Coordinator', ...GC_ACTIVITY_APPROVER_ROLES], true),
                'can_approve' => gcActivityIsApprover(),
            ]);
        }

        $params = [
            ':school_year_id' => (int)$selectedPeriod['school_year_id'],
            ':semester_id' => (int)$selectedPeriod['semester_id'],
        ];
        $summaryStmt = $conn->prepare("
            SELECT COUNT(*) AS total,
                   SUM(status = 'Pending Approval') AS pending,
                   SUM(status = 'Approved' AND activity_date >= CURDATE()) AS upcoming,
                   SUM(status = 'Completed') AS completed
            FROM gc_academic_activities
            WHERE school_year_id = :school_year_id AND semester_id = :semester_id
        ");
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $activityStmt = $conn->prepare("
            SELECT a.id, a.title, a.description, a.category, a.exam_period, a.activity_date,
                   a.status, a.decision_note, a.created_at,
                   a.submitted_by,
                   CONCAT(COALESCE(submitter.first_name, ''), ' ', COALESCE(submitter.last_name, '')) AS submitted_by_name,
                   a.decided_by,
                   CONCAT(COALESCE(decider.first_name, ''), ' ', COALESCE(decider.last_name, '')) AS decided_by_name
            FROM gc_academic_activities a
            LEFT JOIN em_employees submitter ON submitter.employee_id = a.submitted_by
            LEFT JOIN em_employees decider ON decider.employee_id = a.decided_by
            WHERE a.school_year_id = :school_year_id AND a.semester_id = :semester_id
            ORDER BY a.activity_date, a.created_at DESC
        ");
        $activityStmt->execute($params);

        gcActivityRespond(200, [
            'success' => true,
            'periods' => $periods,
            'selected_period' => $selectedPeriod,
            'activities' => $activityStmt->fetchAll(PDO::FETCH_ASSOC),
            'summary' => [
                'total' => (int)($summary['total'] ?? 0),
                'pending' => (int)($summary['pending'] ?? 0),
                'upcoming' => (int)($summary['upcoming'] ?? 0),
                'completed' => (int)($summary['completed'] ?? 0),
            ],
            'categories' => GC_ACTIVITY_CATEGORIES,
            'exam_periods' => GC_ACTIVITY_EXAM_PERIODS,
            'current_employee_id' => (int)$_SESSION['employee_id'],
            'can_create' => in_array((string)($_SESSION['role_name'] ?? ''), ['GenEd Coordinator', ...GC_ACTIVITY_APPROVER_ROLES], true),
            'can_approve' => gcActivityIsApprover(),
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        gcActivityRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        gcActivityRespond(400, ['success' => false, 'message' => 'Invalid request data.']);
    }
    $action = (string)($input['action'] ?? '');
    $employeeId = (int)$_SESSION['employee_id'];
    $roleName = (string)($_SESSION['role_name'] ?? '');

    if ($action === 'create') {
        if (!in_array($roleName, ['GenEd Coordinator', ...GC_ACTIVITY_APPROVER_ROLES], true)) {
            gcActivityRespond(403, ['success' => false, 'message' => 'You are not allowed to submit academic activities.']);
        }

        $title = trim((string)($input['title'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $category = trim((string)($input['category'] ?? ''));
        $examPeriod = trim((string)($input['exam_period'] ?? ''));
        $activityDate = trim((string)($input['activity_date'] ?? ''));
        $schoolYearId = (int)($input['school_year_id'] ?? 0);
        $semesterId = (int)($input['semester_id'] ?? 0);
        $date = DateTime::createFromFormat('!Y-m-d', $activityDate);

        if ($title === '' || strlen($title) > 200 || !in_array($category, GC_ACTIVITY_CATEGORIES, true)
            || !$date || $date->format('Y-m-d') !== $activityDate || $schoolYearId <= 0 || $semesterId <= 0) {
            gcActivityRespond(422, ['success' => false, 'message' => 'Enter a title, valid category, activity date, school year, and semester.']);
        }
        if (($category === 'Common Exams' && !in_array($examPeriod, GC_ACTIVITY_EXAM_PERIODS, true))
            || ($category !== 'Common Exams' && $examPeriod !== '')) {
            gcActivityRespond(422, ['success' => false, 'message' => 'Select a valid common exam type.']);
        }

        $periodCheck = $conn->prepare("
            SELECT 1 FROM rgr_semesters sem
            INNER JOIN rgr_school_years sy ON sy.id = sem.school_year_id
            WHERE sem.id = :semester_id AND sy.id = :school_year_id
              AND sem.is_active = 1 AND sy.is_active = 1
            LIMIT 1
        ");
        $periodCheck->execute([':semester_id' => $semesterId, ':school_year_id' => $schoolYearId]);
        if (!$periodCheck->fetchColumn()) {
            gcActivityRespond(422, ['success' => false, 'message' => 'Choose an active school year and semester.']);
        }

        $insert = $conn->prepare("
            INSERT INTO gc_academic_activities
                (title, description, category, exam_period, activity_date,
                 school_year_id, semester_id, submitted_by)
            VALUES
                (:title, :description, :category, :exam_period, :activity_date,
                 :school_year_id, :semester_id, :submitted_by)
        ");
        $insert->execute([
            ':title' => $title,
            ':description' => $description !== '' ? $description : null,
            ':category' => $category,
            ':exam_period' => $examPeriod !== '' ? $examPeriod : null,
            ':activity_date' => $activityDate,
            ':school_year_id' => $schoolYearId,
            ':semester_id' => $semesterId,
            ':submitted_by' => $employeeId,
        ]);
        gcActivityRespond(201, ['success' => true, 'message' => 'Activity submitted for approval.']);
    }

    $activityId = (int)($input['id'] ?? 0);
    if ($activityId <= 0) {
        gcActivityRespond(422, ['success' => false, 'message' => 'A valid activity ID is required.']);
    }

    if (in_array($action, ['approve', 'reject'], true)) {
        if (!gcActivityIsApprover()) {
            gcActivityRespond(403, ['success' => false, 'message' => 'Only an administrator or college coordinator can review activities.']);
        }
        $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';
        $note = trim((string)($input['decision_note'] ?? ''));
        if ($action === 'reject' && $note === '') {
            gcActivityRespond(422, ['success' => false, 'message' => 'Please provide a reason for rejecting this activity.']);
        }
        $update = $conn->prepare("
            UPDATE gc_academic_activities
            SET status = :status, decided_by = :decided_by, decision_note = :decision_note
            WHERE id = :id AND status = 'Pending Approval'
        ");
        $update->execute([
            ':status' => $newStatus,
            ':decided_by' => $employeeId,
            ':decision_note' => $note !== '' ? $note : null,
            ':id' => $activityId,
        ]);
        if ($update->rowCount() !== 1) {
            gcActivityRespond(409, ['success' => false, 'message' => 'This activity is no longer waiting for approval.']);
        }
        gcActivityRespond(200, ['success' => true, 'message' => 'Activity ' . strtolower($newStatus) . '.']);
    }

    if ($action === 'complete') {
        $complete = $conn->prepare("
            UPDATE gc_academic_activities
            SET status = 'Completed'
            WHERE id = :id AND status = 'Approved'
              AND (submitted_by = :employee_id OR :can_approve = 1)
        ");
        $complete->execute([
            ':id' => $activityId,
            ':employee_id' => $employeeId,
            ':can_approve' => gcActivityIsApprover() ? 1 : 0,
        ]);
        if ($complete->rowCount() !== 1) {
            gcActivityRespond(409, ['success' => false, 'message' => 'Only the submitter or an approver can complete an approved activity.']);
        }
        gcActivityRespond(200, ['success' => true, 'message' => 'Activity marked completed.']);
    }

    gcActivityRespond(400, ['success' => false, 'message' => 'Unknown action.']);
} catch (Throwable $e) {
    error_log('Academic activities API error: ' . $e->getMessage());
    gcActivityRespond(500, ['success' => false, 'message' => 'Unable to process the academic activity request.']);
}
