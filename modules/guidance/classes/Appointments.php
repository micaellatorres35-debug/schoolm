<?php
include_once __DIR__ . '/../../../database/db.php';

class Appointments
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
                ap.appointment_id,
                ap.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                ap.counselor_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                ap.appointment_date,
                ap.purpose,
                ap.meeting_type,
                ap.status,
                ap.remarks,
                ap.case_id,
                ap.created_at
            FROM gd_appointments ap
            JOIN enr_students s
                ON s.student_number = ap.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            JOIN em_employees e
                ON e.employee_id = ap.counselor_id
            {$whereSql}
            ORDER BY ap.appointment_date DESC
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
            FROM gd_appointments ap
            JOIN enr_students s
                ON s.student_number = ap.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            JOIN em_employees e
                ON e.employee_id = ap.counselor_id
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
                OR ap.student_number LIKE :search
                OR ap.purpose LIKE :search
            )';

            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $where[] = 'ap.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['meeting_type'])) {
            $where[] = 'ap.meeting_type = :meeting_type';
            $params['meeting_type'] = $filters['meeting_type'];
        }

        if (!empty($filters['counselor_id'])) {
            $where[] = 'ap.counselor_id = :counselor_id';
            $params['counselor_id'] = $filters['counselor_id'];
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $where[] = 'ap.appointment_date BETWEEN :date_from AND :date_to';
            $params['date_from'] = $filters['date_from'];
            $params['date_to'] = $filters['date_to'];
        }

        $whereSql = $where
            ? 'WHERE ' . implode(' AND ', $where)
            : '';

        return [$whereSql, $params];
    }

    public function getAppointmentOverview(int $appointmentId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT
                ap.appointment_id,
                ap.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                ap.counselor_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                ap.case_id,
                ap.appointment_date,
                ap.purpose,
                ap.meeting_type,
                ap.status,
                ap.remarks,
                ap.created_at
            FROM gd_appointments ap
            JOIN enr_students s
                ON s.student_number = ap.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            JOIN em_employees e
                ON e.employee_id = ap.counselor_id
            WHERE ap.appointment_id = :appointment_id
        ");

        $stmt->execute([
            'appointment_id' => $appointmentId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function isDoubleBooked(
        int $counselorId,
        string $appointmentDate,
        ?int $excludeAppointmentId = null
    ): bool {
        $sql = "
            SELECT COUNT(*)
            FROM gd_appointments
            WHERE counselor_id = :counselor_id
              AND appointment_date = :appointment_date
              AND status NOT IN ('Cancelled', 'No Show')
        ";

        $params = [
            'counselor_id' => $counselorId,
            'appointment_date' => $appointmentDate
        ];

        if ($excludeAppointmentId !== null) {
            $sql .= ' AND appointment_id != :exclude_id';
            $params['exclude_id'] = $excludeAppointmentId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function getOpenCases(): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                c.case_id,
                c.case_number,
                c.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                c.case_type,
                c.status,
                c.counselor_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name
            FROM gd_cases c
            JOIN enr_students s
                ON s.student_number = c.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            JOIN em_employees e
                ON e.employee_id = c.counselor_id
            WHERE c.status IN ('Open', 'In Progress')
            ORDER BY c.opened_at DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAppointment(array $data): int
    {
        if (
            $this->isDoubleBooked(
                (int) $data['counselor_id'],
                $data['appointment_date']
            )
        ) {
            throw new RuntimeException(
                'This counselor already has an appointment at that exact date/time.'
            );
        }

        $stmt = $this->conn->prepare("
            INSERT INTO gd_appointments
            (
                student_number,
                counselor_id,
                case_id,
                appointment_date,
                purpose,
                meeting_type,
                status
            )
            VALUES
            (
                :student_number,
                :counselor_id,
                :case_id,
                :appointment_date,
                :purpose,
                :meeting_type,
                'Pending'
            )
        ");

        $stmt->execute([
            'student_number' => $data['student_number'],
            'counselor_id' => $data['counselor_id'],
            'case_id' => $data['case_id'] ?: null,
            'appointment_date' => $data['appointment_date'],
            'purpose' => $data['purpose'],
            'meeting_type' => $data['meeting_type'] ?? 'Face-to-Face'
        ]);

        return (int) $this->conn->lastInsertId();
    }

    public function updateStatus(
        int $appointmentId,
        string $status,
        ?string $remarks = null
    ): bool {
        $sql = "
            UPDATE gd_appointments
            SET status = :status
        ";

        $params = [
            'status' => $status,
            'appointment_id' => $appointmentId
        ];

        if ($remarks !== null) {
            $sql .= ", remarks = :remarks";
            $params['remarks'] = $remarks;
        }

        $sql .= " WHERE appointment_id = :appointment_id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($params);
    }

    public function reschedule(
        int $appointmentId,
        string $newDate
    ): bool {
        $current = $this->getAppointmentOverview($appointmentId);

        if (!$current) {
            throw new RuntimeException('Appointment not found.');
        }

        if (
            $this->isDoubleBooked(
                (int) $current['counselor_id'],
                $newDate,
                $appointmentId
            )
        ) {
            throw new RuntimeException(
                'This counselor already has an appointment at that exact date/time.'
            );
        }

        $stmt = $this->conn->prepare("
            UPDATE gd_appointments
            SET appointment_date = :appointment_date
            WHERE appointment_id = :appointment_id
        ");

        return $stmt->execute([
            'appointment_date' => $newDate,
            'appointment_id' => $appointmentId
        ]);
    }

    public function addRemarks(
        int $appointmentId,
        string $remarks
    ): bool {
        $stmt = $this->conn->prepare("
            UPDATE gd_appointments
            SET remarks = :remarks
            WHERE appointment_id = :appointment_id
        ");

        return $stmt->execute([
            'remarks' => $remarks,
            'appointment_id' => $appointmentId
        ]);
    }

    public function getCounselorSchedule(
        int $counselorId,
        string $dayOfWeek
    ): array {
        $stmt = $this->conn->prepare("
            SELECT
                start_time,
                end_time,
                availability
            FROM gd_counselor_schedules
            WHERE counselor_id = :counselor_id
              AND day_of_week = :day_of_week
        ");

        $stmt->execute([
            'counselor_id' => $counselorId,
            'day_of_week' => $dayOfWeek
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookedTimes(
        int $counselorId,
        string $date
    ): array {
        $stmt = $this->conn->prepare("
            SELECT appointment_date
            FROM gd_appointments
            WHERE counselor_id = :counselor_id
              AND DATE(appointment_date) = :date
              AND status NOT IN ('Cancelled', 'No Show')
            ORDER BY appointment_date ASC
        ");

        $stmt->execute([
            'counselor_id' => $counselorId,
            'date' => $date
        ]);

        return array_column(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            'appointment_date'
        );
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
}

