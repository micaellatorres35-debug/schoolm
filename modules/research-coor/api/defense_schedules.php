<?php
require_once dirname(__DIR__, 3) . '/auth/guard.php';
require_once dirname(__DIR__, 3) . '/database/db.php';
require_once dirname(__DIR__) . '/classes/EventManager.php';

header('Content-Type: application/json; charset=utf-8');

function defenseRespond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function defenseRequiredText(array $data, string $key, string $label, int $maxLength): string
{
    $value = trim((string)($data[$key] ?? ''));
    if ($value === '' || mb_strlen($value) > $maxLength) {
        defenseRespond(422, ['success' => false, 'message' => "{$label} is required and must be at most {$maxLength} characters."]);
    }
    return $value;
}

try {
    $database = new Database();
    $connection = $database->getConnection();
    $eventManager = new EventManager($connection);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $prefix = 'Research Defense:';
        $events = array_values(array_filter(
            $eventManager->getAllEvents(),
            static function (array $event) use ($prefix): bool {
                return strncmp((string)($event['event_title'] ?? ''), $prefix, strlen($prefix)) === 0;
            }
        ));
        usort($events, static function (array $left, array $right): int {
            return [$left['event_date'] ?? '', $left['start_time'] ?? '']
                <=> [$right['event_date'] ?? '', $right['start_time'] ?? ''];
        });
        defenseRespond(200, ['success' => true, 'events' => $events]);
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        defenseRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    try {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        defenseRespond(400, ['success' => false, 'message' => 'Invalid JSON request body.']);
    }
    if (!is_array($data)) {
        defenseRespond(400, ['success' => false, 'message' => 'The request body must be an object.']);
    }

    $projectTitle = defenseRequiredText($data, 'project_title', 'Research project title', 200);
    $stage = defenseRequiredText($data, 'defense_stage', 'Defense stage', 40);
    $candidate = defenseRequiredText($data, 'candidate', 'Candidate', 120);
    $adviser = defenseRequiredText($data, 'adviser', 'Research adviser', 120);
    $panel = trim((string)($data['panel_members'] ?? ''));
    $meetingMode = defenseRequiredText($data, 'meeting_mode', 'Meeting mode', 20);
    $venue = defenseRequiredText($data, 'venue', 'Venue or meeting location', 255);
    $meetingLink = trim((string)($data['meeting_link'] ?? ''));
    $notes = trim((string)($data['notes'] ?? ''));
    $date = trim((string)($data['defense_date'] ?? ''));
    $startTime = trim((string)($data['start_time'] ?? ''));
    $endTime = trim((string)($data['end_time'] ?? ''));

    if (!in_array($stage, ['Proposal Defense', 'Pre-oral Defense', 'Final Defense', 'Re-defense'], true)) {
        defenseRespond(422, ['success' => false, 'message' => 'Select a valid defense stage.']);
    }
    if (!in_array($meetingMode, ['In-person', 'Online', 'Hybrid'], true)) {
        defenseRespond(422, ['success' => false, 'message' => 'Select a valid meeting mode.']);
    }
    if (mb_strlen($panel) > 200 || mb_strlen($meetingLink) > 255 || mb_strlen($notes) > 1500) {
        defenseRespond(422, ['success' => false, 'message' => 'Panel members, meeting link, or instructions exceed the allowed length.']);
    }
    $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
        defenseRespond(422, ['success' => false, 'message' => 'Enter a valid defense date.']);
    }
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $startTime) ||
        !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $endTime) ||
        $endTime <= $startTime) {
        defenseRespond(422, ['success' => false, 'message' => 'Enter valid times and make sure the end time is after the start time.']);
    }
    if ($meetingLink !== '' && !filter_var($meetingLink, FILTER_VALIDATE_URL)) {
        defenseRespond(422, ['success' => false, 'message' => 'Enter a valid meeting link URL.']);
    }

    $description = implode("\n", [
        'Defense stage: ' . $stage,
        'Research title: ' . $projectTitle,
        'Candidate: ' . $candidate,
        'Adviser: ' . $adviser,
        'Panel members: ' . ($panel !== '' ? $panel : 'Not specified'),
        'Meeting mode: ' . $meetingMode,
        'Meeting link: ' . ($meetingLink !== '' ? $meetingLink : 'Not provided'),
        'Additional instructions: ' . ($notes !== '' ? $notes : 'None'),
    ]);

    $result = $eventManager->createEvent([
        'event_title' => 'Research Defense: ' . $projectTitle,
        'event_type' => 'Academic',
        'event_date' => $date,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'description' => $description,
        'location' => $venue,
        'target_audience' => 'Research Candidate and Panel',
        'status' => 'upcoming',
    ]);
    defenseRespond(201, $result);
} catch (Throwable $error) {
    error_log('Defense schedule API error: ' . $error->getMessage());
    defenseRespond(500, ['success' => false, 'message' => 'Unable to load or save defense schedules right now.']);
}
