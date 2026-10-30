<?php
    include_once __DIR__ . '/../../../database/db.php';
    include_once __DIR__ . '/FileStorage.php';

    class Report {
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

        // ── CREATE / SAVE ────────────────────────────────────────────
        // $data keys: title, report_type, summary, findings, recommendations
        public function saveDraft($data, $report_id = null) {
            $submitted_by  = $_SESSION['employee_id'];
            $department_id = $this->getEmployeeDepartment($submitted_by);

            if ($report_id) {
                $sql = "UPDATE `cc_reports` SET
                            title = :title, report_type = :report_type,
                            summary = :summary, findings = :findings, recommendations = :recommendations
                        WHERE report_id = :report_id AND submitted_by = :submitted_by AND status = 'draft'";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':title'           => $data['title'],
                    ':report_type'     => $data['report_type'],
                    ':summary'         => $data['summary'],
                    ':findings'        => $data['findings'],
                    ':recommendations' => $data['recommendations'],
                    ':report_id'       => $report_id,
                    ':submitted_by'    => $submitted_by,
                ]);
                return $report_id;
            }

            $sql = "INSERT INTO `cc_reports`
                        (title, report_type, summary, findings, recommendations, department_id, submitted_by, status, created_at)
                    VALUES
                        (:title, :report_type, :summary, :findings, :recommendations, :department_id, :submitted_by, 'draft', NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':title'           => $data['title'],
                ':report_type'     => $data['report_type'],
                ':summary'         => $data['summary'],
                ':findings'        => $data['findings'],
                ':recommendations' => $data['recommendations'],
                ':department_id'   => $department_id,
                ':submitted_by'    => $submitted_by,
            ]);
            return $this->conn->lastInsertId();
        }

        // Create (or promote an existing draft belonging to the current user) straight to 'submitted'
        public function submitReport($data, $report_id = null) {
            $report_id = $this->saveDraft($data, $report_id);

            $stmt = $this->conn->prepare(
                "UPDATE `cc_reports` SET status = 'submitted', submitted_at = NOW() WHERE report_id = :report_id"
            );
            $stmt->execute([':report_id' => $report_id]);

            return $report_id;
        }

        private function getEmployeeDepartment($employee_id) {
            $stmt = $this->conn->prepare("SELECT department_id FROM em_employees WHERE employee_id = :employee_id");
            $stmt->execute([':employee_id' => $employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            return $employee['department_id'] ?? null;
        }

        // ── OPTIONAL ATTACHMENT ──────────────────────────────────────
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

        // ── PDF GENERATION (dompdf) ───────────────────────────────────
        // composer require dompdf/dompdf
        // Saves into FileStorage and stores a RELATIVE path (reports/pdf/...) in pdf_path.
        public function generatePdf($report_id) {
            $autoload = __DIR__ . '/../../../vendor/autoload.php';
            if (!file_exists($autoload)) {
                // Thrown as a normal Exception (not a fatal require error) so callers
                // can catch it and still return a valid JSON response.
                throw new \RuntimeException('PDF library not installed. Run: composer require dompdf/dompdf');
            }
            require_once $autoload;

            $report = $this->getReportById($report_id);
            if (!$report) {
                return false;
            }

            $html = $this->buildReportHtml($report);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $fileName     = 'report_' . $report_id . '_' . time() . '.pdf';
            $relativePath = FileStorage::storeBytes($dompdf->output(), 'reports/pdf', $fileName);

            $stmt = $this->conn->prepare("UPDATE `cc_reports` SET pdf_path = :pdf_path WHERE report_id = :report_id");
            $stmt->execute([':pdf_path' => $relativePath, ':report_id' => $report_id]);

            return $relativePath;
        }

        private function buildReportHtml($report) {
            $title           = htmlspecialchars($report['title'] ?? '');
            $reportType      = htmlspecialchars($report['report_type'] ?? 'N/A');
            $department      = htmlspecialchars($report['department_name'] ?? 'N/A');
            $submittedBy     = htmlspecialchars($report['submitted_by_name'] ?? 'N/A');
            $submittedAt     = htmlspecialchars($report['submitted_at'] ?? '');
            $summary         = nl2br(htmlspecialchars($report['summary'] ?? ''));
            $findings        = nl2br(htmlspecialchars($report['findings'] ?? ''));
            $recommendations = nl2br(htmlspecialchars($report['recommendations'] ?? ''));

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
                <h1>{$title}</h1>
                <div class="meta">
                    <span><strong>Type:</strong> {$reportType}</span>
                    <span><strong>Department:</strong> {$department}</span>
                    <span><strong>Submitted by:</strong> {$submittedBy}</span>
                    <span><strong>Date:</strong> {$submittedAt}</span>
                </div>

                <h2>Summary</h2>
                <p>{$summary}</p>

                <h2>Findings</h2>
                <p>{$findings}</p>

                <h2>Recommendations</h2>
                <p>{$recommendations}</p>
            </body>
            </html>
            HTML;
        }

        // ── AI SUMMARIZATION (Google Gemini free tier) ────────────────
        // Requires GEMINI_API_KEY in env (or a defined constant of the same name).
        // Get a free key at https://aistudio.google.com/apikey — no credit card required.
        public function generateAiSummary($report_id) {
            $report = $this->getReportById($report_id);
            if (!$report) {
                return ['success' => false, 'message' => 'Report not found.'];
            }

            $apiKey = getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null);
            if (!$apiKey) {
                return ['success' => false, 'message' => 'AI summarization is not configured (missing GEMINI_API_KEY).'];
            }

            $prompt = "Summarize the following school report in 3-4 concise sentences for a school directress reviewing it. "
                     . "Focus on the key takeaway, the findings, and the recommendation.\n\n"
                     . "Title: {$report['title']}\n"
                     . "Summary: {$report['summary']}\n"
                     . "Findings: {$report['findings']}\n"
                     . "Recommendations: {$report['recommendations']}";

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