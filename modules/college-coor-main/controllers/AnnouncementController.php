<?php
ob_start();


// Roles allowed to create, edit, publish, withdraw, archive, see history and pick audiences.
// Add roles here if others may manage announcements. Keep $annArchiveRoles in the view in step.
const ANN_MANAGE_ROLES = ['School Directress', 'Admin College Coordinator'];

const ANN_PER_PAGE = 10;

function ann_respond(array $payload, int $code = 200) {
    if (ob_get_length()) {
        ob_clean(); // discard any stray warnings/notices before the JSON
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function ann_is_manager(): bool {
    return in_array((string) ($_SESSION['role_name'] ?? ''), ANN_MANAGE_ROLES, true);
}

function ann_require_manager(string $message = 'You are not authorized to manage announcements.') {
    if (!ann_is_manager()) {
        ann_respond(['success' => false, 'message' => $message], 403);
    }
}

function ann_require_post() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        ann_respond(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

// Token comes from the X-CSRF-Token header (fetch) or a hidden csrf_token field (router-submitted forms)
function ann_check_csrf() {
    $expected = (string) ($_SESSION['ann_csrf'] ?? '');
    $sent     = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? ''));

    if ($expected === '' || !hash_equals($expected, $sent)) {
        ann_respond(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.'], 403);
    }
}

function ann_viewer(): array {
    return [
        'employee_id'   => (int) ($_SESSION['employee_id'] ?? 0),
        'department_id' => (int) ($_SESSION['department_id'] ?? 0),
        'unit_id'       => (int) ($_SESSION['unit_id'] ?? 0),
    ];
}

function ann_get(string $key): string {
    return trim((string) ($_GET[$key] ?? ''));
}

function ann_filters(): array {
    return [
        'q'             => mb_substr(ann_get('q'), 0, 100),
        'type'          => ann_get('type'),
        'priority'      => ann_get('priority'),
        'status'        => ann_get('status'),
        'date_from'     => ann_get('date_from'),
        'date_to'       => ann_get('date_to'),
        'department_id' => (int) ann_get('department_id'),
        'sort'          => ann_get('sort'),
    ];
}

// Runs $fetch($offset) for the requested page and clamps the page number to the last page
function ann_paged(callable $fetch): array {
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $res  = $fetch(($page - 1) * ANN_PER_PAGE);
    $pages = max(1, (int) ceil($res['total'] / ANN_PER_PAGE));

    if ($page > $pages) {
        $page = $pages;
        $res  = $fetch(($page - 1) * ANN_PER_PAGE);
    }
    return [
        'rows'     => $res['rows'],
        'total'    => $res['total'],
        'page'     => $page,
        'pages'    => $pages,
        'per_page' => ANN_PER_PAGE,
    ];
}

// Turns $_FILES['attachments'] (single or multiple) into a list of single-file arrays
function ann_files(string $key): array {
    if (empty($_FILES[$key])) {
        return [];
    }
    $f = $_FILES[$key];
    if (!is_array($f['name'])) {
        return [$f];
    }
    $out = [];
    foreach ($f['name'] as $i => $name) {
        $out[] = [
            'name'     => $name,
            'type'     => $f['type'][$i] ?? '',
            'tmp_name' => $f['tmp_name'][$i] ?? '',
            'error'    => $f['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $f['size'][$i] ?? 0,
        ];
    }
    return $out;
}

function ann_post_id(string $key = 'announcement_id') {
    $id = filter_var($_POST[$key] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        ann_respond(['success' => false, 'message' => 'Invalid announcement.'], 422);
    }
    return $id;
}

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Server error.']);
    }
});

