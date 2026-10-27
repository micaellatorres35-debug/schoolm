<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Timeout Debugger</h2>";

// Include the Visitor class
require_once __DIR__ . '/classes/Visitor.php';

try {
    echo "<h3>1. Initializing Visitor class...</h3>";
    $visitor = new Visitor();
    echo "✅ Visitor class initialized successfully!<br>";
    
} catch (Exception $e) {
    die("❌ Failed to initialize Visitor: " . $e->getMessage());
}

// Get all visitors
try {
    echo "<h3>2. Fetching all visitors...</h3>";
    $records = $visitor->getAll();
    echo "✅ Found " . count($records) . " visitors<br>";
    
    if (count($records) > 0) {
        echo "<h4>Sample visitor records:</h4>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Time In</th><th>Time Out</th><th>Status</th></tr>";
        
        foreach (array_slice($records, 0, 5) as $row) {
            $status = empty($row['time_out']) ? 'ACTIVE' : 'COMPLETED';
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['visitor_name']) . "</td>";
            echo "<td>" . $row['time_in'] . "</td>";
            echo "<td>" . ($row['time_out'] ?? 'NULL') . "</td>";
            echo "<td><strong>$status</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "❌ Error fetching visitors: " . $e->getMessage() . "<br>";
}

// Test timeout function with a specific ID if provided
if (isset($_GET['test_id'])) {
    $testId = intval($_GET['test_id']);
    
    echo "<h3>3. Testing timeout for ID: $testId</h3>";
    
    try {
        // Check if exists
        echo "Checking if visitor exists...<br>";
        $visitorRecord = $visitor->getById($testId);
        
        if (!$visitorRecord) {
            echo "❌ Visitor ID $testId not found!<br>";
        } else {
            echo "✅ Visitor found: " . $visitorRecord['visitor_name'] . "<br>";
            
            // Check if active
            echo "Checking if visitor is active...<br>";
            $isActive = $visitor->isActive($testId);
            
            if (!$isActive) {
                echo "❌ Visitor is already timed out!<br>";
                if (!empty($visitorRecord['time_out'])) {
                    echo "Timed out at: " . $visitorRecord['time_out'] . "<br>";
                }
            } else {
                echo "✅ Visitor is active<br>";
                
                // Perform timeout
                echo "Attempting to timeout visitor...<br>";
                $result = $visitor->timeout($testId);
                
                if ($result) {
                    echo "✅ SUCCESS! Visitor timed out successfully!<br>";
                    
                    // Verify
                    $checkAgain = $visitor->getById($testId);
                    echo "New time_out value: " . ($checkAgain['time_out'] ?? 'NULL') . "<br>";
                    
                } else {
                    echo "❌ Failed to timeout visitor<br>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error during timeout test: " . $e->getMessage() . "<br>";
    }
}

echo "<h3>4. Test direct database connection</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sms", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Try direct update
    if (isset($_GET['direct_id'])) {
        $directId = intval($_GET['direct_id']);
        echo "Testing direct SQL update for ID: $directId<br>";
        
        $stmt = $pdo->prepare("UPDATE mon_visitors_log SET time_out = NOW() WHERE id = ? AND time_out IS NULL");
        $stmt->execute([$directId]);
        
        $rows = $stmt->rowCount();
        if ($rows > 0) {
            echo "✅ Direct SQL update successful! Affected rows: $rows<br>";
        } else {
            echo "❌ Direct SQL update failed - visitor may already be timed out or doesn't exist<br>";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

?>

<hr>
<h3>Test Links:</h3>
<ul>
    <?php if (!empty($records)): ?>
        <?php foreach (array_slice($records, 0, 3) as $row): ?>
            <li>
                <strong><?= htmlspecialchars($row['visitor_name']) ?></strong> (ID: <?= $row['id'] ?>) - 
                <a href="?test_id=<?= $row['id'] ?>">Test timeout via class</a> | 
                <a href="?direct_id=<?= $row['id'] ?>">Test direct SQL</a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
    <li><a href="?">Reset</a></li>
</ul>

<p><strong>Note:</strong> Run this file directly to debug timeout issues. Access it at: <code>http://localhost/sms/modules/monitoring/debug_timeout.php</code></p>