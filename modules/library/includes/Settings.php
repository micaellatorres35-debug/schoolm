<?php
class Settings
{
    private PDO $db;

    private static array $allowed = [
        'library_name',
        'max_borrow_days',
        'max_books_per_member',
        'auto_save',
        'ai_reminder_enabled',
        'ai_reminder_threshold',
        'ai_sms_enabled',
        'ai_email_enabled',
        'ai_recommendations_count',
        'ai_rec_min_similarity',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        try {
            $rows = $this->db->query('SELECT setting_key, setting_value FROM lbr_settings')->fetchAll();
            $out  = [];
            foreach ($rows as $row) {
                $out[$row['setting_key']] = $row['setting_value'];
            }
            return $out;
        } catch (PDOException) {
            return [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function save(array $input): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lbr_settings (setting_key, setting_value)
             VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?'
        );
        foreach (self::$allowed as $key) {
            if (isset($input[$key])) {
                $val = trim((string)$input[$key]);
                $stmt->execute([$key, $val, $val]);
            }
        }
    }

    public function updateOverdueStatus(): void
    {
        try {
            // 1. Mark overdue transactions (no fine calculation)
            $this->db->query("
                UPDATE lbr_transactions
                SET status = 'overdue'
                WHERE status != 'returned'
                  AND status != 'lost'
                  AND return_date IS NULL
                  AND due_date < CURDATE()
            ");

            // 2. Fix book status: 'borrowed' → 'available' when no active loans
            $this->db->query("
                UPDATE lbr_books b
                SET b.status = 'available'
                WHERE b.status = 'borrowed'
                  AND NOT EXISTS (
                      SELECT 1 FROM lbr_transactions t
                      WHERE t.book_id = b.id
                        AND t.status IN ('active', 'overdue')
                        AND t.return_date IS NULL
                  )
            ");

            // 3. Fix book status: 'available' → 'borrowed' when active loans exist
            $this->db->query("
                UPDATE lbr_books b
                SET b.status = 'borrowed'
                WHERE b.status = 'available'
                  AND EXISTS (
                      SELECT 1 FROM lbr_transactions t
                      WHERE t.book_id = b.id
                        AND t.status IN ('active', 'overdue')
                        AND t.return_date IS NULL
                  )
            ");

            // 4. Fix stuck 'lost' books
            $this->db->query("
                UPDATE lbr_books b
                SET b.status = 'available'
                WHERE b.status = 'lost'
                  AND NOT EXISTS (
                      SELECT 1 FROM lbr_transactions t
                      WHERE t.book_id = b.id
                        AND t.status = 'lost'
                  )
                  AND b.available_stock > 0
            ");

        } catch (PDOException) {
            // Non-fatal
        }
    }
}