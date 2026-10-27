<?php
include_once __DIR__ . '/../../../database/db.php';

class Faculty {
    private $conn;
    private $table = "cc_faculty";

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Get all faculty members
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY last_name, first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty by code
     */
    public function getByCode($code) {
        $query = "SELECT * FROM " . $this->table . " WHERE faculty_code = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new faculty member
     */
    public function create($faculty_code, $first_name, $last_name, $email, $department) {
        $query = "INSERT INTO " . $this->table . " 
                  (faculty_code, first_name, last_name, email, department)
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $faculty_code = htmlspecialchars(strip_tags($faculty_code));
        $first_name = htmlspecialchars(strip_tags($first_name));
        $last_name = htmlspecialchars(strip_tags($last_name));
        $email = htmlspecialchars(strip_tags($email));
        $department = htmlspecialchars(strip_tags($department));
        
        $stmt->bindParam(1, $faculty_code);
        $stmt->bindParam(2, $first_name);
        $stmt->bindParam(3, $last_name);
        $stmt->bindParam(4, $email);
        $stmt->bindParam(5, $department);
        
        return $stmt->execute();
    }

    /**
     * Update faculty member
     */
    public function update($id, $faculty_code, $first_name, $last_name, $email, $department) {
        $query = "UPDATE " . $this->table . " 
                  SET faculty_code = ?,
                      first_name = ?,
                      last_name = ?,
                      email = ?,
                      department = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $faculty_code = htmlspecialchars(strip_tags($faculty_code));
        $first_name = htmlspecialchars(strip_tags($first_name));
        $last_name = htmlspecialchars(strip_tags($last_name));
        $email = htmlspecialchars(strip_tags($email));
        $department = htmlspecialchars(strip_tags($department));
        $id = htmlspecialchars(strip_tags($id));
        
        $stmt->bindParam(1, $faculty_code);
        $stmt->bindParam(2, $first_name);
        $stmt->bindParam(3, $last_name);
        $stmt->bindParam(4, $email);
        $stmt->bindParam(5, $department);
        $stmt->bindParam(6, $id);
        
        return $stmt->execute();
    }

    /**
     * Delete faculty member
     */
    public function delete($id) {
        // First check if faculty is being used in schedules
        $checkQuery = "SELECT COUNT(*) as count FROM cc_schedule WHERE faculty_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(1, $id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Faculty is assigned to schedules, cannot delete
            return false;
        }
        
        // Also check if faculty has attendance records
        $checkAttendanceQuery = "SELECT COUNT(*) as count FROM mon_attendance WHERE faculty_id = ?";
        $checkAttendanceStmt = $this->conn->prepare($checkAttendanceQuery);
        $checkAttendanceStmt->bindParam(1, $id);
        $checkAttendanceStmt->execute();
        $attendanceResult = $checkAttendanceStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attendanceResult['count'] > 0) {
            // Faculty has attendance records, cannot delete
            return false;
        }
        
        // Proceed with deletion
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    /**
     * Check if faculty code already exists
     */
    public function facultyCodeExists($faculty_code, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE faculty_code = ?";
        
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($exclude_id) {
            $stmt->bindParam(1, $faculty_code);
            $stmt->bindParam(2, $exclude_id);
        } else {
            $stmt->bindParam(1, $faculty_code);
        }
        
        $stmt->execute();
        return $stmt->fetch() ? true : false;
    }

    /**
     * Check if email already exists
     */
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($exclude_id) {
            $stmt->bindParam(1, $email);
            $stmt->bindParam(2, $exclude_id);
        } else {
            $stmt->bindParam(1, $email);
        }
        
        $stmt->execute();
        return $stmt->fetch() ? true : false;
    }

    /**
     * Get faculty by department
     */
    public function getByDepartment($department) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE department = ? 
                  ORDER BY last_name, first_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $department);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search faculty
     */
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE faculty_code LIKE ? 
                  OR first_name LIKE ? 
                  OR last_name LIKE ? 
                  OR email LIKE ? 
                  OR department LIKE ?
                  ORDER BY last_name, first_name";
        
        $stmt = $this->conn->prepare($query);
        $search = "%{$keyword}%";
        $stmt->bindParam(1, $search);
        $stmt->bindParam(2, $search);
        $stmt->bindParam(3, $search);
        $stmt->bindParam(4, $search);
        $stmt->bindParam(5, $search);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty statistics
     */
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_faculty,
                    COUNT(DISTINCT department) as total_departments,
                    GROUP_CONCAT(DISTINCT department ORDER BY department) as departments
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty with schedule counts
     */
    public function getWithScheduleCount($semester, $school_year) {
        $query = "SELECT f.*, 
                         COUNT(sch.id) as schedule_count,
                         GROUP_CONCAT(DISTINCT sch.subject_code) as subjects
                  FROM " . $this->table . " f
                  LEFT JOIN cc_schedule sch ON f.id = sch.faculty_id 
                      AND sch.semester = ? 
                      AND sch.school_year = ?
                  GROUP BY f.id
                  ORDER BY f.last_name, f.first_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty with attendance summary
     */
    public function getWithAttendanceSummary($start_date, $end_date) {
        $query = "SELECT 
                    f.*,
                    COUNT(DISTINCT a.id) as total_attendance_days,
                    SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN a.class_type = 'online' THEN 1 ELSE 0 END) as online_classes,
                    SUM(a.student_count) as total_students_taught
                  FROM " . $this->table . " f
                  LEFT JOIN mon_attendance a ON f.id = a.faculty_id 
                      AND a.attendance_date BETWEEN ? AND ?
                  GROUP BY f.id
                  ORDER BY f.last_name, f.first_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty by name (partial match)
     */
    public function getByName($name) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE first_name LIKE ? OR last_name LIKE ?
                  ORDER BY last_name, first_name
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $search = "%{$name}%";
        $stmt->bindParam(1, $search);
        $stmt->bindParam(2, $search);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty without schedules
     */
    public function getUnassignedFaculty($semester, $school_year) {
        $query = "SELECT f.* 
                  FROM " . $this->table . " f
                  LEFT JOIN cc_schedule sch ON f.id = sch.faculty_id 
                      AND sch.semester = ? 
                      AND sch.school_year = ?
                  WHERE sch.id IS NULL
                  ORDER BY f.last_name, f.first_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Bulk insert faculty
     */
    public function bulkInsert($faculty_list) {
        $successCount = 0;
        $errors = [];
        
        $this->conn->beginTransaction();
        
        try {
            foreach ($faculty_list as $faculty) {
                // Check if faculty code already exists
                if (!$this->facultyCodeExists($faculty['faculty_code'])) {
                    // Check if email already exists (if provided)
                    if (!empty($faculty['email']) && $this->emailExists($faculty['email'])) {
                        $errors[] = "Email already exists: " . $faculty['email'];
                        continue;
                    }
                    
                    $result = $this->create(
                        $faculty['faculty_code'],
                        $faculty['first_name'],
                        $faculty['last_name'],
                        $faculty['email'] ?? null,
                        $faculty['department'] ?? null
                    );
                    
                    if ($result) {
                        $successCount++;
                    } else {
                        $errors[] = "Failed to insert: " . $faculty['faculty_code'];
                    }
                } else {
                    $errors[] = "Faculty code already exists: " . $faculty['faculty_code'];
                }
            }
            
            $this->conn->commit();
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
        
        return [
            'success' => true,
            'inserted' => $successCount,
            'errors' => $errors
        ];
    }
}
?>