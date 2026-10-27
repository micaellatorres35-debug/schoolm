<?php
include_once __DIR__ . '/../../../database/db.php';

/**
 * GuidanceReport
 * Builds the four Guidance report datasets (case summary, incident,
 * appointment/session, scholarship) and manages the saved-report records
 * and PDF storage folder. Dashboard queries stay in Analytics.php.
 */
class GuidanceReport
{
    public const TYPES = [
        'case_summary' => 'Case Summary Report',
        'incident'     => 'Incident Report',
        'appointment'  => 'Appointment / Session Report',
        'scholarship'  => 'Scholarship Report',
    ];

    public const DETAIL_LIMIT = 500;
    public const FILE_PATTERN = '/^gdr_[a-z_]+_\d{8}_\d{6}_[a-f0-9]{8}\.pdf$/';

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

    /* ------------------------------------------------------------------
       Validation / helpers
    ------------------------------------------------------------------ */

    public static function isValidType(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    /**
     * Validates Y-m-d dates and returns an inclusive period.
     * The end date is made inclusive by comparing against the next day (exclusive).
     */
    public static function normalizePeriod(string $from, string $to): ?array
    {
        $f = DateTime::createFromFormat('Y-m-d', $from);
        $t = DateTime::createFromFormat('Y-m-d', $to);

        if (!$f || !$t || $f->format('Y-m-d') !== $from || $t->format('Y-m-d') !== $to) {
            return null;
        }
        if ($f > $t || $f->diff($t)->days > 1830) {
            return null;
        }

        return [
            'from'    => $from,
            'to'      => $to,
            'from_dt' => $from . ' 00:00:00',
            'to_excl' => (clone $t)->modify('+1 day')->format('Y-m-d 00:00:00'),
        ];
    }

    /** PDF storage folder (outside module code, guarded from direct web access). */
    public static function storageDir(): string
    {
        $root = realpath(__DIR__ . '/../../../');
        $dir  = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'guidance_reports';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $guard = $dir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!is_file($guard)) {
            file_put_contents($guard, "Require all denied\n");
        }

        return $dir;
    }

    public static function newFileName(string $type): string
    {
        return 'gdr_' . $type . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
    }

    public function resolveEmployeeId(array $userInfo): int
    {
        if (!empty($userInfo['employee_id'])) {
            return (int) $userInfo['employee_id'];
        }

        if (!empty($userInfo['user_id'])) {
            $stmt = $this->conn->prepare("SELECT employee_id FROM em_employees WHERE user_id = :uid LIMIT 1");
            $stmt->execute(['uid' => (int) $userInfo['user_id']]);
            return (int) $stmt->fetchColumn();
        }

        return 0;
    }

    public function employeeName(int $employeeId): string
    {
        $stmt = $this->conn->prepare("SELECT CONCAT(first_name, ' ', last_name) FROM em_employees WHERE employee_id = :id");
        $stmt->execute(['id' => $employeeId]);

        return (string) ($stmt->fetchColumn() ?: 'Unknown');
    }

    /* ------------------------------------------------------------------
       Report building
    ------------------------------------------------------------------ */

    public function build(string $type, array $period, bool $withDetail = true): array
    {
        $spec     = $this->spec($type);
        $sections = [];
        $charts   = [];

        foreach ($spec['sections'] as $sec) {
            $rows = $this->sectionRows($spec, $sec, $period);

            if (!empty($sec['trend'])) {
                $rows = $this->fillMonths($rows, $period);
            }

            $sections[] = [
                'title' => $sec['title'],
                'kind'  => $sec['kind'],
                'rows'  => $rows,
            ];

            if ($sec['kind']) {
                $charts[] = [
                    'title'  => $sec['title'],
                    'kind'   => $sec['kind'],
                    'labels' => array_column($rows, 'label'),
                    'values' => array_column($rows, 'count'),
                ];
            }
        }

        $result = [
            'type'     => $type,
            'title'    => self::TYPES[$type],
            'period'   => ['from' => $period['from'], 'to' => $period['to']],
            'total'    => (int) array_sum(array_column($sections[0]['rows'], 'count')),
            'sections' => $sections,
            'charts'   => $charts,
            'columns'  => $spec['columns'],
            'rows'     => [],
            'truncated' => false,
        ];

        if ($withDetail) {
            $result['rows']      = $this->detailRows($spec, $period);
            $result['truncated'] = $result['total'] > count($result['rows']);
        }

        return $result;
    }

