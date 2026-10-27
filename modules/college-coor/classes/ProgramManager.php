<?php
class ProgramManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllPrograms() {
        $stmt = $this->conn->prepare("SELECT
                c.id,
                c.code,
                c.name,
                c.years,
                cu.effective_year AS effective_year,
                cu.is_active AS is_active,
                COUNT(DISTINCT CASE WHEN es.enrollment_status = 'enrolled' THEN es.student_id END) AS enrolled_students
            FROM rgr_courses c
            LEFT JOIN rgr_curriculums cu ON cu.course_id = c.id
            LEFT JOIN enr_students es ON es.course_id = c.id AND es.enrollment_status = 'enrolled'
            GROUP BY c.id, c.code, c.name, c.years, cu.effective_year, cu.is_active
            ORDER BY c.code");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseCurriculum($courseId) {
        $courseId = (int) $courseId;
        if ($courseId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT
                id,
                course_id,
                curriculum_name,
                effective_year,
                is_active
            FROM rgr_curriculums
            WHERE course_id = :courseId
            ORDER BY is_active DESC, effective_year DESC, id DESC
            LIMIT 1");
        $stmt->execute([':courseId' => $courseId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getCurriculum($courseId, $curriculumId = null) {
        $courseId = (int) $courseId;
        if ($courseId <= 0) {
            return [];
        }

        $sql = "SELECT
                cs.year_level,
                cs.semester,
                s.code,
                s.name,
                s.units
            FROM rgr_curriculum_subjects cs
            INNER JOIN rgr_subjects s ON s.id = cs.subject_id
            INNER JOIN rgr_curriculums cu ON cu.id = cs.curriculum_id
            WHERE cu.course_id = :courseId";

        $params = [':courseId' => $courseId];

        if ($curriculumId !== null) {
            $sql .= " AND cs.curriculum_id = :curriculumId";
            $params[':curriculumId'] = (int) $curriculumId;
        } else {
            $sql .= " AND cu.is_active = 1";
        }

        $sql .= " ORDER BY cs.year_level, cs.semester, s.code";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $curriculum = [];
        foreach ($rows as $row) {
            $year = (int)($row['year_level'] ?? 0);
            $sem = $row['semester'];
            if (!isset($curriculum[$year])) {
                $curriculum[$year] = [];
            }
            if (!isset($curriculum[$year][$sem])) {
                $curriculum[$year][$sem] = [];
            }
            $curriculum[$year][$sem][] = [
                'code' => $row['code'],
                'name' => $row['name'],
                'units' => (int)($row['units'] ?? 0)
            ];
        }

        $result = [];
        foreach ($curriculum as $year => $semesters) {
            $semArr = [];
            foreach ($semesters as $sem => $subjects) {
                $semArr[] = ['semester' => $sem, 'subjects' => $subjects];
            }
            $result[] = ['year_level' => $year, 'semesters' => $semArr];
        }

        return $result;
    }
}
