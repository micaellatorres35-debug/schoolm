<?php
class SectionManager {
    private $conn;
    private $table = "cc_sections";

    public $id;
    public $section_code;
    public $grade_level;
    public $program;
    public $semester;
    public $school_year;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT 
                    cs.id,
                    cs.section_code,
                    cs.grade_level,
                    cs.program_id,
                    c.code AS program_code,
                    c.name AS program_name,
                    cs.school_year_id,
                    cs.semester_id,
                    cs.adviser_id,
                    CONCAT(COALESCE(cf.first_name, ''), ' ', COALESCE(cf.last_name, '')) AS adviser_name,
                    sy.name AS school_year_name,
                    sem.name AS semester_name,
                    COUNT(es.student_id) AS total_students
                  FROM " . $this->table . " cs
                  LEFT JOIN rgr_courses c ON c.id = cs.program_id
                  LEFT JOIN cc_section_faculty sf ON sf.section_id = cs.id
                    AND sf.role = 'Adviser'
                    AND sf.school_year_id = cs.school_year_id
                    AND sf.semester_id = cs.semester_id
                  LEFT JOIN cc_faculty cf ON sf.faculty_id = cf.id
                  LEFT JOIN rgr_school_years sy ON cs.school_year_id = sy.id
                  LEFT JOIN rgr_semesters sem ON cs.semester_id = sem.id
                  LEFT JOIN enr_students es ON es.section_id = cs.id
                    AND es.enrollment_status = 'enrolled'
                  GROUP BY
                    cs.id,
                    cs.section_code,
                    cs.grade_level,
                    cs.program_id,
                    c.code,
                    c.name,
                    cs.school_year_id,
                    cs.semester_id,
                    cs.adviser_id,
                    cf.first_name,
                    cf.last_name,
                    sy.name,
                    sem.name
                  ORDER BY cs.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBySemester($semester, $school_year) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE semester = ? AND school_year = ? 
                  ORDER BY section_code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester);
        $stmt->bindParam(2, $school_year);
        $stmt->execute();
        return $stmt;
    }
}
?>