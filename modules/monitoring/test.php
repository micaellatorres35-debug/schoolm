<?php
echo "<h2>Path Test</h2>";
echo "Current file: " . __FILE__ . "<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "<br>";

echo "Checking if timeout_handler.php exists:<br>";
if (file_exists(__DIR__ . '/timeout_handler.php')) {
    echo "✅ timeout_handler.php EXISTS in this folder<br>";
    echo "Full path: " . __DIR__ . '/timeout_handler.php' . "<br>";
} else {
    echo "❌ timeout_handler.php NOT found in this folder<br>";
    echo "Files in this directory:<br>";
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo " - " . $file . "<br>";
        }
    }
}

echo "<br>";
echo "<a href='timeout_handler.php?id=5'>Test timeout_handler.php with ID=5</a><br>";
echo "<a href='/sms/modules/monitoring/timeout_handler.php?id=5'>Test with absolute path</a><br>";