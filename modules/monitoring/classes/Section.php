<?php
include_once __DIR__ . '/../../../database/db.php';

class Section {
    private $conn;
    private $table = "cc_sections";

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Get all sections
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY section_code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get section by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get sections by semester
     */
    public function getBySemester($semester, $school_year) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ? 
                  ORDER BY section_code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new section
     */
    public function create($section_code, $grade_level, $program, $semester, $school_year) {
        $query = "INSERT INTO " . $this->table . " 
                  (section_code, grade_level, program, semester, school_year)
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $section_code = htmlspecialchars(strip_tags($section_code));
        $grade_level = htmlspecialchars(strip_tags($grade_level));
        $program = htmlspecialchars(strip_tags($program));
        $semester = htmlspecialchars(strip_tags($semester));
        $school_year = htmlspecialchars(strip_tags($school_year));
        
        $stmt->bindParam(1, $section_code);
        $stmt->bindParam(2, $grade_level);
        $stmt->bindParam(3, $program);
        $stmt->bindParam(4, $semester);
        $stmt->bindParam(5, $school_year);
        
        return $stmt->execute();
    }

    /**
     * Update section
     */
    public function update($id, $section_code, $grade_level, $program, $semester, $school_year) {
        $query = "UPDATE " . $this->table . " 
                  SET section_code = ?,
                      grade_level = ?,
                      program = ?,
                      semester = ?,
                      school_year = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $section_code = htmlspecialchars(strip_tags($section_code));
        $grade_level = htmlspecialchars(strip_tags($grade_level));
        $program = htmlspecialchars(strip_tags($program));
        $semester = htmlspecialchars(strip_tags($semester));
        $school_year = htmlspecialchars(strip_tags($school_year));
        $id = htmlspecialchars(strip_tags($id));
        
        $stmt->bindParam(1, $section_code);
        $stmt->bindParam(2, $grade_level);
        $stmt->bindParam(3, $program);
        $stmt->bindParam(4, $semester);
        $stmt->bindParam(5, $school_year);
        $stmt->bindParam(6, $id);
        
        return $stmt->execute();
    }

    /**
     * Delete section
     */
    public function delete($id) {
        // First check if section is being used in schedules
        $checkQuery = "SELECT COUNT(*) as count FROM cc_schedule WHERE grade_section_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(1, $id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Section is in use, cannot delete
            return false;
        }
        
        // Proceed with deletion
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    /**
     * Check if section code already exists
     */
    public function sectionExists($section_code, $semester, $school_year, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE section_code = ? AND semester = ? AND school_year = ?";
        
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($exclude_id) {
            $stmt->bindParam(1, $section_code);
            $stmt->bindParam(2, $semester);
            $stmt->bindParam(3, $school_year);
            $stmt->bindParam(4, $exclude_id);
        } else {
            $stmt->bindParam(1, $section_code);
            $stmt->bindParam(2, $semester);
            $stmt->bindParam(3, $school_year);
        }
        
        $stmt->execute();
        return $stmt->fetch() ? true : false;
    }

    /**
     * Get sections by grade level
     */
    public function getByGradeLevel($grade_level, $semester, $school_year) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE grade_level = ? AND semester = ? AND school_year = ?
                  ORDER BY section_code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $grade_level);
        $stmt->bindParam(2, $semester);
        $stmt->bindParam(3, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get sections by program
     */
    public function getByProgram($program, $semester, $school_year) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE program = ? AND semester = ? AND school_year = ?
                  ORDER BY section_code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $program);
        $stmt->bindParam(2, $semester);
        $stmt->bindParam(3, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get distinct grade levels
     */
    public function getDistinctGradeLevels($semester, $school_year) {
        $query = "SELECT DISTINCT grade_level 
                  FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ?
                  ORDER BY grade_level";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get distinct programs
     */
    public function getDistinctPrograms($semester, $school_year) {
        $query = "SELECT DISTINCT program 
                  FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ?
                  ORDER BY program";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get section statistics
     */
    public function getStatistics($semester, $school_year) {
        $query = "SELECT 
                    COUNT(*) as total_sections,
                    COUNT(DISTINCT grade_level) as total_grade_levels,
                    COUNT(DISTINCT program) as total_programs,
                    GROUP_CONCAT(DISTINCT grade_level ORDER BY grade_level) as grade_levels,
                    GROUP_CONCAT(DISTINCT program ORDER BY program) as programs
                  FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Search sections
     */
    public function search($keyword, $semester, $school_year) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE (section_code LIKE ? OR grade_level LIKE ? OR program LIKE ?)
                  AND semester = ? AND school_year = ?
                  ORDER BY section_code";
        
        $stmt = $this->conn->prepare($query);
        $search = "%{$keyword}%";
        $stmt->bindParam(1, $search);
        $stmt->bindParam(2, $search);
        $stmt->bindParam(3, $search);
        $stmt->bindParam(4, $semester);
        $stmt->bindParam(5, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get sections with schedule counts
     */
    public function getWithScheduleCount($semester, $school_year) {
        $query = "SELECT s.*, 
                         COUNT(sch.id) as schedule_count,
                         GROUP_CONCAT(DISTINCT sch.subject_code) as subjects
                  FROM " . $this->table . " s
                  LEFT JOIN cc_schedule sch ON s.id = sch.grade_section_id 
                      AND sch.semester = s.semester 
                      AND sch.school_year = s.school_year
                  WHERE s.semester = ? AND s.school_year = ?
                  GROUP BY s.id
                  ORDER BY s.section_code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Bulk insert sections
     */
    public function bulkInsert($sections) {
        $successCount = 0;
        $errors = [];
        
        $this->conn->beginTransaction();
        
        try {
            foreach ($sections as $section) {
                // Check if section already exists
                if (!$this->sectionExists($section['section_code'], $section['semester'], $section['school_year'])) {
                    $result = $this->create(
                        $section['section_code'],
                        $section['grade_level'],
                        $section['program'],
                        $section['semester'],
                        $section['school_year']
                    );
                    
                    if ($result) {
                        $successCount++;
                    } else {
                        $errors[] = "Failed to insert: " . $section['section_code'];
                    }
                } else {
                    $errors[] = "Section already exists: " . $section['section_code'];
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