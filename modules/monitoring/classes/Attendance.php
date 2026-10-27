<?php
include_once __DIR__ . '/../../../database/db.php';

class Attendance {
    private $conn;
    private $table = "mon_attendance";

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Check if attendance record already exists
     */
    public function checkExisting($schedule_id, $attendance_date, $faculty_id) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE schedule_id = ? 
                  AND attendance_date = ? 
                  AND faculty_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $schedule_id);
        $stmt->bindParam(2, $attendance_date);
        $stmt->bindParam(3, $faculty_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insert new attendance record
     */
    public function insertAttendance($data) {
        $query = "INSERT INTO " . $this->table . " 
                  SET schedule_id = :schedule_id, 
                      attendance_date = :attendance_date,
                      faculty_id = :faculty_id, 
                      status = :status, 
                      remarks = :remarks,
                      student_count = :student_count,
                      recorded_by = :recorded_by, 
                      class_type = :class_type, 
                      online_platform = :online_platform, 
                      meeting_link = :meeting_link,
                      meeting_id = :meeting_id, 
                      meeting_password = :meeting_password,
                      online_attendance_file = :online_attendance_file,
                      internet_status = :internet_status, 
                      connectivity_issues = :connectivity_issues";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":schedule_id", $data['schedule_id']);
        $stmt->bindParam(":attendance_date", $data['attendance_date']);
        $stmt->bindParam(":faculty_id", $data['faculty_id']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":remarks", $data['remarks']);
        $stmt->bindParam(":student_count", $data['student_count'], PDO::PARAM_INT);
        $stmt->bindParam(":recorded_by", $data['recorded_by']);
        $stmt->bindParam(":class_type", $data['class_type']);
        $stmt->bindParam(":online_platform", $data['online_platform']);
        $stmt->bindParam(":meeting_link", $data['meeting_link']);
        $stmt->bindParam(":meeting_id", $data['meeting_id']);
        $stmt->bindParam(":meeting_password", $data['meeting_password']);
        $stmt->bindParam(":online_attendance_file", $data['online_attendance_file']);
        $stmt->bindParam(":internet_status", $data['internet_status']);
        $stmt->bindParam(":connectivity_issues", $data['connectivity_issues']);
        
        return $stmt->execute();
    }

    /**
     * Update existing attendance record
     */
    public function updateAttendance($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET status = :status, 
                      remarks = :remarks, 
                      student_count = :student_count,
                      recorded_by = :recorded_by, 
                      recorded_at = NOW(),
                      class_type = :class_type, 
                      online_platform = :online_platform,
                      meeting_link = :meeting_link, 
                      meeting_id = :meeting_id,
                      meeting_password = :meeting_password, 
                      online_attendance_file = :online_attendance_file,
                      internet_status = :internet_status, 
                      connectivity_issues = :connectivity_issues
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":remarks", $data['remarks']);
        $stmt->bindParam(":student_count", $data['student_count'], PDO::PARAM_INT);
        $stmt->bindParam(":recorded_by", $data['recorded_by']);
        $stmt->bindParam(":class_type", $data['class_type']);
        $stmt->bindParam(":online_platform", $data['online_platform']);
        $stmt->bindParam(":meeting_link", $data['meeting_link']);
        $stmt->bindParam(":meeting_id", $data['meeting_id']);
        $stmt->bindParam(":meeting_password", $data['meeting_password']);
        $stmt->bindParam(":online_attendance_file", $data['online_attendance_file']);
        $stmt->bindParam(":internet_status", $data['internet_status']);
        $stmt->bindParam(":connectivity_issues", $data['connectivity_issues']);
        
        return $stmt->execute();
    }

    /**
     * Get attendance by date
     */
    public function getByDate($date) {
        $query = "SELECT 
                    a.*, 
                    s.room, 
                    s.official_time, 
                    s.subject_code,
                    f.first_name, 
                    f.last_name, 
                    sec.section_code,
                    CASE 
                        WHEN a.class_type = 'online' THEN 
                            CONCAT('Online (', a.online_platform, ')')
                        ELSE 
                            CONCAT('Onsite - Room ', s.room)
                    END as class_location,
                    a.internet_status, 
                    a.connectivity_issues,
                    a.student_count
                  FROM " . $this->table . " a
                  JOIN cc_schedule s ON a.schedule_id = s.id
                  JOIN cc_faculty f ON a.faculty_id = f.id
                  JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE a.attendance_date = ?
                  ORDER BY a.class_type, s.room";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get attendance report with filters
     */
    public function getReport($start_date, $end_date, $faculty_id = null) {
        $query = "SELECT 
                    a.*, 
                    s.room, 
                    s.subject_code, 
                    f.first_name, 
                    f.last_name,
                    COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as late_days,
                    COUNT(CASE WHEN a.class_type = 'online' THEN 1 END) as online_classes,
                    COUNT(CASE WHEN a.class_type = 'onsite' THEN 1 END) as onsite_classes,
                    COUNT(CASE WHEN a.internet_status = 'unstable' THEN 1 END) as internet_issues,
                    GROUP_CONCAT(DISTINCT a.online_platform) as platforms_used,
                    SUM(a.student_count) as total_students,
                    AVG(a.student_count) as avg_students_per_class
                  FROM " . $this->table . " a
                  JOIN cc_schedule s ON a.schedule_id = s.id
                  JOIN cc_faculty f ON a.faculty_id = f.id
                  WHERE a.attendance_date BETWEEN ? AND ?";
        
        $params = [$start_date, $end_date];
        
        if ($faculty_id) {
            $query .= " AND a.faculty_id = ?";
            $params[] = $faculty_id;
        }
        
        $query .= " GROUP BY a.faculty_id, s.subject_code
                    ORDER BY f.last_name, a.attendance_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get student count statistics
     */
    public function getStudentCountStats($start_date, $end_date) {
        $query = "SELECT 
                    DATE(attendance_date) as date,
                    SUM(student_count) as daily_total,
                    AVG(student_count) as daily_average,
                    COUNT(DISTINCT faculty_id) as faculty_with_students,
                    MAX(student_count) as max_students_in_class,
                    MIN(student_count) as min_students_in_class
                  FROM " . $this->table . "
                  WHERE attendance_date BETWEEN ? AND ?
                  AND status IN ('Present', 'Late')
                  GROUP BY DATE(attendance_date)
                  ORDER BY date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get online class summary
     */
    public function getOnlineClassSummary($start_date, $end_date) {
        $query = "SELECT 
                    COUNT(*) as total_online_classes,
                    COUNT(DISTINCT faculty_id) as faculty_with_online,
                    online_platform,
                    COUNT(*) as platform_count,
                    internet_status,
                    COUNT(*) as status_count,
                    SUM(student_count) as total_students_online,
                    AVG(student_count) as avg_students_online
                  FROM " . $this->table . "
                  WHERE class_type = 'online'
                  AND attendance_date BETWEEN ? AND ?
                  GROUP BY online_platform, internet_status WITH ROLLUP";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty attendance history
     */
    public function getFacultyHistory($faculty_id, $limit = 30) {
        $query = "SELECT 
                    a.*, 
                    s.subject_code, 
                    sec.section_code,
                    s.official_time
                  FROM " . $this->table . " a
                  JOIN cc_schedule s ON a.schedule_id = s.id
                  JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE a.faculty_id = ?
                  ORDER BY a.attendance_date DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $faculty_id);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single attendance record by ID
     */
    public function getOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Delete attendance record
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
?>