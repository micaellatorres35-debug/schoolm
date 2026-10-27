<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the Visitor class
require_once __DIR__ . '/classes/Visitor.php';

// Initialize Visitor class
try {
    $visitor = new Visitor();
} catch (Exception $e) {
    header('Location: /sms/modules/monitoring/visitor_log.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /sms/modules/monitoring/visitor_log.php?error=missing_id');
    exit;
}

$visitorId = intval($_GET['id']);

try {
    // First check if visitor exists and is active
    $visitorRecord = $visitor->getById($visitorId);
    
    if (!$visitorRecord) {
        header('Location: /sms/modules/monitoring/visitor_log.php?error=not_found');
        exit;
    }
    
    // Check if already timed out
    if (!empty($visitorRecord['time_out'])) {
        header('Location: /sms/modules/monitoring/visitor_log.php?error=already_timedout');
        exit;
    }
    
    // Perform the timeout
    $result = $visitor->timeout($visitorId);
    
    if ($result) {
        // Success - redirect with visitor name
        $visitorName = urlencode($visitorRecord['visitor_name']);
        header("Location: /sms/modules/monitoring/visitor_log.php?success=timedout&name=$visitorName");
        exit;
    } else {
        // Get the actual error from Visitor class
        $errors = $visitor->getErrors();
        $errorMsg = !empty($errors) ? implode(', ', $errors) : 'timeout_failed';
        header("Location: /sms/modules/monitoring/visitor_log.php?error=" . urlencode($errorMsg));
        exit;
    }
    
} catch (Exception $e) {
    header('Location: /sms/modules/monitoring/visitor_log.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>