    private function sectionRows(array $spec, array $sec, array $period): array
    {
        $from = $sec['from'] ?? $spec['count_from'];

        $stmt = $this->conn->prepare("
            SELECT COALESCE({$sec['label']}, 'Unspecified') AS label, COUNT(*) AS count
            FROM {$from}
            WHERE {$spec['date']} >= :from AND {$spec['date']} < :to
            GROUP BY {$sec['group']}
            ORDER BY {$sec['order']}
        ");
        $stmt->execute(['from' => $period['from_dt'], 'to' => $period['to_excl']]);

        return array_map(static function ($row) {
            return ['label' => (string) $row['label'], 'count' => (int) $row['count']];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function detailRows(array $spec, array $period): array
    {
        $stmt = $this->conn->prepare("
            SELECT {$spec['detail_select']}
            FROM {$spec['detail_from']}
            WHERE {$spec['date']} >= :from AND {$spec['date']} < :to
            ORDER BY {$spec['detail_order']}
            LIMIT " . self::DETAIL_LIMIT . "
        ");
        $stmt->execute(['from' => $period['from_dt'], 'to' => $period['to_excl']]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Fills months with no records so the trend chart has a continuous axis. */
    private function fillMonths(array $rows, array $period): array
    {
        $map = [];
        foreach ($rows as $row) {
            $map[$row['label']] = (int) $row['count'];
        }

        $out = [];
        $cur = new DateTime(substr($period['from'], 0, 7) . '-01');
        $end = new DateTime(substr($period['to'], 0, 7) . '-01');

        while ($cur <= $end) {
            $out[] = ['label' => $cur->format('M Y'), 'count' => $map[$cur->format('Y-m')] ?? 0];
            $cur->modify('+1 month');
        }

        return $out;
    }

    private function nameExpr(string $fallbackColumn): string
    {
        return "COALESCE(CONCAT(a.surname, ', ', a.first_name), {$fallbackColumn})";
    }

    private function spec(string $type): array
    {
        $monthly = static function (string $col, string $title): array {
            $expr = "DATE_FORMAT({$col}, '%Y-%m')";
            return ['title' => $title, 'label' => $expr, 'group' => $expr, 'order' => $expr, 'kind' => 'line', 'trend' => true];
        };

        switch ($type) {
            case 'case_summary':
                return [
                    'date'       => 'c.opened_at',
                    'count_from' => 'gd_cases c',
                    'sections'   => [
                        ['title' => 'Cases by Status', 'label' => 'c.status', 'group' => 'c.status',
                         'order' => "FIELD(c.status, 'Open', 'In Progress', 'Closed')", 'kind' => 'bar'],
                        ['title' => 'Cases by Priority', 'label' => 'c.priority', 'group' => 'c.priority',
                         'order' => "FIELD(c.priority, 'Low', 'Medium', 'High', 'Critical')", 'kind' => 'bar'],
                        $monthly('c.opened_at', 'Cases Opened per Month'),
                        ['title' => 'Cases by Type', 'label' => 'c.case_type', 'group' => 'c.case_type',
                         'order' => 'COUNT(*) DESC', 'kind' => null],
                    ],
                    'detail_select' => "c.case_number, " . $this->nameExpr('c.student_number') . " AS student_name,
                        c.case_type, c.priority, c.status, c.opened_at, c.closed_at,
                        CONCAT(e.first_name, ' ', e.last_name) AS counselor_name",
                    'detail_from' => "gd_cases c
                        JOIN enr_students s ON s.student_number = c.student_number
                        LEFT JOIN enr_applicants a ON a.applicant_id = s.applicant_id
                        LEFT JOIN em_employees e ON e.employee_id = c.counselor_id",
                    'detail_order' => 'c.opened_at DESC',
                    'columns' => [
                        ['key' => 'case_number', 'label' => 'Case #'],
                        ['key' => 'student_name', 'label' => 'Student'],
                        ['key' => 'case_type', 'label' => 'Type'],
                        ['key' => 'priority', 'label' => 'Priority'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'opened_at', 'label' => 'Opened'],
                        ['key' => 'closed_at', 'label' => 'Closed'],
                        ['key' => 'counselor_name', 'label' => 'Counselor'],
                    ],
                ];

            case 'incident':
                return [
                    'date'       => 'i.incident_date',
                    'count_from' => 'gd_incidents i',
                    'sections'   => [
                        ['title' => 'Incidents by Severity', 'label' => 'i.severity', 'group' => 'i.severity',
                         'order' => "FIELD(i.severity, 'Minor', 'Moderate', 'Major', 'Critical')", 'kind' => 'bar'],
                        ['title' => 'Incidents by Status', 'label' => 'i.status', 'group' => 'i.status',
                         'order' => "FIELD(i.status, 'Reported', 'Investigating', 'Resolved', 'Closed')", 'kind' => 'bar'],
                        $monthly('i.incident_date', 'Incidents per Month'),
                        ['title' => 'Incidents by Type', 'label' => 'i.incident_type', 'group' => 'i.incident_type',
                         'order' => 'COUNT(*) DESC', 'kind' => null],
                    ],
                    // description / action_taken are intentionally excluded from PDFs.
                    'detail_select' => "i.incident_date, " . $this->nameExpr('i.student_number') . " AS student_name,
                        i.incident_type, i.severity, i.status, i.location,
                        CONCAT(e.first_name, ' ', e.last_name) AS reported_by_name",
                    'detail_from' => "gd_incidents i
                        JOIN enr_students s ON s.student_number = i.student_number
                        LEFT JOIN enr_applicants a ON a.applicant_id = s.applicant_id
                        LEFT JOIN em_employees e ON e.employee_id = i.reported_by",
                    'detail_order' => 'i.incident_date DESC',
                    'columns' => [
                        ['key' => 'incident_date', 'label' => 'Date'],
                        ['key' => 'student_name', 'label' => 'Student'],
                        ['key' => 'incident_type', 'label' => 'Type'],
                        ['key' => 'severity', 'label' => 'Severity'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'location', 'label' => 'Location'],
                        ['key' => 'reported_by_name', 'label' => 'Reported By'],
                    ],
                ];

            case 'appointment':
                return [
                    'date'       => 'ap.appointment_date',
                    'count_from' => 'gd_appointments ap',
                    'sections'   => [
                        ['title' => 'Appointments by Status', 'label' => 'ap.status', 'group' => 'ap.status',
                         'order' => "FIELD(ap.status, 'Pending', 'Approved', 'Completed', 'Cancelled', 'No Show')", 'kind' => 'bar'],
                        ['title' => 'Appointments by Meeting Type', 'label' => 'ap.meeting_type', 'group' => 'ap.meeting_type',
                         'order' => 'COUNT(*) DESC', 'kind' => 'bar'],
                        $monthly('ap.appointment_date', 'Appointments per Month'),
                        ['title' => 'Appointments by Counselor',
                         'from' => 'gd_appointments ap JOIN em_employees e ON e.employee_id = ap.counselor_id',
                         'label' => "CONCAT(e.first_name, ' ', e.last_name)",
                         'group' => 'e.employee_id, e.first_name, e.last_name',
                         'order' => 'COUNT(*) DESC', 'kind' => null],
                    ],
                    // remarks are intentionally excluded from PDFs.
                    'detail_select' => "ap.appointment_date, " . $this->nameExpr('ap.student_number') . " AS student_name,
                        CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                        ap.meeting_type, ap.status, ap.purpose",
                    'detail_from' => "gd_appointments ap
                        JOIN enr_students s ON s.student_number = ap.student_number
                        LEFT JOIN enr_applicants a ON a.applicant_id = s.applicant_id
                        LEFT JOIN em_employees e ON e.employee_id = ap.counselor_id",
                    'detail_order' => 'ap.appointment_date DESC',
                    'columns' => [
                        ['key' => 'appointment_date', 'label' => 'Date'],
                        ['key' => 'student_name', 'label' => 'Student'],
                        ['key' => 'counselor_name', 'label' => 'Counselor'],
                        ['key' => 'meeting_type', 'label' => 'Meeting'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'purpose', 'label' => 'Purpose'],
                    ],
                ];

            case 'scholarship':
                $typeJoin = 'gd_scholarships sc JOIN gd_scholarship_types t ON t.scholarship_type_id = sc.scholarship_type_id';
                return [
                    'date'       => 'sc.applied_at',
                    'count_from' => 'gd_scholarships sc',
                    'sections'   => [
                        ['title' => 'Applications by Status', 'label' => 'sc.status', 'group' => 'sc.status',
                         'order' => "FIELD(sc.status, 'Applied', 'Under Review', 'Approved', 'Rejected', 'Active', 'Completed', 'Terminated')", 'kind' => 'bar'],
                        ['title' => 'Applications by Scholarship Type', 'from' => $typeJoin,
                         'label' => 't.type_name', 'group' => 't.scholarship_type_id, t.type_name',
                         'order' => 'COUNT(*) DESC', 'kind' => 'bar'],
                        $monthly('sc.applied_at', 'Applications per Month'),
                        ['title' => 'Applications by Eligibility Basis', 'from' => $typeJoin,
                         'label' => 't.eligibility_basis', 'group' => 't.eligibility_basis',
                         'order' => 'COUNT(*) DESC', 'kind' => null],
                    ],
                    // justification / review_notes are intentionally excluded from PDFs.
                    'detail_select' => "sc.application_number, " . $this->nameExpr('sc.student_number') . " AS student_name,
                        t.type_name, t.coverage_type, sc.status, sc.applied_at, sc.reviewed_at,
                        CONCAT(e.first_name, ' ', e.last_name) AS counselor_name",
                    'detail_from' => "gd_scholarships sc
                        JOIN gd_scholarship_types t ON t.scholarship_type_id = sc.scholarship_type_id
                        JOIN enr_students s ON s.student_number = sc.student_number
                        LEFT JOIN enr_applicants a ON a.applicant_id = s.applicant_id
                        LEFT JOIN em_employees e ON e.employee_id = sc.counselor_id",
                    'detail_order' => 'sc.applied_at DESC',
                    'columns' => [
                        ['key' => 'application_number', 'label' => 'Application #'],
                        ['key' => 'student_name', 'label' => 'Student'],
                        ['key' => 'type_name', 'label' => 'Scholarship'],
                        ['key' => 'coverage_type', 'label' => 'Coverage'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'applied_at', 'label' => 'Applied'],
                        ['key' => 'reviewed_at', 'label' => 'Reviewed'],
                        ['key' => 'counselor_name', 'label' => 'Counselor'],
                    ],
                ];
        }

        throw new InvalidArgumentException('Unknown report type.');
    }

    /* ------------------------------------------------------------------
       Saved report records
    ------------------------------------------------------------------ */

    public function saveRecord(string $type, array $period, int $recordCount, string $fileName, int $employeeId): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO gd_reports (report_type, period_from, period_to, record_count, file_name, generated_by)
            VALUES (:type, :from, :to, :count, :file, :emp)
        ");
        $stmt->execute([
            'type'  => $type,
            'from'  => $period['from'],
            'to'    => $period['to'],
            'count' => $recordCount,
            'file'  => $fileName,
            'emp'   => $employeeId,
        ]);

        return (int) $this->conn->lastInsertId();
    }

    public function listReports(int $viewerEmployeeId, int $limit = 100): array
    {
        $stmt = $this->conn->prepare("
            SELECT r.report_id, r.report_type, r.period_from, r.period_to, r.record_count,
                   r.generated_by, r.generated_at,
                   CONCAT(e.first_name, ' ', e.last_name) AS generated_by_name
            FROM gd_reports r
            LEFT JOIN em_employees e ON e.employee_id = r.generated_by
            ORDER BY r.generated_at DESC, r.report_id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['type_label'] = self::TYPES[$row['report_type']] ?? $row['report_type'];
            $row['can_delete'] = $viewerEmployeeId > 0 && (int) $row['generated_by'] === $viewerEmployeeId;
        }
        unset($row);

        return $rows;
    }

    public function getReport(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM gd_reports WHERE report_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function deleteRecord(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM gd_reports WHERE report_id = :id");

        return $stmt->execute(['id' => $id]);
    }
}