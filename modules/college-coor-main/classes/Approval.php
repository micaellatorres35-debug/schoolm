<?php
    include_once __DIR__ . '/../../../database/db.php';
    include_once __DIR__ . '/FileStorage.php'; // shared with Report.php — same classes/ folder

    class Approval {
        private $conn;

        const STATUSES = ['draft', 'submitted', 'reviewed', 'approved', 'rejected'];

        public function __construct($pdo = null) {
            if ($pdo instanceof PDO) {
                $this->conn = $pdo;
            } else {
                $database = new Database();
                $this->conn = $database->getConnection();
            }
        }

        // ── LIST / READ ──────────────────────────────────────────────
        // Kept for backward compatibility with any other caller. The redesigned page
        // uses getQueue() / getOverview() instead.
        public function getApprovals($department_id = null, $status = null) {
            $sql = "SELECT
                        a.approval_id, a.title, a.description, a.justification,
                        a.status, a.decision, a.file_path, a.ai_summary,
                        a.submitted_on, a.reviewed_at, a.approved_at AS decided_at,
                        CONCAT(e1.first_name, ' ', e1.last_name) AS submit_by,
                        CONCAT(rv.first_name, ' ', rv.last_name) AS reviewed_by_name,
                        CONCAT(e2.first_name, ' ', e2.last_name) AS approver_id,
                        d.department_name
                    FROM cc_approvals a
                    LEFT JOIN em_employees e1 ON a.submit_by   = e1.employee_id
                    LEFT JOIN em_employees rv ON a.reviewed_by = rv.employee_id
                    LEFT JOIN em_employees e2 ON a.approver_id = e2.employee_id
                    LEFT JOIN em_departments d ON e1.department_id = d.department_id
                    WHERE 1=1";

            $params = [];
            if (!is_null($department_id)) {
                $sql .= " AND e1.department_id = :department_id";
                $params[':department_id'] = $department_id;
            }
            if (!is_null($status)) {
                $sql .= " AND a.status = :status";
                $params[':status'] = $status;
            }
            $sql .= " ORDER BY a.submitted_on DESC, a.created_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // NOTE: a.submit_by stays the raw employee_id here (used by the controller's access check).
        public function getApprovalById($approval_id) {
            $sql = "SELECT
                        a.*,
                        CONCAT(e1.first_name, ' ', e1.last_name) AS submit_by_name,
                        CONCAT(rv.first_name, ' ', rv.last_name) AS reviewed_by_name,
                        CONCAT(e2.first_name, ' ', e2.last_name) AS decided_by_name,
                        e1.department_id AS submitter_department_id,
                        d.department_name
                    FROM cc_approvals a
                    LEFT JOIN em_employees e1 ON a.submit_by   = e1.employee_id
                    LEFT JOIN em_employees rv ON a.reviewed_by = rv.employee_id
                    LEFT JOIN em_employees e2 ON a.approver_id = e2.employee_id
                    LEFT JOIN em_departments d ON e1.department_id = d.department_id
                    WHERE a.approval_id = :approval_id
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':approval_id' => $approval_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        }

        // ── DASHBOARD QUERIES (Decision Command Center) ──────────────
        // Visibility rules (mirror approval_can_access() in the controller):
        //  - Nobody sees another person's draft.
        //  - School Directress sees every submitted+ request in every department.
        //  - Everyone else sees requests from their own department, plus anything they submitted.
        // $viewer = ['is_directress' => bool, 'employee_id' => int, 'department_id' => int]
        private function scopeClause(array $viewer, array &$params) {
            $emp = (int) ($viewer['employee_id'] ?? 0);
            $params[':v_emp1'] = $emp;

            if (!empty($viewer['is_directress'])) {
                return " AND (a.status <> 'draft' OR a.submit_by = :v_emp1)";
            }

            $params[':v_emp2'] = $emp;
            $params[':v_dept'] = (int) ($viewer['department_id'] ?? 0);
            return " AND (a.status <> 'draft' OR a.submit_by = :v_emp1)
                     AND (e1.department_id = :v_dept OR a.submit_by = :v_emp2)";
        }

        // $filters: department_id (?int), status ('pending'|'all'|<real status>),
        //           date_from / date_to ('Y-m-d' on submitted_on), search (title, ID or submitter name)
        // Returns up to 101 rows; the controller trims to 100 and reports has_more.
        public function getQueue(array $viewer, array $filters) {
            $params = [];
            $sql = "SELECT
                        a.approval_id, a.title, a.status, a.submitted_on, a.created_at,
                        (a.file_path IS NOT NULL AND a.file_path <> '') AS has_attachment,
                        CASE WHEN a.status IN ('submitted','reviewed') AND a.submitted_on IS NOT NULL
                             THEN DATEDIFF(NOW(), a.submitted_on) END AS waiting_days,
                        CONCAT(e1.first_name, ' ', e1.last_name) AS submit_by,
                        d.department_name
                    FROM cc_approvals a
                    LEFT JOIN em_employees e1 ON a.submit_by = e1.employee_id
                    LEFT JOIN em_departments d ON e1.department_id = d.department_id
                    WHERE 1=1";
            $sql .= $this->scopeClause($viewer, $params);

            $status = $filters['status'] ?? 'all';
            if ($status === 'pending') {
                $sql .= " AND a.status IN ('submitted','reviewed')";
            } elseif (in_array($status, self::STATUSES, true)) {
                $sql .= " AND a.status = :f_status";
                $params[':f_status'] = $status;
            }

            if (!empty($filters['department_id'])) {
                $sql .= " AND e1.department_id = :f_dept";
                $params[':f_dept'] = (int) $filters['department_id'];
            }
            if (!empty($filters['date_from'])) {
                $sql .= " AND a.submitted_on >= :f_from";
                $params[':f_from'] = $filters['date_from'] . ' 00:00:00';
            }
            if (!empty($filters['date_to'])) {
                $sql .= " AND a.submitted_on < DATE_ADD(:f_to, INTERVAL 1 DAY)";
                $params[':f_to'] = $filters['date_to'];
            }

            $search = trim((string) ($filters['search'] ?? ''));
            if ($search !== '') {
                $like  = '%' . addcslashes($search, '%_\\') . '%';
                $parts = [
                    "a.title LIKE :s_title",
                    "CONCAT(e1.first_name, ' ', e1.last_name) LIKE :s_name",
                ];
                $params[':s_title'] = $like;
                $params[':s_name']  = $like;

                $idPart = ltrim($search, '#');
                if ($idPart !== '' && ctype_digit($idPart)) {
                    $parts[] = "a.approval_id = :s_id";
                    $params[':s_id'] = (int) $idPart;
                }
                $sql .= " AND (" . implode(' OR ', $parts) . ")";
            }

            // Items waiting on the Directress: oldest first. Everything else: newest first.
            $sql .= $status === 'pending'
                ? " ORDER BY a.submitted_on ASC, a.approval_id ASC"
                : " ORDER BY COALESCE(a.submitted_on, a.created_at) DESC, a.approval_id DESC";
            $sql .= " LIMIT 101";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Summary counts (one per real status) + the most recent decisions.
        public function getOverview(array $viewer, $departmentId = null) {
            // Counts
            $params = [];
            $sql = "SELECT a.status, COUNT(*) AS total
                    FROM cc_approvals a
                    LEFT JOIN em_employees e1 ON a.submit_by = e1.employee_id
                    WHERE 1=1" . $this->scopeClause($viewer, $params);
            if ($departmentId) {
                $sql .= " AND e1.department_id = :f_dept";
                $params[':f_dept'] = (int) $departmentId;
            }
            $sql .= " GROUP BY a.status";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            $counts = array_fill_keys(self::STATUSES, 0);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $counts[$row['status']] = (int) $row['total'];
            }

            // Recent decisions
            $params = [];
            $sql = "SELECT
                        a.approval_id, a.title, a.status, a.decision, a.approved_at AS decided_at,
                        CONCAT(e2.first_name, ' ', e2.last_name) AS decided_by_name,
                        d.department_name
                    FROM cc_approvals a
                    LEFT JOIN em_employees e1 ON a.submit_by    = e1.employee_id
                    LEFT JOIN em_employees e2 ON a.approver_id  = e2.employee_id
                    LEFT JOIN em_departments d ON e1.department_id = d.department_id
                    WHERE a.status IN ('approved','rejected')" . $this->scopeClause($viewer, $params);
            if ($departmentId) {
                $sql .= " AND e1.department_id = :f_dept";
                $params[':f_dept'] = (int) $departmentId;
            }
            $sql .= " ORDER BY a.approved_at DESC, a.approval_id DESC LIMIT 8";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            return ['counts' => $counts, 'recent' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        }

        // ── CREATE / SAVE ────────────────────────────────────────────
        // $data keys: title, description, justification
        // Optional (only when a new attachment was uploaded): file_path, file_name, file_size
        // file_path is a RELATIVE storage path (approvals/attachments/...), never an absolute one.

        public function saveDraft($data, $approval_id = null) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $submit_by = $_SESSION['employee_id'] ?? null;

            if (!$submit_by) {
                throw new RuntimeException('You must be logged in to save an approval.');
            }

            // Get the department from the authenticated employee record.
            $stmt = $this->conn->prepare(
                "SELECT department_id
                FROM em_employees
                WHERE employee_id = :employee_id
                LIMIT 1"
            );
            $stmt->execute([':employee_id' => $submit_by]);

            $departmentId = $stmt->fetchColumn();

            if ($departmentId === false || $departmentId === null) {
                throw new RuntimeException(
                    'Cannot save approval: your employee account has no department assigned.'
                );
            }

            if ($approval_id) {
                // Update only a draft owned by the current employee.
                $hasFile = array_key_exists('file_path', $data);

                $sql = "UPDATE cc_approvals SET
                            title = :title,
                            description = :description,
                            justification = :justification,
                            department = :department"
                            . ($hasFile
                                ? ", file_path = :file_path, file_name = :file_name, file_size = :file_size"
                                : "") . "
                        WHERE approval_id = :approval_id
                        AND submit_by = :submit_by
                        AND status = 'draft'";

                $stmt = $this->conn->prepare($sql);

                $params = [
                    ':title'         => $data['title'],
                    ':description'   => $data['description'],
                    ':justification' => $data['justification'],
                    ':department'    => $departmentId,
                    ':approval_id'   => $approval_id,
                    ':submit_by'     => $submit_by,
                ];

                if ($hasFile) {
                    $params[':file_path'] = $data['file_path'];
                    $params[':file_name'] = $data['file_name'] ?? null;
                    $params[':file_size'] = $data['file_size'] ?? null;
                }

                $stmt->execute($params);

                return $approval_id;
            }

            // Create a new draft with its department recorded.
            $sql = "INSERT INTO cc_approvals
                        (title, description, justification, department,
                         file_path, file_name, file_size, submit_by, status, created_at)
                    VALUES
                        (:title, :description, :justification, :department,
                         :file_path, :file_name, :file_size, :submit_by, 'draft', NOW())";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([
                ':title'         => $data['title'],
                ':description'   => $data['description'],
                ':justification' => $data['justification'],
                ':department'    => $departmentId,
                ':file_path'     => $data['file_path'] ?? null,
                ':file_name'     => $data['file_name'] ?? null,
                ':file_size'     => $data['file_size'] ?? null,
                ':submit_by'     => $submit_by,
            ]);

            return $this->conn->lastInsertId();
        }

        // Create (or promote an existing draft belonging to the current user) straight to 'submitted'
        public function submitApproval($data, $approval_id = null) {
            $approval_id = $this->saveDraft($data, $approval_id);

            $stmt = $this->conn->prepare(
                "UPDATE cc_approvals SET status = 'submitted', submitted_on = NOW() WHERE approval_id = :approval_id"
            );
            $stmt->execute([':approval_id' => $approval_id]);

            return $approval_id;
        }

        // ── REVIEW / DECISION (School Directress) ────────────────────
        // Workflow is unchanged: submitted -> reviewed -> approved | rejected.
        // Each UPDATE is guarded by the expected current status, so a stale page can never
        // skip a step or overwrite a decision that was already recorded.
        public function markReviewed($approval_id, $reviewed_by, $notes = null) {
            $sql = "UPDATE cc_approvals SET
                        status = 'reviewed', reviewed_by = :reviewed_by, reviewed_at = NOW(), review_notes = :notes
                    WHERE approval_id = :approval_id AND status = 'submitted'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':reviewed_by' => $reviewed_by,
                ':notes'       => $notes,
                ':approval_id' => $approval_id,
            ]);
            return $stmt->rowCount() > 0;
        }

        public function decide($approval_id, $decided_by, $decision, $notes = null) {
            if (!in_array($decision, ['approved', 'rejected'], true)) {
                return false;
            }
            // A rejection must always carry a reason (also enforced in the controller).
            if ($decision === 'rejected' && ($notes === null || trim($notes) === '')) {
                return false;
            }
            $sql = "UPDATE cc_approvals SET
                        status = :status, decision = :decision,
                        approver_id = :decided_by, approved_at = NOW(), remarks = :notes
                    WHERE approval_id = :approval_id AND status = 'reviewed'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':status'      => $decision,
                ':decision'    => $decision,
                ':decided_by'  => $decided_by,
                ':notes'       => $notes,
                ':approval_id' => $approval_id,
            ]);
            return $stmt->rowCount() > 0;
        }

        // ── AI SUMMARIZATION (Google Gemini free tier) ────────────────
        // Requires GEMINI_API_KEY in env (or a defined constant of the same name).
        public function generateAiSummary($approval_id) {
            $approval = $this->getApprovalById($approval_id);
            if (!$approval) {
                return ['success' => false, 'message' => 'Approval request not found.'];
            }

            $apiKey = getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null);
            if (!$apiKey) {
                return ['success' => false, 'message' => 'AI summarization is not configured (missing GEMINI_API_KEY).'];
            }

            $prompt = "Summarize the following approval request in 3-4 concise sentences for a school directress reviewing it. "
                     . "Focus on what is being requested and why.\n\n"
                     . "Title: {$approval['title']}\n"
                     . "Description: {$approval['description']}\n"
                     . "Justification: {$approval['justification']}";

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
                error_log('[Approval::generateAiSummary] Gemini API error (' . $httpCode . '): ' . $response);
                $result = ['success' => false, 'message' => 'AI summarization failed.'];
                if (defined('APPROVAL_DEBUG') && APPROVAL_DEBUG) {
                    $result['debug'] = 'HTTP ' . $httpCode . ': ' . $response;
                }
                return $result;
            }

            $decoded     = json_decode($response, true);
            $summaryText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$summaryText) {
                return ['success' => false, 'message' => 'No summary returned.'];
            }

            $summaryText = trim($summaryText);

            $stmt = $this->conn->prepare("UPDATE cc_approvals SET ai_summary = :ai_summary WHERE approval_id = :approval_id");
            $stmt->execute([':ai_summary' => $summaryText, ':approval_id' => $approval_id]);

            return ['success' => true, 'summary' => $summaryText];
        }
    }