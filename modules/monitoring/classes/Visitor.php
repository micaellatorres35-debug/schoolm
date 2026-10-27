<?php
/**
 * Visitor Management Class
 * Fixed version with corrected path to database
 */
class Visitor {
    private $conn;
    private $table = 'mon_visitors_log';
    private $errors = [];

    public function __construct() {
    try {
        // Try multiple possible paths for database.php
        $possiblePaths = [
            __DIR__ . '/../../../database/db.php',  // This should be correct
            __DIR__ . '/../../database/db.php',
            __DIR__ . '/../database/db.php',
            'C:/xampp/htdocs/sms/database/db.php',
        ];
        
        $dbFile = null;
        $triedPaths = [];
        
        foreach ($possiblePaths as $path) {
            $triedPaths[] = $path;
            if (file_exists($path)) {
                $dbFile = $path;
                error_log("✅ Found database at: " . $path);
                break;
            } else {
                error_log("❌ Not found: " . $path);
            }
        }
        
        if (!$dbFile) {
            throw new Exception("Database file not found. Tried: " . implode(', ', $triedPaths));
        }
        
        require_once $dbFile;
        
        if (!class_exists('Database')) {
            throw new Exception("Database class not found in " . $dbFile);
        }
        
        $database = new Database();
        $this->conn = $database->getConnection();
        
        if (!$this->conn) {
            throw new Exception("Failed to get database connection");
        }
        
        // Test connection and table
        $this->testTable();
        
    } catch (Exception $e) {
        error_log("❌ Visitor Constructor Error: " . $e->getMessage());
        throw new Exception("Failed to initialize Visitor class: " . $e->getMessage());
    }
}
    /**
     * Test if table exists
     */
    private function testTable() {
        try {
            $result = $this->conn->query("SHOW TABLES LIKE '{$this->table}'");
            if ($result->rowCount() == 0) {
                throw new Exception("Table '{$this->table}' does not exist in database");
            }
            return true;
        } catch (PDOException $e) {
            throw new Exception("Table test failed: " . $e->getMessage());
        }
    }

    /**
     * Insert new visitor (time-in)
     */
    public function insert($name, $purpose, $person, $department, $contact, $id_presented, $recorded_by = 'SYSTEM') {
        try {
            // Log the attempt
            error_log("=== VISITOR INSERT ATTEMPT ===");
            error_log("Time: " . date('Y-m-d H:i:s'));
            error_log("Name: " . $name);
            error_log("Purpose: " . $purpose);
            error_log("Person: " . $person);
            error_log("Department: " . $department);
            error_log("Contact: " . $contact);
            error_log("ID Presented: " . $id_presented);
            error_log("Recorded By: " . $recorded_by);
            
            // Validate
            if (empty($name)) throw new Exception("Name is required");
            if (empty($purpose)) throw new Exception("Purpose is required");
            if (empty($person)) throw new Exception("Person to visit is required");
            
            // Ensure recorded_by has a value
            if (empty($recorded_by)) {
                $recorded_by = 'SYSTEM';
            }
            
            // Prepare SQL
            $sql = "INSERT INTO {$this->table} 
                    (visitor_name, purpose, person_to_visit, department, 
                     contact_number, id_presented, recorded_by, time_in) 
                    VALUES 
                    (:name, :purpose, :person, :department, 
                     :contact, :id_presented, :recorded_by, NOW())";
            
            error_log("SQL: " . $sql);
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':purpose', $purpose);
            $stmt->bindParam(':person', $person);
            $stmt->bindParam(':department', $department);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':id_presented', $id_presented);
            $stmt->bindParam(':recorded_by', $recorded_by);
            
            // Execute
            $result = $stmt->execute();
            
