<?php
require_once __DIR__ . '/DatabaseHelper.php';

class DashboardOverview {
    private $conn;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
            return;
        }

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getSummary(): array {
        $summary = [
            'total_departments' => 0,
            'active_coordinators' => 0,
            'pending_reports' => 0,
            'pending_approvals' => 0,
            'overdue_tasks' => 0,
            'open_concerns' => 0,
        ];

        try {
            $summary['total_departments'] = (int) $this->conn->query("SELECT COUNT(*) FROM cc_roles WHERE LOWER(TRIM(role_name)) LIKE '%coordinator%' AND LOWER(TRIM(role_name)) NOT LIKE '%college coordinator%'")->fetchColumn();
        } catch (Throwable $e) {}

        try {
            $summary['active_coordinators'] = (int) $this->conn->query("SELECT COUNT(*) FROM cc_roles WHERE LOWER(TRIM(role_name)) LIKE '%coordinator%' AND LOWER(TRIM(role_name)) NOT LIKE '%college coordinator%' AND LOWER(TRIM(status)) = 'active'")->fetchColumn();
        } catch (Throwable $e) {}

        try {
            $summary['pending_reports'] = (int) $this->conn->query("SELECT COUNT(*) FROM cc_reports WHERE status IN ('submitted', 'reviewed')")->fetchColumn();
        } catch (Throwable $e) {}

        try {
            $summary['pending_approvals'] = (int) $this->conn->query("SELECT COUNT(*) FROM cc_approvals WHERE status IN ('submitted', 'reviewed')")->fetchColumn();
        } catch (Throwable $e) {}

        try {
            $overdueReports = (int) $this->conn->query("SELECT COUNT(*) FROM cc_reports WHERE status IN ('submitted', 'reviewed') AND submitted_at IS NOT NULL AND DATEDIFF(CURDATE(), submitted_at) >= 7")->fetchColumn();
            $overdueApprovals = (int) $this->conn->query("SELECT COUNT(*) FROM cc_approvals WHERE status IN ('submitted', 'reviewed') AND submitted_on IS NOT NULL AND DATEDIFF(CURDATE(), submitted_on) >= 7")->fetchColumn();
            $overdueConcerns = (int) $this->conn->query("SELECT COUNT(*) FROM cc_issues WHERE status IN ('submitted', 'reviewed') AND submitted_on IS NOT NULL AND DATEDIFF(CURDATE(), submitted_on) >= 7")->fetchColumn();
            $summary['overdue_tasks'] = $overdueReports + $overdueApprovals + $overdueConcerns;
        } catch (Throwable $e) {}

        try {
            $summary['open_concerns'] = (int) $this->conn->query("SELECT COUNT(*) FROM cc_issues WHERE status IN ('submitted', 'reviewed')")->fetchColumn();
        } catch (Throwable $e) {}

        return $summary;
    }

    public function getDepartmentOverview(): array {
        try {
            $query = "
                SELECT
                    r.role_name AS coordinator_role_name,
                    COALESCE(rc.report_count, 0) AS report_count,
                    COALESCE(cc.concern_count, 0) AS concern_count,
                    COALESCE(ac.approval_count, 0) AS approval_count,
                    (
                        COALESCE(rc.report_count, 0)
                        + COALESCE(cc.concern_count, 0)
                        + COALESCE(ac.approval_count, 0)
                    ) AS pending_tasks,
                    'On Track' AS status
                FROM cc_roles r
                LEFT JOIN (
                    SELECT e.role_id, COUNT(*) AS report_count
                    FROM cc_reports rep
                    INNER JOIN em_employees e ON e.employee_id = rep.submitted_by
                    WHERE rep.status IN ('submitted', 'reviewed')
                    GROUP BY e.role_id
                ) rc ON rc.role_id = r.role_id
                LEFT JOIN (
                    SELECT e.role_id, COUNT(*) AS concern_count
                    FROM cc_issues i
                    INNER JOIN em_employees e ON e.employee_id = i.submitted_by
                    WHERE i.status IN ('submitted', 'reviewed')
                    GROUP BY e.role_id
                ) cc ON cc.role_id = r.role_id
                LEFT JOIN (
                    SELECT e.role_id, COUNT(*) AS approval_count
                    FROM cc_approvals a
                    INNER JOIN em_employees e ON e.employee_id = a.submit_by
                    WHERE a.status IN ('submitted', 'reviewed')
                    GROUP BY e.role_id
                ) ac ON ac.role_id = r.role_id
                WHERE LOWER(TRIM(r.role_name)) LIKE '%coordinator%'
                  AND LOWER(TRIM(r.role_name)) NOT LIKE '%admin college coordinator%'
                ORDER BY CASE r.role_name
                    WHEN 'College Coordinator' THEN 1
                    WHEN 'Assistance Staff Coordinator' THEN 2
                    WHEN 'Community Extension Coordinator' THEN 3
                    WHEN 'Community Coordinator' THEN 4
                    WHEN 'Research Coordinator' THEN 5
                    WHEN 'GenEd Coordinator' THEN 6
                    ELSE 99
                END, r.role_name ASC";

            $result = $this->conn->query($query);
            $rows = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];

            foreach ($rows as &$row) {
                $row['status'] = 'On Track';
                $pendingTasks = (int) ($row['pending_tasks'] ?? 0);

                if ($pendingTasks >= 6) {
                    $row['status'] = 'Needs Attention';
                } elseif ($pendingTasks >= 2 && $pendingTasks < 6) {
                    $row['status'] = 'Needs Attention';
                } elseif ($pendingTasks >= 1 && $pendingTasks <= 2) {
                    $row['status'] = 'On Track';
                }

                if ($pendingTasks >= 8) {
                    $row['status'] = 'Overdue';
                }
            }
            unset($row);

            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getPendingSubmissions(): array {
        try {
            $query = "
                SELECT 'report' AS source, r.title AS submission_title, d.department_name AS department_name, r.submitted_at AS submitted_at, r.status AS status,
                       '?page=report-submission' AS action_target
                FROM cc_reports r
                LEFT JOIN em_departments d ON d.department_id = r.department_id
                WHERE r.status IN ('submitted', 'reviewed')

                UNION ALL

                SELECT 'concern' AS source, i.title AS submission_title, d.department_name AS department_name, i.submitted_on AS submitted_at, i.status AS status,
                       '?page=concerns-issues-tracking' AS action_target
                FROM cc_issues i
                LEFT JOIN em_departments d ON d.department_id = i.department
                WHERE i.status IN ('submitted', 'reviewed')

                UNION ALL

                SELECT 'approval' AS source, a.title AS submission_title, d.department_name AS department_name, a.submitted_on AS submitted_at, a.status AS status,
                       '?page=approval-submission' AS action_target
                FROM cc_approvals a
                LEFT JOIN em_employees e ON e.employee_id = a.submit_by
                LEFT JOIN em_departments d ON d.department_id = e.department_id
                WHERE a.status IN ('submitted', 'reviewed')
                ORDER BY submitted_at DESC
                LIMIT 8";

            $result = $this->conn->query($query);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getUpcomingActivities(): array {
        try {
            $query = "
                SELECT event_title, COALESCE(target_audience, 'College Wide') AS department_name, event_date, start_time, end_time, status
                FROM cc_events
                WHERE event_date >= CURDATE() AND status <> 'Completed'
                ORDER BY event_date ASC, start_time ASC
                LIMIT 6";

            $result = $this->conn->query($query);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getAlertItems(): array {
        $alertItems = [];

        try {
            $reportAlerts = $this->conn->query("SELECT r.title, r.submitted_at, d.department_name FROM cc_reports r LEFT JOIN em_departments d ON d.department_id = r.department_id WHERE r.status IN ('submitted', 'reviewed') AND r.submitted_at IS NOT NULL AND DATEDIFF(CURDATE(), r.submitted_at) >= 7 LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($reportAlerts as $alert) {
                $alertItems[] = [
                    'type' => 'Overdue report submission',
                    'message' => ($alert['title'] ?? 'Report') . ' • ' . ($alert['department_name'] ?? 'Department'),
                    'when' => $alert['submitted_at'] ?? null,
                    'href' => '?page=report-submission',
                    'level' => 'warning',
                ];
            }
        } catch (Throwable $e) {}

        try {
            $issueAlerts = $this->conn->query("SELECT i.title, i.submitted_on, d.department_name FROM cc_issues i LEFT JOIN em_departments d ON d.department_id = i.department WHERE i.status IN ('submitted', 'reviewed') AND i.submitted_on IS NOT NULL AND DATEDIFF(CURDATE(), i.submitted_on) >= 7 LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($issueAlerts as $alert) {
                $alertItems[] = [
                    'type' => 'Urgent department concern',
                    'message' => ($alert['title'] ?? 'Concern') . ' • ' . ($alert['department_name'] ?? 'Department'),
                    'when' => $alert['submitted_on'] ?? null,
                    'href' => '?page=concern-submission',
                    'level' => 'danger',
                ];
            }
        } catch (Throwable $e) {}

        try {
            $approvalAlerts = $this->conn->query("SELECT a.title, a.submitted_on, d.department_name FROM cc_approvals a LEFT JOIN em_employees e ON e.employee_id = a.submit_by LEFT JOIN em_departments d ON d.department_id = e.department_id WHERE a.status IN ('submitted', 'reviewed') LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($approvalAlerts as $alert) {
                $alertItems[] = [
                    'type' => 'Pending approval',
                    'message' => ($alert['title'] ?? 'Approval request') . ' • ' . ($alert['department_name'] ?? 'Department'),
                    'when' => $alert['submitted_on'] ?? null,
                    'href' => '?page=approval-submission',
                    'level' => 'info',
                ];
            }
        } catch (Throwable $e) {}

        try {
            $deadlineAlerts = $this->conn->query("SELECT event_title, event_date, start_time FROM cc_events WHERE event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status <> 'Completed' ORDER BY event_date ASC, start_time ASC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($deadlineAlerts as $alert) {
                $alertItems[] = [
                    'type' => 'Upcoming deadline',
                    'message' => ($alert['event_title'] ?? 'Event') . ' • ' . ($alert['event_date'] ?? '') . ' ' . ($alert['start_time'] ?? ''),
                    'when' => null,
                    'href' => '?page=events-management',
                    'level' => 'info',
                ];
            }
        } catch (Throwable $e) {}

        try {
            $conflictEvents = $this->conn->query("SELECT event_date, start_time, COUNT(*) AS event_count FROM cc_events GROUP BY event_date, start_time HAVING COUNT(*) > 1 LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($conflictEvents as $event) {
                $alertItems[] = [
                    'type' => 'Conflicting event schedules',
                    'message' => 'Multiple events on ' . ($event['event_date'] ?? '') . ' at ' . ($event['start_time'] ?? '') . '.',
                    'when' => null,
                    'href' => '?page=events-management',
                    'level' => 'danger',
                ];
            }
        } catch (Throwable $e) {}

        return $alertItems;
    }

    public function getRecentActivities($limit = 8): array {
        $helper = new DatabaseHelper();
        return $helper->getRecentActivities($limit);
    }
}
