<?php
echo "<h2>Timeout Handler Test</h2>";

// Test 1: Can we include the Visitor class?
echo "<h3>Test 1: Including Visitor class</h3>";
if (file_exists(__DIR__ . '/classes/Visitor.php')) {
    echo "✅ Visitor.php found<br>";
    require_once __DIR__ . '/classes/Visitor.php';
    echo "✅ Visitor.php included<br>";
} else {
    echo "❌ Visitor.php NOT found<br>";
    echo "Files in classes folder:<br>";
    if (is_dir(__DIR__ . '/classes')) {
        $files = scandir(__DIR__ . '/classes');
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo " - " . $file . "<br>";
            }
        }
    }
}

// Test 2: Simple output
echo "<h3>Test 2: This file is working</h3>";
echo "If you can see this, the file is accessible<br>";

// Test 3: Try to access timeout_handler.php directly
echo "<h3>Test 3: Testing timeout_handler.php</h3>";
echo "<a href='timeout_handler.php'>Click to test timeout_handler.php directly</a><br>";
echo "<a href='timeout_handler.php?id=5'>Click to test timeout_handler.php with ID=5</a><br>";

// Test 4: Check .htaccess
echo "<h3>Test 4: Checking for .htaccess</h3>";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "✅ .htaccess exists<br>";
    echo "Contents:<br>";
    echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess')) . "</pre>";
} else {
    echo "❌ No .htaccess file found<br>";
}

// Test 5: Check Apache error log location
echo "<h3>Test 5: PHP Info</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
?>