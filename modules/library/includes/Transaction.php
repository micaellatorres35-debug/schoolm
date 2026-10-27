<?php
class Transaction
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Queries
    // ------------------------------------------------------------------

    /**
     * Paginated list of transactions with joined book/borrower info.
     *
     * @return array{transactions: array, total: int, pages: int, page: int}
     */
    public function list(
        string $status = '',
        int    $page   = 1,
        int    $limit  = 10,
        string $search = ''
    ): array {
        $where  = ['1=1'];
        $params = [];

        if ($status) { $where[] = 't.status = ?'; $params[] = $status; }
        if ($search) {
            $where[] = '(b.title LIKE ? OR br.name LIKE ? OR br.borrower_id LIKE ?)';
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s]);
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM lbr_transactions t
            JOIN lbr_books     b  ON b.id  = t.book_id
            JOIN lbr_borrowers br ON br.id = t.borrower_id
            WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT t.*,
                   b.title      AS book_title,
                   b.author     AS book_author,
                   b.cover_url,
                   b.cover_local,
                   br.name        AS borrower_name,
                   br.borrower_id AS member_id,
                   br.type        AS member_type
            FROM lbr_transactions t
            JOIN lbr_books       b  ON b.id  = t.book_id
            JOIN lbr_borrowers   br ON br.id = t.borrower_id
            WHERE {$whereStr}
            ORDER BY t.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return [
            'transactions' => $stmt->fetchAll(),
            'total'        => $total,
            'pages'        => (int)ceil($total / $limit),
            'page'         => $page,
        ];
    }

    /**
     * Returns ALL active + overdue transactions — used by the Return Book tab.
     * No pagination, no limit. Always returns every book that can be returned.
     */
    public function listReturnable(): array
    {
        $stmt = $this->db->query("
            SELECT t.*,
                   b.title        AS book_title,
                   b.author       AS book_author,
                   b.cover_url,
                   b.cover_local,
                   br.name        AS borrower_name,
                   br.borrower_id AS member_id,
                   br.type        AS member_type
            FROM lbr_transactions t
            JOIN lbr_books       b  ON b.id  = t.book_id
            JOIN lbr_borrowers   br ON br.id = t.borrower_id
            WHERE t.status IN ('active', 'overdue')
              AND t.return_date IS NULL
            ORDER BY t.due_date ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Returns all LOST transactions — used by the Lost Books tab.
     */
    public function listLost(): array
    {
        $stmt = $this->db->query("
            SELECT t.id, t.book_id, t.borrower_id, t.borrow_date, t.due_date,
                   t.lost_date, t.lost_notes, t.fine,
                   b.title   AS book_title,
                   b.author  AS book_author,
                   b.genre,
                   b.isbn,
                   b.cover_url,
                   b.cover_local,
                   br.name        AS borrower_name,
                   br.borrower_id AS student_number,
                   br.type        AS borrower_type,
                   br.grade       AS borrower_grade,
                   br.email       AS borrower_email,
                   br.phone       AS borrower_phone
            FROM lbr_transactions t
            JOIN lbr_books     b  ON b.id  = t.book_id
            JOIN lbr_borrowers br ON br.id = t.borrower_id
            WHERE t.status = 'lost'
            ORDER BY t.lost_date DESC, t.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT t.*, b.title
            FROM lbr_transactions t
            JOIN lbr_books b ON b.id = t.book_id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function activeLoanCount(int $borrowerId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM lbr_transactions
             WHERE borrower_id = ? AND status IN ('active','overdue')"
        );
        $stmt->execute([$borrowerId]);
        return (int)$stmt->fetchColumn();
    }

    public function historyForBook(int $bookId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, br.name AS borrower_name, br.borrower_id AS member_id
            FROM lbr_transactions t
            JOIN lbr_borrowers br ON br.id = t.borrower_id
            WHERE t.book_id = ?
            ORDER BY t.borrow_date DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll();
    }

    public function historyForBorrower(int $borrowerId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, b.title AS book_title, b.author AS book_author
            FROM lbr_transactions t
            JOIN lbr_books b ON b.id = t.book_id
            WHERE t.borrower_id = ?
            ORDER BY t.borrow_date DESC
        ");
        $stmt->execute([$borrowerId]);
        return $stmt->fetchAll();
    }

    public function create(
        int    $bookId,
        int    $borrowerId,
        string $borrowDate,
        string $dueDate,
        string $notes = ''
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO lbr_transactions (book_id, borrower_id, borrow_date, due_date, notes)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$bookId, $borrowerId, $borrowDate, $dueDate, $notes]);
        return (int)$this->db->lastInsertId();
    }

    public function markReturned(int $id, string $returnDate, string $condition, float $fine): void
    {
        // 1. Mark transaction as returned
        $this->db->prepare(
            "UPDATE lbr_transactions
             SET status='returned', return_date=?, `condition`=?, fine=?
             WHERE id=?"
        )->execute([$returnDate, $condition, $fine, $id]);

        // 2. Sync to lbr_borrow_history so AI features stay accurate
        $trans = $this->db->prepare(
            "SELECT t.borrower_id, t.book_id, t.borrow_date, t.due_date, b.genre
             FROM lbr_transactions t
             JOIN lbr_books b ON b.id = t.book_id
             WHERE t.id = ?"
        );
        $trans->execute([$id]);
        $row = $trans->fetch();

        if ($row) {
            $daysLate = max(0, (int)((strtotime($returnDate) - strtotime($row['due_date'])) / 86400));
            $wasLate  = $daysLate > 0 ? 1 : 0;

            // Insert history record (no unique key, so plain INSERT)
            $this->db->prepare("
                INSERT INTO lbr_borrow_history
                    (borrower_id, book_id, genre, borrow_date, due_date, return_date, days_late, was_late, fine_charged)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $row['borrower_id'],
                $row['book_id'],
                $row['genre'],
                $row['borrow_date'],
                $row['due_date'],
                $returnDate,
                $daysLate,
                $wasLate,
                $fine,
            ]);
        }
    }
}