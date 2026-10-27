<?php
include_once __DIR__ . '/../../../database/db.php';

class Facility {
    private $conn;
    private $table = 'mon_facilities_monitor';

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Insert new facility issue
     */
    public function insert($room, $type, $desc, $priority, $reported_by) {
        $sql = "INSERT INTO {$this->table}
                (room, issue_type, description, priority, reported_by, date_reported)
                VALUES
                (:room, :type, :desc, :priority, :reported_by, CURDATE())";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':room', $room);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':desc', $desc);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':reported_by', $reported_by);
        
        return $stmt->execute();
    }

    /**
     * Mark issue as fixed
     */
    public function markFixed($id) {
        $sql = "UPDATE {$this->table}
                SET status = 'Fixed', date_fixed = CURDATE()
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Get all facility issues
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table}
                ORDER BY status != 'Pending', priority DESC, date_reported DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single facility issue by ID
     */
    public function getOne($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update facility issue
     */
    public function update($id, $room, $type, $desc, $priority) {
        $sql = "UPDATE {$this->table}
                SET room = :room,
                    issue_type = :type,
                    description = :desc,
                    priority = :priority
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':room', $room);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':desc', $desc);
        $stmt->bindParam(':priority', $priority);
        
        return $stmt->execute();
    }

    /**
     * Delete facility issue
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Get issues by status
     */
    public function getByStatus($status) {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = :status
                ORDER BY priority DESC, date_reported DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get issues by priority
     */
    public function getByPriority($priority) {
        $sql = "SELECT * FROM {$this->table}
                WHERE priority = :priority
                ORDER BY date_reported DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':priority', $priority);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get issues by room
     */
    public function getByRoom($room) {
        $sql = "SELECT * FROM {$this->table}
                WHERE room = :room
                ORDER BY date_reported DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room', $room);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_issues,
                    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'Fixed' THEN 1 END) as fixed_count,
                    COUNT(CASE WHEN priority = 'High' THEN 1 END) as high_priority,
                    COUNT(CASE WHEN priority = 'Medium' THEN 1 END) as medium_priority,
                    COUNT(CASE WHEN priority = 'Low' THEN 1 END) as low_priority,
                    COUNT(DISTINCT room) as rooms_with_issues
                FROM {$this->table}";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>