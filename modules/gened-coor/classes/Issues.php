<?php
    include_once __DIR__ . '/../../../database/db.php';
    include_once __DIR__ . '/FileStorage.php'; // shared with Report.php / Approval.php — same classes/ folder

    class Issues {
        private $conn;

        public function __construct($pdo = null) {
            if ($pdo instanceof PDO) {
                $this->conn = $pdo;
            } else {
                $database = new Database();
                $this->conn = $database->getConnection();
            }
        }

        public function getDepartments() {
            $stmt = $this->conn->prepare("SELECT department_id, department_name FROM em_departments ORDER BY department_name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // ── LIST / READ ──────────────────────────────────────────────
        public function getConcerns($department_id = null, $status = null, $search = '') {
            $sql = "SELECT
                        i.issue_id, i.title, i.details, i.desired_resolution,
                        i.status, i.file_path, i.pdf_path, i.ai_summary,
                        i.submitted_on, i.reviewed_at, i.resolved_at,
                        d.department_name,
                        CONCAT(e.first_name, ' ', e.last_name)  AS submitted_by,
                        CONCAT(rv.first_name, ' ', rv.last_name) AS reviewed_by_name,
                        CONCAT(rs.first_name, ' ', rs.last_name) AS resolved_by_name
                    FROM cc_issues i
                    JOIN em_departments d ON i.department = d.department_id
                    JOIN em_employees e  ON i.submitted_by = e.employee_id
                    LEFT JOIN em_employees rv ON i.reviewed_by = rv.employee_id
                    LEFT JOIN em_employees rs ON i.resolved_by = rs.employee_id
                    WHERE 1=1";

            $params = [];
            if (!is_null($department_id)) {
                $sql .= " AND i.department = :department_id";
                $params[':department_id'] = $department_id;
            }
            if (!is_null($status)) {
                $sql .= " AND i.status = :status";
                $params[':status'] = $status;
            }
            if ($search !== '') {
                $sql .= " AND (i.title LIKE :search OR CONCAT(e.first_name, ' ', e.last_name) LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $sql .= " ORDER BY i.submitted_on DESC, i.created_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // NOTE: i.* keeps the raw i.submitted_by (employee id) and i.department (department id);
        // the controller's access check relies on both.
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

        // ── PDF GENERATION (dompdf) ───────────────────────────────────
        // Saves into FileStorage and stores a RELATIVE path (issues/pdf/...) in pdf_path.
        public function generatePdf($issue_id) {
            $autoload = __DIR__ . '/../../../vendor/autoload.php';
            if (!file_exists($autoload)) {
                throw new \RuntimeException('PDF library not installed. Run: composer require dompdf/dompdf');
            }
            require_once $autoload;

            $concern = $this->getConcernById($issue_id);
            if (!$concern) {
                return false;
            }

            $html = $this->buildConcernHtml($concern);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $fileName     = 'concern_' . $issue_id . '_' . time() . '.pdf';
            $relativePath = FileStorage::storeBytes($dompdf->output(), 'issues/pdf', $fileName);

            $stmt = $this->conn->prepare("UPDATE cc_issues SET pdf_path = :pdf_path WHERE issue_id = :issue_id");
            $stmt->execute([':pdf_path' => $relativePath, ':issue_id' => $issue_id]);

            return $relativePath;
        }

        private function buildConcernHtml($concern) {
            $title       = htmlspecialchars($concern['title'] ?? '');
            $department  = htmlspecialchars($concern['department_name'] ?? 'N/A');
            $submittedBy = htmlspecialchars($concern['submitted_by_name'] ?? 'N/A');
            $submittedOn = htmlspecialchars($concern['submitted_on'] ?? '');
            $details     = nl2br(htmlspecialchars($concern['details'] ?? ''));
            $resolution  = nl2br(htmlspecialchars($concern['desired_resolution'] ?? ''));

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
                <h1>Concern: {$title}</h1>
                <div class="meta">
                    <span><strong>Department:</strong> {$department}</span>
                    <span><strong>Submitted by:</strong> {$submittedBy}</span>
                    <span><strong>Date:</strong> {$submittedOn}</span>
                </div>

                <h2>Details</h2>
                <p>{$details}</p>

                <h2>Desired Resolution</h2>
                <p>{$resolution}</p>
            </body>
            </html>
            HTML;
        }

        // ── AI SUMMARIZATION (Google Gemini free tier) ────────────────
        // Requires GEMINI_API_KEY in env (or a defined constant of the same name).
        // Get a free key at https://aistudio.google.com/apikey — no credit card required.
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
                error_log('[Issues::generateAiSummary] Gemini API error (' . $httpCode . '): ' . $response);
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

            $stmt = $this->conn->prepare("UPDATE cc_issues SET ai_summary = :ai_summary WHERE issue_id = :issue_id");
            $stmt->execute([':ai_summary' => $summaryText, ':issue_id' => $issue_id]);

            return ['success' => true, 'summary' => $summaryText];
        }
    }