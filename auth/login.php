<?php
// auth/login.php
include "../database/db.php";

$db   = new Database();
$conn = $db->getConnection();

session_start();

header('Content-Type: application/json');

define('MAX_ATTEMPTS', 3);
define('LOCKOUT_TIME', 60); // 60 seconds lockout

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeid = trim($_POST['employee_id']);
    $password   = $_POST['password'];

    $ip  = $_SERVER['REMOTE_ADDR'];
    $key = 'login_attempts_' . $ip . '_' . $employeeid;

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
    }

    $attempts = &$_SESSION[$key];
    $elapsed  = time() - $attempts['last_attempt'];

    // Reset if lockout period has passed
    if ($elapsed > LOCKOUT_TIME) {
        $attempts = ['count' => 0, 'last_attempt' => time()];
        $elapsed  = 0;
    }

    // Block if already locked
    if ($attempts['count'] >= MAX_ATTEMPTS) {
        $remaining_seconds = max(0, (int)(LOCKOUT_TIME - $elapsed));
        echo json_encode([
            'success'           => false,
            'locked'            => true,
            'remaining_seconds' => $remaining_seconds,
            'message'           => 'Too many failed attempts.',
        ]);
        exit();
    }

    // ── Authentication ────────────────────────────────────────────────────────
    $stmt = $conn->prepare("
            SELECT
                ua.user_id,
                ua.employee_id,
                ua.password,
                e.first_name,
                e.last_name,
                e.role_id AS role,
                e.department_id AS department,
                e.unit_id,
                e.employment_status,
                r.role_name,
                d.department_name,
                u.unit_name
            FROM user_account ua
            INNER JOIN em_employees e
                ON e.employee_id = ua.employee_id
            LEFT JOIN em_roles r
                ON r.role_id = e.role_id
            LEFT JOIN em_departments d
                ON d.department_id = e.department_id
            LEFT JOIN em_units u
                ON u.unit_id = e.unit_id
            WHERE ua.employee_id = :employeeid
            LIMIT 1
        ");

        $stmt->execute([
            ':employeeid' => $employeeid
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // ── Success: clear attempt tracking & build session ───────────────────
        unset($_SESSION[$key]);

        session_regenerate_id(true);

        $_SESSION['employee_id']     = $user['employee_id'];
        $_SESSION['employee_name']   = trim(
            $user['first_name'] . ' ' . $user['last_name']
        );
        $_SESSION['role']            = (int) $user['role'];
        $_SESSION['role_name']       = $user['role_name'];
        $_SESSION['department_id']   = $user['department'];
        $_SESSION['department_name'] = $user['department_name'];
        $_SESSION['unit_id']         = $user['unit_id'];
        $_SESSION['unit_name']       = $user['unit_name'];

        $redirectMap = [
        14 => 'modules/school-directress/index.php',
        15 => 'modules/guidance/index.php',
        16 => 'modules/registrar/',
        17 => 'modules/enrollment/index.php',
        18 => 'modules/monitoring/index.php',
        19 => 'modules/clinic/index.php',
        20 => 'modules/library/index.php',
        21 => 'modules/laboratory/',
        22 => 'modules/college-coor/index.php',
    ];

        $role = (int) $user['role'];

        if (!isset($redirectMap[$role])) {
            echo json_encode(['success' => false, 'locked' => false, 'message' => 'Invalid role.']);
            exit();
        }

        echo json_encode([
            'success'  => true,
            'redirect' => $redirectMap[$role],
        ]);
        exit();

    } else {
        // ── Failed: increment attempt counter ─────────────────────────────────
        $attempts['count']++;
        $attempts['last_attempt'] = time();

        $remaining_attempts = MAX_ATTEMPTS - $attempts['count'];

        if ($remaining_attempts <= 0) {
            echo json_encode([
                'success'           => false,
                'locked'            => true,
                'remaining_seconds' => (int) LOCKOUT_TIME,
                'message'           => 'Too many failed attempts. Account locked for 15 minutes.',
            ]);
        } else {
            echo json_encode([
                'success'            => false,
                'locked'             => false,
                'remaining_attempts' => $remaining_attempts,
                'message'            => 'Invalid Employee ID or Password.',
            ]);
        }
        exit();
    }
}
