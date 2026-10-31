<?php
    include_once __DIR__ . '/../../../database/db.php';
    include_once __DIR__ . '/FileStorage.php';

    class Report {
        const STATUSES      = ['draft', 'submitted', 'reviewed', 'approved', 'rejected'];
        const TREND_DEFAULT = 6;    // months shown when no date range is chosen
        const TREND_MAX     = 36;   // longest range the trend chart will plot

        private $conn;

        public function __construct($pdo = null) {
            if ($pdo instanceof PDO) {
                $this->conn = $pdo;
            } else {
                $database = new Database();
                $this->conn = $database->getConnection();
            }
        }

        // ── LIST / READ ──────────────────────────────────────────────

        // Original list query, kept for any caller that still uses it.
        public function getReports($department_id = null, $status = null) {
            $sql = "SELECT
                        r.report_id, r.title, r.summary, r.findings, r.recommendations,
                        r.status, r.pdf_path, r.ai_summary,
                        r.attachment_path, r.attachment_name,
                        r.submitted_at, r.reviewed_at, r.decided_at,
                        d.department_name,
                        CONCAT(e.first_name, ' ', e.last_name) AS submitted_by,
                        CONCAT(rv.first_name, ' ', rv.last_name) AS reviewed_by_name,
                        CONCAT(dc.first_name, ' ', dc.last_name) AS decided_by_name,
                        rt.report_type AS report_type
                    FROM `cc_reports` r
                    LEFT JOIN `em_departments` d ON r.department_id = d.department_id
                    LEFT JOIN `em_employees` e  ON r.submitted_by = e.employee_id
                    LEFT JOIN `em_employees` rv ON r.reviewed_by = rv.employee_id
                    LEFT JOIN `em_employees` dc ON r.decided_by = dc.employee_id
                    LEFT JOIN `cc_report_type` rt ON r.report_type = rt.type_id
                    WHERE 1=1";

            $params = [];
            if (!is_null($department_id)) {
                $sql .= " AND r.department_id = :department_id";
                $params[':department_id'] = $department_id;
            }
            if (!is_null($status)) {
                $sql .= " AND r.status = :status";
                $params[':status'] = $status;
            }
            $sql .= " ORDER BY r.submitted_at DESC, r.created_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getReportById($report_id) {
            $sql = "SELECT
                        r.*,
                        d.department_name,
                        CONCAT(e.first_name, ' ', e.last_name)  AS submitted_by_name,
                        CONCAT(rv.first_name, ' ', rv.last_name) AS reviewed_by_name,
                        CONCAT(dc.first_name, ' ', dc.last_name) AS decided_by_name,
                        rt.report_type AS report_type
                    FROM `cc_reports` r
                    LEFT JOIN `em_departments` d ON r.department_id = d.department_id
                    LEFT JOIN `em_employees` e  ON r.submitted_by = e.employee_id
                    LEFT JOIN `em_employees` rv ON r.reviewed_by = rv.employee_id
                    LEFT JOIN `em_employees` dc ON r.decided_by = dc.employee_id
                    LEFT JOIN `cc_report_type` rt ON r.report_type = rt.type_id
                    WHERE r.report_id = :report_id
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':report_id' => $report_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        }

        public function getReportTypesByDepartment($department_id) {
            if ($department_id === null) {
                $sql = "SELECT type_id, report_type FROM `cc_report_type` ORDER BY report_type ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
            } else {
                $sql = "SELECT type_id, report_type FROM `cc_report_type` WHERE department_id = :department_id ORDER BY report_type ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':department_id' => $department_id]);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Distinct type NAMES (several departments can have a type with the same name),
        // optionally limited to one department. Used by the type filter.
        public function getReportTypeNames($department_id = null) {
            if ($department_id === null) {
                $stmt = $this->conn->prepare(
                    "SELECT DISTINCT report_type FROM `cc_report_type`
                     WHERE report_type IS NOT NULL AND report_type <> '' ORDER BY report_type ASC"
                );
                $stmt->execute();
            } else {
                $stmt = $this->conn->prepare(
                    "SELECT DISTINCT report_type FROM `cc_report_type`
                     WHERE department_id = :d AND report_type IS NOT NULL AND report_type <> '' ORDER BY report_type ASC"
                );
                $stmt->execute([':d' => (int) $department_id]);
            }
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'report_type');
        }

        public function reportTypeExists($type_id) {
            $stmt = $this->conn->prepare("SELECT 1 FROM `cc_report_type` WHERE type_id = :id");
            $stmt->execute([':id' => (int) $type_id]);
            return (bool) $stmt->fetchColumn();
        }

        /* ── Filtering shared by the list and the analytics ───────────
           $f keys (all optional, already validated by the controller):
             department_id (int, -1 = "matches nothing"), type (name), status,
             date_from / date_to (Y-m-d, applied to submitted_at), search.
           Every query using this must alias: cc_reports r, em_employees e (submitter). */
        private function buildFilter(array $f, $includeStatus = true) {
            $where  = ['1=1'];
            $params = [];

            if (isset($f['department_id']) && $f['department_id'] !== null && $f['department_id'] !== '') {
                $where[] = 'r.department_id = :dept';
                $params[':dept'] = (int) $f['department_id'];
            }

            $type = trim((string) ($f['type'] ?? ''));
            if ($type !== '') {
                $where[] = 'r.report_type IN (SELECT t.type_id FROM `cc_report_type` t WHERE t.report_type = :tname)';
                $params[':tname'] = $type;
            }

            $status = (string) ($f['status'] ?? '');
            if ($includeStatus && in_array($status, self::STATUSES, true)) {
                $where[] = 'r.status = :status';
                $params[':status'] = $status;
            }

            if (!empty($f['date_from'])) {
                $where[] = 'r.submitted_at >= :df';
                $params[':df'] = $f['date_from'] . ' 00:00:00';
            }
            if (!empty($f['date_to'])) {
                $next = (new DateTime($f['date_to']))->modify('+1 day')->format('Y-m-d');
                $where[] = 'r.submitted_at < :dt';
                $params[':dt'] = $next . ' 00:00:00';
            }

            $search = trim((string) ($f['search'] ?? ''));
            if ($search !== '') {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $where[] = "(r.title LIKE :s1
                             OR CONCAT_WS(' ', e.first_name, e.last_name) LIKE :s2
                             OR r.report_id = :sid)";
                $params[':s1']  = $like;
                $params[':s2']  = $like;
                $params[':sid'] = ctype_digit($search) ? (int) $search : 0;
            }

            return [implode(' AND ', $where), $params];
        }

        private function fromJoins() {
            return " FROM `cc_reports` r
                     LEFT JOIN `em_departments` d ON r.department_id = d.department_id
                     LEFT JOIN `em_employees` e   ON r.submitted_by = e.employee_id ";
        }

        public function countReports(array $f) {
            list($where, $params) = $this->buildFilter($f);
            $stmt = $this->conn->prepare('SELECT COUNT(*)' . $this->fromJoins() . 'WHERE ' . $where);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        }

        public function listReports(array $f, $limit, $offset) {
            list($where, $params) = $this->buildFilter($f);

            $sql = "SELECT r.report_id, r.title, r.status, r.submitted_at, r.created_at,
                           d.department_name,
                           CONCAT(e.first_name, ' ', e.last_name) AS submitted_by,
                           rt.report_type AS report_type
                    " . $this->fromJoins() . "
                    LEFT JOIN `cc_report_type` rt ON r.report_type = rt.type_id
                    WHERE $where
                    ORDER BY r.submitted_at DESC, r.created_at DESC, r.report_id DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /* ── Analytics ────────────────────────────────────────────── */

        // Count per status. The status filter is ignored on purpose, so the
        // distribution shows every status inside the current scope.
        public function getStatusCounts(array $f) {
            list($where, $params) = $this->buildFilter($f, false);
            $stmt = $this->conn->prepare(
                'SELECT r.status, COUNT(*) AS c' . $this->fromJoins() . 'WHERE ' . $where . ' GROUP BY r.status'
            );
            $stmt->execute($params);

            $out = array_fill_keys(self::STATUSES, 0);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $out[$row['status']] = (int) $row['c'];
            }
            return $out;
        }

        // Months shown on the trend chart: the chosen range, otherwise the last six months.
        public function resolveTrendRange(array $f) {
            $today = new DateTime('today');
            $from  = !empty($f['date_from']) ? new DateTime($f['date_from']) : null;
            $to    = !empty($f['date_to'])   ? new DateTime($f['date_to'])   : null;

            if (!$to) {
                $to = ($from && $from > $today) ? clone $from : clone $today;
            }
            if (!$from) {
                $from = (clone $to)->modify('first day of this month')->modify('-' . (self::TREND_DEFAULT - 1) . ' months');
            }
            if ($from > $to) {
                $from = clone $to;
            }

            $start = (clone $from)->modify('first day of this month');
            $end   = (clone $to)->modify('last day of this month');

            // Never plot more than TREND_MAX months
            $earliest = (clone $end)->modify('first day of this month')->modify('-' . (self::TREND_MAX - 1) . ' months');
            if ($start < $earliest) {
                $start = $earliest;
            }
            return [$start, $end];
        }

        // Submissions per month (by submitted_at), zero-filled so quiet months still appear.
        public function getMonthlyTrend(array $f) {
            list($start, $end) = $this->resolveTrendRange($f);

            $f['date_from'] = $start->format('Y-m-d');
            $f['date_to']   = $end->format('Y-m-d');
            list($where, $params) = $this->buildFilter($f);

            $stmt = $this->conn->prepare(
                "SELECT DATE_FORMAT(r.submitted_at, '%Y-%m') AS ym, COUNT(*) AS c"
                . $this->fromJoins() . "WHERE $where AND r.submitted_at IS NOT NULL GROUP BY ym"
            );
            $stmt->execute($params);
            $byMonth = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $byMonth[$row['ym']] = (int) $row['c'];
            }

            $labels = [];
            $values = [];
            $cursor = (clone $start)->modify('first day of this month');
            while ($cursor <= $end) {
                $key      = $cursor->format('Y-m');
                $labels[] = $key;
                $values[] = $byMonth[$key] ?? 0;
                $cursor->modify('+1 month');
            }
            return ['labels' => $labels, 'values' => $values];
        }

        // Reports per department; reports without a department are grouped as "Unassigned".
        public function getDepartmentCounts(array $f) {
            list($where, $params) = $this->buildFilter($f);
            $stmt = $this->conn->prepare(
                "SELECT COALESCE(d.department_name, 'Unassigned') AS name, COUNT(*) AS c"
                . $this->fromJoins() . "WHERE $where
                 GROUP BY r.department_id, d.department_name
                 ORDER BY c DESC, name ASC"
            );
            $stmt->execute($params);

            $rows = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $rows[] = ['name' => $row['name'], 'count' => (int) $row['c']];
            }
            return $rows;
        }

        // ── CREATE / SAVE ────────────────────────────────────────────
        // $data keys: title, report_type, description
        public function saveDraft($data, $report_id = null) {
            $submitted_by  = $_SESSION['employee_id'];
            $department_id = $this->getEmployeeDepartment($submitted_by);

            if ($report_id) {
                $sql = "UPDATE `cc_reports` SET
                            title = :title, report_type = :report_type, description = :description
                        WHERE report_id = :report_id AND submitted_by = :submitted_by AND status = 'draft'";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':title'        => $data['title'],
                    ':report_type'  => $data['report_type'],
                    ':description'  => $data['description'],
                    ':report_id'    => $report_id,
                    ':submitted_by' => $submitted_by,
                ]);
                return $report_id;
            }

            $sql = "INSERT INTO `cc_reports`
                        (title, report_type, description, department_id, submitted_by, status, created_at)
                    VALUES
                        (:title, :report_type, :description, :department_id, :submitted_by, 'draft', NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':title'         => $data['title'],
                ':report_type'   => $data['report_type'],
                ':description'   => $data['description'],
                ':department_id' => $department_id,
                ':submitted_by'  => $submitted_by,
            ]);
            return $this->conn->lastInsertId();
        }

        // Promotes the CURRENT USER'S own draft to 'submitted'. Returns false if nothing changed
        // (not the owner, or not a draft), so one user can't submit someone else's report.
        public function submitDraft($report_id) {
            $stmt = $this->conn->prepare(
                "UPDATE `cc_reports` SET status = 'submitted', submitted_at = NOW()
                 WHERE report_id = :report_id AND submitted_by = :submitted_by AND status = 'draft'"
            );
            $stmt->execute([':report_id' => $report_id, ':submitted_by' => $_SESSION['employee_id']]);
            return $stmt->rowCount() > 0;
        }

        private function getEmployeeDepartment($employee_id) {
            $stmt = $this->conn->prepare("SELECT department_id FROM em_employees WHERE employee_id = :employee_id");
            $stmt->execute([':employee_id' => $employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            return $employee['department_id'] ?? null;
        }

        // ── REPORT FILE (stored in the attachment_* columns) ─────────
        // Only the person who created the report can attach, and only while it is a draft/submitted.
        // $path is the RELATIVE storage path returned by FileStorage::store().
        public function setAttachment($report_id, $path, $name, $mime, $size) {
            $sql = "UPDATE `cc_reports` SET
                        attachment_path = :path, attachment_name = :name,
                        attachment_mime = :mime, attachment_size = :size
                    WHERE report_id = :report_id
                      AND submitted_by = :submitted_by
                      AND status IN ('draft', 'submitted')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':path'         => $path,
                ':name'         => $name,
                ':mime'         => $mime,
                ':size'         => $size,
                ':report_id'    => $report_id,
                ':submitted_by' => $_SESSION['employee_id'],
            ]);
            return $stmt->rowCount() > 0;
        }

        // ── REVIEW / DECISION (School Directress) ────────────────────
        public function markReviewed($report_id, $reviewed_by, $notes = null) {
            $sql = "UPDATE `cc_reports` SET
                        status = 'reviewed', reviewed_by = :reviewed_by, reviewed_at = NOW(), review_notes = :notes
                    WHERE report_id = :report_id AND status = 'submitted'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':reviewed_by' => $reviewed_by,
                ':notes'       => $notes,
                ':report_id'   => $report_id,
            ]);
            return $stmt->rowCount() > 0;
        }

        public function decide($report_id, $decided_by, $decision, $notes = null) {
            if (!in_array($decision, ['approved', 'rejected'], true)) {
                return false;
            }
            $sql = "UPDATE `cc_reports` SET
                        status = :decision, decided_by = :decided_by, decided_at = NOW(), decision_notes = :notes
                    WHERE report_id = :report_id AND status = 'reviewed'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':decision'   => $decision,
                ':decided_by' => $decided_by,
                ':notes'      => $notes,
                ':report_id'  => $report_id,
            ]);
            return $stmt->rowCount() > 0;
        }

        // ── AI SUMMARIZATION (Google Gemini free tier) ────────────────
        // Summarizes whatever text the report has (description, plus the older
        // summary/findings/recommendations on reports written before file submission).
        // Requires GEMINI_API_KEY in env (or a defined constant of the same name).
        public function generateAiSummary($report_id) {
            $report = $this->getReportById($report_id);
            if (!$report) {
                return ['success' => false, 'message' => 'Report not found.'];
            }

            $parts = [];
            foreach (['description' => 'Description', 'summary' => 'Summary', 'findings' => 'Findings', 'recommendations' => 'Recommendations'] as $key => $label) {
                if (trim((string) ($report[$key] ?? '')) !== '') {
                    $parts[] = $label . ': ' . $report[$key];
                }
            }
            if (!$parts) {
                return ['success' => false, 'message' => 'This report has no text to summarize.'];
            }

            $apiKey = getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null);
            if (!$apiKey) {
                return ['success' => false, 'message' => 'AI summarization is not configured (missing GEMINI_API_KEY).'];
            }

            $prompt = "Summarize the following school report in 3-4 concise sentences for a school directress reviewing it. "
                     . "Focus on the key takeaway, the findings, and the recommendation.\n\n"
                     . "Title: {$report['title']}\n" . implode("\n", $parts);

            $payload = json_encode([
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 400,
                ],
            ]);

            $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'x-goog-api-key: ' . $apiKey,
                ],
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                error_log('[Report::generateAiSummary] Gemini API error (' . $httpCode . '): ' . $response);
                return [
                    'success' => false,
                    'message' => 'AI summarization failed.',
                    // TEMP debug info — remove once things are stable.
                    'debug' => 'HTTP ' . $httpCode . ': ' . $response,
                ];
            }

            $decoded     = json_decode($response, true);
            $summaryText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$summaryText) {
                return ['success' => false, 'message' => 'No summary returned.'];
            }

            $summaryText = trim($summaryText);

            $stmt = $this->conn->prepare("UPDATE `cc_reports` SET ai_summary = :ai_summary WHERE report_id = :report_id");
            $stmt->execute([':ai_summary' => $summaryText, ':report_id' => $report_id]);

            return ['success' => true, 'summary' => $summaryText];
        }
    }