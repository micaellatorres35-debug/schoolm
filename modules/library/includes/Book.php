<?php
class Book
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function list(
        string $search = '',
        string $genre  = '',
        string $status = '',
        int    $page   = 1,
        int    $limit  = 10
    ): array {
        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[]  = '(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.genre LIKE ?)';
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s, $s, $s, $s]);
        }
        if ($genre)  { $where[] = 'b.genre = ?';  $params[] = $genre; }
        if ($status) { $where[] = 'b.status = ?'; $params[] = $status; }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM lbr_books b WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT * FROM lbr_books b WHERE {$whereStr} ORDER BY b.id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($params);

        return [
            'books' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int)ceil($total / $limit),
            'page'  => $page,
        ];
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM lbr_books WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function genres(): array
    {
        return $this->db->query('SELECT DISTINCT genre FROM lbr_books ORDER BY genre')
                        ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(
        string  $title,
        string  $author,
        string  $genre        = 'General',
        ?int    $year         = null,
        ?string $isbn         = null,
        string  $description  = '',
        ?string $coverUrl     = null,
        int     $totalStock   = 1
    ): int {
        if ($isbn && !$coverUrl) {
            $coverUrl = "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg";
        }

        $stmt = $this->db->prepare(
            'INSERT INTO lbr_books
                (title, author, genre, year, isbn, description, cover_url, total_stock, available_stock)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $title, $author, $genre, $year, $isbn,
            $description, $coverUrl, $totalStock, $totalStock
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(
        int     $id,
        string  $title,
        string  $author,
        string  $genre        = 'General',
        ?int    $year         = null,
        ?string $isbn         = null,
        string  $description  = '',
        string  $condition    = 'Good',
        ?string $coverUrl     = null,
        int     $totalStock   = 1
    ): void {
        if ($isbn && !$coverUrl) {
            $coverUrl = "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg";
        }

        // Count currently borrowed copies for this book
        $bStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM lbr_transactions
             WHERE book_id = ? AND status IN ('active','overdue')"
        );
        $bStmt->execute([$id]);
        $borrowed = (int)$bStmt->fetchColumn();

        $availableStock = max(0, $totalStock - $borrowed);
        $newStatus      = $availableStock > 0 ? 'available' : 'borrowed';

        $this->db->prepare(
            'UPDATE lbr_books
             SET title=?, author=?, genre=?, year=?, isbn=?, description=?,
                 `condition`=?, cover_url=?, total_stock=?, available_stock=?, status=?
             WHERE id=?'
        )->execute([
            $title, $author, $genre, $year, $isbn,
            $description, $condition, $coverUrl,
            $totalStock, $availableStock, $newStatus, $id
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM lbr_books WHERE id = ?')->execute([$id]);
    }

    public function setStatus(int $id, string $status, ?string $condition = null): void
    {
        if ($condition !== null) {
            $this->db->prepare('UPDATE lbr_books SET status=?, `condition`=? WHERE id=?')
                     ->execute([$status, $condition, $id]);
        } else {
            $this->db->prepare('UPDATE lbr_books SET status=? WHERE id=?')
                     ->execute([$status, $id]);
        }
    }

    // Called when a book is borrowed — decrements available_stock
    public function decrementStock(int $id): void
    {
        $this->db->prepare(
            "UPDATE lbr_books
             SET available_stock = GREATEST(0, available_stock - 1),
                 status = CASE WHEN (available_stock - 1) <= 0 THEN 'borrowed' ELSE 'available' END
             WHERE id = ?"
        )->execute([$id]);
    }

    // Called when a book is returned — increments available_stock
    public function incrementStock(int $id, ?string $condition = null): void
    {
        if ($condition !== null) {
            $this->db->prepare(
                "UPDATE lbr_books
                 SET available_stock = LEAST(total_stock, available_stock + 1),
                     status = 'available',
                     `condition` = ?
                 WHERE id = ?"
            )->execute([$condition, $id]);
        } else {
            $this->db->prepare(
                "UPDATE lbr_books
                 SET available_stock = LEAST(total_stock, available_stock + 1),
                     status = 'available'
                 WHERE id = ?"
            )->execute([$id]);
        }
    }

    // Called when a book is reported lost — removes one copy permanently
    public function decrementStockOnLoss(int $id): void
    {
        $this->db->prepare("
            UPDATE lbr_books
            SET available_stock = GREATEST(0, available_stock - 1),
                total_stock     = GREATEST(0, total_stock     - 1),
                status = CASE
                    WHEN (available_stock - 1) <= 0 THEN 'lost'
                    ELSE 'available'
                END
            WHERE id = ?
        ")->execute([$id]);
    }

    public function setCoverLocal(int $id, string $filename): void
    {
        $this->db->prepare('UPDATE lbr_books SET cover_local=? WHERE id=?')
                 ->execute([$filename, $id]);
    }
}