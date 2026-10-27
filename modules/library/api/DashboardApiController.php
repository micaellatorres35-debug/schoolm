<?php
class DashboardApiController
{
    private PDO         $db;
    private ActivityLog $log;

    public function __construct()
    {
        $this->db  = Database::getInstance();
        $this->log = new ActivityLog();
    }

    public function handle(string $action, array $input): never
    {
        match ($action) {
            'get_stats'              => $this->getStats(),
            'get_report'             => $this->getReport(),
            'get_activity'           => $this->getActivity(),
            'get_registrar_activity' => $this->getRegistrarActivity(),
            'submit_library_report'  => $this->submitLibraryReport($input),
            'get_library_reports'    => $this->getLibraryReports(),
            'get_dean_decisions'     => $this->getDeanDecisions(),
            default                  => Response::error("Unknown dashboard action: {$action}"),
        };
    }

    private function getStats(): never
    {
        $totalBooks     = (int)$this->db->query("SELECT COUNT(*) FROM lbr_books")->fetchColumn();
        $availableBooks = (int)$this->db->query("SELECT COUNT(*) FROM lbr_books WHERE status = 'available'")->fetchColumn();
        $borrowedBooks  = (int)$this->db->query("SELECT COUNT(*) FROM lbr_books WHERE status = 'borrowed'")->fetchColumn();
        $totalMembers   = (int)$this->db->query("SELECT COUNT(*) FROM lbr_borrowers WHERE active = 1")->fetchColumn();
        $overdueBooks   = (int)$this->db->query("SELECT COUNT(*) FROM lbr_transactions WHERE status = 'overdue'")->fetchColumn();
        $totalFines     = $this->db->query("SELECT COALESCE(SUM(fine),0) FROM lbr_transactions WHERE status = 'overdue'")->fetchColumn();

        $inventoryHealth = $this->db->query(
            "SELECT `condition`, COUNT(*) AS count FROM lbr_books GROUP BY `condition` ORDER BY FIELD(`condition`,'Excellent','Good','Fair','Poor')"
        )->fetchAll();

        $dueTodayCount = (int)$this->db->query(
            "SELECT COUNT(*) FROM lbr_transactions WHERE status='active' AND due_date = CURDATE()"
        )->fetchColumn();

        $dueSoonCount = (int)$this->db->query(
            "SELECT COUNT(*) FROM lbr_transactions WHERE status='active' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)"
        )->fetchColumn();

        $activeBorrowings = $this->db->query("
            SELECT t.id AS transaction_id, t.borrow_date, t.due_date, t.status,
                   DATEDIFF(t.due_date, CURDATE()) AS days_until_due,
                   b.title AS book_title, b.genre, b.cover_url, b.cover_local, b.id AS book_id,
                   br.name AS borrower_name, br.borrower_id AS member_id, br.type AS borrower_type
            FROM lbr_transactions t
            JOIN lbr_books     b  ON b.id  = t.book_id
            JOIN lbr_borrowers br ON br.id = t.borrower_id
            WHERE t.status IN ('active','overdue')
            ORDER BY t.due_date ASC LIMIT 10
        ")->fetchAll();

        $recentBooks    = $this->db->query(
            "SELECT id, title, author, genre, status, cover_url, cover_local, added_date
             FROM lbr_books ORDER BY added_date DESC, id DESC LIMIT 4"
        )->fetchAll();

        $recentActivity = $this->log->recentCombined(10);

        $genreInventory = $this->db->query(
            "SELECT genre, COUNT(*) AS total,
                SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) AS available,
                SUM(CASE WHEN status='borrowed'  THEN 1 ELSE 0 END) AS borrowed
             FROM lbr_books GROUP BY genre ORDER BY total DESC LIMIT 8"
        )->fetchAll();

        Response::json([
            'total_books'      => $totalBooks,
            'available_books'  => $availableBooks,
            'borrowed_books'   => $borrowedBooks,
            'total_members'    => $totalMembers,
            'overdue_books'    => $overdueBooks,
            'total_fines'      => number_format((float)$totalFines, 2),
            'due_today'        => $dueTodayCount,
            'due_soon'         => $dueSoonCount,
            'inventory_health' => $inventoryHealth,
            'active_borrowings'=> $activeBorrowings,
            'genre_inventory'  => $genreInventory,
            'recent_books'     => $recentBooks,
            'recent_activity'  => $recentActivity,
        ]);
    }

