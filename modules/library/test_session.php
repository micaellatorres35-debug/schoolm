<?php
// Put this file in your project root and open it AFTER logging in
// It will show you exactly what's in the session and what error index.php throws

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: text/plain');

echo "=== SESSION ===\n";
print_r($_SESSION);
echo "\n";

echo "=== Testing includes ===\n";
$files = [
    'includes/config.php',
    'includes/Database.php',
    'includes/Settings.php',
    'includes/ActivityLog.php',
    'includes/Book.php',
    'includes/Borrower.php',
    'includes/Transaction.php',
];
foreach ($files as $f) {
    if (file_exists(__DIR__ . '/' . $f)) {
        echo "✅ $f exists\n";
    } else {
        echo "❌ $f MISSING\n";
    }
}

echo "\n=== Testing Settings->updateOverdueStatus ===\n";
try {
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . '/includes/Settings.php';
    $s = new Settings();
    $s->updateOverdueStatus();
    echo "✅ updateOverdueStatus OK\n";
    echo "library_name: " . $s->get('library_name') . "\n";
} catch (Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== Testing views/layout.php exists ===\n";
$layout = __DIR__ . '/views/layout.php';
echo file_exists($layout) ? "✅ layout.php exists\n" : "❌ layout.php MISSING\n";

echo "\n=== Checking partials ===\n";
$partials = [
    'views/partials/dashboard.php',
    'views/partials/books.php',
    'views/partials/gallery.php',
    'views/partials/members.php',
    'views/partials/borrow.php',
    'views/partials/transactions.php',
    'views/partials/reports.php',
    'views/partials/settings.php',
    'views/partials/ai_features.php',
    'views/partials/lost_books.php',
];
foreach ($partials as $p) {
    echo (file_exists(__DIR__ . '/' . $p) ? "✅" : "❌ MISSING") . " $p\n";
}
