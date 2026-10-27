<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = Database::getInstance();
    $v  = $db->query('SELECT VERSION()')->fetchColumn();
    $n  = $db->query('SELECT COUNT(*) FROM lbr_books')->fetchColumn();

    echo "<h2>✅ Database Connected</h2>";
    echo "<p>MySQL: <strong>{$v}</strong></p>";
    echo "<p>Database: <strong>" . DB_NAME . "</strong></p>";
    echo "<p>Books in lbr_books: <strong>{$n}</strong></p>";
    echo "<hr><h3>Table Check</h3><ul>";

    foreach ([
        'lbr_books','lbr_borrowers','lbr_transactions','lbr_settings',
        'lbr_activity_log','lbr_borrow_history','lbr_reminders',
        'lbr_book_similarity','lbr_recommendations'
    ] as $t) {
        try {
            $c = $db->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
            echo "<li>✅ <code>{$t}</code> — {$c} rows</li>";
        } catch (PDOException $e) {
            echo "<li>❌ <code>{$t}</code> MISSING</li>";
        }
    }
    echo "</ul><p style='color:#888'>Delete this file after testing.</p>";
} catch (Throwable $e) {
    echo "<h2>❌ Failed</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}