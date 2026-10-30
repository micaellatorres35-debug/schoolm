<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../../database/db.php');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : null;
    $faculty_id = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : null;

    if (!$section_id || !$faculty_id) {
        throw new Exception('Missing required fields: section_id and faculty_id');
    }

    $database = new Database();
    $conn = $database->getConnection();
    $conn->beginTransaction();

    // Load section metadata for school year / semester values
    $sectionStmt = $conn->prepare("SELECT school_year_id, semester_id FROM cc_sections WHERE id = :section_id LIMIT 1");
    $sectionStmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
    $sectionStmt->execute();
    $section = $sectionStmt->fetch(PDO::FETCH_ASSOC);

    if (!$section) {
        throw new Exception('Section not found');
    }

    $school_year_id = isset($section['school_year_id']) ? (int)$section['school_year_id'] : null;
    $semester_id = isset($section['semester_id']) ? (int)$section['semester_id'] : null;

    if (!$school_year_id || !$semester_id) {
        throw new Exception('Section school year or semester information is missing');
    }

    // Update the section adviser field
    $updateSectionStmt = $conn->prepare("UPDATE cc_sections SET adviser_id = :faculty_id WHERE id = :section_id");
    $updateSectionStmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
    $updateSectionStmt->bindParam(':faculty_id', $faculty_id, PDO::PARAM_INT);
    if (!$updateSectionStmt->execute()) {
        throw new Exception('Failed to update section adviser');
    }

    // Ensure there is only one Adviser entry for this section in cc_section_faculty
    $adviserRowsStmt = $conn->prepare("SELECT id, faculty_id FROM cc_section_faculty WHERE section_id = :section_id AND role = 'Adviser'");
    $adviserRowsStmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
    $adviserRowsStmt->execute();
    $adviserRows = $adviserRowsStmt->fetchAll(PDO::FETCH_ASSOC);

    $rowToKeep = null;
    $rowsToDelete = [];
    $firstRow = null;

    foreach ($adviserRows as $row) {
        if ($firstRow === null) {
            $firstRow = $row;
        }

        if ($row['faculty_id'] == $faculty_id && $rowToKeep === null) {
            $rowToKeep = $row;
        }
    }

    if ($rowToKeep === null && $firstRow !== null) {
        $rowToKeep = $firstRow;
    }

    foreach ($adviserRows as $row) {
        if ($rowToKeep && $row['id'] !== $rowToKeep['id']) {
            $rowsToDelete[] = $row['id'];
        }
    }

    if ($rowToKeep) {
        $updateSectionFacultyStmt = $conn->prepare("UPDATE cc_section_faculty SET faculty_id = :faculty_id, school_year_id = :school_year_id, semester_id = :semester_id WHERE id = :id");
        $updateSectionFacultyStmt->bindParam(':faculty_id', $faculty_id, PDO::PARAM_INT);
        $updateSectionFacultyStmt->bindParam(':school_year_id', $school_year_id, PDO::PARAM_INT);
        $updateSectionFacultyStmt->bindParam(':semester_id', $semester_id, PDO::PARAM_INT);
        $updateSectionFacultyStmt->bindParam(':id', $rowToKeep['id'], PDO::PARAM_INT);
        if (!$updateSectionFacultyStmt->execute()) {
            throw new Exception('Failed to update adviser section assignment');
        }
    } else {
        $insertStmt = $conn->prepare("INSERT INTO cc_section_faculty (section_id, faculty_id, role, school_year_id, semester_id) VALUES (:section_id, :faculty_id, 'Adviser', :school_year_id, :semester_id)");
        $insertStmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':faculty_id', $faculty_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':school_year_id', $school_year_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':semester_id', $semester_id, PDO::PARAM_INT);
        if (!$insertStmt->execute()) {
            throw new Exception('Failed to create adviser section assignment');
        }
    }

    if (!empty($rowsToDelete)) {
        $deleteStmt = $conn->prepare("DELETE FROM cc_section_faculty WHERE id IN (" . implode(',', array_map('intval', $rowsToDelete)) . ")");
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to remove duplicate adviser assignments');
        }
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Adviser assigned successfully'
    ]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
