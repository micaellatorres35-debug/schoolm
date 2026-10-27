<?php

include_once __DIR__ . "/../../../database/db.php";

/**
 * Read-only data layer for the School Directress dashboard overview.
 * Uses only existing tables — nothing here writes to the database.
 */
class DirectressDashboard
{
    private $conn;

    /**
     * Start of the period used for "reports submitted" in the Department Status table.
     * Currently the first day of this month. Change this one line to switch periods.
     */
    private $reportPeriodStart = "DATE_FORMAT(CURDATE(), '%Y-%m-01')";

    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database   = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /* ── Small query helpers (a failing panel never breaks the page) ── */

    private function scalar($sql)
    {
        try {
            $stmt = $this->conn->query($sql);
            return $stmt ? (int) $stmt->fetchColumn() : 0;
        } catch (Throwable $e) {
            error_log('[DirectressDashboard] ' . $e->getMessage());
            return 0;
        }
    }

    private function rows($sql)
    {
        try {
            $stmt = $this->conn->query($sql);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            error_log('[DirectressDashboard] ' . $e->getMessage());
            return [];
        }
    }

    /* ── KPI trend lines ─────────────────────────────── */

    public function getKpiTrends($clinicToday)
    {
        $monthStart = "DATE_FORMAT(CURDATE(), '%Y-%m-01')";

        $studentsMonth = $this->scalar(
            "SELECT COUNT(*) FROM enr_students
             WHERE enrollment_status = 'enrolled' AND enrolled_at >= $monthStart"
        );

        $employeesMonth = $this->scalar(
            "SELECT COUNT(*) FROM em_employees
             WHERE employment_status = 'Active'
               AND COALESCE(hire_date, DATE(created_at)) >= $monthStart"
        );

        $clinicYesterday = $this->scalar(
            "SELECT COUNT(*) FROM cln_clinic_visits
             WHERE DATE(visit_date) = CURDATE() - INTERVAL 1 DAY"
        );

        return [
            'students_month'  => $studentsMonth,
            'employees_month' => $employeesMonth,
            'clinic_diff'     => (int) $clinicToday - $clinicYesterday,
        ];
    }

    /* ── Pending Actions ─────────────────────────────── */

    public function getUpcomingEventCount()
    {
        return $this->scalar(
            "SELECT COUNT(*) FROM cc_events
             WHERE status IN ('upcoming','ongoing')
               AND event_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 30 DAY"
        );
    }

    /* ── Recent Activity (built from existing timestamps) ── */

