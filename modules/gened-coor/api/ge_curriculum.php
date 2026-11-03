<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once(__DIR__ . '/../../../database/db.php');

function geCurriculumRespond(int $status, array $data): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function geCurriculumSubjectExists(PDO $conn, int $subjectId): bool {
    $stmt = $conn->prepare("SELECT id FROM rgr_subjects WHERE id = :id AND UPPER(code) LIKE 'GE%' LIMIT 1");
    $stmt->execute([':id' => $subjectId]);
    return (bool)$stmt->fetchColumn();
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    if (!$conn) {
        throw new RuntimeException('Database connection failed.');
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $subjectsStmt = $conn->query("SELECT id, code, name, description, units, lecture_hours, lab_hours FROM rgr_subjects WHERE UPPER(code) LIKE 'GE%' ORDER BY code");
        $coursesStmt = $conn->query("SELECT cu.id AS curriculum_id, cu.course_id, cu.curriculum_name, cu.effective_year, cu.is_active, c.code AS course_code, c.name AS course_name FROM rgr_curriculums cu LEFT JOIN rgr_courses c ON c.id = cu.course_id ORDER BY c.code, cu.effective_year DESC, cu.id DESC");
        $prerequisitesStmt = $conn->query("SELECT p.id, s.id AS subject_id, s.code AS subject_code, s.name AS subject_name, prerequisite.id AS prerequisite_subject_id, prerequisite.code AS prerequisite_code, prerequisite.name AS prerequisite_name FROM enr_prerequisites p INNER JOIN rgr_subjects s ON s.id = p.subject_id INNER JOIN rgr_subjects prerequisite ON prerequisite.id = p.prerequisite_subject_id WHERE UPPER(s.code) LIKE 'GE%' ORDER BY s.code, prerequisite.code");
        $mappingsStmt = $conn->query("SELECT cs.id, cs.curriculum_id, cu.curriculum_name, cu.effective_year, c.code AS course_code, s.id AS subject_id, s.code AS subject_code, s.name AS subject_name, s.units, cs.year_level, cs.semester FROM rgr_curriculum_subjects cs INNER JOIN rgr_curriculums cu ON cu.id = cs.curriculum_id LEFT JOIN rgr_courses c ON c.id = cu.course_id INNER JOIN rgr_subjects s ON s.id = cs.subject_id WHERE UPPER(s.code) LIKE 'GE%' ORDER BY c.code, cu.effective_year DESC, cs.year_level, cs.semester, s.code");

        geCurriculumRespond(200, [
            'success' => true,
            'subjects' => $subjectsStmt->fetchAll(PDO::FETCH_ASSOC),
            'curriculums' => $coursesStmt->fetchAll(PDO::FETCH_ASSOC),
            'prerequisites' => $prerequisitesStmt->fetchAll(PDO::FETCH_ASSOC),
            'mappings' => $mappingsStmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        geCurriculumRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        geCurriculumRespond(400, ['success' => false, 'message' => 'Invalid request data.']);
    }

    $action = (string)($input['action'] ?? '');

    if ($action === 'save_subject') {
        $subjectId = (int)($input['id'] ?? 0);
        $code = strtoupper(trim((string)($input['code'] ?? '')));
        $name = trim((string)($input['name'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $units = filter_var($input['units'] ?? null, FILTER_VALIDATE_INT);
        $lectureHours = filter_var($input['lecture_hours'] ?? null, FILTER_VALIDATE_INT);
        $labHours = filter_var($input['lab_hours'] ?? null, FILTER_VALIDATE_INT);

        if (!preg_match('/^GE[A-Z0-9-]{1,248}$/', $code) || $name === '' || $units === false || $units < 0 || $lectureHours === false || $lectureHours < 0 || $labHours === false || $labHours < 0) {
            geCurriculumRespond(422, ['success' => false, 'message' => 'Enter a GE code, subject name, and valid unit/hour values.']);
        }

        $duplicateStmt = $conn->prepare('SELECT id FROM rgr_subjects WHERE code = :code AND id != :id LIMIT 1');
        $duplicateStmt->execute([':code' => $code, ':id' => $subjectId]);
        if ($duplicateStmt->fetchColumn()) {
            geCurriculumRespond(409, ['success' => false, 'message' => 'That subject code is already in use.']);
        }

        if ($subjectId > 0) {
            if (!geCurriculumSubjectExists($conn, $subjectId)) {
                geCurriculumRespond(404, ['success' => false, 'message' => 'GE subject not found.']);
            }
            $stmt = $conn->prepare('UPDATE rgr_subjects SET code = :code, name = :name, description = :description, units = :units, lecture_hours = :lecture_hours, lab_hours = :lab_hours WHERE id = :id');
            $stmt->execute([':code' => $code, ':name' => $name, ':description' => $description, ':units' => $units, ':lecture_hours' => $lectureHours, ':lab_hours' => $labHours, ':id' => $subjectId]);
            geCurriculumRespond(200, ['success' => true, 'message' => 'GE subject updated.']);
        }

        $stmt = $conn->prepare('INSERT INTO rgr_subjects (code, name, description, units, lecture_hours, lab_hours) VALUES (:code, :name, :description, :units, :lecture_hours, :lab_hours)');
        $stmt->execute([':code' => $code, ':name' => $name, ':description' => $description, ':units' => $units, ':lecture_hours' => $lectureHours, ':lab_hours' => $labHours]);
        geCurriculumRespond(201, ['success' => true, 'message' => 'GE subject added.']);
    }

    if ($action === 'delete_subject') {
        $subjectId = (int)($input['id'] ?? 0);
        if ($subjectId <= 0 || !geCurriculumSubjectExists($conn, $subjectId)) {
            geCurriculumRespond(404, ['success' => false, 'message' => 'GE subject not found.']);
        }
        $dependencyStmt = $conn->prepare('SELECT (SELECT COUNT(*) FROM rgr_curriculum_subjects WHERE subject_id = :subject_id) + (SELECT COUNT(*) FROM cc_faculty_load WHERE subject_id = :subject_id) AS dependency_count');
        $dependencyStmt->execute([':subject_id' => $subjectId]);
        if ((int)$dependencyStmt->fetchColumn() > 0) {
            geCurriculumRespond(409, ['success' => false, 'message' => 'Remove curriculum mappings and faculty assignments before deleting this subject.']);
        }
        $conn->beginTransaction();
        $deletePrerequisites = $conn->prepare('DELETE FROM enr_prerequisites WHERE subject_id = :subject_id OR prerequisite_subject_id = :subject_id');
        $deletePrerequisites->execute([':subject_id' => $subjectId]);
        $deleteSubject = $conn->prepare('DELETE FROM rgr_subjects WHERE id = :id');
        $deleteSubject->execute([':id' => $subjectId]);
        $conn->commit();
        geCurriculumRespond(200, ['success' => true, 'message' => 'GE subject deleted.']);
    }

    if ($action === 'save_prerequisite') {
        $subjectId = (int)($input['subject_id'] ?? 0);
        $prerequisiteId = (int)($input['prerequisite_subject_id'] ?? 0);
        if ($subjectId <= 0 || $prerequisiteId <= 0 || $subjectId === $prerequisiteId || !geCurriculumSubjectExists($conn, $subjectId) || !geCurriculumSubjectExists($conn, $prerequisiteId)) {
            geCurriculumRespond(422, ['success' => false, 'message' => 'Choose two different GE subjects.']);
        }
        $duplicateStmt = $conn->prepare('SELECT id FROM enr_prerequisites WHERE subject_id = :subject_id AND prerequisite_subject_id = :prerequisite_subject_id LIMIT 1');
        $duplicateStmt->execute([':subject_id' => $subjectId, ':prerequisite_subject_id' => $prerequisiteId]);
        if ($duplicateStmt->fetchColumn()) {
            geCurriculumRespond(409, ['success' => false, 'message' => 'That prerequisite is already mapped.']);
        }
        $stmt = $conn->prepare('INSERT INTO enr_prerequisites (subject_id, prerequisite_subject_id) VALUES (:subject_id, :prerequisite_subject_id)');
        $stmt->execute([':subject_id' => $subjectId, ':prerequisite_subject_id' => $prerequisiteId]);
        geCurriculumRespond(201, ['success' => true, 'message' => 'Prerequisite added.']);
    }

    if ($action === 'delete_prerequisite') {
        $stmt = $conn->prepare("DELETE p FROM enr_prerequisites p INNER JOIN rgr_subjects s ON s.id = p.subject_id WHERE p.id = :id AND UPPER(s.code) LIKE 'GE%'");
        $stmt->execute([':id' => (int)($input['id'] ?? 0)]);
        geCurriculumRespond(200, ['success' => true, 'message' => 'Prerequisite removed.']);
    }

    if ($action === 'save_mapping') {
        $curriculumId = (int)($input['curriculum_id'] ?? 0);
        $subjectId = (int)($input['subject_id'] ?? 0);
        $yearLevel = (int)($input['year_level'] ?? 0);
        $semester = trim((string)($input['semester'] ?? ''));
        if ($curriculumId <= 0 || $subjectId <= 0 || $yearLevel < 1 || $yearLevel > 4 || !in_array($semester, ['First', 'Second', 'Summer'], true) || !geCurriculumSubjectExists($conn, $subjectId)) {
            geCurriculumRespond(422, ['success' => false, 'message' => 'Select a curriculum, GE subject, year level, and semester.']);
        }
        $curriculumStmt = $conn->prepare('SELECT id FROM rgr_curriculums WHERE id = :id LIMIT 1');
        $curriculumStmt->execute([':id' => $curriculumId]);
        if (!$curriculumStmt->fetchColumn()) {
            geCurriculumRespond(404, ['success' => false, 'message' => 'Curriculum not found.']);
        }
        $duplicateStmt = $conn->prepare('SELECT id FROM rgr_curriculum_subjects WHERE curriculum_id = :curriculum_id AND subject_id = :subject_id LIMIT 1');
        $duplicateStmt->execute([':curriculum_id' => $curriculumId, ':subject_id' => $subjectId]);
        if ($duplicateStmt->fetchColumn()) {
            geCurriculumRespond(409, ['success' => false, 'message' => 'This subject is already mapped to the selected curriculum.']);
        }
        $stmt = $conn->prepare('INSERT INTO rgr_curriculum_subjects (curriculum_id, subject_id, year_level, semester) VALUES (:curriculum_id, :subject_id, :year_level, :semester)');
        $stmt->execute([':curriculum_id' => $curriculumId, ':subject_id' => $subjectId, ':year_level' => $yearLevel, ':semester' => $semester]);
        geCurriculumRespond(201, ['success' => true, 'message' => 'Curriculum mapping added.']);
    }

    if ($action === 'delete_mapping') {
        $stmt = $conn->prepare("DELETE cs FROM rgr_curriculum_subjects cs INNER JOIN rgr_subjects s ON s.id = cs.subject_id WHERE cs.id = :id AND UPPER(s.code) LIKE 'GE%'");
        $stmt->execute([':id' => (int)($input['id'] ?? 0)]);
        geCurriculumRespond(200, ['success' => true, 'message' => 'Curriculum mapping removed.']);
    }

    geCurriculumRespond(400, ['success' => false, 'message' => 'Unknown action.']);
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('GE curriculum API error: ' . $e->getMessage());
    geCurriculumRespond(500, ['success' => false, 'message' => 'Unable to process the GE curriculum request.']);
}