<?php
    include_once __DIR__ . '/../../../database/db.php';

    class Issues {
        const STATUSES = ['draft', 'submitted', 'reviewed', 'resolved', 'dismissed'];

        private $conn;

        public function __construct($pdo = null) {
            if ($pdo instanceof PDO) {
                $this->conn = $pdo;
            } else {
                $database = new Database();
                $this->conn = $database->getConnection();
            }
        }

        // ── VIEWER HELPERS ───────────────────────────────────────────
        // Single source of truth for "is this the School Directress?" (view + controller).
        public static function isDirectress($userInfo) {
    $roleName = $_SESSION['role_name'] ?? ($userInfo['role'] ?? '');
    return in_array($roleName, ['School Directress', 'Admin College Coordinator'], true);
}

        public static function viewerFrom($userInfo) {
            return [
                'employee_id'   => (int) ($userInfo['employee_id'] ?? $_SESSION['employee_id'] ?? 0),
                'department_id' => (int) ($userInfo['department_id'] ?? $_SESSION['department_id'] ?? 0),
                'is_directress' => self::isDirectress($userInfo),
            ];
        }

        public function getDepartments() {
            $stmt = $this->conn->prepare("SELECT department_id, department_name FROM em_departments ORDER BY department_name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Who may see which rows:
        //  - Drafts: only their author (never the Directress, never department colleagues).
        //  - Submitted and later: the Directress (all departments); everyone else only
        //    their own department's concerns plus anything they submitted themselves.
        // Only binds the parameters it actually uses (PDO rejects unused named params).
        private function visibilitySql(array $viewer, array &$params) {
            $me = (int) $viewer['employee_id'];

            if (!empty($viewer['is_directress'])) {
                $params[':vis_me'] = $me;
                return "(i.status <> 'draft' OR i.submitted_by = :vis_me)";
            }

            $params[':vis_me_draft'] = $me;
            $params[':vis_me_own']   = $me;
            $params[':vis_dept']     = (int) $viewer['department_id'];
            return "((i.status = 'draft' AND i.submitted_by = :vis_me_draft)
                     OR (i.status <> 'draft' AND (i.department = :vis_dept OR i.submitted_by = :vis_me_own)))";
        }

        // Columns for list rows. Deliberately excludes details, desired resolution,
        // notes, AI summary and file paths: the list never needs them.
        private function listColumns() {
            return "i.issue_id, i.title, i.status, i.submitted_on, i.created_at, i.updated_at,
                    (i.file_path IS NOT NULL AND i.file_path <> '') AS has_attachment,
                    i.submitted_by AS submitted_by_id,
                    d.department_name,
                    CONCAT(e.first_name, ' ', e.last_name) AS submitted_by,
                    CASE WHEN i.status IN ('submitted','reviewed') AND i.submitted_on IS NOT NULL
                         THEN DATEDIFF(NOW(), i.submitted_on) END AS days_waiting";
        }

        // ── LIST / READ ──────────────────────────────────────────────
        // $viewer (see viewerFrom) applies the visibility rules above. Without it, drafts
        // are always excluded so nothing can leak through an old call site.
        public function getConcerns($department_id = null, $status = null, $search = '', $viewer = null) {
            $sql = "SELECT " . $this->listColumns() . "
                    FROM cc_issues i
                    JOIN em_departments d ON i.department = d.department_id
                    JOIN em_employees e  ON i.submitted_by = e.employee_id
                    WHERE 1=1";

            $params = [];
            if (is_array($viewer)) {
                $sql .= " AND " . $this->visibilitySql($viewer, $params);
            } else {
                $sql .= " AND i.status <> 'draft'";
            }

            if (!is_null($department_id)) {
                $sql .= " AND i.department = :department_id";
                $params[':department_id'] = (int) $department_id;
            }
            if (!is_null($status) && in_array($status, self::STATUSES, true)) {
                $sql .= " AND i.status = :status";
                $params[':status'] = $status;
            }

            $search = trim((string) $search);
            if ($search !== '') {
                $like   = '%' . addcslashes($search, '%_\\') . '%';
                $clause = "i.title LIKE :search_title OR CONCAT(e.first_name, ' ', e.last_name) LIKE :search_name";
                $params[':search_title'] = $like;
                $params[':search_name']  = $like;

                $idTerm = ltrim($search, '#');
                if (ctype_digit($idTerm)) {
                    $clause .= " OR i.issue_id = :search_id";
                    $params[':search_id'] = (int) $idTerm;
                }
                $sql .= " AND ($clause)";
            }

            $sql .= " ORDER BY COALESCE(i.submitted_on, i.created_at) DESC, i.issue_id DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // NOTE: i.* keeps the raw i.submitted_by (employee id) and i.department (department id);
        // the controller's access check relies on both. The controller whitelists what is sent on.
        public function getConcernById($issue_id) {
            $sql = "SELECT
                        i.*,
                        d.department_name,
                        CONCAT(e.first_name, ' ', e.last_name)  AS submitted_by_name,
                        CONCAT(rv.first_name, ' ', rv.last_name) AS reviewed_by_name,
                        CONCAT(rs.first_name, ' ', rs.last_name) AS resolved_by_name
                    FROM cc_issues i
                    JOIN em_departments d ON i.department = d.department_id
                    JOIN em_employees e  ON i.submitted_by = e.employee_id
                    LEFT JOIN em_employees rv ON i.reviewed_by = rv.employee_id
                    LEFT JOIN em_employees rs ON i.resolved_by = rs.employee_id
                    WHERE i.issue_id = :issue_id
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':issue_id' => $issue_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        }

        // ── OVERVIEW (summary cards + needs-attention list) ──────────
        // Counts submitted/reviewed/resolved/dismissed (drafts are never counted).
        public function getSummaryCounts(array $viewer, $department_id = null) {
            $params = [];
            $sql = "SELECT i.status, COUNT(*) AS total
                    FROM cc_issues i
                    WHERE " . $this->visibilitySql($viewer, $params) . "
                      AND i.status <> 'draft'";
            if (!is_null($department_id)) {
                $sql .= " AND i.department = :department_id";
                $params[':department_id'] = (int) $department_id;
            }
            $sql .= " GROUP BY i.status";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            $counts = ['submitted' => 0, 'reviewed' => 0, 'resolved' => 0, 'dismissed' => 0];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (array_key_exists($row['status'], $counts)) {
                    $counts[$row['status']] = (int) $row['total'];
                }
            }
            $counts['total'] = array_sum($counts);
            return $counts;
        }

        // Unresolved concerns (submitted or reviewed), longest-waiting first.
        // There is no stored priority, so waiting time is the only ordering used.
        public function getAttentionConcerns(array $viewer, $department_id = null, $limit = 5) {
            $params = [];
            $sql = "SELECT " . $this->listColumns() . "
                    FROM cc_issues i
                    JOIN em_departments d ON i.department = d.department_id
                    JOIN em_employees e  ON i.submitted_by = e.employee_id
                    WHERE " . $this->visibilitySql($viewer, $params) . "
                      AND i.status IN ('submitted','reviewed')";
            if (!is_null($department_id)) {
                $sql .= " AND i.department = :department_id";
                $params[':department_id'] = (int) $department_id;
            }
            $sql .= " ORDER BY i.submitted_on ASC, i.issue_id ASC LIMIT " . max(1, (int) $limit);

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // ── CREATE / SAVE ────────────────────────────────────────────
        // $data keys: title, details, desired_resolution, department_id
        // Optional (only when a new attachment was uploaded): file_path, file_name, file_size
        // file_path is a RELATIVE storage path (issues/attachments/...), never an absolute one.
        public function saveDraft($data, $issue_id = null) {
            $submitted_by = $_SESSION['employee_id'];

            if ($issue_id) {
                $hasFile = array_key_exists('file_path', $data);

                $sql = "UPDATE cc_issues SET
                            title = :title, details = :details, desired_resolution = :desired_resolution
                            " . ($hasFile ? ", file_path = :file_path, file_name = :file_name, file_size = :file_size" : "") . "
                        WHERE issue_id = :issue_id AND submitted_by = :submitted_by AND status = 'draft'";
                $stmt = $this->conn->prepare($sql);
                $params = [
                    ':title'              => $data['title'],
                    ':details'            => $data['details'],
                    ':desired_resolution' => $data['desired_resolution'],
                    ':issue_id'           => $issue_id,
                    ':submitted_by'       => $submitted_by,
                ];
                if ($hasFile) {
                    $params[':file_path'] = $data['file_path'];
                    $params[':file_name'] = $data['file_name'] ?? null;
                    $params[':file_size'] = $data['file_size'] ?? null;
                }
                $stmt->execute($params);
                return $issue_id;
            }

            $sql = "INSERT INTO cc_issues
                        (title, details, desired_resolution, department, submitted_by, file_path, file_name, file_size, status, created_at)
                    VALUES
                        (:title, :details, :desired_resolution, :department, :submitted_by, :file_path, :file_name, :file_size, 'draft', NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':title'              => $data['title'],
                ':details'            => $data['details'],
                ':desired_resolution' => $data['desired_resolution'],
                ':department'         => $data['department_id'],
                ':submitted_by'       => $submitted_by,
                ':file_path'          => $data['file_path'] ?? null,
                ':file_name'          => $data['file_name'] ?? null,
                ':file_size'          => $data['file_size'] ?? null,
            ]);
            return $this->conn->lastInsertId();
        }

        // Create (or promote an existing draft belonging to the current user) straight to 'submitted'
        public function submitConcern($data, $issue_id = null) {
            $issue_id = $this->saveDraft($data, $issue_id);

            $stmt = $this->conn->prepare(
                "UPDATE cc_issues SET status = 'submitted', submitted_on = NOW() WHERE issue_id = :issue_id"
            );
            $stmt->execute([':issue_id' => $issue_id]);

            return $issue_id;
        }

        // ── REVIEW / RESOLUTION (School Directress) ───────────────────
        public function markReviewed($issue_id, $reviewed_by, $notes = null) {
            $sql = "UPDATE cc_issues SET
                        status = 'reviewed', reviewed_by = :reviewed_by, reviewed_at = NOW(), review_notes = :notes
                    WHERE issue_id = :issue_id AND status = 'submitted'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':reviewed_by' => $reviewed_by,
                ':notes'       => $notes,
                ':issue_id'    => $issue_id,
            ]);
            return $stmt->rowCount() > 0;
        }

        public function resolve($issue_id, $resolved_by, $decision, $notes = null) {
            if (!in_array($decision, ['resolved', 'dismissed'], true)) {
                return false;
            }
            $sql = "UPDATE cc_issues SET
                        status = :status, resolved_by = :resolved_by, resolved_at = NOW(), resolution_notes = :notes
                    WHERE issue_id = :issue_id AND status = 'reviewed'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':status'      => $decision,
                ':resolved_by' => $resolved_by,
                ':notes'       => $notes,
                ':issue_id'    => $issue_id,
            ]);
            return $stmt->rowCount() > 0;
        }

        // ── AI SUMMARIZATION (Google Gemini free tier) ────────────────
        // Requires GEMINI_API_KEY in env (or a defined constant of the same name).
        // NOTE: this sends the concern text to an external service. The controller only
        // allows it for users who can already open the concern.
        public function generateAiSummary($issue_id) {
            $concern = $this->getConcernById($issue_id);
            if (!$concern) {
                return ['success' => false, 'message' => 'Concern not found.'];
            }

            $apiKey = getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null);
            if (!$apiKey) {
                return ['success' => false, 'message' => 'AI summarization is not configured (missing GEMINI_API_KEY).'];
            }

            $prompt = "Summarize the following concern in 3-4 concise sentences for a school directress reviewing it. "
                     . "Focus on the core issue and what resolution is being sought.\n\n"
                     . "Title: {$concern['title']}\n"
                     . "Details: {$concern['details']}\n"
                     . "Desired Resolution: {$concern['desired_resolution']}";

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
                // Details go to the server log only, never to the browser.
                error_log('[Issues::generateAiSummary] Gemini API error (' . $httpCode . '): ' . $response);
                return ['success' => false, 'message' => 'AI summarization failed.'];
            }

            $decoded     = json_decode($response, true);
            $summaryText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$summaryText) {
                return ['success' => false, 'message' => 'No summary returned.'];
            }

            $summaryText = trim($summaryText);

            $stmt = $this->conn->prepare("UPDATE cc_issues SET ai_summary = :ai_summary WHERE issue_id = :issue_id");
            $stmt->execute([':ai_summary' => $summaryText, ':issue_id' => $issue_id]);

            return ['success' => true, 'summary' => $summaryText];
        }
    }