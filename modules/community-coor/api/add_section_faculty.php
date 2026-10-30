<?php
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['faculty_id']) || !isset($data['section_ids'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $facultyId = (int)$data['faculty_id'];
    $sectionIds = is_array($data['section_ids']) ? $data['section_ids'] : [];

    if ($facultyId <= 0 || empty($sectionIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }

    $database = new Database();
    $conn = $database->getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $sectionQuery = $conn->prepare("SELECT school_year_id, semester_id FROM cc_sections WHERE id = :section_id LIMIT 1");
    $semesterCheckStmt = $conn->prepare("SELECT school_year_id FROM rgr_semesters WHERE id = :semester_id LIMIT 1");

    $conn->beginTransaction();

    $checkStmt = $conn->prepare("SELECT COUNT(*) AS count FROM cc_section_faculty WHERE section_id = :section_id AND faculty_id = :faculty_id AND role = 'Instructor' AND school_year_id = :school_year_id AND semester_id = :semester_id AND (status = 'Active' OR status IS NULL)");
    $insertStmt = $conn->prepare("INSERT INTO cc_section_faculty (section_id, faculty_id, role, school_year_id, semester_id, status, assigned_at, created_at, updated_at) VALUES (:section_id, :faculty_id, 'Instructor', :school_year_id, :semester_id, 'Active', NOW(), NOW(), NOW())");

    $inserted = 0;

    foreach ($sectionIds as $sectionId) {
        $sectionId = (int)$sectionId;
        if ($sectionId <= 0) {
            continue;
        }

        $sectionQuery->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
        $sectionQuery->execute();
        $section = $sectionQuery->fetch(PDO::FETCH_ASSOC);

        if (!$section || empty($section['school_year_id']) || empty($section['semester_id'])) {
            throw new Exception('Selected section is missing academic period information');
        }

        $schoolYearId = (int)$section['school_year_id'];
        $semesterId = (int)$section['semester_id'];

        $semesterCheckStmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
        $semesterCheckStmt->execute();
        $semesterRow = $semesterCheckStmt->fetch(PDO::FETCH_ASSOC);

        if (!$semesterRow || (int)$semesterRow['school_year_id'] !== $schoolYearId) {
            throw new Exception('Selected section has an invalid semester / school year combination');
        }

        $checkStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
        $checkStmt->bindParam(':faculty_id', $facultyId, PDO::PARAM_INT);
        $checkStmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
        $checkStmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'This faculty is already assigned to this section for the selected school year and semester.']);
            exit;
        }

        // Note: do not automatically update existing instructor assignments to 'Reassigned'.
        // Multiple instructors may be active for a section; only insert the new assignment.

        $insertStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
        $insertStmt->bindParam(':faculty_id', $facultyId, PDO::PARAM_INT);
        $insertStmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
        $insertStmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
        $insertStmt->execute();
        $inserted++;
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Section assignments saved successfully',
        'inserted' => $inserted
    ]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>