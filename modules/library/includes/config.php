<?php
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'sms');
define('DB_CHARSET', 'utf8mb4');

define('UPLOAD_DIR', __DIR__ . '/../uploads/covers/');
define('UPLOAD_URL', 'uploads/covers/');

spl_autoload_register(function (string $class): void {
    $base = __DIR__ . '/';
    $dirs = [$base, $base . '../controllers/', $base . '../api/'];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});