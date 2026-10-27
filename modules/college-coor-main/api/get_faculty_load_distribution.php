<?php
/**
 * API Endpoint: Get Faculty Load Distribution Summary
 * Returns count of underloaded, fully loaded, and overloaded faculty
 * Used for Dashboard Overview auto-update
 */

require_once('../classes/FacultyManager.php');
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get all faculty load data
    $manager = new FacultyManager($conn);
    $facultyLoads = $manager->getFacultyLoad();
    
    // Calculate distribution
    $distribution = [
        'underloaded' => 0,
        'fully_loaded' => 0,
        'overloaded' => 0,
        'total_faculty' => count($facultyLoads),
        'timestamp' => date('Y-m-d H:i:s'),
        'last_updated' => gmdate('c')
    ];
    
    // Count faculty by load status
    foreach ($facultyLoads as $faculty) {
        $status = $faculty['load_status'] ?? 'Underloaded';
        
        switch ($status) {
            case 'Overloaded':
                $distribution['overloaded']++;
                break;
            case 'Fully Loaded':
                $distribution['fully_loaded']++;
                break;
            case 'Underloaded':
            default:
                $distribution['underloaded']++;
                break;
        }
    }
    
    // Add percentage calculations
    $total = $distribution['total_faculty'];
    if ($total > 0) {
        $distribution['underloaded_percent'] = round(($distribution['underloaded'] / $total) * 100, 1);
        $distribution['fully_loaded_percent'] = round(($distribution['fully_loaded'] / $total) * 100, 1);
        $distribution['overloaded_percent'] = round(($distribution['overloaded'] / $total) * 100, 1);
    } else {
        $distribution['underloaded_percent'] = 0;
        $distribution['fully_loaded_percent'] = 0;
        $distribution['overloaded_percent'] = 0;
    }
    
    echo json_encode($distribution, JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'status' => 'error'
    ]);
}
?>
