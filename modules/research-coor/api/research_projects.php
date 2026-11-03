<?php
require_once dirname(__DIR__, 3) . '/auth/guard.php';
require_once dirname(__DIR__, 3) . '/database/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function enumValues(string $columnType): array
{
    if (!preg_match('/^enum\((.*)\)$/i', $columnType, $matches)) {
        return [];
    }

    return str_getcsv($matches[1], ',', "'", '\\');
}

function columnTypes(PDO $connection): array
{
    $types = [];
    foreach ($connection->query('SHOW COLUMNS FROM rc_projects')->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $types[$column['Field']] = $column['Type'];
    }
    return $types;
}

function projectRows(PDO $connection): array
{
    $stmt = $connection->query("
        SELECT
            rp.id,
            rp.registration_no,
            rp.project_title,
            rp.category,
            rp.classification,
            rp.student_id,
            rp.timeline,
            rp.status,
            rp.created_at,
            TRIM(CONCAT_WS(' ', ea.first_name, NULLIF(ea.middle_name, ''), ea.surname)) AS leader_name,
            ea.email AS leader_email,
            ea.contact_number AS leader_contact,
            rc.name AS course_name
        FROM rc_projects rp
        LEFT JOIN enr_students es ON es.student_id = rp.student_id
        LEFT JOIN enr_applicants ea ON ea.applicant_id = es.applicant_id
        LEFT JOIN rgr_courses rc ON rc.id = es.course_id
        ORDER BY rp.created_at DESC, rp.id DESC
    ");

    return array_map(static function (array $row): array {
        $timeline = (string) ($row['timeline'] ?? '');
        $startDate = '';
        $targetDate = '';
        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+to\s+(\d{4}-\d{2}-\d{2})$/', $timeline, $dates)) {
            $startDate = $dates[1];
            $targetDate = $dates[2];
        }

        return [
            'id' => (int) $row['id'],
            'registrationNo' => $row['registration_no'],
            'title' => $row['project_title'],
            'studentId' => (int) $row['student_id'],
            'investigator' => $row['leader_name'] ?: 'Student #' . $row['student_id'],
            'leaderEmail' => $row['leader_email'] ?? '',
            'leaderContact' => $row['leader_contact'] ?? '',
            'leaderDepartment' => $row['course_name'] ?? '',
            'category' => $row['category'],
            'classification' => $row['classification'],
            'timeline' => $timeline,
            'startDate' => $startDate,
            'targetDate' => $targetDate,
            'status' => $row['status'] ?: 'Draft',
            'registeredAt' => $row['created_at'] ? substr($row['created_at'], 0, 10) : '',
            'progress' => $row['status'] === 'Completed' ? 100 : 0,
            'description' => '',
            'abstract' => '',
            'researchers' => [],
            'adviser' => '',
            'adviserEmail' => '',
            'completionDate' => '',
            'completionNotes' => '',
            'milestones' => [],
            'monitoringLogs' => [],
            'archived' => false,
            'archivedAt' => '',
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

function requestData(): array
{
    try {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        respond(400, ['success' => false, 'message' => 'Invalid JSON request body.']);
    }

    if (!is_array($data)) {
        respond(400, ['success' => false, 'message' => 'The request body must be an object.']);
    }
    return $data;
}

function requiredValue(array $data, string $key, string $label, int $maxLength): string
{
    $value = trim((string) ($data[$key] ?? ''));
    if ($value === '' || mb_strlen($value) > $maxLength) {
        respond(422, ['success' => false, 'message' => "{$label} is required and must be at most {$maxLength} characters."]);
    }
    return $value;
}

try {
    $database = new Database();
    $connection = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $types = columnTypes($connection);
        $students = $connection->query("
            SELECT
                es.student_id AS id,
                COALESCE(NULLIF(es.student_number, ''), CONCAT('ID ', es.student_id)) AS studentNumber,
                TRIM(CONCAT_WS(' ', ea.first_name, NULLIF(ea.middle_name, ''), ea.surname)) AS name,
                ea.email,
                ea.contact_number AS contact,
                rc.name AS course
            FROM enr_students es
            LEFT JOIN enr_applicants ea ON ea.applicant_id = es.applicant_id
            LEFT JOIN rgr_courses rc ON rc.id = es.course_id
            ORDER BY ea.surname, ea.first_name, es.student_number, es.student_id
        ")->fetchAll(PDO::FETCH_ASSOC);

        respond(200, [
            'success' => true,
            'projects' => projectRows($connection),
            'students' => $students,
            'options' => [
                'categories' => enumValues($types['category'] ?? ''),
                'classifications' => enumValues($types['classification'] ?? ''),
                'statuses' => enumValues($types['status'] ?? ''),
            ],
        ]);
    }

    if (!in_array($method, ['POST', 'PUT'], true)) {
        header('Allow: GET, POST, PUT');
        respond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $data = requestData();
    $types = columnTypes($connection);
    $categories = enumValues($types['category'] ?? '');
    $classifications = enumValues($types['classification'] ?? '');
    $statuses = enumValues($types['status'] ?? '');

    $title = requiredValue($data, 'title', 'Project title', 255);
    $category = requiredValue($data, 'category', 'Research category', 100);
    $classification = requiredValue($data, 'classification', 'Classification', 100);
    $status = requiredValue($data, 'status', 'Project status', 40);
    if (!in_array($category, $categories, true) ||
        !in_array($classification, $classifications, true) ||
        !in_array($status, $statuses, true)) {
        respond(422, ['success' => false, 'message' => 'Select a category, classification, and status available in rc_projects.']);
    }

    $studentId = filter_var($data['studentId'] ?? null, FILTER_VALIDATE_INT);
    if (!$studentId || $studentId < 1) {
        respond(422, ['success' => false, 'message' => 'Pumili ng Project Leader mula sa student records.']);
    }
    $studentCheck = $connection->prepare("
        SELECT student_id
        FROM enr_students
        WHERE student_id = :student_id
    ");
    $studentCheck->execute(['student_id' => $studentId]);
    if (!$studentCheck->fetchColumn()) {
        respond(422, ['success' => false, 'message' => 'Hindi na makita ang napiling Project Leader sa enr_students.']);
    }

    $timeline = requiredValue($data, 'timeline', 'Project timeline', 100);

    if ($method === 'POST') {
        $projectId = 'RSP-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(6)));
        $stmt = $connection->prepare("
            INSERT INTO rc_projects
                (registration_no, project_title, category, classification, student_id, timeline, status)
            VALUES
                (:registration_no, :project_title, :category, :classification, :student_id, :timeline, :status)
        ");
        $stmt->execute([
            'registration_no' => $projectId,
            'project_title' => $title,
            'category' => $category,
            'classification' => $classification,
            'student_id' => $studentId,
            'timeline' => $timeline,
            'status' => $status,
        ]);
        respond(200, ['success' => true, 'id' => (int) $connection->lastInsertId()]);
    }

    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        respond(422, ['success' => false, 'message' => 'A valid project ID is required for updates.']);
    }
    $stmt = $connection->prepare("
        UPDATE rc_projects
        SET project_title = :project_title,
            category = :category,
            classification = :classification,
            student_id = :student_id,
            timeline = :timeline,
            status = :status
        WHERE id = :id
    ");
    $stmt->execute([
        'project_title' => $title,
        'category' => $category,
        'classification' => $classification,
        'student_id' => $studentId,
        'timeline' => $timeline,
        'status' => $status,
        'id' => $id,
    ]);
    $exists = $connection->prepare('SELECT id FROM rc_projects WHERE id = :id');
    $exists->execute(['id' => $id]);
    if (!$exists->fetchColumn()) {
        respond(404, ['success' => false, 'message' => 'Research project not found.']);
    }
    respond(200, ['success' => true, 'id' => $id]);
} catch (Throwable $error) {
    error_log('Research project API error: ' . $error->getMessage());
    respond(500, ['success' => false, 'message' => 'Hindi naproseso ang research project. Suriin ang database configuration at subukang muli.']);
}
