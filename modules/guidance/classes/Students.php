<?php

include_once __DIR__ . '/../../../database/db.php';

class Students
{
    private $conn;

    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /* ---------------------------------------------------------------
       List + pagination
    --------------------------------------------------------------- */
    public function getList(array $filters, int $page, int $pageSize): array
    {
        [$whereSql, $params] = $this->buildListFilters($filters);
        $offset = ($page - 1) * $pageSize;
        $total = $this->countList($whereSql, $params);

        $sql = "
            SELECT
                p.profile_id,
                s.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS name,
                s.year_level,
                s.section_id,
                cs.section_code AS section,
                s.course_id,
                rc.name AS course,
                p.risk_level,
                p.guidance_status,
                p.updated_at
            FROM gd_student_profiles p
            JOIN enr_students s
                ON s.student_number = p.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            LEFT JOIN cc_sections cs
                ON cs.id = s.section_id
            LEFT JOIN rgr_courses rc
                ON rc.id = s.course_id
            {$whereSql}
            ORDER BY p.updated_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = array_map(
            [$this, 'formatListRow'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function formatListRow(array $row): array
    {
        $row['initials'] = $this->initialsFromName($row['name']);

        $section = $row['section'] ?? 'N/A';
        $row['year_section'] = "Year {$row['year_level']} - {$section}";

        $row['updated_at_display'] = date(
            'M d, Y',
            strtotime($row['updated_at'])
        );

        return $row;
    }

    private function initialsFromName(string $name): string
    {
        $parts = array_filter(preg_split('/[\s,]+/', $name));

        $letters = array_map(
            fn($p) => mb_strtoupper(mb_substr($p, 0, 1)),
            array_slice($parts, 0, 2)
        );

        return implode('', $letters);
    }

    private function countList(string $whereSql, array $params): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM gd_student_profiles p
            JOIN enr_students s
                ON s.student_number = p.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            LEFT JOIN cc_sections cs
                ON cs.id = s.section_id
            LEFT JOIN rgr_courses rc
                ON rc.id = s.course_id
            {$whereSql}
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    private function buildListFilters(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(
                a.first_name LIKE :search
                OR a.surname LIKE :search
                OR s.student_number LIKE :search
            )';

            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['year_level'])) {
            $where[] = 's.year_level = :year_level';
            $params['year_level'] = $filters['year_level'];
        }

        if (!empty($filters['section'])) {
            $where[] = 's.section_id = :section';
            $params['section'] = $filters['section'];
        }

        if (!empty($filters['course'])) {
            $where[] = 's.course_id = :course';
            $params['course'] = $filters['course'];
        }

        if (!empty($filters['risk'])) {
            $where[] = 'p.risk_level = :risk';
            $params['risk'] = $filters['risk'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'p.guidance_status = :status';
            $params['status'] = $filters['status'];
        }

        $whereSql = $where
            ? 'WHERE ' . implode(' AND ', $where)
            : '';

        return [$whereSql, $params];
    }

    public function getSummaryCounts(): array
    {
        $totalStmt = $this->conn->prepare("
            SELECT COUNT(*) AS total
            FROM enr_students
            WHERE enrollment_status = 'enrolled'
        ");

        $totalStmt->execute();
        $total = (int) $totalStmt->fetchColumn();

        $stmt = $this->conn->prepare("
            SELECT
                SUM(guidance_status = 'Active') AS active,
                SUM(guidance_status = 'Monitoring') AS monitoring,
                SUM(risk_level = 'High') AS high_risk
            FROM gd_student_profiles
        ");

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'active' => (int) ($row['active'] ?? 0),
            'monitoring' => (int) ($row['monitoring'] ?? 0),
            'high_risk' => (int) ($row['high_risk'] ?? 0),
        ];
    }

    /* ---------------------------------------------------------------
       Profile overview
    --------------------------------------------------------------- */
    public function getOverview(string $studentNumber): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT
                s.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS name,
                s.year_level,
                cs.section_code AS section,
                rc.name AS course,
                a.sex AS gender,
                a.date_of_birth AS birth_date,
                a.email,
                a.contact_number AS phone,
                a.address,
                s.enrollment_status,
                p.risk_level,
                p.guidance_status,
                a.applicant_id
            FROM gd_student_profiles p
            JOIN enr_students s
                ON s.student_number = p.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            LEFT JOIN cc_sections cs
                ON cs.id = s.section_id
            LEFT JOIN rgr_courses rc
                ON rc.id = s.course_id
            WHERE s.student_number = :student_number
        ");

        $stmt->execute([
            'student_number' => $studentNumber
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /* ---------------------------------------------------------------
       Guidance remarks
    --------------------------------------------------------------- */
    public function getRemarks(string $studentNumber): array
    {
        $stmt = $this->conn->prepare("
            SELECT remarks, updated_at
            FROM gd_student_profiles
            WHERE student_number = :student_number
        ");

        $stmt->execute([
            'student_number' => $studentNumber
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['remarks'])) {
            return [];
        }

        return [[
            'date' => $row['updated_at'],
            'by' => '',
            'text' => $row['remarks'],
        ]];
    }

    public function saveRemark(string $studentNumber, string $remarks): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE gd_student_profiles
            SET remarks = :remarks,
                updated_at = NOW()
            WHERE student_number = :student_number
        ");

        return $stmt->execute([
            'remarks' => $remarks,
            'student_number' => $studentNumber,
        ]);
    }

    /* ---------------------------------------------------------------
       Case history
    --------------------------------------------------------------- */
    public function getCaseHistory(string $studentNumber): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                c.case_id,
                c.case_number,
                c.case_type,
                c.priority,
                c.status,
                c.summary,
                c.opened_at,
                c.closed_at,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name
            FROM gd_cases c
            JOIN em_employees e
                ON e.employee_id = c.counselor_id
            WHERE c.student_number = :student_number
            ORDER BY c.opened_at DESC
        ");

        $stmt->execute([
            'student_number' => $studentNumber
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAppointmentHistory(string $studentNumber): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                appointment_date AS date,
                purpose AS title,
                status,
                remarks AS `desc`,
                meeting_type
            FROM gd_appointments
            WHERE student_number = :student_number
            ORDER BY appointment_date DESC
        ");

        $stmt->execute([
            'student_number' => $studentNumber
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIncidentHistory(string $studentNumber): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                incident_date AS date,
                incident_type AS title,
                description AS `desc`,
                severity,
                status
            FROM gd_incidents
            WHERE student_number = :student_number
            ORDER BY incident_date DESC
        ");

        $stmt->execute([
            'student_number' => $studentNumber
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------------------------------------------------------------
       Documents
    --------------------------------------------------------------- */
    public function getDocuments(string $studentNumber): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                document_id AS id,
                file_name AS name,
                document_type AS type,
                uploaded_at AS date
            FROM gd_student_documents
            WHERE student_number = :student_number
            ORDER BY uploaded_at DESC
        ");

        $stmt->execute([
            'student_number' => $studentNumber
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveDocument(array $data): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO gd_student_documents
                (
                    student_number,
                    uploaded_by,
                    document_type,
                    file_name,
                    file_path
                )
            VALUES
                (
                    :student_number,
                    :uploaded_by,
                    :document_type,
                    :file_name,
                    :file_path
                )
        ");

        return $stmt->execute([
            'student_number' => $data['student_number'],
            'uploaded_by' => $data['uploaded_by'],
            'document_type' => $data['document_type'],
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
        ]);
    }
}