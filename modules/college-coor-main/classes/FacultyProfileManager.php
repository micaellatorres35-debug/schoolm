<?php
class FacultyProfileManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    private function formatFacultyName($firstName, $middleName, $lastName, $suffix = null) {
        $parts = [trim((string)$firstName)];

        if (!empty($middleName)) {
            $middleInitial = trim((string)$middleName);
            $middleInitial = strtoupper(substr($middleInitial, 0, 1));
            $parts[] = $middleInitial . '.';
        }

        $parts[] = trim((string)$lastName);

        if (!empty($suffix)) {
            $parts[] = trim((string)$suffix);
        }

        return implode(' ', array_filter($parts, function ($value) {
            return $value !== '' && $value !== null;
        }));
    }

    private function getEducationLevelRank($level) {
        $rankMap = [
            'Doctoral' => 6,
            'Masteral' => 5,
            'College' => 4,
            'Senior High School' => 3,
            'High School' => 2,
            'Elementary' => 1,
        ];

        return $rankMap[$level] ?? 0;
    }

    public function getAllFacultyCredentials() {
        $sql = "SELECT 
                    cf.id AS faculty_id,
                    e.employee_id,
                    e.employee_code,
                    e.first_name,
                    e.middle_name,
                    e.last_name,
                    e.suffix,
                    e.email,
                    d.department_name AS department,
                    e.position_id,
                    p.position_name,
                    e.employment_type,
                    e.employment_status,
                    e.created_at
                FROM cc_faculty cf
                INNER JOIN em_employees e ON e.employee_id = cf.employee_id
                LEFT JOIN em_departments d ON d.department_id = e.department_id
                LEFT JOIN em_positions p ON p.position_id = e.position_id
                WHERE e.is_archived = 0
                ORDER BY e.last_name ASC, e.first_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacultyById($id) {
        $sql = "SELECT 
                    cf.id AS faculty_id,
                    e.employee_id,
                    e.employee_code,
                    e.first_name,
                    e.middle_name,
                    e.last_name,
                    e.suffix,
                    e.email,
                    d.department_name AS department,
                    e.position_id,
                    p.position_name,
                    e.employment_type,
                    e.employment_status,
                    e.created_at
                FROM cc_faculty cf
                INNER JOIN em_employees e ON e.employee_id = cf.employee_id
                LEFT JOIN em_departments d ON d.department_id = e.department_id
                LEFT JOIN em_positions p ON p.position_id = e.position_id
                WHERE cf.id = :id AND e.is_archived = 0
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFacultyEducationSummary() {
        $sql = "SELECT 
                    cf.id AS faculty_id,
                    e.employee_id,
                    e.employee_code,
                    e.first_name,
                    e.middle_name,
                    e.last_name,
                    e.suffix,
                    d.department_name AS department
                FROM cc_faculty cf
                INNER JOIN em_employees e ON e.employee_id = cf.employee_id
                LEFT JOIN em_departments d ON d.department_id = e.department_id
                WHERE e.is_archived = 0
                ORDER BY e.last_name ASC, e.first_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [];

        foreach ($employees as $employee) {
            $records = $this->getFacultyEducationRecords((int)$employee['employee_id']);

            if (empty($records)) {
                $summary[] = [
                    'faculty_id' => (int)$employee['faculty_id'],
                    'employee_id' => (int)$employee['employee_id'],
                    'employee_code' => $employee['employee_code'],
                    'faculty_name' => $this->formatFacultyName(
                        $employee['first_name'],
                        $employee['middle_name'],
                        $employee['last_name'],
                        $employee['suffix']
                    ),
                    'department' => $employee['department'] ?? 'Not provided',
                    'highest_degree' => 'No record yet',
                    'school_name' => 'No record yet',
                    'year_graduated' => 'No record yet',
                    'records' => [],
                ];
                continue;
            }

            usort($records, function ($a, $b) {
                $left = $this->getEducationLevelRank($a['level'] ?? '');
                $right = $this->getEducationLevelRank($b['level'] ?? '');

                if ($left !== $right) {
                    return $right <=> $left;
                }

                $leftYear = is_numeric($a['year_graduated'] ?? null) ? (int)$a['year_graduated'] : 0;
                $rightYear = is_numeric($b['year_graduated'] ?? null) ? (int)$b['year_graduated'] : 0;

                if ($leftYear !== $rightYear) {
                    return $rightYear <=> $leftYear;
                }

                return ((int)($b['education_id'] ?? 0)) <=> ((int)($a['education_id'] ?? 0));
            });

            $bestRecord = $records[0];

            $summary[] = [
                'faculty_id' => (int)$employee['faculty_id'],
                'employee_id' => (int)$employee['employee_id'],
                'employee_code' => $employee['employee_code'],
                'faculty_name' => $this->formatFacultyName(
                    $employee['first_name'],
                    $employee['middle_name'],
                    $employee['last_name'],
                    $employee['suffix']
                ),
                'department' => $employee['department'] ?? 'Not provided',
                'highest_degree' => $bestRecord['level'] ?? 'No record yet',
                'school_name' => $bestRecord['school_name'] ?? 'No record yet',
                'year_graduated' => $bestRecord['year_graduated'] ?? 'No record yet',
                'records' => $records,
            ];
        }

        return $summary;
    }

    public function getFacultyEducationRecords($employeeId) {
        $sql = "SELECT 
                    education_id,
                    employee_id,
                    level,
                    school_name,
                    course,
                    year_graduated,
                    honors,
                    created_at,
                    updated_at
                FROM em_education
                WHERE employee_id = :employee_id
                ORDER BY 
                    CASE level
                        WHEN 'Doctoral' THEN 6
                        WHEN 'Masteral' THEN 5
                        WHEN 'College' THEN 4
                        WHEN 'Senior High School' THEN 3
                        WHEN 'High School' THEN 2
                        WHEN 'Elementary' THEN 1
                        ELSE 0
                    END DESC,
                    CAST(year_graduated AS UNSIGNED) DESC,
                    education_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacultyTrainingSummary() {
        $sql = "SELECT 
                    cf.id AS faculty_id,
                    e.employee_id,
                    e.employee_code,
                    e.first_name,
                    e.middle_name,
                    e.last_name,
                    e.suffix,
                    e.employment_type,
                    e.employment_status
                FROM cc_faculty cf
                INNER JOIN em_employees e ON e.employee_id = cf.employee_id
                WHERE e.is_archived = 0
                ORDER BY e.last_name ASC, e.first_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [];

        foreach ($employees as $employee) {
            $facultyName = $this->formatFacultyName(
                $employee['first_name'],
                $employee['middle_name'],
                $employee['last_name'],
                $employee['suffix']
            );

            $trainingRecords = $this->getFacultyTrainingRecords((int)$employee['employee_id']);
            $engagementRecords = $this->getFacultyEngagementRecords((int)$employee['employee_id']);

            foreach ($trainingRecords as $record) {
                $summary[] = [
                    'employee_id' => (int)$employee['employee_id'],
                    'employee_code' => $employee['employee_code'],
                    'faculty_name' => $facultyName,
                    'type' => 'Training / Certification',
                    'title' => $record['cert_name'] ?? 'N/A',
                    'organization' => $record['issuing_organization'] ?? 'N/A',
                    'date_period' => $record['date_issued'] ?? 'N/A',
                    'status' => 'Completed',
                    'employment_type' => $employee['employment_type'] ?? 'N/A',
                    'employment_status' => $employee['employment_status'] ?? 'N/A',
                    'record_type' => 'training',
                    'record_source' => 'em_certifications',
                    'record_id' => $record['cert_id'] ?? null,
                    'outcome' => null,
                    'records' => [$record],
                ];
            }

            foreach ($engagementRecords as $record) {
                $startDate = $record['start_date'] ?? null;
                $endDate = $record['end_date'] ?? null;
                $datePeriod = $startDate && $endDate ? $startDate . ' to ' . $endDate : ($startDate ?? $endDate ?? 'N/A');

                $summary[] = [
                    'employee_id' => (int)$employee['employee_id'],
                    'employee_code' => $employee['employee_code'],
                    'faculty_name' => $facultyName,
                    'type' => $record['engagement_type'] ?? 'N/A',
                    'title' => $record['title'] ?? 'N/A',
                    'organization' => $record['organization'] ?? 'N/A',
                    'date_period' => $datePeriod,
                    'status' => $record['status'] ?? 'N/A',
                    'employment_type' => $employee['employment_type'] ?? 'N/A',
                    'employment_status' => $employee['employment_status'] ?? 'N/A',
                    'record_type' => 'engagement',
                    'record_source' => 'cc_certification_engagements',
                    'record_id' => $record['id'] ?? null,
                    'outcome' => $record['outcome'] ?? null,
                    'records' => [$record],
                ];
            }

            if (empty($trainingRecords) && empty($engagementRecords)) {
                $summary[] = [
                    'employee_id' => (int)$employee['employee_id'],
                    'employee_code' => $employee['employee_code'],
                    'faculty_name' => $facultyName,
                    'type' => 'No record yet',
                    'title' => 'No record yet',
                    'organization' => 'No record yet',
                    'date_period' => 'No record yet',
                    'status' => 'No record yet',
                    'employment_type' => $employee['employment_type'] ?? 'N/A',
                    'employment_status' => $employee['employment_status'] ?? 'N/A',
                    'record_type' => 'empty',
                    'record_source' => null,
                    'record_id' => null,
                    'outcome' => null,
                    'records' => [],
                ];
            }
        }

        return $summary;
    }

    public function getFacultyTrainingRecords($employeeId) {
        $sql = "SELECT 
                    cert_id,
                    employee_id,
                    cert_name,
                    issuing_organization,
                    date_issued,
                    expiry_date,
                    created_at,
                    updated_at
                FROM em_certifications
                WHERE employee_id = :employee_id
                ORDER BY date_issued DESC, cert_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacultyEngagementRecords($employeeId) {
        $sql = "SELECT
                    id,
                    employee_id,
                    engagement_type,
                    title,
                    organization,
                    start_date,
                    end_date,
                    status,
                    outcome,
                    approved_by,
                    approved_at,
                    certificate_generated_at,
                    archived_at,
                    created_at,
                    updated_at
                FROM cc_certification_engagements
                WHERE employee_id = :employee_id
                ORDER BY start_date DESC, id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEligibleEngagementEmployees() {
        $sql = "SELECT 
                    employee_id,
                    employee_code,
                    first_name,
                    middle_name,
                    last_name,
                    suffix,
                    employment_type,
                    position_id,
                    employment_status
                FROM em_employees
                WHERE is_archived = 0
                  AND employment_status = 'Active'
                  AND employment_type IN ('Part-time')
                ORDER BY last_name ASC, first_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeById($employeeId) {
        $sql = "SELECT employee_id, employee_code, first_name, middle_name, last_name, suffix, employment_type, employment_status, is_archived
                FROM em_employees
                WHERE employee_id = :employee_id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function hasActiveEngagementForEmployee($employeeId) {
        $sql = "SELECT id
                FROM cc_certification_engagements
                WHERE employee_id = :employee_id
                  AND archived_at IS NULL
                  AND status != 'Archived'
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addEngagement(array $data) {
        try {
            $engagementType = $data['engagement_type'] ?? '';
            $allowedTypes = ['Part-time'];
            if (!in_array($engagementType, $allowedTypes, true)) {
                error_log('addEngagement: Invalid engagement_type: ' . $engagementType);
                return false;
            }

            $allowedOutcomes = ['Continue', 'Regularize', 'End Engagement', 'Not Applicable'];
            $outcome = $data['outcome'] ?? 'Not Applicable';
            if (!in_array($outcome, $allowedOutcomes, true)) {
                $outcome = 'Not Applicable';
            }

            $sql = "INSERT INTO cc_certification_engagements
                    (employee_id, engagement_type, title, organization, start_date, end_date, status, outcome, approved_by, approved_at, certificate_generated_at, archived_at, created_at, updated_at)
                    VALUES
                    (:employee_id, :engagement_type, :title, :organization, :start_date, :end_date, :status, :outcome, NULL, NULL, NULL, NULL, NOW(), NOW())";

            $employeeId = $data['employee_id'];
            $title = $data['title'];
            $organization = $data['organization'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log('addEngagement: prepare failed: ' . json_encode($this->conn->errorInfo()));
                return false;
            }

            $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(':engagement_type', $engagementType);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':organization', $organization);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $status = $data['status'] ?? 'Pending';
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':outcome', $outcome);

            $ok = $stmt->execute();
            if ($ok) {
                $insertId = (int)$this->conn->lastInsertId();
                error_log('addEngagement: SUCCESS - inserted ID: ' . $insertId);
                return $insertId;
            } else {
                error_log('addEngagement: execute failed: ' . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (Exception $e) {
            error_log('addEngagement: Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function getEngagementById($engagementId) {
        $sql = "SELECT
                    id,
                    employee_id,
                    engagement_type,
                    title,
                    organization,
                    start_date,
                    end_date,
                    status,
                    outcome,
                    approved_by,
                    approved_at,
                    certificate_generated_at,
                    archived_at,
                    created_at,
                    updated_at
                FROM cc_certification_engagements
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $engagementId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function approveEngagement($engagementId, $approvedBy) {
        $sql = "UPDATE cc_certification_engagements
                SET status = 'Approved',
                    approved_by = :approved_by,
                    approved_at = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':approved_by', $approvedBy, PDO::PARAM_INT);
        $stmt->bindParam(':id', $engagementId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function markEngagementCompleted($engagementId, $outcome = 'Not Applicable') {
        $sql = "UPDATE cc_certification_engagements
                SET status = 'Completed',
                    outcome = :outcome,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':outcome', $outcome);
        $stmt->bindParam(':id', $engagementId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function generateEngagementCertificate($engagementId) {
        $sql = "UPDATE cc_certification_engagements
                SET certificate_generated_at = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $engagementId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function archiveEngagement($engagementId) {
        $sql = "UPDATE cc_certification_engagements
                SET status = 'Archived',
                    archived_at = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $engagementId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function restoreEngagement($engagementId) {
        // The existing schema has no previous-status column. Restore archived
        // engagements to the active workflow state used after approval.
        $sql = "UPDATE cc_certification_engagements
                SET status = 'Approved',
                    archived_at = NULL,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                  AND status = 'Archived'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $engagementId, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function getFacultyProfileWithDocuments($facultyId) {
        $faculty = $this->getFacultyById((int)$facultyId);

        if (!$faculty) {
            return null;
        }

        $employeeId = (int)$faculty['employee_id'];

        $docSql = "SELECT 
                    ed.document_id,
                    ed.employee_id,
                    ed.document_name,
                    ed.document_type,
                    ed.file_path,
                    ed.file_name,
                    ed.file_size,
                    ed.mime_type,
                    ed.category,
                    ed.expiry_date,
                    er.status AS requirement_status
                FROM employee_documents ed
                LEFT JOIN employee_requirements er ON er.employee_id = ed.employee_id AND er.document_id = ed.document_id
                WHERE ed.employee_id = :employee_id
                ORDER BY ed.created_at DESC";

        $docStmt = $this->conn->prepare($docSql);
        $docStmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $docStmt->execute();
        $documents = $docStmt->fetchAll(PDO::FETCH_ASSOC);

        $faculty['documents'] = $documents;
        return $faculty;
    }
    public function getFacultyShiftScheduleByEmployeeId($employeeId) {
        $weekStart = date('Y-m-d', strtotime('monday this week'));

        $sql = "SELECT
                    e.employee_id,
                    CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS faculty_name,
                    d.department_name AS department,
                    aes.employee_shift_id,
                    aes.weekday,
                    aes.effective_from,
                    aes.effective_to,
                    aes.is_active,
                    COALESCE(ts.shift_name, 'Custom Shift') AS shift_name,
                    COALESCE(ts.break_duration, 0) AS break_duration,
                    COALESCE(aes.start_time, ts.start_time) AS start_time,
                    COALESCE(aes.end_time, ts.end_time) AS end_time,
                    aes.break_start,
                    aes.break_end
                FROM ta_employee_shifts aes
                INNER JOIN em_employees e ON e.employee_id = aes.employee_id
                LEFT JOIN em_departments d ON d.department_id = e.department_id
                LEFT JOIN ta_shifts ts ON ts.shift_id = aes.shift_id
                WHERE aes.employee_id = :employee_id
                  AND aes.is_active = 1
                  AND aes.effective_to >= :week_start
                ORDER BY aes.effective_from ASC, aes.employee_shift_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->bindParam(':week_start', $weekStart);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        $dayNames = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        $weeklySchedule = [];
        $minFrom = null;
        $maxTo = null;

        foreach ($rows as $row) {
            if ($minFrom === null || $row['effective_from'] < $minFrom) {
                $minFrom = $row['effective_from'];
            }
            if ($maxTo === null || $row['effective_to'] > $maxTo) {
                $maxTo = $row['effective_to'];
            }

            $day = $dayNames[(int)$row['weekday']] ?? null;
            if ($day === null || isset($weeklySchedule[$day])) {
                continue; // pinakamalapit na petsa lang bawat araw
            }

            $weeklySchedule[$day] = [
                'weekday'     => $day,
                'start_time'  => $row['start_time']  ?? '--',
                'break_start' => $row['break_start'] ?? '--',
                'break_end'   => $row['break_end']   ?? '--',
                'end_time'    => $row['end_time']    ?? '--',
                'is_active'   => 1,
            ];
        }

        $first = $rows[0];

        return [
            'employee_id'      => (int)$first['employee_id'],
            'faculty_name'     => preg_replace('/\s+/', ' ', trim((string)$first['faculty_name'])) ?: 'N/A',
            'department'       => $first['department'] ?? 'Not provided',
            'shift_name'       => $first['shift_name'] ?? 'N/A',
            'shift_start_time' => $first['start_time'] ?? '--',
            'shift_end_time'   => $first['end_time'] ?? '--',
            'break_duration'   => $first['break_duration'] ?? '--',
            'effective_from'   => $minFrom,
            'effective_to'     => $maxTo,
            'status'           => 'Active',
            'weekly_schedule'  => $weeklySchedule,
            'weekly_schedule_available' => !empty($weeklySchedule),
        ];
    }

    public function getFacultyRequirementStatusSummary() {
        $sql = "SELECT er.status, COUNT(*) AS total
                FROM em_requirements er
                INNER JOIN cc_faculty cf ON cf.employee_id = er.employee_id
                GROUP BY er.status
                ORDER BY er.status ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [
            'Complete' => 0,
            'Pending' => 0,
            'Incomplete / Missing' => 0,
        ];

        foreach ($rows as $row) {
            $status = trim((string)($row['status'] ?? ''));
            $count = (int)($row['total'] ?? 0);

            if ($status === 'Submitted') {
                $summary['Complete'] = $count;
            } elseif ($status === 'For Follow-up') {
                $summary['Pending'] = $count;
            } elseif ($status === 'Missing') {
                $summary['Incomplete / Missing'] = $count;
            }
        }

        $hasData = array_sum($summary) > 0;
        return $hasData ? $summary : [];
    }

    public function getFacultyRequirements($employeeId) {
        $sql = "SELECT
                    requirement_id,
                    employee_id,
                    document_id,
                    requirement_name,
                    status,
                    remarks,
                    submitted_date,
                    follow_up_date
                FROM em_requirements
                WHERE employee_id = :employee_id
                ORDER BY requirement_name ASC, requirement_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
