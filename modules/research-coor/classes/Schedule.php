<?php
class Schedule {
    private $conn;
    private $table = "cc_schedule";

    public $id;
    public $room_id;
    public $start_time;
    public $end_time;
    public $day_of_week;
    public $subject_code;
    public $grade_section_id;
    public $faculty_id;
    public $faculty_load_id;
    public $schedule_type;
    public $semester;
    public $school_year;
    public $section_id;
    public $subject_id;
    public $school_year_id;
    public $semester_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function resolveSemesterId($semesterValue) {
        if (empty($semesterValue)) {
            return null;
        }

        if (is_numeric($semesterValue)) {
            return (int)$semesterValue;
        }

        $stmt = $this->conn->prepare("SELECT id FROM rgr_semesters WHERE name = :name LIMIT 1");
        $stmt->bindParam(':name', $semesterValue, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['id'] : null;
    }

    private function resolveSchoolYearId($schoolYearValue) {
        if (empty($schoolYearValue)) {
            return null;
        }

        if (is_numeric($schoolYearValue)) {
            return (int)$schoolYearValue;
        }

        $stmt = $this->conn->prepare("SELECT id FROM rgr_school_years WHERE name = :name LIMIT 1");
        $stmt->bindParam(':name', $schoolYearValue, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['id'] : null;
    }

    private function resolveSubjectId($subjectCode) {
        if (empty($subjectCode)) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT id FROM rgr_subjects WHERE code = :code LIMIT 1");
        $stmt->bindParam(':code', $subjectCode, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['id'] : null;
    }

    // Get schedule by date and time (existing method)
    public function getByDateTime($date, $time_breaker) {
        $day = date('l', strtotime($date));

        $query = "SELECT s.*, f.first_name, f.last_name, sec.section_code,
                         sem.name AS semester_name, sy.name AS school_year_name,
                         {$this->roomSelectExpression()}
                  FROM {$this->table} s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.section_id = sec.id
                  LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
                  LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
                  {$this->roomJoinClause()}
                  WHERE s.day_of_week = ?
                  AND s.start_time = ?
                  AND sem.name = ?
                  AND sy.name = ?
                  ORDER BY s.start_time";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $day, PDO::PARAM_STR);
        $stmt->bindParam(2, $time_breaker, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->semester, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->school_year, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // Create new schedule (existing method)
    public function create() {
        try {
            $isBreak = isset($this->schedule_type) && strtolower(trim($this->schedule_type)) === 'break time';

            $sectionId = (int)($this->section_id ?: $this->grade_section_id ?: 0);
            $subjectId = !empty($this->subject_id) ? (int)$this->subject_id : $this->resolveSubjectId($this->subject_code ?? '');
            $semesterId = !empty($this->semester_id) ? (int)$this->semester_id : $this->resolveSemesterId($this->semester ?? '');
            $schoolYearId = !empty($this->school_year_id) ? (int)$this->school_year_id : $this->resolveSchoolYearId($this->school_year ?? '');

            if (!$semesterId || !$schoolYearId) {
                throw new PDOException('Missing valid semester or school year data.');
            }

            if (!$isBreak && (!$sectionId || !$subjectId)) {
                throw new PDOException('Missing valid section or subject data.');
            }

            if (empty($this->faculty_id)) {
                throw new PDOException('Missing valid faculty data.');
            }

            $insertColumns = [
                'faculty_load_id',
                'status',
                'day_of_week',
                'school_year_id',
                'semester_id',
                'start_time',
                'end_time'
            ];

            $values = [
                ':faculty_load_id',
                ':status',
                ':day_of_week',
                ':school_year_id',
                ':semester_id',
                ':start_time',
                ':end_time'
            ];

            // Break Time still belongs to a faculty, but has no class relationships.
            $insertColumns[] = 'section_id';
            $values[] = ':section_id';
            $insertColumns[] = 'faculty_id';
            $values[] = ':faculty_id';
            $insertColumns[] = 'subject_id';
            $values[] = ':subject_id';

            if ($this->scheduleHasColumn('schedule_type')) {
                $insertColumns[] = 'schedule_type';
                $values[] = ':schedule_type';
            }

            $insertColumns[] = 'room_id';
            $values[] = ':room_id';

            $query = "INSERT INTO {$this->table} (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $values) . ")";
            $stmt = $this->conn->prepare($query);

            $facultyLoadId = $isBreak ? null : (!empty($this->faculty_load_id) ? (int)$this->faculty_load_id : null);
            $status = 'Scheduled';
            $dayOfWeek = trim((string)($this->day_of_week ?? ''));
            $facultyId = (int)($this->faculty_id ?? 0);
            $startTime = trim((string)($this->start_time ?? ''));
            $endTime = trim((string)($this->end_time ?? ''));
            $roomId = !empty($this->room_id) ? (int)$this->room_id : null;

            $stmt->bindValue(':faculty_load_id', $facultyLoadId, $facultyLoadId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':day_of_week', $dayOfWeek, PDO::PARAM_STR);
            $stmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
            $stmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
            $stmt->bindParam(':start_time', $startTime, PDO::PARAM_STR);
            $stmt->bindParam(':end_time', $endTime, PDO::PARAM_STR);

            $stmt->bindValue(':section_id', $isBreak ? null : $sectionId, $isBreak ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':faculty_id', $facultyId, PDO::PARAM_INT);
            $stmt->bindValue(':subject_id', $isBreak ? null : $subjectId, $isBreak ? PDO::PARAM_NULL : PDO::PARAM_INT);

            if ($this->scheduleHasColumn('schedule_type')) {
                $stmt->bindParam(':schedule_type', $this->schedule_type, PDO::PARAM_STR);
            }

            $stmt->bindValue(':room_id', $roomId, $roomId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Schedule::create error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update() {
        try {
            $isBreak = strtolower(trim((string)$this->schedule_type)) === 'break time';
            $fields = [
                'room_id = :room_id',
                'schedule_type = :schedule_type',
                'day_of_week = :day_of_week',
                'start_time = :start_time',
                'end_time = :end_time',
                'semester_id = :semester_id',
                'school_year_id = :school_year_id',
                'faculty_load_id = :faculty_load_id'
            ];

            if ($isBreak) {
                $fields[] = 'faculty_id = :faculty_id';
                $fields[] = 'subject_id = NULL';
                $fields[] = 'section_id = NULL';
            } else {
                $fields[] = 'faculty_id = :faculty_id';
                $fields[] = 'subject_id = :subject_id';
                $fields[] = 'section_id = :section_id';
            }

            $stmt = $this->conn->prepare(
                "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id"
            );

            $roomId = !empty($this->room_id) ? (int)$this->room_id : null;
            $scheduleType = $this->schedule_type ?: 'Class';
            $day = trim((string)$this->day_of_week);
            $start = trim((string)$this->start_time);
            $end = trim((string)$this->end_time);
            $semesterId = (int)$this->semester_id;
            $schoolYearId = (int)$this->school_year_id;
            $facultyLoadId = $isBreak ? null : (!empty($this->faculty_load_id) ? (int)$this->faculty_load_id : null);

            $stmt->bindValue(':room_id', $roomId, $roomId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':schedule_type', $scheduleType, PDO::PARAM_STR);
            $stmt->bindValue(':day_of_week', $day, PDO::PARAM_STR);
            $stmt->bindValue(':start_time', $start, PDO::PARAM_STR);
            $stmt->bindValue(':end_time', $end, PDO::PARAM_STR);
            $stmt->bindValue(':semester_id', $semesterId, PDO::PARAM_INT);
            $stmt->bindValue(':school_year_id', $schoolYearId, PDO::PARAM_INT);
            $stmt->bindValue(':faculty_load_id', $facultyLoadId, $facultyLoadId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id', (int)$this->id, PDO::PARAM_INT);

            if ($isBreak) {
                $stmt->bindValue(':faculty_id', (int)$this->faculty_id, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':faculty_id', (int)$this->faculty_id, PDO::PARAM_INT);
                $stmt->bindValue(':subject_id', (int)$this->subject_id, PDO::PARAM_INT);
                $stmt->bindValue(':section_id', (int)($this->section_id ?: $this->grade_section_id), PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Schedule::update error: ' . $e->getMessage());
            throw $e;
        }
    }


    // NEW: Delete schedule
     public function delete() {
    try {
        // delete attendance first
        $stmt1 = $this->conn->prepare(
            "DELETE FROM mon_attendance WHERE schedule_id = ?"
        );
        $stmt1->execute([$this->id]);

        // delete schedule
        $stmt2 = $this->conn->prepare(
            "DELETE FROM cc_schedule WHERE id = ?"
        );
        return $stmt2->execute([$this->id]);

    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
}

    // NEW: Get all schedules with filters
    public function getAllWithFilters($semester, $school_year, $day_of_week = null, $room = null) {
        // Build query dynamically to support both id-based and name-based semester/school_year
        $select = "SELECT s.*, f.first_name, f.last_name, f.faculty_code,
                         sec.section_code, sec.grade_level, c.code AS program,
                         subj.code AS subject_code, subj.name AS subject_name,
                         subj.units AS subject_units,
                         sem.name AS semester_name, sy.name AS school_year_name,
                         {$this->roomSelectExpression()}";

        $from = " FROM {$this->table} s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.section_id = sec.id
                  LEFT JOIN rgr_courses c ON sec.program_id = c.id
                  LEFT JOIN rgr_subjects subj ON s.subject_id = subj.id
                  LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
                  LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
                  {$this->roomJoinClause()}";

        $useIds = is_numeric($semester) && is_numeric($school_year);
        $where = " WHERE 1=1";
        $params = [];

        if ($useIds) {
            $where .= " AND s.semester_id = ? AND s.school_year_id = ?";
            $params[] = (int)$semester;
            $params[] = (int)$school_year;
        } else {
            $where .= " AND sem.name = ? AND sy.name = ?";
            $params[] = $semester;
            $params[] = $school_year;
        }

        if ($day_of_week) {
            $where .= " AND s.day_of_week = ?";
            $params[] = $day_of_week;
        }

        if ($room) {
            $where .= " AND s.room_id = ?";
            $params[] = (int)$room;
        }

        $order = " ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time";
        $order .= ", room";

        $query = $select . $from . $where . $order;
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    // NEW: Get distinct days for a semester and school year
    public function getDistinctDays($semester, $school_year) {
        $useIds = is_numeric($semester) && is_numeric($school_year);
        if ($useIds) {
            $query = "SELECT DISTINCT s.day_of_week FROM " . $this->table . " s WHERE s.semester_id = ? AND s.school_year_id = ? ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $semester, PDO::PARAM_INT);
            $stmt->bindParam(2, $school_year, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        }

        $query = "SELECT DISTINCT s.day_of_week
                  FROM " . $this->table . " s
                  LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
                  LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
                  WHERE sem.name = ? AND sy.name = ?
                  ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester, PDO::PARAM_STR);
        $stmt->bindParam(2, $school_year, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // NEW: Get distinct rooms for a semester and school year
    public function getDistinctRooms($semester, $school_year) {
        $query = "SELECT DISTINCT cr.id AS room_id, cr.room_name AS room
                  FROM cc_room cr
                  INNER JOIN {$this->table} s ON s.room_id = cr.id
                  WHERE cr.status = 'Available'
                  AND s.semester_id = ?
                  AND s.school_year_id = ?
                  ORDER BY cr.room_name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, (int)$semester, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$school_year, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // NEW: Get schedules by faculty
    public function getByFaculty($faculty_id, $semester, $school_year) {
        $query = "SELECT s.*, sec.section_code, sec.grade_level, subj.code AS subject_code, subj.name AS subject_name,
                         cr.room_name AS room
                  FROM " . $this->table . " s
                  LEFT JOIN cc_sections sec ON s.section_id = sec.id
                  LEFT JOIN rgr_subjects subj ON s.subject_id = subj.id
                  LEFT JOIN cc_room cr ON s.room_id = cr.id
                  LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
                  LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
                  WHERE s.faculty_id = ?
                  AND sem.name = ?
                  AND sy.name = ?
                  ORDER BY s.day_of_week, s.start_time";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $faculty_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $semester, PDO::PARAM_STR);
        $stmt->bindParam(3, $school_year, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // NEW: Get schedules by section
    public function getBySection($section_id, $semester, $school_year) {
        $query = "SELECT s.*, f.first_name, f.last_name, f.faculty_code, subj.code AS subject_code, subj.name AS subject_name,
                         cr.room_name AS room
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN rgr_subjects subj ON s.subject_id = subj.id
                  LEFT JOIN cc_room cr ON s.room_id = cr.id
                  LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
                  LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
                  WHERE s.section_id = ?
                  AND sem.name = ?
                  AND sy.name = ?
                  ORDER BY s.day_of_week, s.start_time";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $section_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $semester, PDO::PARAM_STR);
        $stmt->bindParam(3, $school_year, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // NEW: Get schedules by room
    public function getByRoom($room, $semester, $school_year) {
        $query = "SELECT s.*, f.first_name, f.last_name, sec.section_code, subj.code AS subject_code, subj.name AS subject_name,
                         cr.room_name AS room
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.section_id = sec.id
                  LEFT JOIN rgr_subjects subj ON s.subject_id = subj.id
                  LEFT JOIN cc_room cr ON s.room_id = cr.id
                  LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
                  LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
                  WHERE s.room_id = ?
                  AND sem.name = ?
                  AND sy.name = ?
                  ORDER BY s.day_of_week, s.start_time";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $room, PDO::PARAM_INT);
        $stmt->bindParam(2, $semester, PDO::PARAM_STR);
        $stmt->bindParam(3, $school_year, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // NEW: Get schedule statistics
    public function getStatistics($semester, $school_year) {
        // Support id-based or name-based filters
        $useIds = is_numeric($semester) && is_numeric($school_year);
        $query = "SELECT 
                    COUNT(*) as total_schedules,
                    COUNT(DISTINCT {$this->roomReferenceExpression()}) as total_rooms,
                    COUNT(DISTINCT s.faculty_id) as total_faculty,
                    COUNT(DISTINCT s.section_id) as total_sections,
                    COUNT(DISTINCT s.subject_id) as total_subjects
                  FROM {$this->table} s
                  {$this->roomJoinClause()}";

        if ($useIds) {
            $query .= " WHERE s.semester_id = ? AND s.school_year_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $semester, PDO::PARAM_INT);
            $stmt->bindParam(2, $school_year, PDO::PARAM_INT);
        } else {
            $query .= " LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
                        LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
                        WHERE sem.name = ? AND sy.name = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $semester, PDO::PARAM_STR);
            $stmt->bindParam(2, $school_year, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // NEW: Check for schedule conflicts
    public function checkConflict($room, $day_of_week, $start_time, $end_time, $semester, $school_year, $exclude_id = null) {
        $roomField = 's.room_id = ?';

        $useIds = is_numeric($semester) && is_numeric($school_year);

        $query = "SELECT COUNT(*) as conflict_count FROM " . $this->table . " s";
        if (!$useIds) {
            $query .= " LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id";
        }

        $query .= " WHERE {$roomField} AND s.day_of_week = ?";

        if ($useIds) {
            $query .= " AND s.semester_id = ? AND s.school_year_id = ?";
        } else {
            $query .= " AND sem.name = ? AND sy.name = ?";
        }

        $query .= " AND (
                    (s.start_time < ? AND s.end_time > ?) OR
                    (s.start_time < ? AND s.end_time > ?) OR
                    (s.start_time >= ? AND s.end_time <= ?)
                  )";

        if ($exclude_id) {
            $query .= " AND s.id != ?";
        }

        $stmt = $this->conn->prepare($query);
        $pos = 1;
        $stmt->bindParam($pos++, $room, PDO::PARAM_INT);
        $stmt->bindParam($pos++, $day_of_week, PDO::PARAM_STR);

        if ($useIds) {
            $stmt->bindParam($pos++, $semester, PDO::PARAM_INT);
            $stmt->bindParam($pos++, $school_year, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($pos++, $semester, PDO::PARAM_STR);
            $stmt->bindParam($pos++, $school_year, PDO::PARAM_STR);
        }

        $stmt->bindParam($pos++, $end_time, PDO::PARAM_STR);
        $stmt->bindParam($pos++, $start_time, PDO::PARAM_STR);
        $stmt->bindParam($pos++, $end_time, PDO::PARAM_STR);
        $stmt->bindParam($pos++, $start_time, PDO::PARAM_STR);
        $stmt->bindParam($pos++, $start_time, PDO::PARAM_STR);
        $stmt->bindParam($pos++, $end_time, PDO::PARAM_STR);

        if ($exclude_id) {
            $stmt->bindParam($pos++, $exclude_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['conflict_count'] ?? 0) > 0;
    }

    // NEW: Get weekly schedule summary
    public function getWeeklySummary($semester, $school_year) {
        $useIds = is_numeric($semester) && is_numeric($school_year);
        $query = "SELECT 
                    s.day_of_week,
                    COUNT(*) as total_classes,
                    GROUP_CONCAT(DISTINCT cr.room_name ORDER BY cr.room_name SEPARATOR ', ') as rooms,
                    MIN(s.start_time) as earliest_start,
                    MAX(s.end_time) as latest_end
                  FROM " . $this->table . " s
                  LEFT JOIN cc_room cr ON s.room_id = cr.id";

        if ($useIds) {
            $query .= " WHERE s.semester_id = ? AND s.school_year_id = ?";
            $query .= " GROUP BY s.day_of_week ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $semester, PDO::PARAM_INT);
            $stmt->bindParam(2, $school_year, PDO::PARAM_INT);
        } else {
            $query .= " LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id WHERE sem.name = ? AND sy.name = ? GROUP BY s.day_of_week ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $semester, PDO::PARAM_STR);
            $stmt->bindParam(2, $school_year, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt;
    }

    // NEW: Search schedules
    public function search($keyword, $semester, $school_year) {
        $useIds = is_numeric($semester) && is_numeric($school_year);
        $query = "SELECT s.*, f.first_name, f.last_name, f.faculty_code,
                         sec.section_code, sec.grade_level, subj.code AS subject_code, subj.name AS subject_name,
                        cr.room_name AS room
                  FROM " . $this->table . " s
                  LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                  LEFT JOIN cc_sections sec ON s.section_id = sec.id
                  LEFT JOIN rgr_subjects subj ON s.subject_id = subj.id
                  LEFT JOIN cc_room cr ON s.room_id = cr.id";

            if ($useIds) {
                $query .= " WHERE (cr.room_name LIKE ? OR subj.code LIKE ? OR f.first_name LIKE ? OR f.last_name LIKE ? OR sec.section_code LIKE ?) AND s.semester_id = ? AND s.school_year_id = ? ORDER BY s.day_of_week, s.start_time";
            } else {
                $query .= " LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id WHERE (cr.room_name LIKE ? OR subj.code LIKE ? OR f.first_name LIKE ? OR f.last_name LIKE ? OR sec.section_code LIKE ?) AND sem.name = ? AND sy.name = ? ORDER BY s.day_of_week, s.start_time";
        }

        $stmt = $this->conn->prepare($query);
        $search_pattern = "%{$keyword}%";
        $stmt->bindParam(1, $search_pattern, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_pattern, PDO::PARAM_STR);
        $stmt->bindParam(3, $search_pattern, PDO::PARAM_STR);
        $stmt->bindParam(4, $search_pattern, PDO::PARAM_STR);
        $stmt->bindParam(5, $search_pattern, PDO::PARAM_STR);

        if ($useIds) {
            $stmt->bindParam(6, $semester, PDO::PARAM_INT);
            $stmt->bindParam(7, $school_year, PDO::PARAM_INT);
        } else {
            $stmt->bindParam(6, $semester, PDO::PARAM_STR);
            $stmt->bindParam(7, $school_year, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt;
    }

    // NEW: Check if schedule exists
    public function scheduleExists($room, $day_of_week, $start_time, $subject_code, $semester, $school_year) {
        $roomField = 's.room_id = ?';

        $useIds = is_numeric($semester) && is_numeric($school_year);

        $query = "SELECT s.id FROM " . $this->table . " s";
        if (!$useIds) {
            $query .= " LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id";
        }

        $query .= " WHERE {$roomField} AND s.day_of_week = ? AND s.start_time = ? AND s.subject_id = ?";

        if ($useIds) {
            $query .= " AND s.semester_id = ? AND s.school_year_id = ?";
        } else {
            $query .= " AND sem.name = ? AND sy.name = ?";
        }

        $subjectId = $this->resolveSubjectId($subject_code);
        $stmt = $this->conn->prepare($query);
        $pos = 1;
        $stmt->bindParam($pos++, $room, PDO::PARAM_INT);
        $stmt->bindParam($pos++, $day_of_week, PDO::PARAM_STR);
        $stmt->bindParam($pos++, $start_time, PDO::PARAM_STR);
        $stmt->bindParam($pos++, $subjectId, PDO::PARAM_INT);

        if ($useIds) {
            $stmt->bindParam($pos++, $semester, PDO::PARAM_INT);
            $stmt->bindParam($pos++, $school_year, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($pos++, $semester, PDO::PARAM_STR);
            $stmt->bindParam($pos++, $school_year, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function scheduleHasColumn($columnName) {
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM `{$this->table}` LIKE :column_name");
            $stmt->bindParam(':column_name', $columnName, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function tableExists($tableName) {
        try {
            $stmt = $this->conn->prepare("SHOW TABLES LIKE :table_name");
            $stmt->bindParam(':table_name', $tableName, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function roomJoinClause() {
        if ($this->tableExists('cc_room')) {
            return ' LEFT JOIN cc_room cr ON cr.id = s.room_id ';
        }
        return '';
    }

    private function roomSelectExpression() {
        if ($this->tableExists('cc_room')) {
            return 'cr.room_name AS room';
        }
        return 'NULL AS room';
    }

    private function roomReferenceExpression() {
        if ($this->tableExists('cc_room')) {
            return 's.room_id';
        }
        return '\'\'';
    }
}
?>