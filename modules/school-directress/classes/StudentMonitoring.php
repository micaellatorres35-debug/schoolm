<?php

/**
 * StudentMonitoring
 *
 * Handles all data access for the Student Monitoring page (School Directress
 * module). Reads directly from enr_students and related enrollment tables.
 * Follows the same pattern as Employee.php / Announcement.php: optional
 * constructor injection, falling back to Database::getConnection().
 */
class StudentMonitoring
{
    private PDO $conn;

    public function __construct(?PDO $conn = null)
    {
        if ($conn) {
            $this->conn = $conn;
        } else {
            require_once __DIR__ . '/../../../database/db.php';
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Fetches students, distinct courses, and computed stats in one call.
     * If the query fails, returns empty results with $error set rather
     * than masking the failure with fake data.
     */
    public function getDashboardData(): array
    {
        try {
            $students = $this->getStudents();
            $courses = $this->getDistinctCourses();

            return [
                'students' => $students,
                'courses' => $courses,
                'stats' => $this->computeStats($students),
                'error' => null,
            ];
        } catch (PDOException $e) {
            return [
                'students' => [],
                'courses' => [],
                'stats' => $this->computeStats([]),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getStudents(): array
    {
        $stmt = $this->conn->query("
            SELECT
                es.student_number,
                ea.first_name,
                ea.middle_name,
                ea.surname AS last_name,
                ea.sex AS gender,
                ea.date_of_birth AS birth_date,
                rc.code AS course_code,
                rc.name AS course,
                es.year_level,
                es.section_id AS section,
                ea.email,
                ea.contact_number AS contact,
                ea.address_complete AS address,
                es.enrollment_status AS academic_status,
                es.enrolled_at
            FROM enr_students AS es
            JOIN cc_sections AS cs ON es.section_id = cs.id
            JOIN enr_applicants AS ea ON es.applicant_id = ea.applicant_id
            JOIN rgr_courses AS rc ON es.course_id = rc.id
            ORDER BY es.enrolled_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Pulls the distinct courses currently used by enrolled students. */
    public function getDistinctCourses(): array
    {
        $stmt = $this->conn->query("
            SELECT DISTINCT rc.id, rc.code, rc.name
            FROM enr_students AS es
            JOIN rgr_courses AS rc ON es.course_id = rc.id
            WHERE es.course_id IS NOT NULL
            ORDER BY rc.name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function computeStats(array $students): array
    {
        $stats = ['total' => count($students), 'enrolled' => 0, 'on_leave' => 0, 'graduated' => 0, 'dropped' => 0];

        foreach ($students as $r) {
            $key = $r['academic_status'];
            if (isset($stats[$key])) {
                $stats[$key]++;
            }
        }

        return $stats;
    }

    /* ── Presentation helpers ──────────────────────────────────────── */

    public static function fullName(array $r): string
    {
        return trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'][0] . '. ' : '') . $r['last_name']);
    }

    public static function initials(array $r): string
    {
        return strtoupper(substr($r['first_name'], 0, 1) . substr($r['last_name'], 0, 1));
    }

    public static function age(?string $birthDate): ?int
    {
        if (!$birthDate) {
            return null;
        }
        return (int) date_diff(date_create($birthDate), date_create('today'))->y;
    }

    public static function statusMeta(string $s): array
    {
        $map = [
            'enrolled' => ['label' => 'Enrolled', 'cls' => 's-active'],
            'on_leave' => ['label' => 'On Leave', 'cls' => 's-inactive'],
            'graduated' => ['label' => 'Graduated', 'cls' => 's-graduated'],
            'dropped' => ['label' => 'Dropped', 'cls' => 's-default'],
        ];

        return $map[$s] ?? ['label' => ucfirst($s), 'cls' => 's-default'];
    }

    /** year_level is stored as an integer and treated as a 1–5 standing. */
    public static function yearLabel($y): string
    {
        $y = (int) $y;
        $labels = ['', '1st', '2nd', '3rd', '4th', '5th'];
        if ($y < 1) {
            return '—';
        }
        return ($labels[$y] ?? "{$y}th") . ' Yr';
    }

}

