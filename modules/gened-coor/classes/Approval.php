<?php
    include_once __DIR__ . '/../../../database/db.php';
    include_once __DIR__ . '/FileStorage.php'; // shared with Report.php — same classes/ folder

    class Approval {
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
        // $department_id filters by the SUBMITTER's department (e1.department), matching the
        // original join. $status filters by workflow status (draft/submitted/reviewed/approved/rejected).
        public function getApprovals($department_id = null, $status = null) {
            $sql = "SELECT
                        a.approval_id, a.title, a.description, a.justification,
                        a.status, a.decision, a.file_path, a.pdf_path, a.ai_summary,
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
        // submitter_department_id mirrors the join used by getApprovals() for department filtering.
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
                        (
                            title,
                            description,
                            justification,
                            department,
                            file_path,
                            file_name,
                            file_size,
                            submit_by,
                            status,
                            created_at
                        )
                    VALUES
                        (
                            :title,
                            :description,
                            :justification,
                            :department,
                            :file_path,
                            :file_name,
                            :file_size,
                            :submit_by,
                            'draft',
                            NOW()
                        )";

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

        // ── PDF GENERATION (dompdf) ───────────────────────────────────
        // Saves into FileStorage and stores a RELATIVE path (approvals/pdf/...) in pdf_path.
        public function generatePdf($approval_id) {
            $autoload = __DIR__ . '/../../../vendor/autoload.php';
            if (!file_exists($autoload)) {
                throw new \RuntimeException('PDF library not installed. Run: composer require dompdf/dompdf');
            }
            require_once $autoload;

            $approval = $this->getApprovalById($approval_id);
            if (!$approval) {
                return false;
            }

            $html = $this->buildApprovalHtml($approval);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $fileName     = 'approval_' . $approval_id . '_' . time() . '.pdf';
            $relativePath = FileStorage::storeBytes($dompdf->output(), 'approvals/pdf', $fileName);

            $stmt = $this->conn->prepare("UPDATE cc_approvals SET pdf_path = :pdf_path WHERE approval_id = :approval_id");
            $stmt->execute([':pdf_path' => $relativePath, ':approval_id' => $approval_id]);

            return $relativePath;
        }

        private function buildApprovalHtml($approval) {
            $title         = htmlspecialchars($approval['title'] ?? '');
            $department    = htmlspecialchars($approval['department_name'] ?? 'N/A');
            $submittedBy   = htmlspecialchars($approval['submit_by_name'] ?? 'N/A');
            $submittedOn   = htmlspecialchars($approval['submitted_on'] ?? '');
            $description   = nl2br(htmlspecialchars($approval['description'] ?? ''));
            $justification = nl2br(htmlspecialchars($approval['justification'] ?? ''));

            return <<<HTML
            <html>
            <head>
                <style>
                    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
                    h1 { font-size: 18px; margin-bottom: 4px; }
                    .meta { font-size: 11px; color: #555; margin-bottom: 20px; }
                    .meta span { display: inline-block; margin-right: 18px; }
                    h2 { font-size: 13px; margin-top: 22px; margin-bottom: 6px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
                    p { line-height: 1.5; }
                </style>
            </head>
            <body>
                <h1>Approval Request: {$title}</h1>
                <div class="meta">
                    <span><strong>Department:</strong> {$department}</span>
                    <span><strong>Submitted by:</strong> {$submittedBy}</span>
                    <span><strong>Date:</strong> {$submittedOn}</span>
                </div>

                <h2>Description</h2>
                <p>{$description}</p>

                <h2>Justification</h2>
                <p>{$justification}</p>
            </body>
            </html>
            HTML;
        }

        // ── AI SUMMARIZATION (Google Gemini free tier) ────────────────
        // Requires GEMINI_API_KEY in env (or a defined constant of the same name).
        // Get a free key at https://aistudio.google.com/apikey — no credit card required.
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

            $stmt = $this->conn->prepare("UPDATE cc_approvals SET ai_summary = :ai_summary WHERE approval_id = :approval_id");
            $stmt->execute([':ai_summary' => $summaryText, ':approval_id' => $approval_id]);

            return ['success' => true, 'summary' => $summaryText];
        }
    }