            if ($result) {
                $lastId = $this->conn->lastInsertId();
                error_log("✅ INSERT SUCCESSFUL! ID: " . $lastId);
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ INSERT FAILED: " . json_encode($errorInfo));
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("❌ PDO ERROR: " . $e->getMessage());
            error_log("SQL State: " . ($e->errorInfo[0] ?? 'N/A'));
            error_log("Error Code: " . ($e->errorInfo[1] ?? 'N/A'));
            error_log("Error Message: " . ($e->errorInfo[2] ?? 'N/A'));
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("❌ GENERAL ERROR: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $result = $this->conn->query("SELECT 1");
            return "Database connection OK";
        } catch (Exception $e) {
            throw new Exception("Connection test failed: " . $e->getMessage());
        }
    }

    /**
     * Get all visitors
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY time_in DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            throw new Exception("Error loading visitors: " . $e->getMessage());
        }
    }

    /**
     * Get visitor by ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            throw new Exception("Error loading visitor: " . $e->getMessage());
        }
    }

    /**
     * Time out visitor
     */
    public function timeout($id) {
    try {
        // Clear any previous errors
        $this->errors = [];
        
        // Validate ID
        if (empty($id) || !is_numeric($id)) {
            $this->errors[] = "Invalid visitor ID";
            error_log("Timeout failed: Invalid ID - " . $id);
            return false;
        }
        
        $id = intval($id);
        
        // Check if visitor exists and is active first
        $visitor = $this->getById($id);
        if (!$visitor) {
            $this->errors[] = "Visitor not found";
            error_log("Timeout failed: Visitor ID $id not found");
            return false;
        }
        
        // Check if already timed out
        if (!empty($visitor['time_out'])) {
            $this->errors[] = "Visitor already timed out";
            error_log("Timeout failed: Visitor ID $id already timed out at " . $visitor['time_out']);
            return false;
        }
        
        // Prepare SQL with explicit check for NULL time_out
        $sql = "UPDATE {$this->table} 
                SET time_out = NOW() 
                WHERE id = :id AND time_out IS NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $rowCount = $stmt->rowCount();
        
        if ($rowCount > 0) {
            error_log("✅ Timeout successful for ID: " . $id);
            return true;
        } else {
            // Double-check why no rows were affected
            $checkAgain = $this->getById($id);
            if (!empty($checkAgain['time_out'])) {
                $this->errors[] = "Visitor was already timed out";
                error_log("Timeout failed: Visitor ID $id was already timed out (detected in check)");
            } else {
                $this->errors[] = "No rows affected - update failed";
                error_log("Timeout failed: No rows affected for ID $id");
            }
            return false;
        }
        
    } catch (PDOException $e) {
        $this->errors[] = "Database error: " . $e->getMessage();
        error_log("❌ PDO Error in timeout for ID $id: " . $e->getMessage());
        error_log("SQL State: " . ($e->errorInfo[0] ?? 'N/A'));
        error_log("Error Code: " . ($e->errorInfo[1] ?? 'N/A'));
        error_log("Error Message: " . ($e->errorInfo[2] ?? 'N/A'));
        return false;
    } catch (Exception $e) {
        $this->errors[] = "Unexpected error: " . $e->getMessage();
        error_log("❌ General Error in timeout for ID $id: " . $e->getMessage());
        return false;
    }
}

    /**
     * Check if visitor exists
     */
    public function exists($id) {
        try {
            $sql = "SELECT id FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log("Error in exists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if visitor is active
     */
    public function isActive($id) {
        try {
            $sql = "SELECT id FROM {$this->table} WHERE id = :id AND time_out IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log("Error in isActive: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get today's visitors
     */
    public function getTodayVisitors() {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE DATE(time_in) = CURDATE() ORDER BY time_in DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getTodayVisitors: " . $e->getMessage());
            throw new Exception("Error loading today's visitors: " . $e->getMessage());
        }
    }

    /**
     * Get active visitors
     */
    public function getActiveVisitors() {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE time_out IS NULL ORDER BY time_in DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getActiveVisitors: " . $e->getMessage());
            throw new Exception("Error loading active visitors: " . $e->getMessage());
        }
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get last insert ID
     */
    public function getLastInsertId() {
        try {
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            return null;
        }
    }
}
?>