<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

require_once '../../../database/db.php';

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('No data provided');
    }

    $faculty_id = $input['faculty_id'] ?? null;
    $assignments = $input['assignments'] ?? [];

    // Validation
    if (!$faculty_id || empty($assignments)) {
        throw new Exception('Missing required fields');
    }

    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sectionQuery = "SELECT school_year_id, semester_id FROM cc_sections WHERE id = :section_id LIMIT 1";
    $sectionStmt = $db->prepare($sectionQuery);

    $semesterCheckQuery = "SELECT school_year_id FROM rgr_semesters WHERE id = :semester_id LIMIT 1";
    $semesterCheckStmt = $db->prepare($semesterCheckQuery);

    // Start transaction
    $db->beginTransaction();

    // Check for existing duplicates and insert using section period values
    $checkQuery = "SELECT COUNT(*) as cnt FROM cc_faculty_load 
                   WHERE faculty_id = :faculty_id 
                   AND section_id = :section_id 
                   AND subject_id = :subject_id 
                   AND school_year_id = :school_year_id 
                   AND semester_id = :semester_id";
    $checkStmt = $db->prepare($checkQuery);

    $insertQuery = "INSERT INTO cc_faculty_load (faculty_id, section_id, subject_id, school_year_id, semester_id, created_at) 
                    VALUES (:faculty_id, :section_id, :subject_id, :school_year_id, :semester_id, NOW())";
    $insertStmt = $db->prepare($insertQuery);

    foreach ($assignments as $assignment) {
        $section_id = (int)$assignment['section_id'];
        $subject_id = (int)$assignment['subject_id'];

        if ($section_id <= 0 || $subject_id <= 0) {
            throw new Exception('Invalid section or subject assignment');
        }

        $sectionStmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
        $sectionStmt->execute();
        $section = $sectionStmt->fetch(PDO::FETCH_ASSOC);

        if (!$section || empty($section['school_year_id']) || empty($section['semester_id'])) {
            throw new Exception('Selected section is missing academic period information');
        }

        $school_year_id = (int)$section['school_year_id'];
        $semester_id = (int)$section['semester_id'];

        $semesterCheckStmt->bindParam(':semester_id', $semester_id, PDO::PARAM_INT);
        $semesterCheckStmt->execute();
        $semesterRow = $semesterCheckStmt->fetch(PDO::FETCH_ASSOC);
        if (!$semesterRow || (int)$semesterRow['school_year_id'] !== $school_year_id) {
            throw new Exception('Selected section has an invalid semester / school year combination');
        }

        $checkStmt->execute([
            ':faculty_id' => $faculty_id,
            ':section_id' => $section_id,
            ':subject_id' => $subject_id,
            ':school_year_id' => $school_year_id,
            ':semester_id' => $semester_id
        ]);

        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($result['cnt'] > 0) {
            throw new Exception("Assignment already exists for this faculty, section, and subject in the selected semester and year");
        }

        $insertStmt->execute([
            ':faculty_id' => $faculty_id,
            ':section_id' => $section_id,
            ':subject_id' => $subject_id,
            ':school_year_id' => $school_year_id,
            ':semester_id' => $semester_id
        ]);
    }

    // Calculate Classes Assigned (COUNT DISTINCT section_id)
    $school_year_id = (int)$section['school_year_id'];
    $classesQuery = "SELECT COUNT(DISTINCT section_id) as classes_assigned 
                     FROM cc_faculty_load 
                     WHERE faculty_id = :faculty_id 
                     AND school_year_id = :school_year_id 
                     AND semester_id = :semester_id";

    $classesStmt = $db->prepare($classesQuery);
    $classesStmt->execute([
        ':faculty_id' => $faculty_id,
        ':school_year_id' => $school_year_id,
        ':semester_id' => $semester_id
    ]);

    $classesResult = $classesStmt->fetch(PDO::FETCH_ASSOC);
    $classes_assigned = $classesResult['classes_assigned'] ?? 0;

    // Calculate Total Units (SUM rgr_subjects.units)
    $unitsQuery = "SELECT COALESCE(SUM(s.units), 0) as total_units 
                   FROM cc_faculty_load fl
                   JOIN rgr_subjects s ON fl.subject_id = s.id
                   WHERE fl.faculty_id = :faculty_id 
                   AND fl.school_year_id = :school_year_id 
                   AND fl.semester_id = :semester_id";

    $unitsStmt = $db->prepare($unitsQuery);
    $unitsStmt->execute([
        ':faculty_id' => $faculty_id,
        ':school_year_id' => $school_year_id,
        ':semester_id' => $semester_id
    ]);

    $unitsResult = $unitsStmt->fetch(PDO::FETCH_ASSOC);
    $total_units = $unitsResult['total_units'] ?? 0;

    // Calculate Load Status (max load = 15)
    $max_load = 15;
    if ($total_units < $max_load) {
        $load_status = 'Underloaded';
    } elseif ($total_units == $max_load) {
        $load_status = 'Normal';
    } else {
        $load_status = 'Overloaded';
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Assignments saved successfully',
        'data' => [
            'classes_assigned' => $classes_assigned,
            'total_units' => $total_units,
            'load_status' => $load_status
        ]
    ]);
} catch (Exception $e) {
    if (isset($db)) {
        try {
            $db->rollBack();
        } catch (Exception $rollbackError) {
            // Ignore rollback errors
        }
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