try {
    include_once __DIR__ . '/../../../auth/session.php';
    require_once __DIR__ . '/../classes/Announcement.php';
    require_once __DIR__ . '/../classes/FileStorage.php';

    $viewer     = ann_viewer();
    $employeeId = $viewer['employee_id'];
    if ($employeeId < 1) {
        ann_respond(['success' => false, 'message' => 'Please sign in again.'], 401);
    }

    $action = (string) ($_GET['action'] ?? $_POST['action'] ?? '');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        // A file bigger than post_max_size arrives as an empty POST
        if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            ann_respond(['success' => false, 'message' => 'The upload is too large. Attachments may be up to 10 MB each.'], 413);
        }
        ann_check_csrf();
    }

    $model = new Announcement();

    // Make sure anything scheduled for now or earlier is live before we read lists or counts
    if (in_array($action, ['feed', 'feed_state', 'summary', 'detail', 'manage_list'], true)) {
        try {
            $model->processScheduled();
        } catch (\Throwable $e) {
            error_log('AnnouncementController processScheduled: ' . $e->getMessage());
        }
    }

    switch ($action) {

        /* ── Reading (any signed-in employee; visibility enforced in SQL) ── */

        case 'feed':
            $filters = ann_filters();
            ann_respond(['success' => true, 'data' => ann_paged(function ($offset) use ($model, $viewer, $filters) {
                return $model->getFeed($viewer, $filters, ANN_PER_PAGE, $offset);
            }) + ['server_time' => date('Y-m-d H:i:s')]]);
            break;

        case 'feed_state': // polled by the page; reload the list only when this changes
            ann_respond(['success' => true, 'data' => $model->getFeedState($viewer)]);
            break;

        case 'summary':
            if (ann_is_manager()) {
                ann_respond(['success' => true, 'data' => $model->getSummary()]);
            }
            $state = $model->getFeedState($viewer);
            ann_respond(['success' => true, 'data' => ['active' => $state['count']]]);
            break;

        case 'detail':
            $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                ann_respond(['success' => false, 'message' => 'Invalid announcement.'], 422);
            }
            ann_respond(['success' => true, 'data' => $model->getDetail($id, $viewer, ann_is_manager())]);
            break;

        case 'file': // authorised stream; never a direct link to storage
            $attId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($attId === false) {
                ann_respond(['success' => false, 'message' => 'Invalid attachment.'], 422);
            }
            $att = $model->getAttachmentForViewer($attId, $viewer, ann_is_manager());
            $abs = FileStorage::resolve($att['file_path']);
            if (!$abs) {
                ann_respond(['success' => false, 'message' => 'The file is no longer available.'], 404);
            }
            FileStorage::stream($abs, $att['original_name'], FileStorage::mimeFor($att['original_name']), ann_get('download') === '1');
            break;

        /* ── Reading (managers) ── */

        case 'manage_list':
            ann_require_manager();
            $filters = ann_filters();
            ann_respond(['success' => true, 'data' => ann_paged(function ($offset) use ($model, $filters) {
                return $model->getManageList($filters, ANN_PER_PAGE, $offset);
            })]);
            break;

        case 'audience_options':
            ann_require_manager();
            ann_respond(['success' => true, 'data' => $model->getAudienceOptions()]);
            break;

        case 'search_employees':
            ann_require_manager();
            ann_respond(['success' => true, 'data' => ['rows' => $model->searchEmployees(ann_get('q'))]]);
            break;

        case 'history':
            ann_require_manager();
            $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                ann_respond(['success' => false, 'message' => 'Invalid announcement.'], 422);
            }
            ann_respond(['success' => true, 'data' => ['rows' => $model->getHistory($id)]]);
            break;

        case 'list_archived': // existing Archived tab
            ann_require_manager('You are not authorized to manage the announcement archive.');
            $search = mb_substr(ann_get('search'), 0, 100);
            $page   = max(1, (int) ($_GET['page'] ?? 1));
            $total  = $model->countArchived($search);
            $pages  = max(1, (int) ceil($total / ANN_PER_PAGE));
            if ($page > $pages) {
                $page = $pages;
            }
            ann_respond([
                'success' => true,
                'data'    => [
                    'rows'     => $model->getArchived($search, ANN_PER_PAGE, ($page - 1) * ANN_PER_PAGE),
                    'total'    => $total,
                    'page'     => $page,
                    'pages'    => $pages,
                    'per_page' => ANN_PER_PAGE,
                ],
            ]);
            break;

        /* ── Writing (managers, POST + CSRF checked above) ── */

        case 'create': // legacy quick-post form: publishes to the entire campus immediately
            ann_require_post();
            ann_require_manager('You are not authorized to post announcements.');

            $title   = trim((string) ($_POST['title'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            $dateRaw = trim((string) ($_POST['publish_date'] ?? ''));

            $ts = $dateRaw !== '' ? strtotime($dateRaw) : time();
            if ($ts === false) {
                ann_respond(['success' => false, 'message' => 'Invalid publish date.'], 422);
            }
            if (mb_strlen($title) > 255) {
                ann_respond(['success' => false, 'message' => 'Title is too long.'], 422);
            }

            $imageFile = (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)
                ? $_FILES['image'] : null;

            // The message is optional, but an announcement cannot be completely empty
            if ($title === '' && $message === '' && !$imageFile) {
                ann_respond(['success' => false, 'message' => 'Add a title, a message or an image.'], 422);
            }

            $result = $model->postAnnouncement($title, $message, date('Y-m-d H:i:s', $ts), $employeeId, $imageFile);
            if (is_array($result)) {
                ann_respond(['success' => false, 'message' => $result['message'] ?? 'Could not post announcement.'], 422);
            }
            ann_respond(['success' => true, 'message' => 'Announcement posted.', 'data' => ['announcement_id' => (int) $result]], 201);
            break;

        case 'save': // create a draft, or edit a draft / scheduled / published announcement
            ann_require_post();
            ann_require_manager();

            $id = (int) ($_POST['announcement_id'] ?? 0);
            $in = [
                'title'                 => $_POST['title'] ?? '',
                'message'               => $_POST['message'] ?? '',
                'announcement_type'     => $_POST['announcement_type'] ?? 'General Announcement',
                'priority'              => $_POST['priority'] ?? 'Normal',
                'audience_scope'        => $_POST['audience_scope'] ?? 'Campus',
                'department_ids'        => $_POST['department_ids'] ?? [],
                'unit_ids'              => $_POST['unit_ids'] ?? [],
                'employee_ids'          => $_POST['employee_ids'] ?? [],
                'publish_date'          => $_POST['publish_date'] ?? '',
                'expires_at'            => $_POST['expires_at'] ?? '',
                'issuing_department_id' => (int) ($_SESSION['department_id'] ?? 0),
            ];

            $savedId = $model->save(
                $in,
                $employeeId,
                ann_files('attachments'),
                (array) ($_POST['remove_attachment_ids'] ?? []),
                $id > 0 ? $id : null
            );
            ann_respond(['success' => true, 'message' => $id > 0 ? 'Announcement updated.' : 'Draft saved.', 'data' => ['announcement_id' => $savedId]]);
            break;

        case 'publish': // Publish Now, or Schedule when schedule_at is given
            ann_require_post();
            ann_require_manager();

            $scheduleAt = trim((string) ($_POST['schedule_at'] ?? ''));
            $res = $model->publish(ann_post_id(), $employeeId, $scheduleAt !== '' ? $scheduleAt : null);

            ann_respond(['success' => true, 'data' => $res, 'message' => $res['status'] === 'Scheduled'
                ? 'Announcement scheduled for ' . date('F j, Y g:i A', strtotime($res['publish_date'])) . '.'
                : 'Announcement published.']);
            break;

        case 'unschedule':
            ann_require_post();
            ann_require_manager();
            $model->unschedule(ann_post_id(), $employeeId);
            ann_respond(['success' => true, 'message' => 'Announcement moved back to draft.']);
            break;

        case 'withdraw':
            ann_require_post();
            ann_require_manager();
            $model->withdraw(ann_post_id(), $employeeId, (string) ($_POST['reason'] ?? ''));
            ann_respond(['success' => true, 'message' => 'Announcement withdrawn.']);
            break;

        case 'reopen':
            ann_require_post();
            ann_require_manager();
            $model->reopen(ann_post_id(), $employeeId);
            ann_respond(['success' => true, 'message' => 'Announcement reopened as a draft.']);
            break;

        case 'delete_draft':
            ann_require_post();
            ann_require_manager();
            $model->deleteDraft(ann_post_id(), $employeeId);
            ann_respond(['success' => true, 'message' => 'Draft deleted.']);
            break;

        case 'archive':
            ann_require_post();
            ann_require_manager('You are not authorized to manage the announcement archive.');
            $model->archiveAnnouncement(ann_post_id(), $employeeId);
            ann_respond(['success' => true, 'message' => 'Announcement archived.']);
            break;

        case 'restore':
            ann_require_post();
            ann_require_manager('You are not authorized to manage the announcement archive.');
            $model->restoreAnnouncement(ann_post_id(), $employeeId);
            ann_respond(['success' => true, 'message' => 'Announcement restored.']);
            break;

        default:
            ann_respond(['success' => false, 'message' => 'Invalid or missing action.'], 400);
    }

} catch (AnnouncementException $e) {
    ann_respond(['success' => false, 'message' => $e->getMessage()], $e->status);
} catch (\Throwable $e) {
    error_log('AnnouncementController: ' . $e->getMessage());
    ann_respond(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}