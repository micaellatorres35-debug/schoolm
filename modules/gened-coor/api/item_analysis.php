<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function genedItemAnalysisRespond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['employee_id'])) {
    genedItemAnalysisRespond(401, ['success' => false, 'message' => 'Please sign in to access item analysis.']);
}

if (!in_array(
    (string)($_SESSION['role_name'] ?? ''),
    ['GenEd Coordinator', 'System Administrator', 'Admin College Coordinator'],
    true
)) {
    genedItemAnalysisRespond(403, ['success' => false, 'message' => 'You are not allowed to access GenEd item analysis.']);
}

require_once dirname(__DIR__, 3) . '/database/db.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    $action = (string)($_GET['action'] ?? 'schedules');

    $scheduleQuery = "
        SELECT exam_schedule.id AS schedule_id,
               exam.id AS exam_id,
               exam.exam_name,
               exam.exam_type,
               exam.status AS exam_status,
               exam.school_year_id,
               school_year.name AS school_year,
               exam.semester_id,
               semester.name AS semester,
               subject.id AS subject_id,
               subject.code AS subject_code,
               subject.name AS subject_name,
               course.id AS department_id,
               course.code AS department,
               course.name AS department_name,
               section.id AS section_id,
               section.section_code,
               section.grade_level,
               exam_schedule.exam_date,
               exam_schedule.start_time,
               exam_schedule.end_time,
               exam_schedule.status AS schedule_status,
               (
                   SELECT GROUP_CONCAT(
                       DISTINCT TRIM(CONCAT_WS(' ', assigned_faculty.first_name, assigned_faculty.last_name))
                       ORDER BY assigned_faculty.last_name, assigned_faculty.first_name
                       SEPARATOR ', '
                   )
                   FROM gc_faculty_load faculty_load
                   INNER JOIN gc_faculty assigned_faculty ON assigned_faculty.id = faculty_load.faculty_id
                   WHERE faculty_load.subject_id = subject.id
                     AND faculty_load.section_id = section.id
                     AND faculty_load.school_year_id = exam.school_year_id
                     AND faculty_load.semester_id = exam.semester_id
               ) AS faculty_names
        FROM cc_exam_schedule exam_schedule
        INNER JOIN cc_exams exam ON exam.id = exam_schedule.exam_id
        INNER JOIN rgr_school_years school_year ON school_year.id = exam.school_year_id
        INNER JOIN rgr_semesters semester ON semester.id = exam.semester_id
            AND semester.school_year_id = exam.school_year_id
        INNER JOIN rgr_subjects subject ON subject.id = exam_schedule.subject_id
            AND subject.archived_at IS NULL
            AND UPPER(subject.code) REGEXP '^(GE.*|FIL [1-3]|NSTP [1-2]|PE [1-2])$'
        INNER JOIN gc_sections section ON section.id = exam_schedule.section_id
        INNER JOIN rgr_courses course ON course.id = section.program_id
        WHERE exam_schedule.schedule_type = 'Exam'
          AND exam_schedule.id = (
              SELECT MIN(duplicate_schedule.id)
              FROM cc_exam_schedule duplicate_schedule
              WHERE duplicate_schedule.exam_id = exam_schedule.exam_id
                AND duplicate_schedule.subject_id = exam_schedule.subject_id
                AND duplicate_schedule.section_id = exam_schedule.section_id
                AND duplicate_schedule.schedule_type = 'Exam'
          )
    ";

    if ($action === 'schedules') {
        $schoolYears = $conn->query("
            SELECT id, name
            FROM rgr_school_years
            ORDER BY id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        $semesters = $conn->query("
            SELECT DISTINCT name
            FROM rgr_semesters
            WHERE name IN ('1st Semester', '2nd Semester')
            ORDER BY name
        ")->fetchAll(PDO::FETCH_COLUMN);
        $departments = $conn->query("
            SELECT id, code, name
            FROM rgr_courses
            ORDER BY code
        ")->fetchAll(PDO::FETCH_ASSOC);
        $subjects = $conn->query("
            SELECT id, code, name
            FROM rgr_subjects
            WHERE archived_at IS NULL
              AND UPPER(code) REGEXP '^(GE.*|FIL [1-3]|NSTP [1-2]|PE [1-2])$'
            ORDER BY code
        ")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $conn->query("
            SELECT section.id, section.section_code, course.id AS department_id,
                   course.code AS department_code, section.grade_level,
                   school_year.name AS school_year, semester.name AS semester
            FROM gc_sections section
            INNER JOIN rgr_courses course ON course.id = section.program_id
            LEFT JOIN rgr_school_years school_year ON school_year.id = section.school_year_id
            LEFT JOIN rgr_semesters semester ON semester.id = section.semester_id
                AND semester.school_year_id = section.school_year_id
            ORDER BY section.school_year_id DESC, section.semester_id DESC,
                     course.code, section.section_code, section.id
        ")->fetchAll(PDO::FETCH_ASSOC);
        $schedules = $conn->query($scheduleQuery . "
            ORDER BY exam.school_year_id DESC, exam.semester_id DESC,
                     exam_schedule.exam_date DESC, exam.exam_name,
                     subject.code, section.section_code
        ")->fetchAll(PDO::FETCH_ASSOC);
        genedItemAnalysisRespond(200, [
            'success' => true,
            'schedules' => $schedules,
            'filters' => [
                'school_years' => $schoolYears,
                'semesters' => $semesters,
                'departments' => $departments,
                'subjects' => $subjects,
                'sections' => $sections,
                'exam_types' => [
                    ['value' => 'Preliminary', 'label' => 'Prelim'],
                    ['value' => 'Midterm', 'label' => 'Midterm'],
                    ['value' => 'Final', 'label' => 'Final Exam'],
                ],
            ],
        ]);
    }

    if ($action === 'details') {
        $scheduleId = filter_var($_GET['schedule_id'] ?? null, FILTER_VALIDATE_INT);
        $departmentFilter = trim((string)($_GET['department_id'] ?? ''));
        $sectionFilter = trim((string)($_GET['section_id'] ?? ''));
        $departmentId = $departmentFilter === '' ? 0 : filter_var($departmentFilter, FILTER_VALIDATE_INT);
        $sectionId = $sectionFilter === '' ? 0 : filter_var($sectionFilter, FILTER_VALIDATE_INT);
        if ($scheduleId === false || $scheduleId === null || $scheduleId <= 0) {
            genedItemAnalysisRespond(422, ['success' => false, 'message' => 'A valid scheduled exam is required.']);
        }
        if ($departmentId === false || $sectionId === false) {
            genedItemAnalysisRespond(422, ['success' => false, 'message' => 'Invalid department or section filter.']);
        }

        $scheduleStmt = $conn->prepare($scheduleQuery . ' AND exam_schedule.id = :schedule_id LIMIT 1');
        $scheduleStmt->execute([':schedule_id' => $scheduleId]);
        $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
        if (!$schedule) {
            genedItemAnalysisRespond(404, ['success' => false, 'message' => 'The selected GenEd exam schedule was not found.']);
        }

        $studentStmt = $conn->prepare("
            SELECT student.student_id,
                   student.student_number,
                   TRIM(CONCAT_WS(' ',
                       applicant.first_name,
                       NULLIF(applicant.middle_name, ''),
                       applicant.surname,
                       NULLIF(applicant.suffix, '')
                   )) AS student_name,
                   score.score
            FROM enr_students student
            LEFT JOIN enr_applicants applicant ON applicant.applicant_id = student.applicant_id
            LEFT JOIN gc_item_analysis_scores score
                ON score.student_id = student.student_id
                AND score.exam_schedule_id = :schedule_id
            WHERE student.section_id = :section_id
              AND student.course_id = :course_id
              AND student.enrollment_status = 'enrolled'
            ORDER BY applicant.surname, applicant.first_name, student.student_number
        ");
        $studentStmt->execute([
            ':schedule_id' => $scheduleId,
            ':section_id' => (int)$schedule['section_id'],
            ':course_id' => (int)$schedule['department_id'],
        ]);
        $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

        $settingsStmt = $conn->prepare("
            SELECT total_items, passing_score
            FROM gc_item_analysis_settings
            WHERE exam_id = :exam_id AND subject_id = :subject_id
            LIMIT 1
        ");
        $settingsStmt->execute([
            ':exam_id' => (int)$schedule['exam_id'],
            ':subject_id' => (int)$schedule['subject_id'],
        ]);
        $settings = $settingsStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $sectionResultsStmt = $conn->prepare("
            SELECT course.code AS department,
                   section.section_code,
                   COUNT(DISTINCT student.student_id) AS total_students,
                   COUNT(DISTINCT CASE
                       WHEN score.score IS NOT NULL THEN student.student_id
                   END) AS scored_students,
                   COUNT(DISTINCT CASE
                       WHEN score.score >= settings.passing_score THEN student.student_id
                   END) AS passed,
                   COUNT(DISTINCT CASE
                       WHEN score.score IS NOT NULL AND score.score < settings.passing_score
                       THEN student.student_id
                   END) AS failed,
                   AVG(score.score) AS average_score
            FROM cc_exam_schedule exam_schedule
            INNER JOIN cc_exams exam ON exam.id = exam_schedule.exam_id
            INNER JOIN gc_sections section ON section.id = exam_schedule.section_id
            INNER JOIN rgr_courses course ON course.id = section.program_id
            LEFT JOIN gc_item_analysis_settings settings
                ON settings.exam_id = exam.id
                AND settings.subject_id = exam_schedule.subject_id
            LEFT JOIN enr_students student
                ON student.section_id = section.id
                AND student.course_id = section.program_id
                AND student.enrollment_status = 'enrolled'
            LEFT JOIN gc_item_analysis_scores score
                ON score.exam_schedule_id = exam_schedule.id
                AND score.student_id = student.student_id
            WHERE exam.id = :exam_id
              AND exam_schedule.subject_id = :subject_id
              AND exam_schedule.schedule_type = 'Exam'
              AND section.school_year_id = exam.school_year_id
              AND section.semester_id = exam.semester_id
              AND exam_schedule.id = (
                  SELECT MIN(duplicate_schedule.id)
                  FROM cc_exam_schedule duplicate_schedule
                  WHERE duplicate_schedule.exam_id = exam_schedule.exam_id
                    AND duplicate_schedule.subject_id = exam_schedule.subject_id
                    AND duplicate_schedule.section_id = exam_schedule.section_id
                    AND duplicate_schedule.schedule_type = 'Exam'
              )
              AND (:department_filter_is_all = 1 OR section.program_id = :department_filter_id)
              AND (:section_filter_is_all = 1 OR section.id = :section_filter_id)
            GROUP BY course.code, section.section_code
            ORDER BY course.code, section.section_code
        ");
        $sectionResultsStmt->execute([
            ':exam_id' => (int)$schedule['exam_id'],
            ':subject_id' => (int)$schedule['subject_id'],
            ':department_filter_is_all' => $departmentId ? 0 : 1,
            ':department_filter_id' => (int)$departmentId,
            ':section_filter_is_all' => $sectionId ? 0 : 1,
            ':section_filter_id' => (int)$sectionId,
        ]);
        $sectionResults = $sectionResultsStmt->fetchAll(PDO::FETCH_ASSOC);
        $summary = [
            'total_students' => 0,
            'scored_students' => 0,
            'passed' => 0,
            'failed' => 0,
            'sections' => count($sectionResults),
            'average_score' => null,
        ];
        $scoreTotal = 0.0;
        foreach ($sectionResults as $sectionResult) {
            foreach (['total_students', 'scored_students', 'passed', 'failed'] as $key) {
                $summary[$key] += (int)$sectionResult[$key];
            }
            if ($sectionResult['average_score'] !== null) {
                $scoreTotal += (float)$sectionResult['average_score'] * (int)$sectionResult['scored_students'];
            }
        }
        $summary['passing_rate'] = $summary['scored_students'] > 0
            ? round($summary['passed'] / $summary['scored_students'] * 100, 2)
            : null;
        $summary['failure_rate'] = $summary['scored_students'] > 0
            ? round($summary['failed'] / $summary['scored_students'] * 100, 2)
            : null;
        $summary['average_score'] = $summary['scored_students'] > 0
            ? round($scoreTotal / $summary['scored_students'], 2)
            : null;

        genedItemAnalysisRespond(200, [
            'success' => true,
            'schedule' => $schedule,
            'students' => $students,
            'settings' => $settings,
            'summary' => $summary,
            'section_results' => $sectionResults,
            'score_data_available' => $summary['scored_students'] > 0,
            'item_response_data_available' => false,
        ]);
    }

    if ($action === 'save_scores') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            genedItemAnalysisRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            genedItemAnalysisRespond(400, ['success' => false, 'message' => 'Invalid score data.']);
        }
        $scheduleId = filter_var($input['schedule_id'] ?? null, FILTER_VALIDATE_INT);
        $totalItems = filter_var($input['total_items'] ?? null, FILTER_VALIDATE_INT);
        $passingScore = filter_var($input['passing_score'] ?? null, FILTER_VALIDATE_FLOAT);
        $scores = $input['scores'] ?? null;
        if ($scheduleId === false || !$scheduleId || $totalItems === false || $totalItems < 1
            || $passingScore === false || $passingScore < 0 || $passingScore > $totalItems
            || !is_array($scores)) {
            genedItemAnalysisRespond(422, [
                'success' => false,
                'message' => 'Enter valid total items, a passing score within the exam range, and student scores.',
            ]);
        }

        $scheduleStmt = $conn->prepare($scheduleQuery . ' AND exam_schedule.id = :schedule_id LIMIT 1');
        $scheduleStmt->execute([':schedule_id' => $scheduleId]);
        $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
        if (!$schedule) {
            genedItemAnalysisRespond(404, ['success' => false, 'message' => 'The selected GenEd exam schedule was not found.']);
        }

        $conn->beginTransaction();
        $settingsStmt = $conn->prepare("
            INSERT INTO gc_item_analysis_settings
                (exam_id, subject_id, total_items, passing_score, updated_by)
            VALUES
                (:exam_id, :subject_id, :total_items, :passing_score, :updated_by)
            ON DUPLICATE KEY UPDATE
                total_items = VALUES(total_items),
                passing_score = VALUES(passing_score),
                updated_by = VALUES(updated_by)
        ");
        $settingsStmt->execute([
            ':exam_id' => (int)$schedule['exam_id'],
            ':subject_id' => (int)$schedule['subject_id'],
            ':total_items' => $totalItems,
            ':passing_score' => $passingScore,
            ':updated_by' => (int)$_SESSION['employee_id'],
        ]);

        $studentCheck = $conn->prepare("
            SELECT student_id
            FROM enr_students
            WHERE student_id = :student_id
              AND section_id = :section_id
              AND course_id = :course_id
              AND enrollment_status = 'enrolled'
            LIMIT 1
        ");
        $scoreStmt = $conn->prepare("
            INSERT INTO gc_item_analysis_scores
                (exam_schedule_id, student_id, score, entered_by)
            VALUES
                (:schedule_id, :student_id, :score, :entered_by)
            ON DUPLICATE KEY UPDATE
                score = VALUES(score),
                entered_by = VALUES(entered_by)
        ");
        $savedScores = 0;
        foreach ($scores as $scoreRow) {
            if (!is_array($scoreRow)) {
                throw new InvalidArgumentException('Invalid student score entry.');
            }
            $studentId = filter_var($scoreRow['student_id'] ?? null, FILTER_VALIDATE_INT);
            $scoreValue = $scoreRow['score'] ?? null;
            if ($studentId === false || !$studentId || $scoreValue === null || $scoreValue === '') {
                continue;
            }
            $scoreValue = filter_var($scoreValue, FILTER_VALIDATE_FLOAT);
            if ($scoreValue === false || $scoreValue < 0 || $scoreValue > $totalItems) {
                throw new InvalidArgumentException('Each student score must be between zero and total items.');
            }
            $studentCheck->execute([
                ':student_id' => $studentId,
                ':section_id' => (int)$schedule['section_id'],
                ':course_id' => (int)$schedule['department_id'],
            ]);
            if (!$studentCheck->fetchColumn()) {
                throw new InvalidArgumentException('A submitted student is not currently enrolled in the selected section.');
            }
            $scoreStmt->execute([
                ':schedule_id' => $scheduleId,
                ':student_id' => $studentId,
                ':score' => $scoreValue,
                ':entered_by' => (int)$_SESSION['employee_id'],
            ]);
            $savedScores++;
        }
        $conn->commit();
        genedItemAnalysisRespond(200, [
            'success' => true,
            'message' => "Exam settings saved and {$savedScores} student score(s) recorded.",
            'saved_scores' => $savedScores,
        ]);
    }

    genedItemAnalysisRespond(400, ['success' => false, 'message' => 'Unsupported item analysis request.']);
} catch (InvalidArgumentException $error) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    genedItemAnalysisRespond(422, ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('GenEd item analysis API error: ' . $error->getMessage());
    genedItemAnalysisRespond(500, ['success' => false, 'message' => 'Unable to load GenEd item analysis data.']);
}
