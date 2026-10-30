<?php
class FacultyManager {
    private $conn;
    public function __construct($conn) { 
        $this->conn = $conn; 
    }
    
    public function getFacultyLoad() {
        $sql = "SELECT 
            f.id,
            f.faculty_code,
            CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
            COALESCE(d.department_name, f.department, 'Department not assigned') AS department,
            (SELECT COUNT(DISTINCT sf.section_id)
             FROM cc_section_faculty sf
             INNER JOIN rgr_school_years sy ON sf.school_year_id = sy.id AND sy.is_active = 1
             INNER JOIN rgr_semesters sem ON sf.semester_id = sem.id AND sem.is_active = 1
             WHERE sf.faculty_id = f.id
               AND sf.role = 'Instructor'
               AND (sf.status = 'Active' OR sf.status IS NULL)) AS assigned_sections,
            (SELECT COUNT(*)
             FROM cc_faculty_load fl
             INNER JOIN rgr_school_years sy ON fl.school_year_id = sy.id AND sy.is_active = 1
             INNER JOIN rgr_semesters sem ON fl.semester_id = sem.id AND sem.is_active = 1
             WHERE fl.faculty_id = f.id) AS assigned_subjects,
            COALESCE(t.teaching_units, 0) AS teaching_units,
            COALESCE(t.max_load, f.max_load, 15) AS max_load,
            COALESCE(t.teaching_units, 0) AS total_units,
            CASE
                WHEN COALESCE(t.teaching_units, 0) > COALESCE(t.max_load, f.max_load, 15) THEN 'Overloaded'
                WHEN COALESCE(t.teaching_units, 0) = COALESCE(t.max_load, f.max_load, 15) THEN 'Fully Loaded'
                ELSE 'Underloaded'
            END AS load_status
        FROM cc_faculty f
        INNER JOIN em_employees e ON e.employee_id = f.employee_id
        LEFT JOIN em_departments d ON d.department_id = e.department_id
        LEFT JOIN (
            SELECT 
                fl.faculty_id,
                COALESCE(fac.max_load, 15) AS max_load,
                COALESCE(SUM(COALESCE(s.units, 0)), 0) AS teaching_units
            FROM cc_faculty_load fl
            INNER JOIN cc_faculty fac ON fac.id = fl.faculty_id
            INNER JOIN rgr_school_years sy ON fl.school_year_id = sy.id AND sy.is_active = 1
            INNER JOIN rgr_semesters sem ON fl.semester_id = sem.id AND sem.is_active = 1
            LEFT JOIN rgr_subjects s ON fl.subject_id = s.id
            GROUP BY fl.faculty_id, fac.max_load
        ) t ON t.faculty_id = f.id
        ORDER BY f.first_name, f.last_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacultyLoadDetails($facultyId) {
        $sql = "SELECT 
            f.id,
            f.faculty_code,
            CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
            COALESCE(d.department_name, f.department, 'Department not assigned') AS department,
            (SELECT COUNT(DISTINCT sf.section_id)
             FROM cc_section_faculty sf
             INNER JOIN rgr_school_years sy ON sf.school_year_id = sy.id AND sy.is_active = 1
             INNER JOIN rgr_semesters sem ON sf.semester_id = sem.id AND sem.is_active = 1
             WHERE sf.faculty_id = f.id
               AND sf.role = 'Instructor'
               AND (sf.status = 'Active' OR sf.status IS NULL)) AS assigned_sections,
            (SELECT COUNT(*)
             FROM cc_faculty_load fl
             INNER JOIN rgr_school_years sy ON fl.school_year_id = sy.id AND sy.is_active = 1
             INNER JOIN rgr_semesters sem ON fl.semester_id = sem.id AND sem.is_active = 1
             WHERE fl.faculty_id = f.id) AS assigned_subjects,
            COALESCE(t.teaching_units, 0) AS teaching_units,
            COALESCE(t.max_load, f.max_load, 15) AS max_load,
            COALESCE(t.teaching_units, 0) AS total_units,
            CASE
                WHEN COALESCE(t.teaching_units, 0) > COALESCE(t.max_load, f.max_load, 15) THEN 'Overloaded'
                WHEN COALESCE(t.teaching_units, 0) = COALESCE(t.max_load, f.max_load, 15) THEN 'Fully Loaded'
                ELSE 'Underloaded'
            END AS load_status
        FROM cc_faculty f
        INNER JOIN em_employees e ON e.employee_id = f.employee_id
        LEFT JOIN em_departments d ON d.department_id = e.department_id
        LEFT JOIN (
            SELECT 
                fl.faculty_id,
                COALESCE(fac.max_load, 15) AS max_load,
                COALESCE(SUM(COALESCE(s.units, 0)), 0) AS teaching_units
            FROM cc_faculty_load fl
            INNER JOIN cc_faculty fac ON fac.id = fl.faculty_id
            INNER JOIN rgr_school_years sy ON fl.school_year_id = sy.id AND sy.is_active = 1
            INNER JOIN rgr_semesters sem ON fl.semester_id = sem.id AND sem.is_active = 1
            LEFT JOIN rgr_subjects s ON fl.subject_id = s.id
            WHERE fl.faculty_id = :faculty_id
            GROUP BY fl.faculty_id, fac.max_load
        ) t ON t.faculty_id = f.id
        WHERE f.id = :faculty_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':faculty_id', $facultyId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFacultyAssignments($facultyId) {
        // First, get all faculty load assignments
        $sql = "SELECT 
            fl.id,
            fl.section_id,
            sec.section_code,
            s.code AS subject_code,
            s.name AS subject_name,
            COALESCE(s.units, 0) AS units,
            COALESCE(s.lecture_hours, 0) AS lecture_hours,
            COALESCE(s.lab_hours, 0) AS lab_hours,
            'Assigned' AS assignment_status,
            sy.name AS school_year,
            sem.name AS semester
        FROM cc_faculty_load fl
        LEFT JOIN cc_sections sec ON fl.section_id = sec.id
        LEFT JOIN rgr_subjects s ON fl.subject_id = s.id
        INNER JOIN rgr_school_years sy ON fl.school_year_id = sy.id AND sy.is_active = 1
        INNER JOIN rgr_semesters sem ON fl.semester_id = sem.id AND sem.is_active = 1
        WHERE fl.faculty_id = :faculty_id
        ORDER BY sy.name DESC, sem.name DESC, sec.section_code, s.code";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':faculty_id', $facultyId);
        $stmt->execute();
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // For each assignment, fetch its schedules
        $scheduleSql = "SELECT 
            day_of_week,
            start_time,
            end_time,
            r.room_code
        FROM cc_schedule cs
        LEFT JOIN cc_room r ON cs.room_id = r.id
        WHERE cs.faculty_load_id = :faculty_load_id 
        AND cs.status = 'Scheduled'
        ORDER BY FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
                 cs.start_time";
        
        $scheduleStmt = $this->conn->prepare($scheduleSql);
        
        // Enrich assignments with schedule data
        foreach ($assignments as &$assignment) {
            $scheduleStmt->bindParam(':faculty_load_id', $assignment['id']);
            $scheduleStmt->execute();
            $schedules = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($schedules)) {
                $assignment['schedule_status'] = 'Scheduled';
                $days = array_map(function($s) { return $s['day_of_week']; }, $schedules);
                $times = array_map(function($s) { 
                    return date("H:i", strtotime($s['start_time'])) . '-' . date("H:i", strtotime($s['end_time'])); 
                }, $schedules);
                $rooms = array_map(function($s) { return $s['room_code'] ?: '-'; }, $schedules);
                
                $assignment['day_of_week'] = implode(', ', array_unique($days));
                $assignment['time_range'] = implode(', ', $times);
                $assignment['room_code'] = implode(', ', array_unique($rooms));
            } else {
                $assignment['schedule_status'] = 'Not Yet Scheduled';
                $assignment['day_of_week'] = '-';
                $assignment['time_range'] = '-';
                $assignment['room_code'] = '-';
            }
        }
        
        return $assignments;
    }

    public function getFacultyAssignmentsHistory($facultyId) {
        $sql = "SELECT 
            fl.id,
            fl.section_id,
            sec.section_code,
            s.code AS subject_code,
            s.name AS subject_name,
            COALESCE(s.units, 0) AS units,
            COALESCE(s.lecture_hours, 0) AS lecture_hours,
            COALESCE(s.lab_hours, 0) AS lab_hours,
            COALESCE(sy.name, 'Unknown School Year') AS school_year,
            COALESCE(sem.name, 'Unknown Semester') AS semester,
            'Inactive' AS status,
            CASE
                WHEN COALESCE(sy.is_active, 0) != 1 AND COALESCE(sem.is_active, 0) != 1 THEN 'School Year and Semester Inactive'
                WHEN COALESCE(sy.is_active, 0) != 1 THEN 'School Year Inactive'
                WHEN COALESCE(sem.is_active, 0) != 1 THEN 'Semester Inactive'
                ELSE 'Academic Period Inactive'
            END AS reason,
            fl.created_at AS assignment_date
        FROM cc_faculty_load fl
        LEFT JOIN cc_sections sec ON fl.section_id = sec.id
        LEFT JOIN rgr_subjects s ON fl.subject_id = s.id
        LEFT JOIN rgr_school_years sy ON fl.school_year_id = sy.id
        LEFT JOIN rgr_semesters sem ON fl.semester_id = sem.id
        WHERE fl.faculty_id = :faculty_id
          AND (COALESCE(sy.is_active, 0) != 1 OR COALESCE(sem.is_active, 0) != 1)
        ORDER BY sy.name DESC, sem.name DESC, sec.section_code, subject_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':faculty_id', $facultyId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacultySections($facultyId) {
        $sql = "SELECT 
            sf.id AS assignment_id,
            sf.section_id,
            sf.role,
            sf.status,
            sf.assigned_at,
            sf.ended_at,
            sf.created_at,
            sf.updated_at,
            sec.section_code,
            sec.grade_level,
            sec.program_id,
            c.code AS program_code,
            c.name AS program_name,
            sf.school_year_id,
            sf.semester_id,
            sy.name AS school_year,
            sem.name AS semester,
            sem.school_year_id AS semester_school_year_id,
            CASE WHEN sem.school_year_id IS NOT NULL AND sem.school_year_id != sf.school_year_id THEN 1 ELSE 0 END AS period_mismatch
        FROM cc_section_faculty sf
        INNER JOIN cc_sections sec ON sf.section_id = sec.id
        LEFT JOIN rgr_courses c ON sec.program_id = c.id
        INNER JOIN rgr_school_years sy ON sf.school_year_id = sy.id AND sy.is_active = 1
        INNER JOIN rgr_semesters sem ON sf.semester_id = sem.id AND sem.is_active = 1
        WHERE sf.faculty_id = :faculty_id
        ORDER BY sy.name DESC, sem.name DESC, sec.section_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':faculty_id', $facultyId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAssignments($facultyId, $assignments) {
        try {
            $this->conn->beginTransaction();

            // Prepare reusable queries
            $sectionQuery = "SELECT id, program_id, grade_level, school_year_id, semester_id FROM cc_sections WHERE id = :section_id LIMIT 1";
            $sectionStmt = $this->conn->prepare($sectionQuery);

            $sectionFacultyQuery = "SELECT COUNT(*) AS count FROM cc_section_faculty WHERE section_id = :section_id AND faculty_id = :faculty_id AND role = 'Instructor' AND (status = 'Active' OR status IS NULL)";
            $sectionFacultyStmt = $this->conn->prepare($sectionFacultyQuery);

            $semesterCheckQuery = "SELECT name, school_year_id FROM rgr_semesters WHERE id = :semester_id LIMIT 1";
            $semesterCheckStmt = $this->conn->prepare($semesterCheckQuery);

            $activeCurriculumQuery = "SELECT id FROM rgr_curriculums WHERE course_id = :program_id AND is_active = 1 LIMIT 1";
            $activeCurriculumStmt = $this->conn->prepare($activeCurriculumQuery);

            $curriculumSubjectQuery = "SELECT cs.id, s.code, s.units 
                                       FROM rgr_curriculum_subjects cs
                                       INNER JOIN rgr_subjects s ON cs.subject_id = s.id
                                       WHERE cs.curriculum_id = :curriculum_id 
                                       AND cs.subject_id = :subject_id
                                       AND cs.year_level = :year_level
                                       AND cs.semester = :semester
                                       LIMIT 1";
            $curriculumSubjectStmt = $this->conn->prepare($curriculumSubjectQuery);

            $duplicateCheckQuery = "SELECT COUNT(*) AS count FROM cc_faculty_load 
                                    WHERE section_id = :section_id 
                                    AND subject_id = :subject_id 
                                    LIMIT 1";
            $duplicateCheckStmt = $this->conn->prepare($duplicateCheckQuery);

            $insertSql = "INSERT INTO cc_faculty_load 
                    (faculty_id, section_id, subject_id, school_year_id, semester_id) 
                    VALUES (:faculty_id, :section_id, :subject_id, :school_year_id, :semester_id)";
            $insertStmt = $this->conn->prepare($insertSql);

            foreach ($assignments as $assignment) {
                $sectionId = (int)$assignment['section_id'];
                $subjectId = (int)$assignment['subject_id'];

                // 1. Verify section exists and get its details
                $sectionStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
                $sectionStmt->execute();
                $section = $sectionStmt->fetch(PDO::FETCH_ASSOC);

                if (!$section) {
                    throw new Exception('Selected section does not exist.');
                }

                // 2. Verify faculty is assigned as instructor to this section
                $sectionFacultyStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
                $sectionFacultyStmt->bindParam(':faculty_id', $facultyId, PDO::PARAM_INT);
                $sectionFacultyStmt->execute();
                $sectionFacultyResult = $sectionFacultyStmt->fetch(PDO::FETCH_ASSOC);

                if (!$sectionFacultyResult || (int)$sectionFacultyResult['count'] === 0) {
                    throw new Exception('This faculty is not assigned as the instructor of the selected section.');
                }

                // 3. Verify academic period information exists
                if (empty($section['school_year_id']) || empty($section['semester_id'])) {
                    throw new Exception('Selected section is missing academic period information');
                }

                $schoolYearId = (int)$section['school_year_id'];
                $semesterId = (int)$section['semester_id'];
                $programId = (int)$section['program_id'];
                $gradeLevel = $section['grade_level'];

                // 4. Verify semester-school year consistency
                $semesterCheckStmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
                $semesterCheckStmt->execute();
                $semesterRow = $semesterCheckStmt->fetch(PDO::FETCH_ASSOC);

                if (!$semesterRow || (int)$semesterRow['school_year_id'] !== $schoolYearId) {
                    throw new Exception('Selected section has an invalid semester / school year combination');
                }

                $semesterName = $semesterRow['name'];

                // 5. Verify active curriculum exists for this program
                $activeCurriculumStmt->bindParam(':program_id', $programId, PDO::PARAM_INT);
                $activeCurriculumStmt->execute();
                $curriculumRow = $activeCurriculumStmt->fetch(PDO::FETCH_ASSOC);

                if (!$curriculumRow) {
                    throw new Exception('No active curriculum found for this section\'s program. Contact the Registrar.');
                }

                $curriculumId = (int)$curriculumRow['id'];

                // 6. Convert grade level string to numeric year level
                $yearLevel = $this->parseYearLevel($gradeLevel);

                // 7. Verify subject belongs to the active curriculum with matching year level and semester
                $curriculumSubjectStmt->bindParam(':curriculum_id', $curriculumId, PDO::PARAM_INT);
                $curriculumSubjectStmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
                $curriculumSubjectStmt->bindParam(':year_level', $yearLevel, PDO::PARAM_INT);
                $curriculumSubjectStmt->bindParam(':semester', $semesterName);
                $curriculumSubjectStmt->execute();
                $curriculumSubject = $curriculumSubjectStmt->fetch(PDO::FETCH_ASSOC);

                if (!$curriculumSubject) {
                    throw new Exception('This subject is not part of the active curriculum for this section, year level, and semester.');
                }

                // 8. Prevent duplicate subject assignments
                $duplicateCheckStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
                $duplicateCheckStmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
                $duplicateCheckStmt->execute();
                $duplicateResult = $duplicateCheckStmt->fetch(PDO::FETCH_ASSOC);

                if ($duplicateResult && (int)$duplicateResult['count'] > 0) {
                    throw new Exception('This subject is already assigned to this section.');
                }

                // 9. Insert the assignment
                $insertStmt->bindParam(':faculty_id', $facultyId, PDO::PARAM_INT);
                $insertStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
                $insertStmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
                $insertStmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
                $insertStmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
                $insertStmt->execute();
            }

            $this->conn->commit();
            
            // Regenerate faculty load summary for all affected school year/semester combinations
            // Collect unique school_year_id and semester_id combos that were touched
            $affectedPeriods = [];
            foreach ($assignments as $assignment) {
                $sectionId = (int)$assignment['section_id'];
                $sectionStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
                $sectionStmt->execute();
                $section = $sectionStmt->fetch(PDO::FETCH_ASSOC);
                if ($section) {
                    $key = $section['school_year_id'] . '-' . $section['semester_id'];
                    $affectedPeriods[$key] = [
                        'school_year_id' => (int)$section['school_year_id'],
                        'semester_id' => (int)$section['semester_id']
                    ];
                }
            }
            
            // Regenerate summary for each unique school_year_id/semester_id pair
            foreach ($affectedPeriods as $period) {
                $this->generateFacultyLoadSummary($period['school_year_id'], $period['semester_id']);
            }
            
            return ['success' => true, 'message' => 'Assignments saved successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error saving assignments: ' . $e->getMessage()];
        }
    }

    private function parseYearLevel($gradeLevel) {
        if (is_numeric($gradeLevel)) {
            return (int)$gradeLevel;
        }
        
        // Extract number from strings like "1st Year", "2nd Year", "1st", "2nd", etc.
        if (preg_match('/(\d+)/', $gradeLevel, $matches)) {
            return (int)$matches[1];
        }
        
        // Default to 1 if parsing fails
        return 1;
    }

    public function deleteAssignment($facultyLoadId) {
        try {
            // First, retrieve the assignment details before deletion
            $selectSql = "SELECT faculty_id, school_year_id, semester_id FROM cc_faculty_load WHERE id = :id";
            $selectStmt = $this->conn->prepare($selectSql);
            $selectStmt->bindParam(':id', $facultyLoadId);
            $selectStmt->execute();
            $assignment = $selectStmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete the assignment
            $deleteSql = "DELETE FROM cc_faculty_load WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteSql);
            $deleteStmt->bindParam(':id', $facultyLoadId);
            $deleteStmt->execute();
            
            // Regenerate faculty load summary for the affected period
            if ($assignment) {
                $this->generateFacultyLoadSummary((int)$assignment['school_year_id'], (int)$assignment['semester_id']);
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function generateFacultyLoadSummary($schoolYearId, $semesterId) {
        try {
            $sql = "INSERT INTO cc_faculty_load_summary 
                    (faculty_id, school_year_id, semester_id, total_units, max_load, load_status, computed_at)
                    SELECT 
                        f.id,
                        fl.school_year_id,
                        fl.semester_id,
                        COALESCE(SUM(s.units), 0) AS total_units,
                        COALESCE(f.max_load, 15) AS max_load,
                        CASE 
                            WHEN COALESCE(SUM(s.units), 0) > COALESCE(f.max_load, 15) THEN 'Overloaded'
                            WHEN COALESCE(SUM(s.units), 0) = COALESCE(f.max_load, 15) THEN 'Fully Loaded'
                            ELSE 'Underloaded'
                        END AS load_status,
                        NOW() AS computed_at
                    FROM cc_faculty f
                    LEFT JOIN cc_faculty_load fl ON f.id = fl.faculty_id
                    LEFT JOIN rgr_subjects s ON fl.subject_id = s.id
                    WHERE fl.school_year_id = :school_year_id
                      AND fl.semester_id = :semester_id
                    GROUP BY f.id, fl.school_year_id, fl.semester_id
                    ON DUPLICATE KEY UPDATE 
                        total_units = VALUES(total_units),
                        load_status = VALUES(load_status),
                        computed_at = VALUES(computed_at)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
            $stmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Faculty load summary generated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error generating faculty load summary: ' . $e->getMessage()];
        }
    }

    public function getFacultyLoadSummary($schoolYearId, $semesterId) {
        $sql = "SELECT 
            fls.id,
            fls.faculty_id,
            fls.school_year_id,
            fls.semester_id,
            fls.total_units,
            fls.max_load,
            fls.load_status,
            fls.computed_at,
            f.faculty_code,
            f.first_name,
            f.last_name,
            f.department
        FROM cc_faculty_load_summary fls
        INNER JOIN cc_faculty f ON fls.faculty_id = f.id
        WHERE fls.school_year_id = :school_year_id
          AND fls.semester_id = :semester_id
        ORDER BY f.first_name, f.last_name";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
        $stmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
