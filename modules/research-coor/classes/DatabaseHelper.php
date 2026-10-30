
<?php
include_once __DIR__ . '/../../../database/db.php';

class DatabaseHelper {
    public function getUpcomingEvents($limit = 5) {
        $stmt = $this->conn->prepare("SELECT * FROM cc_events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get calendar events with upcoming status
    public function getCalendarEvents() {
        try {
            $stmt = $this->conn->prepare("SELECT event_title, event_date, start_time, status FROM cc_events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Summary methods for dashboard
    public function getTotalProgram() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM rgr_courses");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getTotalStudents() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM rgr_students");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getTotalFaculty() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM cc_faculty");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getTotalSections() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM cc_sections");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getTotalSubjects() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM rgr_subjects");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Chart Data Methods
    public function getStudentsPerProgram() {
        try {
            // Enrollment System is the source of truth for current student counts.
            // Keep the program master data from rgr_courses so labels come from
            // the existing SMS database program records.
            $stmt = $this->conn->prepare("SELECT 
                c.code AS program_code,
                COUNT(es.student_id) AS student_count
            FROM rgr_courses c
            INNER JOIN enr_students es ON es.course_id = c.id
                AND es.enrollment_status = 'enrolled'
            GROUP BY c.id, c.code, c.name
            ORDER BY student_count DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getFacultyLoadDistribution() {
        try {
            $stmt = $this->conn->prepare("SELECT 
                CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
                COUNT(DISTINCT fl.id) AS classes_assigned,
                COALESCE(SUM(s.units), 0) AS total_units
            FROM cc_faculty f
            LEFT JOIN (
                SELECT fl.id, fl.faculty_id, fl.subject_id
                FROM cc_faculty_load fl
                INNER JOIN rgr_school_years sy ON sy.id = fl.school_year_id
                    AND sy.is_active = 1
                INNER JOIN rgr_semesters sem ON sem.id = fl.semester_id
                    AND sem.is_active = 1
            ) fl ON fl.faculty_id = f.id
            LEFT JOIN rgr_subjects s ON fl.subject_id = s.id
            GROUP BY f.id, f.first_name, f.last_name
            ORDER BY total_units DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getStudentAcademicStatus() {
        try {
            $stmt = $this->conn->prepare("SELECT 
                CASE 
                    WHEN enrollment_status = 'enrolled' THEN 'Active'
                    WHEN enrollment_status = 'on_leave' THEN 'On Leave'
                    WHEN enrollment_status = 'graduated' THEN 'Graduated'
                    WHEN enrollment_status = 'dropped' THEN 'Dropped'
                    ELSE enrollment_status
                END as status,
                COUNT(*) as count
            FROM enr_students
            GROUP BY enrollment_status
            ORDER BY 
                CASE enrollment_status
                    WHEN 'enrolled' THEN 1
                    WHEN 'on_leave' THEN 2
                    WHEN 'graduated' THEN 3
                    WHEN 'dropped' THEN 4
                    ELSE 4
                END");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get recent activities from all modules
     */
    public function getRecentActivities($limit = 15) {
        try {
            $activities = [];

            // 1. Recent Events
            try {
                $stmt = $this->conn->prepare("SELECT event_id, event_title, 'Academic Event' as activity_type, created_at FROM cc_events ORDER BY created_at DESC LIMIT 5");
                $stmt->execute();
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($events as $event) {
                    $activities[] = [
                        'id' => $event['event_id'],
                        'type' => $event['activity_type'],
                        'description' => 'Created event: ' . $event['event_title'],
                        'module' => 'Academic Events',
                        'timestamp' => $event['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 2. Recent Appointments
            try {
                $stmt = $this->conn->prepare("SELECT appointment_id, appointment_type, 'Appointment Scheduled' as activity_title, created_at FROM cc_appointments ORDER BY created_at DESC LIMIT 5");
                $stmt->execute();
                $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($appointments as $apt) {
                    $activities[] = [
                        'id' => $apt['appointment_id'],
                        'type' => $apt['activity_title'],
                        'description' => 'Scheduled ' . strtolower($apt['appointment_type']) . ' appointment',
                        'module' => 'Appointments',
                        'timestamp' => $apt['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 3. Recent Schedule Assignments
            try {
                $stmt = $this->conn->prepare("SELECT cs.id, CONCAT(f.first_name, ' ', f.last_name) as faculty_name, cs.subject_code, cs.created_at 
                    FROM cc_schedule cs 
                    LEFT JOIN cc_faculty f ON cs.faculty_id = f.id 
                    ORDER BY cs.created_at DESC LIMIT 5");
                $stmt->execute();
                $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($schedules as $sch) {
                    $activities[] = [
                        'id' => $sch['id'],
                        'type' => 'Faculty Assignment',
                        'description' => 'Assigned ' . ($sch['faculty_name'] ?? 'Faculty') . ' to ' . $sch['subject_code'],
                        'module' => 'Faculty Schedule',
                        'timestamp' => $sch['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 4. Recent Section Updates
            try {
                $stmt = $this->conn->prepare("SELECT section_id, section_code, CONCAT(f.first_name, ' ', f.last_name) as adviser, cc_sections.created_at 
                    FROM cc_sections
                    LEFT JOIN cc_faculty f ON cc_sections.adviser_id = f.id
                    ORDER BY cc_sections.created_at DESC LIMIT 5");
                $stmt->execute();
                $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($sections as $sec) {
                    $activities[] = [
                        'id' => $sec['section_id'],
                        'type' => 'Section Management',
                        'description' => 'Section ' . $sec['section_code'] . ' assigned to ' . ($sec['adviser'] ?? 'TBA'),
                        'module' => 'Academics',
                        'timestamp' => $sec['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 5. Recent Student Enrollments
            try {
                $stmt = $this->conn->prepare("SELECT student_id, id_number, first_name, last_name, created_at 
                    FROM rgr_students 
                    ORDER BY created_at DESC LIMIT 5");
                $stmt->execute();
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($students as $stud) {
                    $activities[] = [
                        'id' => $stud['student_id'],
                        'type' => 'Student Enrollment',
                        'description' => 'New student enrolled: ' . $stud['first_name'] . ' ' . $stud['last_name'],
                        'module' => 'Enrollment',
                        'timestamp' => $stud['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 6. Faculty Management: certifications, engagements, and faculty loads
            try {
                $stmt = $this->conn->prepare("SELECT ce.id, ce.title, ce.created_at,
                        CONCAT(e.first_name, ' ', e.last_name) AS faculty_name
                    FROM cc_certification_engagements ce
                    LEFT JOIN em_employees e ON e.employee_id = ce.employee_id
                    ORDER BY ce.created_at DESC LIMIT 10");
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $activities[] = [
                        'id' => 'engagement-' . $row['id'],
                        'type' => 'Faculty Management',
                        'description' => 'Added engagement for ' . ($row['faculty_name'] ?? 'Faculty') . ': ' . ($row['title'] ?? 'Untitled'),
                        'module' => 'Faculty Management',
                        'timestamp' => $row['created_at']
                    ];
                }
            } catch (Exception $e) {}

            try {
                $stmt = $this->conn->prepare("SELECT id, faculty_id, created_at
                    FROM cc_faculty_load ORDER BY created_at DESC LIMIT 10");
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $activities[] = [
                        'id' => 'faculty-load-' . $row['id'],
                        'type' => 'Faculty Management',
                        'description' => 'Updated faculty load assignment',
                        'module' => 'Faculty Management',
                        'timestamp' => $row['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 7. Academics Management: sections and academic assignments
            try {
                $stmt = $this->conn->prepare("SELECT id, section_code, created_at
                    FROM cc_sections ORDER BY created_at DESC LIMIT 10");
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $activities[] = [
                        'id' => 'section-' . $row['id'],
                        'type' => 'Academics Management',
                        'description' => 'Created or updated section ' . ($row['section_code'] ?? 'Section'),
                        'module' => 'Academics Management',
                        'timestamp' => $row['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 8. Class Scheduling
            try {
                $stmt = $this->conn->prepare("SELECT id, subject_code, created_at
                    FROM cc_schedule ORDER BY created_at DESC LIMIT 10");
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $activities[] = [
                        'id' => 'schedule-' . $row['id'],
                        'type' => 'Class Scheduling',
                        'description' => 'Created class schedule' . (!empty($row['subject_code']) ? ': ' . $row['subject_code'] : ''),
                        'module' => 'Class Scheduling',
                        'timestamp' => $row['created_at']
                    ];
                }
            } catch (Exception $e) {}

            // 9. Requests and Reports / Report Submission
            try {
                $stmt = $this->conn->prepare("SELECT report_id, title, submitted_at
                    FROM sd_reports ORDER BY submitted_at DESC LIMIT 10");
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $activities[] = [
                        'id' => 'report-' . $row['report_id'],
                        'type' => 'Report Submission',
                        'description' => 'Submitted report: ' . ($row['title'] ?? 'Untitled report'),
                        'module' => 'Requests and Reports',
                        'timestamp' => $row['submitted_at']
                    ];
                }
            } catch (Exception $e) {}

            // 10. Concern Submission
            try {
                $stmt = $this->conn->prepare("SELECT issue_id, title, submitted_on
                    FROM sd_issues ORDER BY submitted_on DESC LIMIT 10");
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $activities[] = [
                        'id' => 'concern-' . $row['issue_id'],
                        'type' => 'Concern Submission',
                        'description' => 'Submitted concern: ' . ($row['title'] ?? 'Untitled concern'),
                        'module' => 'Requests and Reports',
                        'timestamp' => $row['submitted_on']
                    ];
                }
            } catch (Exception $e) {}

            // 11. Approval Submission
            try {
                $stmt = $this->conn->prepare("SELECT approval_id, title, submitted_on
                    FROM sd_approvals ORDER BY submitted_on DESC LIMIT 10");
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $activities[] = [
                        'id' => 'approval-' . $row['approval_id'],
                        'type' => 'Approval Submission',
                        'description' => 'Submitted approval request: ' . ($row['title'] ?? 'Untitled request'),
                        'module' => 'Requests and Reports',
                        'timestamp' => $row['submitted_on']
                    ];
                }
            } catch (Exception $e) {}

            // Sort by timestamp descending
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            // Return limited results
            return array_slice($activities, 0, $limit);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Reusable fetchAll method for OOP controllers
     */
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
