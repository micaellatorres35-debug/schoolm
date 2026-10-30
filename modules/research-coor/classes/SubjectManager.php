<?php
class SubjectManager {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getAllSubjects() {
        $stmt = $this->conn->prepare("SELECT * FROM rgr_subjects");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSubjectAssignments() {
        $stmt = $this->conn->prepare("SELECT
                s.id,
                s.code,
                s.name,
                s.units,
                s.lecture_hours,
                s.lab_hours,
                cs.year_level,
                cs.semester,
                cu.id AS curriculum_id,
                cu.curriculum_name,
                cu.effective_year,
                cu.is_active,
                c.code AS course_code,
                c.name AS course_name
            FROM rgr_subjects s
            LEFT JOIN rgr_curriculum_subjects cs ON cs.subject_id = s.id
            LEFT JOIN rgr_curriculums cu ON cu.id = cs.curriculum_id
            LEFT JOIN rgr_courses c ON c.id = cu.course_id
            ORDER BY s.code, cs.year_level, cs.semester");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
