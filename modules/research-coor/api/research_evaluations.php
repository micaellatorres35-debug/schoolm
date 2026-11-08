<?php
require_once dirname(__DIR__, 3) . '/auth/guard.php';
require_once dirname(__DIR__, 3) . '/database/db.php';

header('Content-Type: application/json; charset=utf-8');

function evaluationRespond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function evaluationRequest(): array
{
    try {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        evaluationRespond(400, ['success' => false, 'message' => 'Invalid JSON request body.']);
    }
    if (!is_array($data)) {
        evaluationRespond(400, ['success' => false, 'message' => 'The request body must be an object.']);
    }
    return $data;
}

function evaluationText(array $data, string $key, string $label, int $maxLength, bool $required = false): string
{
    $value = trim((string) ($data[$key] ?? ''));
    if (($required && $value === '') || mb_strlen($value) > $maxLength) {
        evaluationRespond(422, ['success' => false, 'message' => "{$label} is required and must be at most {$maxLength} characters."]);
    }
    return $value;
}

function evaluationDate(array $data, string $key, string $label, bool $required = false): ?string
{
    $value = trim((string) ($data[$key] ?? ''));
    if ($value === '') {
        if ($required) {
            evaluationRespond(422, ['success' => false, 'message' => "{$label} is required."]);
        }
        return null;
    }
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        evaluationRespond(422, ['success' => false, 'message' => "{$label} must be a valid date."]);
    }
    return $value;
}

