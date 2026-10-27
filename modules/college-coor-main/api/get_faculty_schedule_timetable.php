<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../../database/db.php');
require_once(__DIR__ . '/../classes/Schedule.php');

try {
    $faculty_id = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : null;
    $semester_id = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : null;
    $school_year_id = isset($_GET['school_year_id']) ? (int)$_GET['school_year_id'] : null;

    if (!$faculty_id || !$semester_id || !$school_year_id) {
        throw new Exception('Missing required parameters: faculty_id, semester_id, school_year_id');
    }

    $database = new Database();
    $conn = $database->getConnection();

    // Query to get all schedules for the faculty with full details
    $query = "SELECT 
                cs.id,
                cs.faculty_id,
                cs.day_of_week,
                cs.start_time,
                cs.end_time,
                cs.schedule_type,
                subj.code AS subject_code,
                subj.name AS subject_name,
                sec.section_code,
                cr.room_code,
                cr.room_name,
                CONCAT(cr.room_code, ' - ', cr.room_name) AS room_full,
                sem.name AS semester_name,
                sy.name AS school_year_name,
                f.first_name,
                f.last_name,
                f.faculty_code
              FROM cc_schedule cs
              LEFT JOIN cc_faculty f ON cs.faculty_id = f.id
              LEFT JOIN cc_sections sec ON cs.section_id = sec.id
              LEFT JOIN rgr_subjects subj ON cs.subject_id = subj.id
              LEFT JOIN cc_room cr ON cs.room_id = cr.id
              LEFT JOIN rgr_semesters sem ON cs.semester_id = sem.id
              LEFT JOIN rgr_school_years sy ON cs.school_year_id = sy.id
              WHERE cs.faculty_id = :faculty_id
                AND cs.semester_id = :semester_id
                AND cs.school_year_id = :school_year_id
              ORDER BY 
                FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                CAST(cs.start_time AS TIME)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':faculty_id', $faculty_id, PDO::PARAM_INT);
    $stmt->bindParam(':semester_id', $semester_id, PDO::PARAM_INT);
    $stmt->bindParam(':school_year_id', $school_year_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get faculty info and semester/school year name from the first record or separate query
    $facultyInfo = null;
    $semesterName = null;
    $schoolYearName = null;

    if (count($schedules) > 0) {
        $first = $schedules[0];
        $facultyInfo = [
            'first_name' => $first['first_name'],
            'last_name' => $first['last_name'],
            'faculty_code' => $first['faculty_code']
        ];
        $semesterName = $first['semester_name'];
        $schoolYearName = $first['school_year_name'];
    } else {
        // If no schedules, still fetch faculty and semester/school year info
        $facultyQuery = "SELECT first_name, last_name, faculty_code FROM cc_faculty WHERE id = :faculty_id";
        $facultyStmt = $conn->prepare($facultyQuery);
        $facultyStmt->bindParam(':faculty_id', $faculty_id, PDO::PARAM_INT);
        $facultyStmt->execute();
        $facultyInfo = $facultyStmt->fetch(PDO::FETCH_ASSOC);

        $semesterQuery = "SELECT name FROM rgr_semesters WHERE id = :semester_id";
        $semesterStmt = $conn->prepare($semesterQuery);
        $semesterStmt->bindParam(':semester_id', $semester_id, PDO::PARAM_INT);
        $semesterStmt->execute();
        $semResult = $semesterStmt->fetch(PDO::FETCH_ASSOC);
        $semesterName = $semResult ? $semResult['name'] : null;

        $schoolYearQuery = "SELECT name FROM rgr_school_years WHERE id = :school_year_id";
        $schoolYearStmt = $conn->prepare($schoolYearQuery);
        $schoolYearStmt->bindParam(':school_year_id', $school_year_id, PDO::PARAM_INT);
        $schoolYearStmt->execute();
        $syResult = $schoolYearStmt->fetch(PDO::FETCH_ASSOC);
        $schoolYearName = $syResult ? $syResult['name'] : null;
    }

    echo json_encode([
        'success' => true,
        'schedules' => $schedules,
        'faculty_info' => $facultyInfo,
        'semester_name' => $semesterName,
        'school_year_name' => $schoolYearName
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
