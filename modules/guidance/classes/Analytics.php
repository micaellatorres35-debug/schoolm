<?php
include_once __DIR__ . '/../../../database/db.php';

class Analytics
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

    public function getDashboardSummary(): array
    {
        $activeCases = $this->scalar("SELECT COUNT(*) FROM gd_cases WHERE status IN ('Open', 'In Progress')");
        $monitoring = $this->scalar("SELECT COUNT(*) FROM gd_student_profiles WHERE guidance_status = 'Monitoring'");
        $todaysAppts = $this->scalar("SELECT COUNT(*) FROM gd_appointments WHERE DATE(appointment_date) = CURDATE() AND status != 'Cancelled'");
        $pendingRefs = $this->scalar("SELECT COUNT(*) FROM gd_referrals WHERE referral_status = 'Pending'");
        $highRisk = $this->scalar("SELECT COUNT(*) FROM gd_student_profiles WHERE risk_level = 'High'");
        $recentIncdt = $this->scalar("SELECT COUNT(*) FROM gd_incidents WHERE incident_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $monthlySess = $this->scalar("SELECT COUNT(*) FROM gd_counseling_sessions WHERE MONTH(session_date) = MONTH(CURDATE()) AND YEAR(session_date) = YEAR(CURDATE())");

        return [
            'active_cases' => $activeCases,
            'students_monitoring' => $monitoring,
            'todays_appointments' => $todaysAppts,
            'pending_referrals' => $pendingRefs,
            'high_risk_students' => $highRisk,
            'recent_incidents' => $recentIncdt,
            'monthly_counseling_sessions' => $monthlySess
        ];
    }

    public function getTodaysAppointmentsList(int $limit = 5): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                ap.appointment_date,
                ap.purpose,
                ap.status,
                CONCAT(a.surname, ', ', a.first_name) AS student_name
            FROM gd_appointments ap
            JOIN enr_students s ON s.student_number = ap.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            WHERE DATE(ap.appointment_date) = CURDATE()
            ORDER BY ap.appointment_date ASC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingReferralsList(int $limit = 5): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                r.referral_date,
                r.referral_source,
                c.case_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name
            FROM gd_referrals r
            JOIN gd_cases c ON c.case_id = r.case_id
            JOIN enr_students s ON s.student_number = c.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            WHERE r.referral_status = 'Pending'
            ORDER BY r.referral_date ASC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHighRiskStudentsList(int $limit = 5): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                p.risk_level,
                p.guidance_status,
                s.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name
            FROM gd_student_profiles p
            JOIN enr_students s ON s.student_number = p.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            WHERE p.risk_level = 'High'
            ORDER BY p.updated_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentIncidentsList(int $limit = 5): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                i.incident_date,
                i.incident_type,
                i.severity,
                i.status,
                CONCAT(a.surname, ', ', a.first_name) AS student_name
            FROM gd_incidents i
            JOIN enr_students s ON s.student_number = i.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            ORDER BY i.incident_date DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCasesByStatus(): array
    {
        return $this->groupedCounts("
            SELECT status AS label, COUNT(*) AS count
            FROM gd_cases
            GROUP BY status
        ");
    }

    public function getAppointmentsByStatus(): array
    {
        return $this->groupedCounts("
            SELECT status AS label, COUNT(*) AS count
            FROM gd_appointments
            GROUP BY status
        ");
    }

    public function getIncidentsBySeverity(): array
    {
        return $this->groupedCounts("
            SELECT severity AS label, COUNT(*) AS count
            FROM gd_incidents
            GROUP BY severity
        ");
    }

    private function groupedCounts(string $sql): array
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateReport(string $type, array $filters): array
    {
        return match ($type) {
            'student_counseling' => $this->studentCounselingReport($filters),
            'referral' => $this->referralReport($filters),
            'appointment' => $this->appointmentReport($filters),
            'incident' => $this->incidentReport($filters),
            'monthly_guidance' => $this->periodGuidanceReport($filters, 'month'),
            'yearly_guidance' => $this->periodGuidanceReport($filters, 'year'),
            'counselor_workload' => $this->counselorWorkloadReport($filters),
            'student_risk' => $this->studentRiskSummary($filters),
            default => []
        };
    }

    private function dateWhere(array $filters, string $column): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "{$column} >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "{$column} <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        return [$where, $params];
    }

    private function studentCounselingReport(array $filters): array
    {
        [$where, $params] = $this->dateWhere($filters, 'cs.session_date');
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->conn->prepare("
            SELECT
                cs.session_date,
                cs.session_type,
                cs.duration_minutes,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                c.case_number
            FROM gd_counseling_sessions cs
            JOIN gd_cases c ON c.case_id = cs.case_id
            JOIN enr_students s ON s.student_number = c.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            JOIN sms_employee e ON e.employee_id = cs.counselor_id
            {$whereSql}
            ORDER BY cs.session_date DESC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function referralReport(array $filters): array
    {
        [$where, $params] = $this->dateWhere($filters, 'r.referral_date');
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->conn->prepare("
            SELECT
                r.referral_date,
                r.referral_source,
                r.referral_status,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                c.case_number
            FROM gd_referrals r
            JOIN gd_cases c ON c.case_id = r.case_id
            JOIN enr_students s ON s.student_number = c.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            {$whereSql}
            ORDER BY r.referral_date DESC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function appointmentReport(array $filters): array
    {
        [$where, $params] = $this->dateWhere($filters, 'ap.appointment_date');
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->conn->prepare("
            SELECT
                ap.appointment_date,
                ap.meeting_type,
                ap.status,
                ap.purpose,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name
            FROM gd_appointments ap
            JOIN enr_students s ON s.student_number = ap.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            JOIN sms_employee e ON e.employee_id = ap.counselor_id
            {$whereSql}
            ORDER BY ap.appointment_date DESC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function incidentReport(array $filters): array
    {
        [$where, $params] = $this->dateWhere($filters, 'i.incident_date');
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->conn->prepare("
            SELECT
                i.incident_date,
                i.incident_type,
                i.severity,
                i.status,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                CONCAT(e.first_name, ' ', e.last_name) AS reported_by_name
            FROM gd_incidents i
            JOIN enr_students s ON s.student_number = i.student_number
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            JOIN sms_employee e ON e.employee_id = i.reported_by
            {$whereSql}
            ORDER BY i.incident_date DESC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function periodGuidanceReport(array $filters, string $granularity): array
    {
        $year = $filters['year'] ?? date('Y');

        if ($granularity === 'month') {
            $month = $filters['month'] ?? date('n');
            $dateFrom = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            $dateTo = date('Y-m-t 23:59:59', strtotime($dateFrom));

            return [[
                'period' => date('F Y', strtotime($dateFrom)),
                'cases_opened' => $this->scalarBetween('gd_cases', 'opened_at', $dateFrom, $dateTo),
                'cases_closed' => $this->scalarBetween('gd_cases', 'closed_at', $dateFrom, $dateTo),
                'referrals_submitted' => $this->scalarBetween('gd_referrals', 'referral_date', $dateFrom, $dateTo),
                'sessions_held' => $this->scalarBetween('gd_counseling_sessions', 'session_date', $dateFrom, $dateTo),
                'appointments_completed' => $this->scalarBetween('gd_appointments', 'appointment_date', $dateFrom, $dateTo, "status = 'Completed'"),
                'incidents_reported' => $this->scalarBetween('gd_incidents', 'incident_date', $dateFrom, $dateTo)
            ]];
        }

        $rows = [];

        for ($m = 1; $m <= 12; $m++) {
            $dateFrom = sprintf('%04d-%02d-01 00:00:00', $year, $m);
            $dateTo = date('Y-m-t 23:59:59', strtotime($dateFrom));

            $rows[] = [
                'period' => date('F Y', strtotime($dateFrom)),
                'cases_opened' => $this->scalarBetween('gd_cases', 'opened_at', $dateFrom, $dateTo),
                'cases_closed' => $this->scalarBetween('gd_cases', 'closed_at', $dateFrom, $dateTo),
                'referrals_submitted' => $this->scalarBetween('gd_referrals', 'referral_date', $dateFrom, $dateTo),
                'sessions_held' => $this->scalarBetween('gd_counseling_sessions', 'session_date', $dateFrom, $dateTo),
                'appointments_completed' => $this->scalarBetween('gd_appointments', 'appointment_date', $dateFrom, $dateTo, "status = 'Completed'"),
                'incidents_reported' => $this->scalarBetween('gd_incidents', 'incident_date', $dateFrom, $dateTo)
            ];
        }

        return $rows;
    }

    private function counselorWorkloadReport(array $filters): array
    {
        [$where, $params] = $this->dateWhere($filters, 'c.opened_at');
        $whereSql = $where ? 'AND ' . implode(' AND ', $where) : '';

        $stmt = $this->conn->prepare("
            SELECT
                e.employee_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                p.position_name,
                COUNT(DISTINCT c.case_id) AS total_cases,
                SUM(c.status IN ('Open', 'In Progress')) AS active_cases,
                (
                    SELECT COUNT(*)
                    FROM gd_appointments ap
                    WHERE ap.counselor_id = e.employee_id
                ) AS total_appointments,
                (
                    SELECT COUNT(*)
                    FROM gd_counseling_sessions cs
                    WHERE cs.counselor_id = e.employee_id
                ) AS total_sessions
            FROM sms_employee e
            JOIN sd_position p ON p.position_id = e.position
            LEFT JOIN gd_cases c
                ON c.counselor_id = e.employee_id {$whereSql}
            WHERE e.department = 8
            AND e.status = 'active'
            GROUP BY e.employee_id, counselor_name, p.position_name
            ORDER BY total_cases DESC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function studentRiskSummary(array $filters): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                s.student_number,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                s.course_id,
                s.section_id,
                s.year_level,
                p.risk_level,
                p.guidance_status,
                p.updated_at,
                (
                    SELECT COUNT(*)
                    FROM gd_cases c
                    WHERE c.student_number = s.student_number
                ) AS total_cases,
                (
                    SELECT COUNT(*)
                    FROM gd_incidents i
                    WHERE i.student_number = s.student_number
                ) AS total_incidents
            FROM gd_student_profiles p
            JOIN enr_students s
                ON s.student_number = p.student_number
            JOIN enr_applicants a
                ON a.applicant_id = s.applicant_id
            ORDER BY
                FIELD(p.risk_level, 'High', 'Moderate', 'Low'),
                p.updated_at DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function scalar(string $sql): int
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function scalarBetween(
        string $table,
        string $column,
        string $from,
        string $to,
        ?string $extraWhere = null
    ): int {
        $sql = "
            SELECT COUNT(*)
            FROM {$table}
            WHERE {$column} BETWEEN :from AND :to
        ";

        if ($extraWhere) {
            $sql .= " AND {$extraWhere}";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'from' => $from,
            'to' => $to
        ]);

        return (int) $stmt->fetchColumn();
    }
}