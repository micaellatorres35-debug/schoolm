<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function genedSubjectActivitiesRespond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function genedSubjectActivitiesCanManage(): bool
{
    $roleName = strtolower(trim((string)($_SESSION['role_name'] ?? '')));
    return (int)($_SESSION['role'] ?? 0) === 30
        || in_array($roleName, [
            'gened coordinator',
            'system administrator',
            'admin college coordinator',
        ], true);
}

function genedSubjectActivitiesCanApprove(): bool
{
    return in_array(strtolower(trim((string)($_SESSION['role_name'] ?? ''))), [
        'system administrator',
        'admin college coordinator',
    ], true);
}

const GENED_SUBJECT_ACTIVITY_CATEGORIES = [
    'Common Exams',
    'Book Reports',
    'Quiz Bees & Contests',
    'Research & Paper Presentations',
    'Orientation & Curriculum Review',
    'Meetings & Training',
];

const GENED_SUBJECT_ACTIVITY_EXAM_PERIODS = ['Prelim', 'Midterm', 'Final', 'Item Analysis'];

if (empty($_SESSION['employee_id'])) {
    genedSubjectActivitiesRespond(401, ['success' => false, 'message' => 'Please sign in to access GenEd activities.']);
}

require_once dirname(__DIR__, 3) . '/database/db.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    if (!$conn) {
        throw new RuntimeException('Database connection failed.');
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $periods = $conn->query("
            SELECT school_year.id AS school_year_id,
                   school_year.name AS school_year,
                   school_year.is_active AS school_year_active,
                   semester.id AS semester_id,
                   semester.name AS semester,
                   semester.is_active AS semester_active
            FROM rgr_school_years school_year
            LEFT JOIN rgr_semesters semester ON semester.school_year_id = school_year.id
            WHERE school_year.is_active = 1
            ORDER BY semester.is_active DESC, semester.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $assignmentRows = $conn->query("
            SELECT DISTINCT faculty_load.school_year_id,
                   faculty_load.semester_id,
                
                   section.id AS section_id,
                   section.section_code,
                   section.program_id AS department_id,
                   course.code AS department_code,
                   course.name AS department_name,
                   subject.id AS subject_id,
                   subject.code AS subject_code,
                   subject.name AS subject_name,
                   school_year.name AS school_year,
                   semester.name AS semester,
                   faculty.id AS faculty_id,
                   TRIM(CONCAT_WS(' ', faculty.first_name, faculty.last_name)) AS faculty_name
            FROM gc_faculty_load faculty_load
            INNER JOIN gc_sections section
                ON section.id = faculty_load.section_id
               AND section.school_year_id = faculty_load.school_year_id
               AND section.semester_id = faculty_load.semester_id
            INNER JOIN rgr_courses course ON course.id = section.program_id
            INNER JOIN rgr_subjects subject
                ON subject.id = faculty_load.subject_id
               AND subject.archived_at IS NULL
               AND UPPER(subject.code) REGEXP '^(GE.*|FIL [1-3]|NSTP [1-2]|PE [1-2])$'
            INNER JOIN gc_faculty faculty ON faculty.id = faculty_load.faculty_id
            INNER JOIN em_employees employee
                ON employee.employee_id = faculty.employee_id
               AND employee.is_archived = 0
               AND employee.employment_status = 'Active'
            INNER JOIN rgr_school_years school_year
                ON school_year.id = faculty_load.school_year_id
               AND school_year.is_active = 1
            INNER JOIN rgr_semesters semester
                ON semester.id = faculty_load.semester_id
               AND semester.school_year_id = faculty_load.school_year_id
               AND semester.is_active = 1
            ORDER BY school_year.id DESC, semester.id DESC, course.code,
                     section.section_code, subject.code, faculty.last_name, faculty.first_name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $assignmentOptions = [];
        $assignmentIndexes = [];
        foreach ($assignmentRows as $row) {
            $key = implode(':', [
                (int)$row['school_year_id'],
                (int)$row['semester_id'],
                (int)$row['section_id'],
                (int)$row['subject_id'],
            ]);
            if (!isset($assignmentIndexes[$key])) {
                $assignmentIndexes[$key] = count($assignmentOptions);
                $assignmentOptions[] = [
                    'key' => $key,
                    'school_year_id' => (int)$row['school_year_id'],
                    'school_year' => $row['school_year'],
                    'semester_id' => (int)$row['semester_id'],
                    'semester' => $row['semester'],
                    'department_id' => (int)$row['department_id'],
                    'department_code' => $row['department_code'],
                    'department_name' => $row['department_name'],
                    'section_id' => (int)$row['section_id'],
                    'section_code' => $row['section_code'],
                    'subject_id' => (int)$row['subject_id'],
                    'subject_code' => $row['subject_code'],
                    'subject_name' => $row['subject_name'],
                    'faculty' => [],
                ];
            }
            $index = $assignmentIndexes[$key];
            $assignmentOptions[$index]['faculty'][] = [
                'id' => (int)$row['faculty_id'],
                'name' => $row['faculty_name'],
            ];
        }

        $activities = $conn->query("
            SELECT activity.id,
                   activity.subject_id,
                   subject.code AS subject_code,
                   subject.name AS subject_name,
                   activity.section_id,
                   section.section_code,
                   section.program_id AS department_id,
                   course.code AS department_code,
                   course.name AS department_name,
                   activity.school_year_id,
                   school_year.name AS school_year,
                   activity.semester_id,
                   semester.name AS semester,
                   activity.title,
                   activity.description,
                   activity.category,
                   activity.exam_period,
                   activity.activity_date,
                   activity.status,
                   activity.submitted_by,
                   TRIM(CONCAT_WS(' ', submitter.first_name, submitter.last_name)) AS submitted_by_name,
                   activity.decided_by,
                   TRIM(CONCAT_WS(' ', decider.first_name, decider.last_name)) AS decided_by_name,
                   activity.decision_note
            FROM gc_subject_activities activity
            INNER JOIN rgr_subjects subject ON subject.id = activity.subject_id
            INNER JOIN gc_sections section ON section.id = activity.section_id
            INNER JOIN rgr_courses course ON course.id = section.program_id
            INNER JOIN rgr_school_years school_year ON school_year.id = activity.school_year_id
                AND school_year.is_active = 1
            INNER JOIN rgr_semesters semester
                ON semester.id = activity.semester_id
               AND semester.school_year_id = activity.school_year_id
            LEFT JOIN em_employees submitter ON submitter.employee_id = activity.submitted_by
            LEFT JOIN em_employees decider ON decider.employee_id = activity.decided_by
            ORDER BY activity.activity_date, activity.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $teacherRows = $conn->query("
            SELECT assignment.activity_id,
                   assignment.faculty_id,
                   TRIM(CONCAT_WS(' ', faculty.first_name, faculty.last_name)) AS faculty_name
            FROM gc_subject_activity_faculty assignment
            INNER JOIN gc_faculty faculty ON faculty.id = assignment.faculty_id
            ORDER BY faculty.last_name, faculty.first_name
        ")->fetchAll(PDO::FETCH_ASSOC);
        $teachersByActivity = [];
        foreach ($teacherRows as $teacher) {
            $teachersByActivity[(int)$teacher['activity_id']][] = [
                'id' => (int)$teacher['faculty_id'],
                'name' => $teacher['faculty_name'],
            ];
        }
        foreach ($activities as &$activity) {
            $activity['id'] = (int)$activity['id'];
            $activity['teachers'] = $teachersByActivity[$activity['id']] ?? [];
        }
        unset($activity);

        genedSubjectActivitiesRespond(200, [
            'success' => true,
            'periods' => $periods,
            'assignment_options' => $assignmentOptions,
            'activities' => $activities,
            'categories' => GENED_SUBJECT_ACTIVITY_CATEGORIES,
            'exam_periods' => GENED_SUBJECT_ACTIVITY_EXAM_PERIODS,
            'current_employee_id' => (int)$_SESSION['employee_id'],
            'can_create' => genedSubjectActivitiesCanManage(),
            'can_approve' => genedSubjectActivitiesCanApprove(),
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        genedSubjectActivitiesRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        genedSubjectActivitiesRespond(400, ['success' => false, 'message' => 'Invalid request data.']);
    }
    $action = (string)($input['action'] ?? '');
    $employeeId = (int)$_SESSION['employee_id'];

    if ($action === 'create') {
        if (!genedSubjectActivitiesCanManage()) {
            genedSubjectActivitiesRespond(403, ['success' => false, 'message' => 'You are not allowed to create GenEd activities.']);
        }

        $title = trim((string)($input['title'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $category = trim((string)($input['category'] ?? ''));
        $examPeriod = trim((string)($input['exam_period'] ?? ''));
        $activityDate = trim((string)($input['activity_date'] ?? ''));
        $schoolYearId = (int)($input['school_year_id'] ?? 0);
        $semesterId = (int)($input['semester_id'] ?? 0);
        $sectionId = (int)($input['section_id'] ?? 0);
        $subjectId = (int)($input['subject_id'] ?? 0);
        $facultyIdsInput = $input['faculty_ids'] ?? null;
        $date = DateTime::createFromFormat('!Y-m-d', $activityDate);

        if ($title === '' || strlen($title) > 200
            || !in_array($category, GENED_SUBJECT_ACTIVITY_CATEGORIES, true)
            || !$date || $date->format('Y-m-d') !== $activityDate
            || $schoolYearId <= 0 || $semesterId <= 0 || $sectionId <= 0 || $subjectId <= 0
            || !is_array($facultyIdsInput) || !$facultyIdsInput) {
            genedSubjectActivitiesRespond(422, ['success' => false, 'message' => 'Complete the activity details, subject assignment, date, and at least one teacher.']);
        }
        if (($category === 'Common Exams' && !in_array($examPeriod, GENED_SUBJECT_ACTIVITY_EXAM_PERIODS, true))
            || ($category !== 'Common Exams' && $examPeriod !== '')) {
            genedSubjectActivitiesRespond(422, ['success' => false, 'message' => 'Select a valid common exam period.']);
        }

        $facultyIds = [];
        foreach ($facultyIdsInput as $facultyIdInput) {
            $facultyId = filter_var($facultyIdInput, FILTER_VALIDATE_INT);
            if ($facultyId === false || $facultyId <= 0) {
                genedSubjectActivitiesRespond(422, ['success' => false, 'message' => 'Choose valid eligible teachers.']);
            }
            $facultyIds[] = (int)$facultyId;
        }
        $facultyIds = array_values(array_unique($facultyIds));

        $placeholders = [];
        $eligibilityParams = [
            ':school_year_id' => $schoolYearId,
            ':semester_id' => $semesterId,
            ':section_id' => $sectionId,
            ':subject_id' => $subjectId,
        ];
        foreach ($facultyIds as $index => $facultyId) {
            $placeholder = ':faculty_id_' . $index;
            $placeholders[] = $placeholder;
            $eligibilityParams[$placeholder] = $facultyId;
        }
        $eligibleTeachers = $conn->prepare("
            SELECT DISTINCT faculty_load.faculty_id
            FROM gc_faculty_load faculty_load
            INNER JOIN gc_sections section
                ON section.id = faculty_load.section_id
               AND section.school_year_id = faculty_load.school_year_id
               AND section.semester_id = faculty_load.semester_id
            INNER JOIN rgr_subjects subject
                ON subject.id = faculty_load.subject_id
               AND subject.archived_at IS NULL
               AND UPPER(subject.code) REGEXP '^(GE.*|FIL [1-3]|NSTP [1-2]|PE [1-2])$'
            INNER JOIN gc_faculty faculty ON faculty.id = faculty_load.faculty_id
            INNER JOIN em_employees employee
                ON employee.employee_id = faculty.employee_id
               AND employee.is_archived = 0
               AND employee.employment_status = 'Active'
            INNER JOIN rgr_school_years school_year
                ON school_year.id = faculty_load.school_year_id
               AND school_year.is_active = 1
            INNER JOIN rgr_semesters semester
                ON semester.id = faculty_load.semester_id
               AND semester.school_year_id = faculty_load.school_year_id
               AND semester.is_active = 1
            WHERE faculty_load.school_year_id = :school_year_id
              AND faculty_load.semester_id = :semester_id
              AND faculty_load.section_id = :section_id
              AND faculty_load.subject_id = :subject_id
              AND faculty_load.faculty_id IN (" . implode(', ', $placeholders) . ")
        ");
        $eligibleTeachers->execute($eligibilityParams);
        $eligibleFacultyIds = array_map('intval', $eligibleTeachers->fetchAll(PDO::FETCH_COLUMN));
        if (count($eligibleFacultyIds) !== count($facultyIds)) {
            genedSubjectActivitiesRespond(422, [
                'success' => false,
                'message' => 'Every selected teacher must be actively assigned to this subject and section for the selected period.',
            ]);
        }

        $conn->beginTransaction();
        $insert = $conn->prepare("
            INSERT INTO gc_subject_activities
                (subject_id, section_id, school_year_id, semester_id,
                 title, description, category, exam_period, activity_date, submitted_by)
            VALUES
                (:subject_id, :section_id, :school_year_id, :semester_id,
                 :title, :description, :category, :exam_period, :activity_date, :submitted_by)
        ");
        $insert->execute([
            ':subject_id' => $subjectId,
            ':section_id' => $sectionId,
            ':school_year_id' => $schoolYearId,
            ':semester_id' => $semesterId,
            ':title' => $title,
            ':description' => $description !== '' ? $description : null,
            ':category' => $category,
            ':exam_period' => $examPeriod !== '' ? $examPeriod : null,
            ':activity_date' => $activityDate,
            ':submitted_by' => $employeeId,
        ]);
        $activityId = (int)$conn->lastInsertId();
        $linkTeacher = $conn->prepare("
            INSERT INTO gc_subject_activity_faculty (activity_id, faculty_id)
            VALUES (:activity_id, :faculty_id)
        ");
        foreach ($facultyIds as $facultyId) {
            $linkTeacher->execute([':activity_id' => $activityId, ':faculty_id' => $facultyId]);
        }
        $conn->commit();

        genedSubjectActivitiesRespond(201, ['success' => true, 'message' => 'Activity submitted for approval.']);
    }

    $activityId = (int)($input['id'] ?? 0);
    if ($activityId <= 0) {
        genedSubjectActivitiesRespond(422, ['success' => false, 'message' => 'A valid activity ID is required.']);
    }

    if (in_array($action, ['approve', 'reject'], true)) {
        if (!genedSubjectActivitiesCanApprove()) {
            genedSubjectActivitiesRespond(403, ['success' => false, 'message' => 'Only an administrator or college coordinator can review activities.']);
        }
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        $decisionNote = trim((string)($input['decision_note'] ?? ''));
        if ($action === 'reject' && $decisionNote === '') {
            genedSubjectActivitiesRespond(422, ['success' => false, 'message' => 'Please provide a reason for rejecting this activity.']);
        }
        $update = $conn->prepare("
            UPDATE gc_subject_activities
            SET status = :status, decided_by = :decided_by, decision_note = :decision_note
            WHERE id = :id AND status = 'Pending Approval'
        ");
        $update->execute([
            ':status' => $status,
            ':decided_by' => $employeeId,
            ':decision_note' => $decisionNote !== '' ? $decisionNote : null,
            ':id' => $activityId,
        ]);
        if ($update->rowCount() !== 1) {
            genedSubjectActivitiesRespond(409, ['success' => false, 'message' => 'This activity is no longer waiting for approval.']);
        }
        genedSubjectActivitiesRespond(200, ['success' => true, 'message' => 'Activity ' . strtolower($status) . '.']);
    }

    if ($action === 'complete') {
        $complete = $conn->prepare("
            UPDATE gc_subject_activities
            SET status = 'Completed'
            WHERE id = :id AND status = 'Approved'
              AND (submitted_by = :employee_id OR :can_approve = 1)
        ");
        $complete->execute([
            ':id' => $activityId,
            ':employee_id' => $employeeId,
            ':can_approve' => genedSubjectActivitiesCanApprove() ? 1 : 0,
        ]);
        if ($complete->rowCount() !== 1) {
            genedSubjectActivitiesRespond(409, ['success' => false, 'message' => 'Only the submitter or an approver can complete an approved activity.']);
        }
        genedSubjectActivitiesRespond(200, ['success' => true, 'message' => 'Activity marked completed.']);
    }

    genedSubjectActivitiesRespond(400, ['success' => false, 'message' => 'Unknown action.']);
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('GenEd subject activities API error: ' . $error->getMessage());
    genedSubjectActivitiesRespond(500, ['success' => false, 'message' => 'Unable to process the GenEd activity request.']);
}
