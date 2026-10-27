<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';

header('Content-Type: text/plain');

try {
    $db = Database::getInstance();
    echo "✅ Database connected OK\n\n";

    // Test 1: Check user_account for employee 1005
    $stmt = $db->prepare("SELECT * FROM user_account WHERE employee_id = ?");
    $stmt->execute([1005]);
    $ua = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "user_account row for employee_id=1005:\n";
    print_r($ua);
    echo "\n";

    // Test 2: Check sms_employee for 1005
    $stmt2 = $db->prepare("SELECT * FROM sms_employee WHERE employee_id = ?");
    $stmt2->execute([1005]);
    $emp = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "sms_employee row for employee_id=1005:\n";
    print_r($emp);
    echo "\n";

    // Test 3: Verify password
    if ($ua) {
        $result = password_verify('library123', $ua['password']);
        echo "password_verify('library123', hash) = " . ($result ? "TRUE ✅" : "FALSE ❌") . "\n\n";
    }

    // Test 4: Full join query
    $stmt3 = $db->prepare("
        SELECT ua.user_id, ua.password, e.employee_id, e.first_name, e.last_name, e.status
        FROM user_account ua
        INNER JOIN sms_employee e ON e.employee_id = ua.employee_id
        WHERE ua.employee_id = ?
        LIMIT 1
    ");
    $stmt3->execute([1005]);
    $joined = $stmt3->fetch(PDO::FETCH_ASSOC);
    echo "Full join result:\n";
    print_r($joined);

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
