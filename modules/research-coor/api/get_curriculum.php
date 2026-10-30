<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once(__DIR__ . '/../classes/ProgramManager.php');
require_once(__DIR__ . '/../../../database/db.php');

header('Content-Type: application/json; charset=utf-8');

function sendJsonResponse(int $statusCode, array $payload): void {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$courseIdRaw = $_GET['course_id'] ?? $_GET['program_id'] ?? null;
if ($courseIdRaw === null || $courseIdRaw === '' || $courseIdRaw === 'N/A' || $courseIdRaw === 'null') {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'Course ID is required.'
    ]);
}

$courseId = filter_var($courseIdRaw, FILTER_VALIDATE_INT);
if ($courseId === false || (int)$courseId <= 0) {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'Invalid course ID.'
    ]);
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $programManager = new ProgramManager($conn);

    $courseStmt = $conn->prepare("SELECT id, code, name, years FROM rgr_courses WHERE id = :id");
    $courseStmt->execute([':id' => $courseId]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        sendJsonResponse(404, [
            'success' => false,
            'message' => 'Program not found.'
        ]);
    }

    $curriculumRow = $programManager->getCourseCurriculum($courseId);
    if (!$curriculumRow) {
        sendJsonResponse(200, [
            'success' => false,
            'message' => 'No curriculum found for this program.',
            'program_name' => $course['name'],
            'curriculum_name' => 'No curriculum data found',
            'effective_year' => 'No curriculum data found',
            'status' => 'No curriculum data found',
            'subject_flow' => [],
            'enrollment_summary' => [
                'total_enrolled_students' => 0,
                'year_levels' => [
                    'first_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0],
                    'second_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0],
                    'third_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0],
                    'fourth_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0]
                ]
            ]
        ]);
    }

    $curriculumId = (int)($curriculumRow['id'] ?? 0);
    $curriculum = $programManager->getCurriculum($courseId, $curriculumId);

    $subjectCountStmt = $conn->prepare("SELECT COUNT(*) AS subject_count
        FROM rgr_curriculum_subjects cs
        INNER JOIN rgr_curriculums cu ON cu.id = cs.curriculum_id
        WHERE cu.course_id = :courseId AND cs.curriculum_id = :curriculumId");
    $subjectCountStmt->execute([':courseId' => $courseId, ':curriculumId' => $curriculumId]);
    $subjectCountRow = $subjectCountStmt->fetch(PDO::FETCH_ASSOC);

    $summaryStmt = $conn->prepare("SELECT
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 1 THEN 1 ELSE 0 END) AS first_year_count,
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 2 THEN 1 ELSE 0 END) AS second_year_count,
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 3 THEN 1 ELSE 0 END) AS third_year_count,
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 4 THEN 1 ELSE 0 END) AS fourth_year_count,
            COUNT(*) AS total_enrolled_students
        FROM enr_students es
        WHERE es.course_id = :courseId
          AND es.enrollment_status = 'enrolled'");
    $summaryStmt->execute([':courseId' => $courseId]);
    $summaryRow = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    $unitSummaryStmt = $conn->prepare("SELECT
            cs.year_level,
            COALESCE(SUM(rs.units), 0) AS total_curriculum_units
        FROM rgr_curriculum_subjects cs
        INNER JOIN rgr_subjects rs ON rs.id = cs.subject_id
        WHERE cs.curriculum_id = :curriculumId
        GROUP BY cs.year_level");
    $unitSummaryStmt->execute([':curriculumId' => $curriculumId]);
    $unitSummaryRows = $unitSummaryStmt->fetchAll(PDO::FETCH_ASSOC);

    $yearUnitMap = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    foreach ($unitSummaryRows as $row) {
        $yearLevel = (int)($row['year_level'] ?? 0);
        if (isset($yearUnitMap[$yearLevel])) {
            $yearUnitMap[$yearLevel] = (int)($row['total_curriculum_units'] ?? 0);
        }
    }

    $status = ((int)($curriculumRow['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive';

    sendJsonResponse(200, [
        'success' => true,
        'program_name' => $course['name'],
        'curriculum_name' => $curriculumRow['curriculum_name'] ?? 'No curriculum data found',
        'effective_year' => (string)($curriculumRow['effective_year'] ?? 'No curriculum data found'),
        'status' => $status,
        'subject_flow' => $curriculum,
        'enrollment_summary' => [
            'total_enrolled_students' => (int)($summaryRow['total_enrolled_students'] ?? 0),
            'year_levels' => [
                'first_year' => [
                    'enrolled_students' => (int)($summaryRow['first_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[1]
                ],
                'second_year' => [
                    'enrolled_students' => (int)($summaryRow['second_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[2]
                ],
                'third_year' => [
                    'enrolled_students' => (int)($summaryRow['third_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[3]
                ],
                'fourth_year' => [
                    'enrolled_students' => (int)($summaryRow['fourth_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[4]
                ]
            ]
        ],
        'debug' => [
            'course_id' => $courseId,
            'curriculum_id' => $curriculumId,
            'curriculum_lookup' => 'found',
            'mapped_subject_count' => (int)($subjectCountRow['subject_count'] ?? 0)
        ]
    ]);
} catch (Throwable $e) {
    error_log('get_curriculum exception: ' . $e->getMessage());
    sendJsonResponse(500, [
        'success' => false,
        'message' => 'Unable to load curriculum details.'
    ]);
}
?>