    private function getReport(): never
    {
        $stats = [
            'total_books'   => (int)$this->db->query("SELECT COUNT(*) FROM lbr_books")->fetchColumn(),
            'available'     => (int)$this->db->query("SELECT COUNT(*) FROM lbr_books WHERE status='available'")->fetchColumn(),
            'borrowed'      => (int)$this->db->query("SELECT COUNT(*) FROM lbr_books WHERE status='borrowed'")->fetchColumn(),
            'overdue'       => (int)$this->db->query("SELECT COUNT(*) FROM lbr_transactions WHERE status='overdue'")->fetchColumn(),
            'total_members' => (int)$this->db->query("SELECT COUNT(*) FROM lbr_borrowers")->fetchColumn(),
            'total_fines'   => number_format(
                (float)$this->db->query("SELECT COALESCE(SUM(fine),0) FROM lbr_transactions WHERE status='overdue'")->fetchColumn(), 2
            ),
        ];

        $genreBreakdown  = $this->db->query("SELECT genre, COUNT(*) AS count FROM lbr_books GROUP BY genre ORDER BY count DESC")->fetchAll();
        $memberBreakdown = $this->db->query("SELECT type, COUNT(*) AS count FROM lbr_borrowers GROUP BY type ORDER BY count DESC")->fetchAll();
        $overdueList     = $this->db->query("
            SELECT t.due_date, t.fine, b.title AS book_title, br.name AS borrower_name
            FROM lbr_transactions t
            JOIN lbr_books     b  ON b.id  = t.book_id
            JOIN lbr_borrowers br ON br.id = t.borrower_id
            WHERE t.status = 'overdue' ORDER BY t.due_date ASC
        ")->fetchAll();
        $topBorrowed     = $this->db->query("
            SELECT b.title, b.author, COUNT(t.id) AS borrow_count
            FROM lbr_transactions t JOIN lbr_books b ON b.id = t.book_id
            GROUP BY b.id ORDER BY borrow_count DESC LIMIT 5
        ")->fetchAll();

        Response::json(compact('stats', 'genreBreakdown', 'memberBreakdown', 'overdueList', 'topBorrowed'));
    }

    private function getActivity(): never
    {
        $limit    = (int)($_GET['limit'] ?? 20);
        $activity = $this->log->recentCombined($limit);
        Response::json(['activity' => $activity]);
    }

    private function getRegistrarActivity(): never
    {
        $limit    = (int)($_GET['limit'] ?? 20);
        $activity = $this->log->recentRegistrar($limit);
        Response::json(['activity' => $activity]);
    }

    // ── Submit library report to sd_reports (department_id=5 = Library) ──
    private function submitLibraryReport(array $input): never
    {
        $title      = trim($input['title']       ?? '');
        $reportType = (int)($input['report_type'] ?? 16);
        $description= trim($input['description'] ?? '');

        if (!$title) Response::error('Report title is required.');

        // Get current librarian's employee_id from session
        $submittedBy = null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['employee_id'])) {
            $submittedBy = (int)$_SESSION['employee_id'];
        } else {
            // Try to find from user_id in session
            if (!empty($_SESSION['user_id'])) {
                $stmt = $this->db->prepare("SELECT employee_id FROM user_account WHERE user_id = ?");
                $stmt->execute([(int)$_SESSION['user_id']]);
                $row = $stmt->fetch();
                if ($row) $submittedBy = (int)$row['employee_id'];
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO sd_reports (title, description, department_id, submitted_by, status, report_type)
            VALUES (?, ?, 5, ?, 'Pending', ?)
        ");
        $stmt->execute([$title, $description, $submittedBy, $reportType]);

        $this->log->log('Report Submitted', "Library report \"{$title}\" submitted for dean approval");
        Response::success("Report \"{$title}\" submitted for dean approval!");
    }

    // ── Get this library's submitted reports ──────────────────────
    private function getLibraryReports(): never
    {
        $stmt = $this->db->query("
            SELECT r.report_id, r.title, r.description, r.status, r.submitted_at,
                   rt.report_type AS report_type_name
            FROM sd_reports r
            LEFT JOIN sd_report_type rt ON rt.type_id = r.report_type
            WHERE r.department_id = 5
            ORDER BY r.submitted_at DESC
            LIMIT 20
        ");
        Response::json(['reports' => $stmt->fetchAll()]);
    }

    // ── Get all dean decisions across departments ─────────────────
    private function getDeanDecisions(): never
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    r.report_id,
                    r.title,
                    r.description,
                    r.status,
                    r.submitted_at,
                    rt.report_type   AS report_type_name,
                    d.department_name,
                    CONCAT(e.first_name, ' ', e.last_name) AS submitted_by_name
                FROM sd_reports r
                LEFT JOIN sd_report_type rt ON rt.type_id    = r.report_type
                LEFT JOIN sd_department  d  ON d.department_id = r.department_id
                LEFT JOIN sms_employee   e  ON e.employee_id   = r.submitted_by
                ORDER BY r.submitted_at DESC
                LIMIT 50
            ");
            Response::json(['reports' => $stmt->fetchAll()]);
        } catch (PDOException $e) {
            Response::error('Could not load dean decisions: ' . $e->getMessage(), 500);
        }
    }
}
