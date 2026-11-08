<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function genedBookReportRespond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['employee_id'])) {
    genedBookReportRespond(401, ['success' => false, 'message' => 'Please sign in to access GenEd book reports.']);
}

if (!in_array(
    (string)($_SESSION['role_name'] ?? ''),
    ['GenEd Coordinator', 'System Administrator', 'Admin College Coordinator'],
    true
)) {
    genedBookReportRespond(403, ['success' => false, 'message' => 'You are not allowed to access GenEd book reports.']);
}

require_once dirname(__DIR__, 3) . '/database/db.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $periods = $conn->query("
        SELECT school_year.id AS school_year_id,
               school_year.name AS school_year,
               school_year.is_active AS school_year_active,
               semester.id AS semester_id,
               semester.name AS semester,
               semester.is_active AS semester_active
        FROM rgr_school_years school_year
        INNER JOIN rgr_semesters semester ON semester.school_year_id = school_year.id
        ORDER BY school_year.is_active DESC, semester.is_active DESC,
                 school_year.id DESC, semester.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $assignments = $conn->query("
        SELECT faculty_load.id AS faculty_load_id,
               faculty_load.school_year_id,
               faculty_load.semester_id,
               section.program_id AS department_id,
               course.code AS department_code,
               course.name AS department_name,
               section.id AS section_id,
               section.section_code,
               section.grade_level,
               subject.id AS subject_id,
               subject.code AS subject_code,
               subject.name AS subject_name,
               faculty.id AS faculty_id,
               TRIM(CONCAT_WS(' ', faculty.first_name, faculty.last_name)) AS faculty_name,
               school_year.name AS school_year,
               semester.name AS semester,
               (
                   SELECT COUNT(*)
                   FROM enr_students student
                   WHERE student.section_id = section.id
                     AND student.course_id = section.program_id
                     AND student.enrollment_status = 'enrolled'
               ) AS enrolled_students
        FROM gc_faculty_load faculty_load
        INNER JOIN gc_sections section ON section.id = faculty_load.section_id
            AND section.school_year_id = faculty_load.school_year_id
            AND section.semester_id = faculty_load.semester_id
        INNER JOIN rgr_courses course ON course.id = section.program_id
        INNER JOIN rgr_subjects subject ON subject.id = faculty_load.subject_id
            AND subject.archived_at IS NULL
            AND UPPER(subject.code) REGEXP '^(GE.*|FIL [1-3]|NSTP [1-2]|PE [1-2])$'
        INNER JOIN gc_faculty faculty ON faculty.id = faculty_load.faculty_id
        INNER JOIN rgr_school_years school_year ON school_year.id = faculty_load.school_year_id
        INNER JOIN rgr_semesters semester ON semester.id = faculty_load.semester_id
            AND semester.school_year_id = faculty_load.school_year_id
        ORDER BY school_year.id DESC, semester.id DESC,
                 course.code, section.section_code, subject.code,
                 faculty.last_name, faculty.first_name, faculty_load.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    $departments = [];
    foreach ($assignments as $assignment) {
        $departmentId = (int)$assignment['department_id'];
        $departments[$departmentId] = [
            'id' => $departmentId,
            'code' => $assignment['department_code'],
            'name' => $assignment['department_name'],
        ];
    }

    genedBookReportRespond(200, [
        'success' => true,
        'periods' => $periods,
        'departments' => array_values($departments),
        'assignments' => $assignments,
    ]);
} catch (Throwable $error) {
    error_log('GenEd Book Report catalog error: ' . $error->getMessage());
    genedBookReportRespond(500, ['success' => false, 'message' => 'Unable to load GenEd sections, subjects, and faculty assignments.']);
}
