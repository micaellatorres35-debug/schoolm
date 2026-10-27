<?php
ob_start();

const UM_PER_PAGE      = 12;
const UM_ACTIVITY_MAX  = 50;
const UM_EXPORT_MAX    = 10000;
const UM_ALLOWED_ROLE  = 'School Directress';

function um_respond(array $payload, int $code = 200) {
    if (ob_get_length()) {
        ob_clean(); // discard any stray warnings/notices before the JSON
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
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

function um_filters(): array {
    $status = strtolower(trim((string) ($_GET['status'] ?? '')));
    return [
        'search'     => mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100),
        'role'       => (int) ($_GET['role'] ?? 0),
        'department' => (int) ($_GET['department'] ?? 0),
        'unit'       => (int) ($_GET['unit'] ?? 0),
        'status'     => in_array($status, ['active', 'deactivated'], true) ? $status : '',
    ];
}

function um_post(string $key): string {
    return trim((string) ($_POST[$key] ?? ''));
}

try {
    // All includes live inside the try block
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../../../auth/guard.php';
    require_once __DIR__ . '/../classes/User.php';

    // Authorization on EVERY request, not just in the view
    if (($_SESSION['role_name'] ?? '') !== UM_ALLOWED_ROLE) {
        um_respond(['success' => false, 'message' => 'You are not authorized to manage user accounts.'], 403);
    }

    $action = (string) ($_GET['action'] ?? '');
    $writeActions = ['create', 'update', 'deactivate', 'reactivate', 'unlock'];

    if (in_array($action, $writeActions, true)) {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            um_respond(['success' => false, 'message' => 'Method not allowed.'], 405);
        }
        $expected = (string) ($_SESSION['um_csrf'] ?? '');
        $sent     = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if ($expected === '' || !hash_equals($expected, $sent)) {
            um_respond(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.'], 403);
        }
    }

    $actorId = (int) $_SESSION['employee_id'];
    $model   = new User();

    switch ($action) {

        case 'list':
            $filters = um_filters();
            $page    = max(1, (int) ($_GET['page'] ?? 1));
            $total   = $model->countAccounts($filters);
            $pages   = max(1, (int) ceil($total / UM_PER_PAGE));
            if ($page > $pages) {
                $page = $pages;
            }
            um_respond([
                'success' => true,
                'data'    => [
                    'rows'     => $model->getAccounts($filters, UM_PER_PAGE, ($page - 1) * UM_PER_PAGE),
                    'total'    => $total,
                    'page'     => $page,
                    'pages'    => $pages,
                    'per_page' => UM_PER_PAGE,
                    'stats'    => $model->getAccountStats(),
                ],
            ]);
            break;

        case 'export':
            um_respond([
                'success' => true,
                'data'    => ['rows' => $model->getAccounts(um_filters(), UM_EXPORT_MAX, 0)],
            ]);
            break;

        case 'details':
            $userId  = (int) ($_GET['user_id'] ?? 0);
            $details = $userId > 0 ? $model->getAccountDetails($userId) : null;
            if (!$details) {
                um_respond(['success' => false, 'message' => 'Account not found.'], 404);
            }
            $details['activity_total'] = $model->countActivity($userId);
            um_respond(['success' => true, 'data' => $details]);
            break;

        case 'activity':
            $userId = (int) ($_GET['user_id'] ?? 0);
            if ($userId <= 0) {
                um_respond(['success' => false, 'message' => 'Account not found.'], 404);
            }
            $limit  = min(UM_ACTIVITY_MAX, max(1, (int) ($_GET['limit'] ?? 15)));
            $offset = max(0, (int) ($_GET['offset'] ?? 0));
            um_respond([
                'success' => true,
                'data'    => [
                    'rows'  => $model->getActivity($userId, $limit, $offset),
                    'total' => $model->countActivity($userId),
                ],
            ]);
            break;

        case 'lookup_employees':
            $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
            if (mb_strlen($q) < 2) {
                um_respond(['success' => true, 'data' => ['rows' => []]]);
            }
            um_respond(['success' => true, 'data' => ['rows' => $model->lookupEmployees($q)]]);
            break;

        case 'create':
            $result = $model->createAccount(
                (int) um_post('employee_id'),
                (int) um_post('role_id'),
                (int) um_post('department_id'),
                (int) um_post('unit_id'),
                um_post('email'),
                (string) ($_POST['password'] ?? ''),   // not trimmed: spaces may be intentional
                $actorId
            );
            um_respond(['success' => true, 'message' => 'Account created.', 'data' => $result], 201);
            break;

        case 'update':
            $result = $model->updateAccount(
                (int) um_post('user_id'),
                (int) um_post('role_id'),
                (int) um_post('department_id'),
                (int) um_post('unit_id'),
                um_post('email'),
                (string) ($_POST['password'] ?? ''),
                $actorId
            );
            um_respond(['success' => true, 'message' => 'Account updated.', 'data' => $result]);
            break;

        case 'deactivate':
            $result = $model->setAccountStatus((int) um_post('user_id'), true, um_post('reason'), $actorId);
            um_respond(['success' => true, 'message' => 'Account deactivated.', 'data' => $result]);
            break;

        case 'reactivate':
            $result = $model->setAccountStatus((int) um_post('user_id'), false, null, $actorId);
            um_respond(['success' => true, 'message' => 'Account reactivated.', 'data' => $result]);
            break;

        case 'unlock':
            $result = $model->unlockAccount((int) um_post('user_id'), $actorId);
            um_respond(['success' => true, 'message' => 'Account unlocked.', 'data' => $result]);
            break;

        default:
            um_respond(['success' => false, 'message' => 'Unknown action.'], 400);
    }

} catch (AccountException $e) {
    um_respond(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors], $e->status);
} catch (\Throwable $e) {
    error_log('UserController: ' . $e->getMessage());
    um_respond(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}