function ensureEvaluationTables(PDO $connection): void
{
    $connection->exec("
        CREATE TABLE IF NOT EXISTS rc_evaluation_forms (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            stage VARCHAR(40) NOT NULL,
            criteria_json TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_rc_evaluation_form_name_stage (name, stage)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $connection->exec("
        CREATE TABLE IF NOT EXISTS rc_evaluations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            form_id INT UNSIGNED NULL,
            panel_member VARCHAR(150) NOT NULL,
            panel_role VARCHAR(80) NOT NULL DEFAULT '',
            evaluation_date DATE NOT NULL,
            score DECIMAL(5,2) NULL,
            recommendation VARCHAR(40) NOT NULL,
            criteria_results TEXT NOT NULL,
            comments TEXT NOT NULL,
            recommendations TEXT NOT NULL,
            revision_requirements TEXT NOT NULL,
            revision_status VARCHAR(20) NOT NULL DEFAULT 'Not Required',
            revision_due_date DATE NULL,
            defense_date DATE NULL,
            defense_outcome VARCHAR(40) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_rc_evaluations_project (project_id),
            KEY idx_rc_evaluations_form (form_id),
            KEY idx_rc_evaluations_due_date (revision_due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $hasCriteriaResults = $connection->query("SHOW COLUMNS FROM rc_evaluations LIKE 'criteria_results'")->fetch(PDO::FETCH_ASSOC);
    if (!$hasCriteriaResults) {
        $connection->exec("ALTER TABLE rc_evaluations ADD COLUMN criteria_results TEXT NULL");
        $connection->exec("UPDATE rc_evaluations SET criteria_results = '[]' WHERE criteria_results IS NULL");
        $connection->exec("ALTER TABLE rc_evaluations MODIFY criteria_results TEXT NOT NULL");
    }

    if ((int) $connection->query('SELECT COUNT(*) FROM rc_evaluation_forms')->fetchColumn() === 0) {
        $insert = $connection->prepare('INSERT IGNORE INTO rc_evaluation_forms (name, stage, criteria_json) VALUES (:name, :stage, :criteria)');
        foreach ([
            ['Proposal Defense Evaluation', 'Proposal Defense', ['Research problem and objectives', 'Literature review', 'Methodology', 'Feasibility and ethics', 'Presentation and response to questions']],
            ['Progress Review Evaluation', 'Progress Review', ['Progress against objectives', 'Research methods and data', 'Challenges and mitigation', 'Work plan and timeline', 'Presentation and response to questions']],
            ['Final Defense Evaluation', 'Final Defense', ['Research contribution', 'Results and analysis', 'Conclusions and recommendations', 'Organization and writing', 'Presentation and response to questions']],
        ] as [$name, $stage, $criteria]) {
            $insert->execute(['name' => $name, 'stage' => $stage, 'criteria' => json_encode($criteria, JSON_UNESCAPED_UNICODE)]);
        }
    }
}

function evaluationOptions(): array
{
    return [
        'recommendations' => ['Pending', 'Passed', 'Passed with Revisions', 'Re-defense Required', 'Failed'],
        'revisionStatuses' => ['Not Required', 'Pending', 'In Progress', 'Completed'],
        'defenseOutcomes' => ['Scheduled', 'Passed', 'Passed with Revisions', 'Re-defense Required', 'Failed', 'Not Applicable'],
        'stages' => ['Proposal Defense', 'Progress Review', 'Final Defense', 'Other'],
    ];
}

function validChoice(string $value, array $choices, string $label): string
{
    if (!in_array($value, $choices, true)) {
        evaluationRespond(422, ['success' => false, 'message' => "Select a valid {$label}."]);
    }
    return $value;
}

try {
    $database = new Database();
    $connection = $database->getConnection();
    ensureEvaluationTables($connection);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $projects = $connection->query("
            SELECT id, registration_no AS registrationNo, project_title AS title, status
            FROM rc_projects
            ORDER BY created_at DESC, id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        $forms = $connection->query('SELECT id, name, stage, criteria_json FROM rc_evaluation_forms ORDER BY stage, name')->fetchAll(PDO::FETCH_ASSOC);
        $forms = array_map(static function (array $form): array {
            $form['id'] = (int) $form['id'];
            $form['criteria'] = json_decode($form['criteria_json'], true) ?: [];
            unset($form['criteria_json']);
            return $form;
        }, $forms);
        $evaluations = $connection->query("
            SELECT e.id, e.project_id AS projectId, p.registration_no AS registrationNo,
                p.project_title AS projectTitle, p.status AS projectStatus,
                e.form_id AS formId, f.name AS formName, f.stage AS formStage,
                e.panel_member AS panelMember, e.panel_role AS panelRole,
                e.evaluation_date AS evaluationDate, e.score,
                e.recommendation, e.criteria_results AS criteriaResults,
                e.comments, e.recommendations,
                e.revision_requirements AS revisionRequirements,
                e.revision_status AS revisionStatus,
                e.revision_due_date AS revisionDueDate, e.defense_date AS defenseDate,
                e.defense_outcome AS defenseOutcome
            FROM rc_evaluations e
            INNER JOIN rc_projects p ON p.id = e.project_id
            LEFT JOIN rc_evaluation_forms f ON f.id = e.form_id
            ORDER BY e.evaluation_date DESC, e.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($evaluations as &$evaluation) {
            $evaluation['id'] = (int) $evaluation['id'];
            $evaluation['projectId'] = (int) $evaluation['projectId'];
            $evaluation['formId'] = $evaluation['formId'] === null ? null : (int) $evaluation['formId'];
            $evaluation['score'] = $evaluation['score'] === null ? null : (float) $evaluation['score'];
            $evaluation['criteriaResults'] = json_decode($evaluation['criteriaResults'], true) ?: [];
            $evaluation['revisionOverdue'] = $evaluation['revisionStatus'] !== 'Completed'
                && $evaluation['revisionStatus'] !== 'Not Required'
                && !empty($evaluation['revisionDueDate'])
                && $evaluation['revisionDueDate'] < date('Y-m-d');
        }
        unset($evaluation);
        evaluationRespond(200, ['success' => true, 'projects' => $projects, 'forms' => $forms, 'evaluations' => $evaluations, 'options' => evaluationOptions()]);
    }

    if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        header('Allow: GET, POST, PUT, DELETE');
        evaluationRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $data = evaluationRequest();
    $type = (string) ($data['type'] ?? '');
    if (!in_array($type, ['evaluation', 'form'], true)) {
        evaluationRespond(422, ['success' => false, 'message' => 'Select an evaluation or form record.']);
    }

    if ($method === 'DELETE') {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            evaluationRespond(422, ['success' => false, 'message' => 'A valid record ID is required.']);
        }
        if ($type === 'evaluation') {
            $stmt = $connection->prepare('DELETE FROM rc_evaluations WHERE id = :id');
        } else {
            $used = $connection->prepare('SELECT COUNT(*) FROM rc_evaluations WHERE form_id = :id');
            $used->execute(['id' => $id]);
            if ((int) $used->fetchColumn() > 0) {
                evaluationRespond(409, ['success' => false, 'message' => 'This form is linked to panel results and cannot be deleted.']);
            }
            $stmt = $connection->prepare('DELETE FROM rc_evaluation_forms WHERE id = :id');
        }
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            evaluationRespond(404, ['success' => false, 'message' => 'The selected record was not found.']);
        }
        evaluationRespond(200, ['success' => true]);
    }

    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    if ($method === 'PUT' && (!$id || $id < 1)) {
        evaluationRespond(422, ['success' => false, 'message' => 'A valid record ID is required for updates.']);
    }

    if ($type === 'form') {
        $name = evaluationText($data, 'name', 'Form name', 150, true);
        $stage = validChoice(evaluationText($data, 'stage', 'Evaluation stage', 40, true), evaluationOptions()['stages'], 'evaluation stage');
        $criteriaInput = $data['criteria'] ?? [];
        $criteria = is_array($criteriaInput) ? $criteriaInput : preg_split('/\r\n|\r|\n/', (string) $criteriaInput);
        $criteria = array_values(array_filter(array_map(static fn($criterion) => trim((string) $criterion), $criteria), static fn($criterion) => $criterion !== ''));
        if (count($criteria) < 1 || count($criteria) > 30) {
            evaluationRespond(422, ['success' => false, 'message' => 'Enter 1 to 30 criteria, each no longer than 250 characters.']);
        }
        foreach ($criteria as $criterion) {
            if (mb_strlen($criterion) > 250) {
                evaluationRespond(422, ['success' => false, 'message' => 'Enter 1 to 30 criteria, each no longer than 250 characters.']);
            }
        }
        $criteriaJson = json_encode($criteria, JSON_UNESCAPED_UNICODE);
        if ($method === 'POST') {
            $stmt = $connection->prepare('INSERT INTO rc_evaluation_forms (name, stage, criteria_json) VALUES (:name, :stage, :criteria)');
        } else {
            $stmt = $connection->prepare('UPDATE rc_evaluation_forms SET name = :name, stage = :stage, criteria_json = :criteria WHERE id = :id');
        }
        $parameters = ['name' => $name, 'stage' => $stage, 'criteria' => $criteriaJson];
        if ($method === 'PUT') {
            $parameters['id'] = $id;
        }
        $stmt->execute($parameters);
        if ($method === 'PUT' && $stmt->rowCount() === 0) {
            $exists = $connection->prepare('SELECT id FROM rc_evaluation_forms WHERE id = :id');
            $exists->execute(['id' => $id]);
            if (!$exists->fetchColumn()) {
                evaluationRespond(404, ['success' => false, 'message' => 'The selected form was not found.']);
            }
        }
        evaluationRespond(200, ['success' => true, 'id' => $method === 'POST' ? (int) $connection->lastInsertId() : $id]);
    }

    $projectId = filter_var($data['projectId'] ?? null, FILTER_VALIDATE_INT);
    $formId = filter_var($data['formId'] ?? null, FILTER_VALIDATE_INT);
    if (!$projectId || $projectId < 1 || !$formId || $formId < 1) {
        evaluationRespond(422, ['success' => false, 'message' => 'Choose a registered project and evaluation form.']);
    }
    foreach ([['SELECT id FROM rc_projects WHERE id = :id', $projectId, 'The selected research project no longer exists.'], ['SELECT id FROM rc_evaluation_forms WHERE id = :id', $formId, 'The selected evaluation form no longer exists.']] as [$sql, $value, $message]) {
        $check = $connection->prepare($sql);
        $check->execute(['id' => $value]);
        if (!$check->fetchColumn()) {
            evaluationRespond(422, ['success' => false, 'message' => $message]);
        }
    }
    $panelMember = evaluationText($data, 'panelMember', 'Panel member', 150, true);
    $panelRole = evaluationText($data, 'panelRole', 'Panel role', 80);
    $evaluationDate = evaluationDate($data, 'evaluationDate', 'Evaluation date', true);
    $choices = evaluationOptions();
    $recommendation = validChoice(evaluationText($data, 'recommendation', 'Panel recommendation', 40, true), $choices['recommendations'], 'panel recommendation');
    $formCriteriaQuery = $connection->prepare('SELECT criteria_json FROM rc_evaluation_forms WHERE id = :id');
    $formCriteriaQuery->execute(['id' => $formId]);
    $formCriteria = json_decode((string) $formCriteriaQuery->fetchColumn(), true);
    if (!is_array($formCriteria) || !$formCriteria) {
        evaluationRespond(422, ['success' => false, 'message' => 'The selected evaluation form has no valid criteria.']);
    }
    $submittedCriteria = $data['criteriaResults'] ?? null;
    if (!is_array($submittedCriteria) || count($submittedCriteria) !== count($formCriteria)) {
        evaluationRespond(422, ['success' => false, 'message' => 'Rate every criterion in the selected evaluation form.']);
    }
    $criteriaResults = [];
    $criterionTotal = 0;
    foreach ($formCriteria as $index => $criterion) {
        $result = $submittedCriteria[$index] ?? null;
        $rating = is_array($result) ? filter_var($result['rating'] ?? null, FILTER_VALIDATE_INT) : false;
        if (!is_array($result) || ($result['criterion'] ?? null) !== $criterion || $rating === false || $rating < 1 || $rating > 5) {
            evaluationRespond(422, ['success' => false, 'message' => 'Rate every criterion on a scale from 1 to 5.']);
        }
        $criteriaResults[] = ['criterion' => $criterion, 'rating' => $rating];
        $criterionTotal += $rating;
    }
    $score = round(($criterionTotal / count($criteriaResults)) * 20, 2);
    $comments = evaluationText($data, 'comments', 'Panel comments', 5000);
    $recommendations = evaluationText($data, 'recommendations', 'Recommendations', 5000);
    $revisionRequirements = evaluationText($data, 'revisionRequirements', 'Revision requirements', 5000);
    $revisionStatus = validChoice(evaluationText($data, 'revisionStatus', 'Revision status', 20, true), $choices['revisionStatuses'], 'revision status');
    $revisionDueDate = evaluationDate($data, 'revisionDueDate', 'Revision due date');
    $defenseDate = evaluationDate($data, 'defenseDate', 'Defense date');
    $defenseOutcome = validChoice(evaluationText($data, 'defenseOutcome', 'Defense outcome', 40, true), $choices['defenseOutcomes'], 'defense outcome');
    $requiresRevision = in_array($recommendation, ['Passed with Revisions', 'Re-defense Required'], true)
        || in_array($defenseOutcome, ['Passed with Revisions', 'Re-defense Required'], true);
    if ($revisionStatus !== 'Not Required' && $revisionRequirements === '') {
        evaluationRespond(422, ['success' => false, 'message' => 'Add the required revisions or select Not Required.']);
    }
    if ($revisionStatus === 'Not Required' && ($revisionRequirements !== '' || $requiresRevision)) {
        evaluationRespond(422, ['success' => false, 'message' => 'Select a revision status for the listed revision requirements.']);
    }

    $columns = '(project_id, form_id, panel_member, panel_role, evaluation_date, score, recommendation, criteria_results, comments, recommendations, revision_requirements, revision_status, revision_due_date, defense_date, defense_outcome)';
    $values = '(:project_id, :form_id, :panel_member, :panel_role, :evaluation_date, :score, :recommendation, :criteria_results, :comments, :recommendations, :revision_requirements, :revision_status, :revision_due_date, :defense_date, :defense_outcome)';
    $parameters = [
        'project_id' => $projectId, 'form_id' => $formId, 'panel_member' => $panelMember, 'panel_role' => $panelRole,
        'evaluation_date' => $evaluationDate, 'score' => $score, 'recommendation' => $recommendation,
        'criteria_results' => json_encode($criteriaResults, JSON_UNESCAPED_UNICODE),
        'comments' => $comments, 'recommendations' => $recommendations, 'revision_requirements' => $revisionRequirements,
        'revision_status' => $revisionStatus, 'revision_due_date' => $revisionDueDate,
        'defense_date' => $defenseDate, 'defense_outcome' => $defenseOutcome,
    ];
    if ($method === 'POST') {
        $stmt = $connection->prepare("INSERT INTO rc_evaluations {$columns} VALUES {$values}");
    } else {
        $stmt = $connection->prepare('UPDATE rc_evaluations SET project_id=:project_id, form_id=:form_id, panel_member=:panel_member, panel_role=:panel_role, evaluation_date=:evaluation_date, score=:score, recommendation=:recommendation, criteria_results=:criteria_results, comments=:comments, recommendations=:recommendations, revision_requirements=:revision_requirements, revision_status=:revision_status, revision_due_date=:revision_due_date, defense_date=:defense_date, defense_outcome=:defense_outcome WHERE id=:id');
        $parameters['id'] = $id;
    }
    $stmt->execute($parameters);
    if ($method === 'PUT' && $stmt->rowCount() === 0) {
        $exists = $connection->prepare('SELECT id FROM rc_evaluations WHERE id = :id');
        $exists->execute(['id' => $id]);
        if (!$exists->fetchColumn()) {
            evaluationRespond(404, ['success' => false, 'message' => 'The selected evaluation was not found.']);
        }
    }
    evaluationRespond(200, ['success' => true, 'id' => $method === 'POST' ? (int) $connection->lastInsertId() : $id]);
} catch (PDOException $error) {
    error_log('Research evaluation API database error: ' . $error->getMessage());
    if ($error->getCode() === '23000') {
        evaluationRespond(409, ['success' => false, 'message' => 'A form with this name and evaluation stage already exists.']);
    }
    evaluationRespond(500, ['success' => false, 'message' => 'Unable to save the evaluation data. Check the research database connection and try again.']);
} catch (Throwable $error) {
    error_log('Research evaluation API error: ' . $error->getMessage());
    evaluationRespond(500, ['success' => false, 'message' => 'Unable to complete the evaluation request. Check the research database connection and try again.']);
}
