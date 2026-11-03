<?php
ob_start();

// Who may open this module, and who may change organizational records.
// Set DM_WRITE_ROLES to [] to make the whole module read-only.
const DM_READ_ROLES   = ['School Directress', 'Admin College Coordinator'];
const DM_WRITE_ROLES  = ['School Directress', 'Admin College Coordinator'];
const DM_MATCH_CHIPS  = 3;

function dm_respond(array $payload, int $code = 200) {
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

function dm_post(string $key): string {
    return trim((string) ($_POST[$key] ?? ''));
}

try {
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../../../auth/guard.php';
    require_once __DIR__ . '/../classes/Department.php';
    require_once __DIR__ . '/../classes/Unit.php';

    $action = (string) ($_GET['action'] ?? '');
    $role   = (string) ($_SESSION['role_name'] ?? '');

    $writeActions = ['save_department', 'save_unit', 'set_department_status', 'set_unit_status'];
    $isWrite      = in_array($action, $writeActions, true);

    // Kept for any other page that already calls it: login required, no role gate, read-only.
    $legacyRead = ($action === 'get_departments');

    if (!$legacyRead) {
        if (!in_array($role, DM_READ_ROLES, true)) {
            dm_respond(['success' => false, 'message' => 'You are not authorized to view this module.'], 403);
        }
        if ($isWrite) {
            if (!in_array($role, DM_WRITE_ROLES, true)) {
                dm_respond(['success' => false, 'message' => 'You are not authorized to change departments or units.'], 403);
            }
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                dm_respond(['success' => false, 'message' => 'Method not allowed.'], 405);
            }
            $expected = (string) ($_SESSION['dm_csrf'] ?? '');
            $sent     = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if ($expected === '' || !hash_equals($expected, $sent)) {
                dm_respond(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.'], 403);
            }
        }
    }

    $departments = new Department();
    $units       = new Unit();

    switch ($action) {

        case 'get_departments': // legacy shape: department_id, department_name, head_name, employee_count
            $rows = array_map(function ($d) {
                return [
                    'department_id'   => $d['department_id'],
                    'department_name' => $d['department_name'],
                    'head_name'       => $d['head_name'],
                    'employee_count'  => $d['employee_count'],
                ];
            }, $departments->getDepartmentOverview([]));
            dm_respond(['success' => true, 'data' => $rows]);
            break;

        case 'list':
            $status = strtolower(trim((string) ($_GET['status'] ?? '')));
            $filters = [
                'search' => mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100),
                'status' => in_array($status, ['active', 'inactive'], true) ? $status : '',
            ];
            $rows = $departments->getDepartmentOverview($filters);
            $ids  = array_map(function ($r) { return (int) $r['department_id']; }, $rows);

            $matched = [];
            foreach ($departments->getMatchingUnits($ids, $filters['search']) as $deptId => $names) {
                $matched[$deptId] = array_slice($names, 0, DM_MATCH_CHIPS);
            }

            dm_respond([
                'success' => true,
                'data'    => [
                    'departments' => $rows,
                    'matched'     => (object) $matched,
                    'stats'       => $departments->getOrgStats(),
                ],
            ]);
            break;

        case 'department':
            $id  = (int) ($_GET['id'] ?? 0);
            $dep = $id > 0 ? $departments->getDepartment($id) : null;
            if (!$dep) {
                dm_respond(['success' => false, 'message' => 'Department not found.'], 404);
            }
            dm_respond(['success' => true, 'data' => ['department' => $dep, 'units' => $units->getUnitsDetailed($id)]]);
            break;

        case 'unit':
            $id   = (int) ($_GET['id'] ?? 0);
            $unit = $id > 0 ? $units->getUnit($id) : null;
            if (!$unit) {
                dm_respond(['success' => false, 'message' => 'Unit not found.'], 404);
            }
            dm_respond(['success' => true, 'data' => ['unit' => $unit, 'employees' => $units->getUnitEmployees($id)]]);
            break;

        case 'options':
            dm_respond(['success' => true, 'data' => ['departments' => $departments->getDepartmentOptions()]]);
            break;

        case 'save_department':
            $id = (int) dm_post('id');
            $result = $id > 0
                ? $departments->updateDepartment($id, dm_post('name'), dm_post('description'), dm_post('status'))
                : $departments->createDepartment(dm_post('name'), dm_post('description'), dm_post('status') ?: 'Active');
            dm_respond(['success' => true, 'message' => $id > 0 ? 'Department updated.' : 'Department added.', 'data' => $result], $id > 0 ? 200 : 201);
            break;

        case 'save_unit':
            $id = (int) dm_post('id');
            $result = $id > 0
                ? $units->updateUnit($id, (int) dm_post('department_id'), dm_post('name'), dm_post('description'), dm_post('status'))
                : $units->createUnit((int) dm_post('department_id'), dm_post('name'), dm_post('description'), dm_post('status') ?: 'Active');
            dm_respond(['success' => true, 'message' => $id > 0 ? 'Unit updated.' : 'Unit added.', 'data' => $result], $id > 0 ? 200 : 201);
            break;

        case 'set_department_status':
            $result = $departments->setDepartmentStatus((int) dm_post('id'), dm_post('status'));
            dm_respond(['success' => true, 'message' => 'Department status updated.', 'data' => $result]);
            break;

        case 'set_unit_status':
            $result = $units->setUnitStatus((int) dm_post('id'), dm_post('status'));
            dm_respond(['success' => true, 'message' => 'Unit status updated.', 'data' => $result]);
            break;

        default:
            dm_respond(['success' => false, 'message' => 'Invalid or missing action.'], 400);
    }

} catch (OrgException $e) {
    dm_respond(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors], $e->status);
} catch (\Throwable $e) {
    error_log('DepartmentController: ' . $e->getMessage());
    dm_respond(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}