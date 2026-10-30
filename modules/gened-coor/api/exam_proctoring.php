<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 3) . '/database/db.php';

function respond(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function inputValue(string $key, $default = null) {
    $source = $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
    return array_key_exists($key, $source) ? $source[$key] : $default;
}

try {
    $db = (new Database())->getConnection();
    $action = (string)inputValue('action', 'list');

    if ($action === 'faculties') {
        $stmt = $db->query("SELECT id, faculty_code, first_name, last_name
            FROM cc_faculty ORDER BY last_name, first_name");
        respond(['success' => true, 'faculties' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'exams') {
        $stmt = $db->query("SELECT e.id, e.exam_name, e.exam_type, e.school_year_id, e.semester_id,
                e.start_date, e.end_date, e.status, sy.name AS school_year_name, sem.name AS semester_name
            FROM cc_exams e
            LEFT JOIN rgr_school_years sy ON sy.id = e.school_year_id
            LEFT JOIN rgr_semesters sem ON sem.id = e.semester_id
            ORDER BY e.start_date DESC, e.exam_name");
        respond(['success' => true, 'exams' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'subjects') {
        $stmt = $db->query('SELECT id, code, name FROM rgr_subjects ORDER BY code');
        respond(['success' => true, 'subjects' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'curriculum-subjects') {
        $stmt = $db->query("SELECT cur.course_id, cs.year_level, cs.subject_id
            FROM rgr_curriculum_subjects cs
            JOIN rgr_curriculums cur ON cur.id = cs.curriculum_id
            WHERE cur.is_active = 1");
        respond(['success' => true, 'curriculum_subjects' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'sections') {
        $stmt = $db->query('SELECT id, section_code, program_id, grade_level FROM cc_sections ORDER BY section_code');
        respond(['success' => true, 'sections' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'rooms') {
        $stmt = $db->query("SELECT id, room_name FROM cc_room WHERE status = 'Available' ORDER BY room_name");
        respond(['success' => true, 'rooms' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'create_exam') {
        $examName = trim((string)inputValue('exam_name', ''));
        $examType = trim((string)inputValue('exam_type', ''));
        $schoolYearId = (int)inputValue('school_year_id', 0);
        $semesterId = (int)inputValue('semester_id', 0);
        $startDate = trim((string)inputValue('start_date', ''));
        $endDate = trim((string)inputValue('end_date', ''));
        $status = trim((string)inputValue('status', ''));
        if ($examType === 'Prelim') {
            $examType = 'Preliminary';
        }
        $allowedExamTypes = ['Preliminary', 'Midterm', 'Final', 'Special'];
        if ($examName === '' || $examType === '' || !$schoolYearId || !$semesterId || $startDate === '' || $endDate === '' || $status === '') {
            respond(['success' => false, 'message' => 'All examination fields are required.'], 422);
        }
        if (!in_array($examType, $allowedExamTypes, true)) {
            respond(['success' => false, 'message' => 'Invalid examination type.'], 422);
        }
        if ($endDate < $startDate) respond(['success' => false, 'message' => 'End date must not be before start date.'], 422);
        $stmt = $db->prepare('INSERT INTO cc_exams
            (exam_name, exam_type, school_year_id, semester_id, start_date, end_date, status, created_at, updated_at)
            VALUES (:exam_name, :exam_type, :school_year_id, :semester_id, :start_date, :end_date, :status, NOW(), NOW())');
        $stmt->execute([
            ':exam_name' => $examName, ':exam_type' => $examType, ':school_year_id' => $schoolYearId,
            ':semester_id' => $semesterId, ':start_date' => $startDate, ':end_date' => $endDate, ':status' => $status
        ]);
        respond(['success' => true, 'message' => 'Examination created successfully.', 'id' => (int)$db->lastInsertId()]);
    }

    if ($action === 'create_schedule') {
        $examId = (int)inputValue('exam_id', 0);
        $scheduleType = trim((string)inputValue('schedule_type', 'Exam'));
        $subjectId = (int)inputValue('subject_id', 0);
        $sectionId = (int)inputValue('section_id', 0);
        $roomId = (int)inputValue('room_id', 0);
        $examDate = trim((string)inputValue('exam_date', ''));
        $startTime = trim((string)inputValue('start_time', ''));
        $endTime = trim((string)inputValue('end_time', ''));
        $status = trim((string)inputValue('status', ''));
        if (!$examId || !in_array($scheduleType, ['Exam', 'Break Time'], true) || $examDate === '' || $startTime === '' || $endTime === '' || $status === '') {
            respond(['success' => false, 'message' => 'All exam schedule fields are required.'], 422);
        }
        if ($scheduleType === 'Exam' && (!$subjectId || !$sectionId || !$roomId)) {
            respond(['success' => false, 'message' => 'Subject, section, and room are required for an Exam schedule.'], 422);
        }
        // For Break Time, subject must be empty; require section and room so break is tied to a specific group
        if ($scheduleType === 'Break Time') {
            if ($subjectId) respond(['success' => false, 'message' => 'Break Time schedules must not have a subject.'], 422);
            if (!$sectionId || !$roomId) respond(['success' => false, 'message' => 'Section and room are required for Break Time schedules.'], 422);
        }
        if ($endTime <= $startTime) respond(['success' => false, 'message' => 'End time must be after start time.'], 422);
        $stmt = $db->prepare('SELECT start_date, end_date FROM cc_exams WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $examId]);
        $exam = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$exam) respond(['success' => false, 'message' => 'The selected examination does not exist.'], 422);
        if ($examDate < $exam['start_date'] || $examDate > $exam['end_date']) {
            respond(['success' => false, 'message' => 'Exam date must be within the examination date range.'], 422);
        }
        $stmt = $db->prepare('INSERT INTO cc_exam_schedule
            (exam_id, schedule_type, subject_id, section_id, room_id, exam_date, start_time, end_time, status, created_at, updated_at)
            VALUES (:exam_id, :schedule_type, :subject_id, :section_id, :room_id, :exam_date, :start_time, :end_time, :status, NOW(), NOW())');
        $stmt->bindValue(':exam_id', $examId, PDO::PARAM_INT);
        $stmt->bindValue(':schedule_type', $scheduleType, PDO::PARAM_STR);
        // For Exam: subject/section/room required. For Break Time: subject null, section & room required.
        $stmt->bindValue(':subject_id', $scheduleType === 'Exam' ? $subjectId : null, $scheduleType === 'Exam' ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':section_id', $sectionId ? $sectionId : null, $sectionId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':room_id', $roomId ? $roomId : null, $roomId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':exam_date', $examDate, PDO::PARAM_STR);
        $stmt->bindValue(':start_time', $startTime, PDO::PARAM_STR);
        $stmt->bindValue(':end_time', $endTime, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Exam schedule created successfully.', 'id' => (int)$db->lastInsertId()]);
    }

    if ($action === 'exam-schedules') {
        $sql = "SELECT es.id, es.exam_id, es.schedule_type, es.subject_id, es.section_id, es.room_id,
                    es.exam_date, es.start_time, es.end_time, es.status,
                    e.exam_name, e.exam_type,
                    subj.code AS subject_code, subj.name AS subject_name,
                    sec.section_code, cr.room_name
                FROM cc_exam_schedule es
                INNER JOIN cc_exams e ON e.id = es.exam_id
                LEFT JOIN rgr_subjects subj ON subj.id = es.subject_id
                LEFT JOIN cc_sections sec ON sec.id = es.section_id
                LEFT JOIN cc_room cr ON cr.id = es.room_id
                WHERE 1 = 1";
        $params = [];
        if ((int)inputValue('semester_id', 0)) {
            $sql .= ' AND e.semester_id = :semester_id';
            $params[':semester_id'] = (int)inputValue('semester_id');
        }
        if ((int)inputValue('school_year_id', 0)) {
            $sql .= ' AND e.school_year_id = :school_year_id';
            $params[':school_year_id'] = (int)inputValue('school_year_id');
        }
        if ((int)inputValue('exam_id', 0)) {
            $sql .= ' AND e.id = :exam_id';
            $params[':exam_id'] = (int)inputValue('exam_id');
        }
        $sql .= ' ORDER BY es.exam_date, e.exam_name, cr.room_name, sec.section_code, es.start_time';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        respond(['success' => true, 'exam_schedules' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'update_schedule') {
        $id = (int)inputValue('id', 0);
        $examId = (int)inputValue('exam_id', 0);
        $scheduleType = trim((string)inputValue('schedule_type', 'Exam'));
        $subjectId = (int)inputValue('subject_id', 0);
        $sectionId = (int)inputValue('section_id', 0);
        $roomId = (int)inputValue('room_id', 0);
        $examDate = trim((string)inputValue('exam_date', ''));
        $startTime = trim((string)inputValue('start_time', ''));
        $endTime = trim((string)inputValue('end_time', ''));
        $status = trim((string)inputValue('status', ''));
        if (!$id || !$examId || !in_array($scheduleType, ['Exam', 'Break Time'], true) || $examDate === '' || $startTime === '' || $endTime === '' || $status === '') {
            respond(['success' => false, 'message' => 'All exam schedule fields are required.'], 422);
        }
        if ($endTime <= $startTime) respond(['success' => false, 'message' => 'End time must be after start time.'], 422);
        if ($scheduleType === 'Exam' && (!$subjectId || !$sectionId || !$roomId)) {
            respond(['success' => false, 'message' => 'Subject, section, and room are required for an Exam schedule.'], 422);
        }
        if ($scheduleType === 'Break Time') {
            if ($subjectId) respond(['success' => false, 'message' => 'Break Time schedules must not have a subject.'], 422);
            if (!$sectionId || !$roomId) respond(['success' => false, 'message' => 'Section and room are required for Break Time schedules.'], 422);
        }
        $stmt = $db->prepare('SELECT start_date, end_date FROM cc_exams WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $examId]);
        $exam = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$exam) respond(['success' => false, 'message' => 'The selected examination does not exist.'], 422);
        if ($examDate < $exam['start_date'] || $examDate > $exam['end_date']) respond(['success' => false, 'message' => 'Exam date must be within the examination date range.'], 422);
        $stmt = $db->prepare('UPDATE cc_exam_schedule SET exam_id = :exam_id, schedule_type = :schedule_type,
            subject_id = :subject_id, section_id = :section_id, room_id = :room_id, exam_date = :exam_date,
            start_time = :start_time, end_time = :end_time, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->bindValue(':exam_id', $examId, PDO::PARAM_INT);
        $stmt->bindValue(':schedule_type', $scheduleType, PDO::PARAM_STR);
        $stmt->bindValue(':subject_id', $scheduleType === 'Exam' ? $subjectId : null, $scheduleType === 'Exam' ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':section_id', $sectionId ? $sectionId : null, $sectionId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':room_id', $roomId ? $roomId : null, $roomId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':exam_date', $examDate, PDO::PARAM_STR);
        $stmt->bindValue(':start_time', $startTime, PDO::PARAM_STR);
        $stmt->bindValue(':end_time', $endTime, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Exam schedule updated successfully.']);
    }

    // List assignments grouped by exam_date + room + section + exam + faculty + role + status
    if ($action === 'list') {
        $sql = "SELECT
                MIN(ep.id) AS id,
                MIN(ep.exam_schedule_id) AS exam_schedule_id,
                e.exam_name,
                GROUP_CONCAT(DISTINCT subj.code ORDER BY es.start_time SEPARATOR ', ') AS subject_codes,
                MIN(es.id) AS sample_schedule_id,
                es.room_id,
                es.section_id,
                sec.section_code,
                cr.room_name,
                es.exam_date,
                MIN(es.start_time) AS start_time,
                MAX(es.end_time) AS end_time,
                ep.faculty_id,
                f.first_name,
                f.last_name,
                ep.role,
                ep.status,
                e.semester_id,
                e.school_year_id,
                e.id AS exam_id
            FROM cc_exam_proctor ep
            INNER JOIN cc_exam_schedule es ON es.id = ep.exam_schedule_id AND es.schedule_type = 'Exam'
            INNER JOIN cc_exams e ON e.id = es.exam_id
            LEFT JOIN rgr_subjects subj ON subj.id = es.subject_id
            LEFT JOIN cc_sections sec ON sec.id = es.section_id
            LEFT JOIN cc_room cr ON cr.id = es.room_id
            INNER JOIN cc_faculty f ON f.id = ep.faculty_id
            WHERE 1 = 1";
        $params = [];
        foreach (['semester_id' => 'e.semester_id', 'school_year_id' => 'e.school_year_id', 'exam_id' => 'e.id'] as $key => $column) {
            $value = (int)inputValue($key, 0);
            if ($value) {
                $sql .= " AND {$column} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        $status = trim((string)inputValue('status', ''));
        if ($status !== '') {
            $sql .= ' AND ep.status = :status';
            $params[':status'] = $status;
        }
        $sql .= " GROUP BY es.exam_date, es.room_id, es.section_id, e.id, ep.faculty_id, ep.role, ep.status
            ORDER BY es.exam_date, MIN(es.start_time), e.exam_name, f.last_name, f.first_name";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        respond(['success' => true, 'assignments' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'create') {
        $examScheduleId = (int)inputValue('exam_schedule_id', 0);
        $facultyId = (int)inputValue('faculty_id', 0);
        $role = trim((string)inputValue('role', ''));
        // validate role
        $allowedRoles = ['Lead Proctor', 'Proctor', 'Reliever'];
        if (!in_array($role, $allowedRoles, true)) respond(['success' => false, 'message' => 'Invalid proctor role.'], 422);
        if (!$examScheduleId || !$facultyId || $role === '') {
            respond(['success' => false, 'message' => 'Exam schedule, faculty, and role are required.'], 422);
        }

        $check = $db->prepare("SELECT id FROM cc_exam_schedule WHERE id = :id AND schedule_type = 'Exam' LIMIT 1");
        $check->execute([':id' => $examScheduleId]);
        if (!$check->fetchColumn()) respond(['success' => false, 'message' => 'The selected exam schedule does not exist.'], 422);
        $check = $db->prepare('SELECT id FROM cc_faculty WHERE id = :id LIMIT 1');
        $check->execute([':id' => $facultyId]);
        if (!$check->fetchColumn()) respond(['success' => false, 'message' => 'The selected faculty does not exist.'], 422);

        // Determine grouping for the selected schedule: exam_date + room_id + section_id + exam_id
        $stmt = $db->prepare('SELECT id, exam_id, exam_date, room_id, section_id FROM cc_exam_schedule WHERE id = :id AND schedule_type = "Exam" LIMIT 1');
        $stmt->execute([':id' => $examScheduleId]);
        $base = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$base) respond(['success' => false, 'message' => 'The selected exam schedule does not exist.'], 422);

        $groupStmt = $db->prepare('SELECT id FROM cc_exam_schedule WHERE schedule_type = "Exam" AND exam_id = :exam_id AND exam_date = :exam_date AND room_id = :room_id AND section_id = :section_id');
        $groupStmt->execute([':exam_id' => $base['exam_id'], ':exam_date' => $base['exam_date'], ':room_id' => $base['room_id'], ':section_id' => $base['section_id']]);
        $scheduleIds = array_column($groupStmt->fetchAll(PDO::FETCH_ASSOC), 'id');
        if (!$scheduleIds) respond(['success' => false, 'message' => 'No exam schedules found in the selected group.'], 422);

        // If other CURRENT assignments exist for this group, prevent creating another one
        $inPlaceholder = implode(',', array_fill(0, count($scheduleIds), '?'));
        $existingStmt = $db->prepare("SELECT id FROM cc_exam_proctor WHERE exam_schedule_id IN ($inPlaceholder) AND status IN ('Assigned','Confirmed') LIMIT 1");
        $existingStmt->execute($scheduleIds);
        if ($existingStmt->fetchColumn()) {
            respond(['success' => false, 'message' => 'A current proctor assignment already exists for this grouping. Cancel or reassign via Edit.'], 409);
        }

        // Insert assignment rows for all schedules in the group with status 'Assigned'
        $insertStmt = $db->prepare('INSERT INTO cc_exam_proctor
            (exam_schedule_id, faculty_id, role, status, assigned_at, created_at, updated_at)
            VALUES (:exam_schedule_id, :faculty_id, :role, :status, NOW(), NOW(), NOW())');
        foreach ($scheduleIds as $sid) {
            $insertStmt->execute([':exam_schedule_id' => $sid, ':faculty_id' => $facultyId, ':role' => $role, ':status' => 'Assigned']);
        }
        respond(['success' => true, 'message' => 'Exam proctor assigned successfully.']);
    }

    if ($action === 'update') {
        $id = (int)inputValue('id', 0);
        $facultyId = (int)inputValue('faculty_id', 0);
        $role = trim((string)inputValue('role', ''));
        $status = trim((string)inputValue('status', ''));
        if (!$id || !$facultyId || $role === '' || $status === '') respond(['success' => false, 'message' => 'Assignment, faculty, role, and status are required.'], 422);
        $allowedRoles = ['Lead Proctor', 'Proctor', 'Reliever'];
        $allowedStatuses = ['Assigned', 'Confirmed', 'Completed', 'Cancelled'];
        if (!in_array($role, $allowedRoles, true)) respond(['success' => false, 'message' => 'Invalid proctor role.'], 422);
        if (!in_array($status, $allowedStatuses, true)) respond(['success' => false, 'message' => 'Invalid proctor status.'], 422);

        // Fetch the schedule for this assignment to determine group
        $stmt = $db->prepare('SELECT ep.id AS ep_id, ep.faculty_id AS current_faculty, es.id AS schedule_id, es.exam_id, es.exam_date, es.room_id, es.section_id
            FROM cc_exam_proctor ep
            INNER JOIN cc_exam_schedule es ON es.id = ep.exam_schedule_id
            WHERE ep.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) respond(['success' => false, 'message' => 'The proctor assignment does not exist.'], 404);

        $check = $db->prepare('SELECT id FROM cc_faculty WHERE id = :id LIMIT 1');
        $check->execute([':id' => $facultyId]);
        if (!$check->fetchColumn()) respond(['success' => false, 'message' => 'The selected faculty does not exist.'], 422);

        $groupStmt = $db->prepare('SELECT id FROM cc_exam_schedule WHERE schedule_type = "Exam" AND exam_id = :exam_id AND exam_date = :exam_date AND room_id = :room_id AND section_id = :section_id');
        $groupStmt->execute([':exam_id' => $row['exam_id'], ':exam_date' => $row['exam_date'], ':room_id' => $row['room_id'], ':section_id' => $row['section_id']]);
        $scheduleIds = array_column($groupStmt->fetchAll(PDO::FETCH_ASSOC), 'id');
        if (!$scheduleIds) respond(['success' => false, 'message' => 'No exam schedules found in the assignment group.'], 422);

        // If faculty changed -> treat as reassignment: cancel old records and create new ones
        if ((int)$row['current_faculty'] !== $facultyId) {
            $inPlaceholder = implode(',', array_fill(0, count($scheduleIds), '?'));
            $cancelStmt = $db->prepare("UPDATE cc_exam_proctor SET status = 'Cancelled', updated_at = NOW() WHERE exam_schedule_id IN ($inPlaceholder) AND status IN ('Assigned','Confirmed')");
            $cancelStmt->execute($scheduleIds);

            $insertStmt = $db->prepare('INSERT INTO cc_exam_proctor (exam_schedule_id, faculty_id, role, status, assigned_at, created_at, updated_at) VALUES (:exam_schedule_id, :faculty_id, :role, :status, NOW(), NOW(), NOW())');
            foreach ($scheduleIds as $sid) {
                $insertStmt->execute([':exam_schedule_id' => $sid, ':faculty_id' => $facultyId, ':role' => $role, ':status' => 'Assigned']);
            }
            respond(['success' => true, 'message' => 'Exam proctor reassigned successfully.']);
        }

        // If faculty is same, update role/status for existing non-cancelled records in the group
        $inPlaceholder = implode(',', array_fill(0, count($scheduleIds), '?'));
        $updateSql = "UPDATE cc_exam_proctor SET role = :role, status = :status, updated_at = NOW() WHERE exam_schedule_id IN ($inPlaceholder) AND faculty_id = :faculty_id AND status <> 'Cancelled'";
        $updateStmt = $db->prepare($updateSql);
        $pos = 1;
        foreach ($scheduleIds as $sid) { $updateStmt->bindValue($pos, $sid, PDO::PARAM_INT); $pos++; }
        $updateStmt->bindValue(':role', $role);
        $updateStmt->bindValue(':status', $status);
        $updateStmt->bindValue(':faculty_id', $facultyId, PDO::PARAM_INT);
        $updateStmt->execute();
        respond(['success' => true, 'message' => 'Exam proctor assignment updated successfully.']);
    }

    if ($action === 'cancel') {
        $id = (int)inputValue('id', 0);
        if (!$id) respond(['success' => false, 'message' => 'Assignment is required.'], 422);
        // Cancel the entire group to which this assignment belongs
        $stmt = $db->prepare('SELECT es.exam_id, es.exam_date, es.room_id, es.section_id FROM cc_exam_proctor ep INNER JOIN cc_exam_schedule es ON es.id = ep.exam_schedule_id WHERE ep.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) respond(['success' => false, 'message' => 'Assignment not found.'], 404);
        $cancelGroupStmt = $db->prepare('UPDATE cc_exam_proctor ep INNER JOIN cc_exam_schedule es ON es.id = ep.exam_schedule_id SET ep.status = "Cancelled", ep.updated_at = NOW() WHERE es.schedule_type = "Exam" AND es.exam_id = :exam_id AND es.exam_date = :exam_date AND es.room_id = :room_id AND es.section_id = :section_id');
        $cancelGroupStmt->execute([':exam_id' => $row['exam_id'], ':exam_date' => $row['exam_date'], ':room_id' => $row['room_id'], ':section_id' => $row['section_id']]);
        respond(['success' => true, 'message' => 'Exam proctor assignment cancelled.']);
    }

    respond(['success' => false, 'message' => 'Unsupported action.'], 400);
} catch (Throwable $e) {
    error_log('exam_proctoring.php: ' . $e->getMessage());
    respond(['success' => false, 'message' => 'Unable to process exam proctoring request.'], 500);
}
