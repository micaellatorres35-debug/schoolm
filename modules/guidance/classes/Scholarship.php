<?php
include_once __DIR__ . '/../../../database/db.php';

class Scholarship
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

    public function getList(array $filters, int $page, int $pageSize): array
    {
        [$whereSql, $params] = $this->buildListFilters($filters);

        $offset = ($page - 1) * $pageSize;
        $total = $this->countList($whereSql, $params);

        $sql = "
            SELECT
                sc.scholarship_id,
                sc.application_number,
                sc.scholarship_type_id,
                sc.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                st.type_name AS scholarship_name,
                st.sponsor,
                st.fixed_amount,
                st.coverage_type,
                st.eligibility_basis,
                st.description,
                sc.justification,
                sc.status,
                sc.counselor_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                sc.reviewed_by,
                sc.reviewed_at,
                sc.attachment_path,
                sc.attachment_name,
                sc.applied_at,
                sc.created_at,
                sc.updated_at
            FROM gd_scholarships sc
            JOIN gd_scholarship_types st
                ON st.scholarship_type_id = sc.scholarship_type_id
            JOIN enr_students s
                ON s.student_number = sc.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            LEFT JOIN em_employees e
                ON e.employee_id = sc.counselor_id
            {$whereSql}
            ORDER BY sc.applied_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    }

    private function countList(string $whereSql, array $params): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM gd_scholarships sc
            JOIN gd_scholarship_types st
                ON st.scholarship_type_id = sc.scholarship_type_id
            JOIN enr_students s
                ON s.student_number = sc.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            LEFT JOIN em_employees e
                ON e.employee_id = sc.counselor_id
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
                sc.application_number LIKE :search
                OR sc.student_number LIKE :search
                OR a.first_name LIKE :search
                OR a.surname LIKE :search
                OR st.type_name LIKE :search
                OR st.sponsor LIKE :search
            )';

            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $where[] = 'sc.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['eligibility_basis'])) {
            $where[] = 'st.eligibility_basis = :eligibility_basis';
            $params['eligibility_basis'] = $filters['eligibility_basis'];
        }

        if (!empty($filters['coverage_type'])) {
            $where[] = 'st.coverage_type = :coverage_type';
            $params['coverage_type'] = $filters['coverage_type'];
        }

        if (!empty($filters['counselor_id'])) {
            $where[] = 'sc.counselor_id = :counselor_id';
            $params['counselor_id'] = $filters['counselor_id'];
        }

        if (!empty($filters['scholarship_type_id'])) {
            $where[] = 'sc.scholarship_type_id = :scholarship_type_id';
            $params['scholarship_type_id'] = $filters['scholarship_type_id'];
        }

        $whereSql = $where
            ? 'WHERE ' . implode(' AND ', $where)
            : '';

        return [$whereSql, $params];
    }

    public function getOverview(int $scholarshipId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT
                sc.scholarship_id,
                sc.application_number,
                sc.scholarship_type_id,
                sc.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                st.type_name AS scholarship_name,
                st.sponsor,
                st.fixed_amount,
                st.coverage_type,
                st.eligibility_basis,
                st.description,
                sc.justification,
                sc.status,
                sc.review_notes,
                sc.counselor_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                sc.reviewed_by,
                r.first_name AS reviewer_first_name,
                r.last_name AS reviewer_last_name,
                sc.attachment_path,
                sc.attachment_name,
                sc.applied_at,
                sc.reviewed_at,
                sc.created_at,
                sc.updated_at
            FROM gd_scholarships sc
            JOIN gd_scholarship_types st
                ON st.scholarship_type_id = sc.scholarship_type_id
            JOIN enr_students s
                ON s.student_number = sc.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            LEFT JOIN em_employees e
                ON e.employee_id = sc.counselor_id
            LEFT JOIN em_employees r
                ON r.employee_id = sc.reviewed_by
            WHERE sc.scholarship_id = :scholarship_id
        ");

        $stmt->execute([
            'scholarship_id' => $scholarshipId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['reviewer_name'] = $row['reviewed_by']
            ? trim(
                ($row['reviewer_first_name'] ?? '') . ' ' .
                ($row['reviewer_last_name'] ?? '')
            )
            : null;

        unset(
            $row['reviewer_first_name'],
            $row['reviewer_last_name']
        );

        return $row;
    }

    public function createApplication(array $data): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO gd_scholarships
            (
                application_number,
                scholarship_type_id,
                student_number,
                counselor_id,
                justification,
                status,
                attachment_path,
                attachment_name,
                applied_at
            )
            VALUES
            (
                :application_number,
                :scholarship_type_id,
                :student_number,
                :counselor_id,
                :justification,
                'Applied',
                :attachment_path,
                :attachment_name,
                NOW()
            )
        ");

        $stmt->execute([
            'application_number' => $data['application_number'],
            'scholarship_type_id' => $data['scholarship_type_id'],
            'student_number' => $data['student_number'],
            'counselor_id' => $data['counselor_id'] ?? null,
            'justification' => $data['justification'] ?? null,
            'attachment_path' => $data['attachment_path'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null
        ]);

        return (int) $this->conn->lastInsertId();
    }

    public function markUnderReview(int $scholarshipId): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships
            SET status = 'Under Review'
            WHERE scholarship_id = :id
            AND status = 'Applied'
        ");

        $stmt->execute([
            'id' => $scholarshipId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function reviewApplication(
        int $scholarshipId,
        string $decision,
        int $reviewerId,
        ?string $notes = null
    ): bool {
        $status = $decision === 'approve'
            ? 'Approved'
            : 'Rejected';

        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships
            SET
                status = :status,
                review_notes = :notes,
                reviewed_by = :reviewer_id,
                reviewed_at = NOW()
            WHERE scholarship_id = :id
            AND status = 'Under Review'
        ");

        $stmt->execute([
            'status' => $status,
            'notes' => $notes,
            'reviewer_id' => $reviewerId,
            'id' => $scholarshipId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function activate(int $scholarshipId): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships
            SET status = 'Active'
            WHERE scholarship_id = :id
            AND status = 'Approved'
        ");

        $stmt->execute([
            'id' => $scholarshipId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function finalize(
        int $scholarshipId,
        string $outcome,
        ?string $notes = null
    ): bool {
        $status = $outcome === 'completed'
            ? 'Completed'
            : 'Terminated';

        $stmt = $this->conn->prepare("
            SELECT review_notes
            FROM gd_scholarships
            WHERE scholarship_id = :id
            AND status = 'Active'
        ");

        $stmt->execute([
            'id' => $scholarshipId
        ]);

        $existing = $stmt->fetchColumn();

        if ($existing === false) {
            return false;
        }

        $notes = trim($notes ?? '');
        $tag = "[{$status}] {$notes}";

        $updatedNotes = $notes === ''
            ? $existing
            : (
                empty($existing)
                    ? $tag
                    : $existing . "\n\n" . $tag
            );

        $update = $this->conn->prepare("
            UPDATE gd_scholarships
            SET
                status = :status,
                review_notes = :notes
            WHERE scholarship_id = :id
            AND status = 'Active'
        ");

        $update->execute([
            'status' => $status,
            'notes' => $updatedNotes,
            'id' => $scholarshipId
        ]);

        return $update->rowCount() > 0;
    }

    public function setAttachment(
        int $scholarshipId,
        string $path,
        string $originalName
    ): bool {
        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships
            SET
                attachment_path = :path,
                attachment_name = :name
            WHERE scholarship_id = :id
        ");

        return $stmt->execute([
            'path' => $path,
            'name' => $originalName,
            'id' => $scholarshipId
        ]);
    }

    public function getCounselors(): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                e.employee_id,
                CONCAT(e.first_name, ' ', e.last_name) AS name,
                p.position_name
            FROM em_employees e
            JOIN em_positions p
                ON p.position_id = e.position_id
            WHERE e.unit_id = 17
            AND e.employment_status = 'active'
            ORDER BY e.last_name ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function studentExists(string $studentNumber): bool
    {
        $stmt = $this->conn->prepare("
            SELECT 1
            FROM enr_students
            WHERE student_number = :sn
        ");

        $stmt->execute([
            'sn' => $studentNumber
        ]);

        return (bool) $stmt->fetchColumn();
    }
}