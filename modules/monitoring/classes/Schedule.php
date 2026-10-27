<?php
include_once __DIR__ . '/../../../database/db.php';

 class Schedule {
    private $conn;
    private $table = "cc_schedule";

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Get schedule by date and time - IMPROVED VERSION
     */
    public function getByDateTime($date, $time_breaker, $semester, $school_year) {
        $day = date('l', strtotime($date));
        
        // Parse time breaker to handle various formats
        $times = explode('-', $time_breaker);
        $start_time = trim($times[0]);
        $end_time = trim($times[1]);
        
        // Convert to 24-hour format for database comparison
        $start_24hr = date('H:i:s', strtotime($start_time));
        $end_24hr = date('H:i:s', strtotime($end_time));
        
        // More flexible query - matches by time range or exact official_time
        $query = "SELECT s.*, f.first_name, f.last_name, sec.section_code 
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE s.day_of_week = :day 
                  AND s.semester = :semester 
                  AND s.school_year = :school_year
                  AND (
                      s.official_time = :time_breaker 
                      OR (TIME(s.start_time) BETWEEN :start_time AND :end_time)
                      OR (TIME(s.end_time) BETWEEN :start_time AND :end_time)
                      OR (TIME(s.start_time) <= :start_time AND TIME(s.end_time) >= :end_time)
                  )
                  ORDER BY s.room, s.start_time";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":day", $day);
        $stmt->bindParam(":semester", $semester);
        $stmt->bindParam(":school_year", $school_year);
        $stmt->bindParam(":time_breaker", $time_breaker);
        $stmt->bindParam(":start_time", $start_24hr);
        $stmt->bindParam(":end_time", $end_24hr);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Debug method to check what schedules exist
     */
    public function debugSchedules($semester, $school_year) {
        $query = "SELECT s.*, f.first_name, f.last_name, sec.section_code 
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE s.semester = ? AND s.school_year = ?
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$semester, $school_year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new schedule
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  SET room = :room, 
                      official_time = :official_time, 
                      start_time = :start_time, 
                      end_time = :end_time,
                      day_of_week = :day_of_week, 
                      subject_code = :subject_code,
                      grade_section_id = :grade_section_id, 
                      faculty_id = :faculty_id,
                      semester = :semester, 
                      school_year = :school_year";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $data['room'] = htmlspecialchars(strip_tags($data['room']));
        $data['official_time'] = htmlspecialchars(strip_tags($data['official_time']));
        $data['start_time'] = htmlspecialchars(strip_tags($data['start_time']));
        $data['end_time'] = htmlspecialchars(strip_tags($data['end_time']));
        $data['day_of_week'] = htmlspecialchars(strip_tags($data['day_of_week']));
        $data['subject_code'] = htmlspecialchars(strip_tags($data['subject_code']));
        $data['grade_section_id'] = htmlspecialchars(strip_tags($data['grade_section_id']));
        $data['faculty_id'] = htmlspecialchars(strip_tags($data['faculty_id']));
        $data['semester'] = htmlspecialchars(strip_tags($data['semester']));
        $data['school_year'] = htmlspecialchars(strip_tags($data['school_year']));
        
        $stmt->bindParam(":room", $data['room']);
        $stmt->bindParam(":official_time", $data['official_time']);
        $stmt->bindParam(":start_time", $data['start_time']);
        $stmt->bindParam(":end_time", $data['end_time']);
        $stmt->bindParam(":day_of_week", $data['day_of_week']);
        $stmt->bindParam(":subject_code", $data['subject_code']);
        $stmt->bindParam(":grade_section_id", $data['grade_section_id']);
        $stmt->bindParam(":faculty_id", $data['faculty_id']);
        $stmt->bindParam(":semester", $data['semester']);
        $stmt->bindParam(":school_year", $data['school_year']);
        
        return $stmt->execute();
    }

    /**
     * Get schedule by ID
     */
    public function getById($id) {
        $query = "SELECT s.*, f.first_name, f.last_name, f.faculty_code,
                         sec.section_code, sec.grade_level, sec.program
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE s.id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update schedule
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET room = :room, 
                      official_time = :official_time, 
                      start_time = :start_time, 
                      end_time = :end_time,
                      day_of_week = :day_of_week, 
                      subject_code = :subject_code,
                      grade_section_id = :grade_section_id, 
                      faculty_id = :faculty_id,
                      semester = :semester, 
                      school_year = :school_year
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $data['room'] = htmlspecialchars(strip_tags($data['room']));
        $data['official_time'] = htmlspecialchars(strip_tags($data['official_time']));
        $data['start_time'] = htmlspecialchars(strip_tags($data['start_time']));
        $data['end_time'] = htmlspecialchars(strip_tags($data['end_time']));
        $data['day_of_week'] = htmlspecialchars(strip_tags($data['day_of_week']));
        $data['subject_code'] = htmlspecialchars(strip_tags($data['subject_code']));
        $data['grade_section_id'] = htmlspecialchars(strip_tags($data['grade_section_id']));
        $data['faculty_id'] = htmlspecialchars(strip_tags($data['faculty_id']));
        $data['semester'] = htmlspecialchars(strip_tags($data['semester']));
        $data['school_year'] = htmlspecialchars(strip_tags($data['school_year']));
        $id = htmlspecialchars(strip_tags($id));
        
        $stmt->bindParam(":room", $data['room']);
        $stmt->bindParam(":official_time", $data['official_time']);
        $stmt->bindParam(":start_time", $data['start_time']);
        $stmt->bindParam(":end_time", $data['end_time']);
        $stmt->bindParam(":day_of_week", $data['day_of_week']);
        $stmt->bindParam(":subject_code", $data['subject_code']);
        $stmt->bindParam(":grade_section_id", $data['grade_section_id']);
        $stmt->bindParam(":faculty_id", $data['faculty_id']);
        $stmt->bindParam(":semester", $data['semester']);
        $stmt->bindParam(":school_year", $data['school_year']);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    /**
     * Delete schedule
     */
    public function delete($id) {
        try {
            // Start transaction
            $this->conn->beginTransaction();
            
            // Delete attendance records first
            $stmt1 = $this->conn->prepare("DELETE FROM mon_attendance WHERE schedule_id = ?");
            $stmt1->execute([$id]);
            
            // Delete schedule
            $stmt2 = $this->conn->prepare("DELETE FROM cc_schedule WHERE id = ?");
            $result = $stmt2->execute([$id]);
            
            // Commit transaction
            $this->conn->commit();
            return $result;
            
        } catch (PDOException $e) {
            // Rollback on error
            $this->conn->rollBack();
            error_log("Error deleting schedule: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all schedules with filters
     */
    public function getAllWithFilters($semester, $school_year, $day_of_week = null, $room = null) {
        $query = "SELECT s.*, f.first_name, f.last_name, f.faculty_code,
                         sec.section_code, sec.grade_level, sec.program
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE s.semester = ? AND s.school_year = ?";
        
        $params = [$semester, $school_year];
        
        if ($day_of_week) {
            $query .= " AND s.day_of_week = ?";
            $params[] = $day_of_week;
        }
        
        if ($room) {
            $query .= " AND s.room = ?";
            $params[] = $room;
        }
        
        $query .= " ORDER BY 
                    FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                    s.start_time, s.room";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get distinct days for a semester and school year
     */
    public function getDistinctDays($semester, $school_year) {
        $query = "SELECT DISTINCT day_of_week 
                  FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ? 
                  ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get distinct rooms for a semester and school year
     */
    public function getDistinctRooms($semester, $school_year) {
        $query = "SELECT DISTINCT room 
                  FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ? 
                  ORDER BY room";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get schedules by faculty
     */
    public function getByFaculty($faculty_id, $semester, $school_year) {
        $query = "SELECT s.*, sec.section_code, sec.grade_level
                  FROM " . $this->table . " s
                  LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE s.faculty_id = ? 
                  AND s.semester = ? 
                  AND s.school_year = ?
                  ORDER BY s.day_of_week, s.start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $faculty_id);
        $stmt->bindParam(2, $semester);
        $stmt->bindParam(3, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get schedules by section
     */
    public function getBySection($section_id, $semester, $school_year) {
        $query = "SELECT s.*, f.first_name, f.last_name, f.faculty_code
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  WHERE s.grade_section_id = ? 
                  AND s.semester = ? 
                  AND s.school_year = ?
                  ORDER BY s.day_of_week, s.start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $section_id);
        $stmt->bindParam(2, $semester);
        $stmt->bindParam(3, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get schedules by room
     */
    public function getByRoom($room, $semester, $school_year) {
        $query = "SELECT s.*, f.first_name, f.last_name, sec.section_code
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE s.room = ? 
                  AND s.semester = ? 
                  AND s.school_year = ?
                  ORDER BY s.day_of_week, s.start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $room);
        $stmt->bindParam(2, $semester);
        $stmt->bindParam(3, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get schedule statistics
     */
    public function getStatistics($semester, $school_year) {
        $query = "SELECT 
                    COUNT(*) as total_schedules,
                    COUNT(DISTINCT room) as total_rooms,
                    COUNT(DISTINCT faculty_id) as total_faculty,
                    COUNT(DISTINCT grade_section_id) as total_sections,
                    COUNT(DISTINCT subject_code) as total_subjects
                  FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check for schedule conflicts
     */
    public function checkConflict($room, $day_of_week, $start_time, $end_time, $semester, $school_year, $exclude_id = null) {
        $query = "SELECT COUNT(*) as conflict_count 
                  FROM " . $this->table . " 
                  WHERE room = ? 
                  AND day_of_week = ? 
                  AND semester = ? 
                  AND school_year = ?
                  AND (
                    (start_time < ? AND end_time > ?) OR
                    (start_time < ? AND end_time > ?) OR
                    (start_time >= ? AND end_time <= ?)
                  )";
        
        $params = [$room, $day_of_week, $semester, $school_year, $end_time, $start_time, $end_time, $start_time, $start_time, $end_time];
        
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['conflict_count'] > 0;
    }

    /**
     * Get weekly schedule summary
     */
    public function getWeeklySummary($semester, $school_year) {
        $query = "SELECT 
                    day_of_week,
                    COUNT(*) as total_classes,
                    GROUP_CONCAT(DISTINCT room ORDER BY room) as rooms,
                    MIN(start_time) as earliest_start,
                    MAX(end_time) as latest_end
                  FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ?
                  GROUP BY day_of_week
                  ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search schedules
     */
    public function search($keyword, $semester, $school_year) {
        $query = "SELECT s.*, f.first_name, f.last_name, f.faculty_code,
                         sec.section_code, sec.grade_level
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.grade_section_id = sec.id
                  WHERE (s.room LIKE ? OR s.subject_code LIKE ? 
                         OR f.first_name LIKE ? OR f.last_name LIKE ?
                         OR sec.section_code LIKE ?)
                  AND s.semester = ? AND s.school_year = ?
                  ORDER BY s.day_of_week, s.start_time";
        
        $stmt = $this->conn->prepare($query);
        $search_pattern = "%{$keyword}%";
        $stmt->bindParam(1, $search_pattern);
        $stmt->bindParam(2, $search_pattern);
        $stmt->bindParam(3, $search_pattern);
        $stmt->bindParam(4, $search_pattern);
        $stmt->bindParam(5, $search_pattern);
        $stmt->bindParam(6, $semester);
        $stmt->bindParam(7, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if schedule exists
     */
    public function scheduleExists($room, $day_of_week, $start_time, $subject_code, $semester, $school_year) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE room = ? AND day_of_week = ? 
                  AND start_time = ? AND subject_code = ?
                  AND semester = ? AND school_year = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $room);
        $stmt->bindParam(2, $day_of_week);
        $stmt->bindParam(3, $start_time);
        $stmt->bindParam(4, $subject_code);
        $stmt->bindParam(5, $semester);
        $stmt->bindParam(6, $school_year);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }
}
?>