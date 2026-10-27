<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = trim($_POST['employee_id'] ?? '');
    $password   = trim($_POST['password']    ?? '');

    if (!$employeeId || !$password) {
        $error = 'Employee ID and password are required.';
    } else {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare("
                SELECT
                    ua.user_id,
                    ua.password        AS hashed_password,
                    e.employee_id,
                    e.first_name,
                    e.last_name,
                    e.department,
                    e.status           AS emp_status,
                    r.role_name,
                    p.position_name
                FROM user_account ua
                INNER JOIN sms_employee  e  ON e.employee_id  = ua.employee_id
                LEFT  JOIN sd_roles      r  ON r.role_id      = e.role
                LEFT  JOIN sd_position   p  ON p.position_id  = e.position
                WHERE ua.employee_id = :eid
                LIMIT 1
            ");
            $stmt->execute([':eid' => (int)$employeeId]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'No account found for Employee ID <strong>' . htmlspecialchars($employeeId) . '</strong>.';
            } elseif ($user['emp_status'] !== 'active') {
                $error = 'This account is inactive. Contact your administrator.';
            } elseif (!password_verify($password, $user['hashed_password'])) {
                $error = 'Incorrect password. Please try again.';
            } else {
                session_regenerate_id(true);
                $_SESSION['logged_in']   = true;
                $_SESSION['user_id']     = $user['user_id'];
                $_SESSION['employee_id'] = $user['employee_id'];
                $_SESSION['name']        = trim($user['first_name'] . ' ' . $user['last_name']);
                $_SESSION['role']        = $user['role_name']     ?? 'Staff';
                $_SESSION['position']    = $user['position_name'] ?? 'Staff';
                $_SESSION['department']  = $user['department'];
                header('Location: index.php');
                exit;
            }

        } catch (PDOException $e) {
            error_log('[login.php] ' . $e->getMessage());
            $error = 'A database error occurred. Please contact your administrator.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCP Library — Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary:       #200082;
            --primary-light: #5146b7;
            --gray:          #64748b;
            --dark:          #1e293b;
            --light:         #f8fafc;
            --border:        #e2e8f0;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--primary);
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .page-loader {
            position: fixed; inset: 0;
            background: var(--primary);
            z-index: 9999;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            transition: opacity .5s ease, visibility .5s ease;
        }
        .page-loader.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        .loader-logo {
            width: 80px; height: 80px; border-radius: 50%;
            border: 3px solid rgba(255,255,255,.3);
            background: #fff; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            animation: loaderPulse 2s ease infinite;
        }
        .loader-logo img { width: 90%; height: 90%; object-fit: contain; }
        .loader-spinner {
            width: 36px; height: 36px;
            border: 3px solid rgba(255,255,255,.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        .loader-text {
            margin-top: 1rem; color: rgba(255,255,255,.7);
            font-size: .78rem; letter-spacing: 2px; text-transform: uppercase;
        }

        .bg-layer {
            position: fixed; inset: 0;
            background: url('assets/images/bg.jpg') center/cover no-repeat;
            z-index: 0;
        }
        .bg-overlay {
            position: fixed; inset: 0;
            background: linear-gradient(135deg, rgba(32,0,130,.88), rgba(0,0,0,.75));
            z-index: 1;
        }

        .login-wrapper {
            position: relative; z-index: 2;
            display: flex;
            width: 100%; max-width: 960px; min-height: 560px;
            border-radius: 20px; overflow: hidden;
            box-shadow: 0 24px 64px rgba(0,0,0,.5);
            margin: 1rem;
            animation: fadeUp .6s ease .2s both;
        }

        .login-left {
            flex: 1; padding: 3rem 2.5rem;
            background: linear-gradient(160deg, rgba(32,0,130,.95), rgba(81,70,183,.9));
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; position: relative; overflow: hidden;
        }
        .login-left::before {
            content: ''; position: absolute;
            width: 300px; height: 300px;
            background: rgba(255,255,255,.04);
            border-radius: 50%; top: -80px; right: -80px;
        }
        .login-left::after {
            content: ''; position: absolute;
            width: 200px; height: 200px;
            background: rgba(255,255,255,.04);
            border-radius: 50%; bottom: -60px; left: -60px;
        }
        .school-logo-wrap {
            width: 110px; height: 110px; border-radius: 50%;
            border: 4px solid rgba(255,255,255,.3);
            background: #fff; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.3);
        }
        .school-logo-wrap img { width: 90%; height: 90%; object-fit: contain; }
        .login-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem; color: #fff;
            margin-bottom: .5rem; line-height: 1.3;
        }
        .login-left p { color: rgba(255,255,255,.7); font-size: .875rem; max-width: 260px; line-height: 1.6; }
        .divider-line { width: 50px; height: 3px; background: rgba(255,255,255,.4); border-radius: 2px; margin: 1rem auto; }
        .school-tag {
            margin-top: 1.75rem; padding: .4rem 1rem;
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
            border-radius: 50px; color: rgba(255,255,255,.8); font-size: .78rem;
        }
        .login-right {
            flex: 1.1; background: #fff; padding: 3rem 2.5rem;
            display: flex; flex-direction: column; justify-content: center;
        }
        .login-right h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem; color: var(--dark); margin-bottom: .4rem;
        }
        .subtitle { color: var(--gray); font-size: .875rem; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block; font-size: .8rem; font-weight: 600;
            color: var(--dark); margin-bottom: .45rem;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .input-wrap { position: relative; }
        .input-wrap .ico {
            position: absolute; left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray); font-size: .875rem; pointer-events: none;
        }
        .input-wrap input {
            width: 100%; padding: .875rem 1rem .875rem 2.6rem;
            border: 2px solid var(--border); border-radius: 10px;
            font-size: .95rem; font-family: 'Inter', sans-serif;
            color: var(--dark); background: var(--light);
            transition: all .2s; outline: none;
        }
        .input-wrap input:focus {
            border-color: var(--primary); background: #fff;
            box-shadow: 0 0 0 3px rgba(32,0,130,.1);
        }
        .toggle-pw {
            position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--gray); font-size: .875rem; padding: 0;
        }
        .toggle-pw:hover { color: var(--primary); }
        .alert {
            padding: .875rem 1rem; border-radius: 8px;
            font-size: .875rem; margin-bottom: 1.1rem;
            display: flex; align-items: flex-start; gap: .5rem;
            animation: slideIn .3s ease;
        }
        .alert-error { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; }
        .alert i { flex-shrink: 0; margin-top: 2px; }
        .btn-login {
            width: 100%; padding: .95rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff; border: none; border-radius: 10px;
            font-size: .95rem; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all .3s; letter-spacing: .3px;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            margin-top: .5rem;
        }
        .btn-login:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(32,0,130,.3); }
        .btn-login:active  { transform: translateY(0); }
        .btn-login:disabled { opacity: .7; cursor: not-allowed; }

        .login-hint {
            margin-top: 1.5rem; padding: .875rem 1rem;
            background: var(--light); border-radius: 8px;
            border: 1px solid var(--border); font-size: .8rem; color: var(--gray);
        }
        .login-hint strong { color: var(--dark); }

        @keyframes spin       { to { transform: rotate(360deg); } }
        @keyframes loaderPulse{ 0%,100%{box-shadow:0 0 0 0 rgba(255,255,255,.3)} 50%{box-shadow:0 0 0 10px rgba(255,255,255,0)} }
        @keyframes fadeUp     { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        @keyframes slideIn    { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }

        @media (max-width: 640px) {
            .login-left { display: none; }
            .login-right { padding: 2rem 1.5rem; }
            .login-wrapper { min-height: auto; }
        }
    </style>
</head>
<body>

<div class="page-loader" id="pageLoader">
    <div class="loader-logo"><img src="assets/images/bcp-logo.png" alt="BCP"></div>
    <div class="loader-spinner"></div>
    <div class="loader-text">Loading...</div>
</div>

<div class="bg-layer"></div>
<div class="bg-overlay"></div>

<div class="login-wrapper">

    <div class="login-left">
        <div class="school-logo-wrap">
            <img src="assets/images/bcp-logo.png" alt="BCP Logo">
        </div>
        <h1>Bestlink College<br>of the Philippines</h1>
        <div class="divider-line"></div>
        <p>School Library Management System<br>Professional Edition</p>
        <div class="school-tag">
            <i class="fas fa-map-marker-alt" style="margin-right:.4rem"></i> Est. 2002
        </div>
    </div>

    <div class="login-right">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in with your SMS employee credentials</p>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= $error ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="employee_id">Employee ID</label>
                <div class="input-wrap">
                    <i class="fas fa-id-badge ico"></i>
                    <input
                        type="number"
                        id="employee_id"
                        name="employee_id"
                        placeholder="Enter your Employee ID (e.g. 1005)"
                        value="<?= htmlspecialchars($_POST['employee_id'] ?? '') ?>"
                        required
                        autofocus
                        autocomplete="username"
                        min="1"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="passwordInput">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock ico"></i>
                    <input
                        type="password"
                        id="passwordInput"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Sign In</span>
            </button>
        </form>

        <div class="login-hint">
            <strong>How to login:</strong> Use your <strong>Employee ID number</strong>
            and your <strong>SMS system password</strong>.<br>
            <span style="color:#aaa;font-size:.75rem">
                Example IDs: 1002, 1005, 1009 &nbsp;|&nbsp;
                Credentials managed by your system admin
            </span>
        </div>
    </div>

</div>

<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        document.getElementById('pageLoader').classList.add('hidden');
    }, 600);
});
function togglePw() {
    var input = document.getElementById('passwordInput');
    var icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type    = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type    = 'password';
        icon.className = 'fas fa-eye';
    }
}
document.getElementById('loginForm').addEventListener('submit', function (e) {
    var empId = document.getElementById('employee_id').value.trim();
    var pass  = document.getElementById('passwordInput').value;
    if (!empId || !pass) { e.preventDefault(); return; }

    var btn = document.getElementById('loginBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Signing in...</span>';
});

window.addEventListener('pageshow', function () {
    var btn = document.getElementById('loginBtn');
    btn.disabled  = false;
    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i><span>Sign In</span>';
});
</script>
</body>
</html>