    public function getRecentActivity($limit = 5)
    {
        $limit = max(1, min((int) $limit, 20));

        return $this->rows("
            SELECT t.*, TIMESTAMPDIFF(MINUTE, t.happened_at, NOW()) AS mins_ago
            FROM (
                SELECT 'report' AS type, r.title AS title, d.department_name AS department,
                       CAST(r.status AS CHAR) AS meta, r.submitted_at AS happened_at
                FROM sd_reports r
                LEFT JOIN em_departments d ON d.department_id = r.department_id
                WHERE r.submitted_at IS NOT NULL

                UNION ALL

                SELECT 'approval', a.title, d.department_name,
                       CAST(a.status AS CHAR), COALESCE(a.approved_at, a.reviewed_at)
                FROM sd_approvals a
                LEFT JOIN em_departments d ON d.department_id = a.department
                WHERE a.status IN ('approved','rejected')
                  AND COALESCE(a.approved_at, a.reviewed_at) IS NOT NULL

                UNION ALL

                SELECT 'issue', i.title, d.department_name,
                       CAST(i.status AS CHAR), i.submitted_on
                FROM sd_issues i
                LEFT JOIN em_departments d ON d.department_id = i.department
                WHERE i.submitted_on IS NOT NULL

                UNION ALL

                SELECT 'announcement', n.title, NULL, '', n.publish_date
                FROM sd_announcements n
                WHERE n.publish_date IS NOT NULL AND n.publish_date <= NOW()

                UNION ALL

                SELECT 'event', e.event_title, NULL, '', e.created_at
                FROM cc_events e
            ) t
            ORDER BY t.happened_at DESC
            LIMIT $limit
        ");
    }

    /* ── Department Status ───────────────────────────── */

    public function getDepartmentStatus()
    {
        $rows = $this->rows("
            SELECT d.department_id, d.department_name,
                (SELECT COUNT(*) FROM sd_report_type t
                  WHERE t.department_id = d.department_id) AS required_reports,
                (SELECT COUNT(DISTINCT r.report_type) FROM sd_reports r
                  WHERE r.department_id = d.department_id
                    AND r.status IN ('submitted','reviewed','approved')
                    AND r.submitted_at >= {$this->reportPeriodStart}) AS submitted_reports,
                (SELECT COUNT(*) FROM sd_issues i
                  WHERE i.department = d.department_id AND i.status = 'submitted') AS concerns,
                (SELECT COUNT(*) FROM sd_approvals a
                  WHERE a.department = d.department_id AND a.status = 'submitted') AS approvals
            FROM em_departments d
            WHERE d.status = 'Active'
              AND EXISTS (SELECT 1 FROM sd_report_type x WHERE x.department_id = d.department_id)
            ORDER BY d.department_name ASC
        ");

        foreach ($rows as &$row) {
            $row['required_reports']  = (int) $row['required_reports'];
            $row['submitted_reports'] = min((int) $row['submitted_reports'], $row['required_reports']);
            $row['concerns']          = (int) $row['concerns'];
            $row['approvals']         = (int) $row['approvals'];
        }
        unset($row);

        return $rows;
    }

    /* ── Enrollment Trend (new enrollments per month) ── */

    public function getEnrollmentTrend()
    {
        $rows = $this->rows("
            SELECT DATE_FORMAT(enrolled_at, '%Y-%m') AS ym, COUNT(*) AS total
            FROM enr_students
            WHERE enrolled_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-01-01'), INTERVAL 6 MONTH)
            GROUP BY ym
        ");

        $map = [];
        foreach ($rows as $row) {
            $map[$row['ym']] = (int) $row['total'];
        }

        // Last 6 months, oldest first
        $six = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts    = mktime(0, 0, 0, (int) date('n') - $i, 1, (int) date('Y'));
            $six[] = [
                'label' => date('M', $ts),
                'value' => isset($map[date('Y-m', $ts)]) ? $map[date('Y-m', $ts)] : 0,
            ];
        }

        // January up to the current month
        $year = [];
        for ($m = 1; $m <= (int) date('n'); $m++) {
            $key    = date('Y') . '-' . str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $year[] = [
                'label' => date('M', mktime(0, 0, 0, $m, 1)),
                'value' => isset($map[$key]) ? $map[$key] : 0,
            ];
        }

        return ['six' => $six, 'year' => $year];
    }

    /* ── Student Population by Course (top N + Others) ── */

    public function getCoursePopulation($top = 5)
    {
        $rows = $this->rows("
            SELECT c.code AS label, COUNT(*) AS total
            FROM enr_students s
            JOIN rgr_courses c ON c.id = s.course_id
            WHERE s.enrollment_status = 'enrolled'
            GROUP BY c.id, c.code
            ORDER BY total DESC, c.code ASC
        ");

        $grand = 0;
        foreach ($rows as $row) {
            $grand += (int) $row['total'];
        }

        $slices = [];
        $others = 0;
        foreach ($rows as $i => $row) {
            if ($i < $top) {
                $slices[] = [
                    'label' => $row['label'],
                    'count' => (int) $row['total'],
                    'pct'   => $grand > 0 ? ((int) $row['total'] / $grand) * 100 : 0,
                    'tone'  => $i + 1,
                ];
            } else {
                $others += (int) $row['total'];
            }
        }
        if ($others > 0) {
            $slices[] = [
                'label' => 'Others',
                'count' => $others,
                'pct'   => $grand > 0 ? ($others / $grand) * 100 : 0,
                'tone'  => 6,
            ];
        }

        return ['total' => $grand, 'slices' => $slices];
    }

    /* ── Announcements & Events ──────────────────────── */

    public function getAnnouncements($limit = 4)
    {
        $limit = max(1, min((int) $limit, 20));

        return $this->rows("
            SELECT announcement_id, title, publish_date,
                   TIMESTAMPDIFF(DAY, publish_date, NOW()) AS days_ago
            FROM sd_announcements
            WHERE publish_date IS NOT NULL AND publish_date <= NOW()
            ORDER BY publish_date DESC
            LIMIT $limit
        ");
    }

    public function getUpcomingEvents($limit = 4)
    {
        $limit = max(1, min((int) $limit, 20));

        return $this->rows("
            SELECT event_id, event_title, event_type, event_date, start_time
            FROM cc_events
            WHERE status IN ('upcoming','ongoing') AND event_date >= CURDATE()
            ORDER BY event_date ASC, start_time ASC
            LIMIT $limit
        ");
    }

    /* ── Display helpers ─────────────────────────────── */

    public static function timeAgo($minutes)
    {
        $minutes = max(0, (int) $minutes);

        if ($minutes < 1)    return 'Just now';
        if ($minutes < 60)   return $minutes . 'm ago';
        if ($minutes < 1440) return floor($minutes / 60) . 'h ago';

        $days = floor($minutes / 1440);
        if ($days < 30)      return $days . 'd ago';

        return floor($days / 30) . 'mo ago';
    }

    /** Returns [headline, icon name, tone] for an activity row. */
    public static function describeActivity($row)
    {
        $meta = isset($row['meta']) ? $row['meta'] : '';

        switch ($row['type']) {
            case 'report':
                return ['A report was submitted', 'file', 'info'];
            case 'approval':
                return $meta === 'rejected'
                    ? ['Approval declined', 'x', 'alert']
                    : ['Approval completed', 'check', 'ok'];
            case 'issue':
                return ['New concern filed', 'alert', 'alert'];
            case 'announcement':
                return ['Announcement published', 'megaphone', 'info'];
            default:
                return ['Event scheduled', 'calendar', 'info'];
        }
    }
}