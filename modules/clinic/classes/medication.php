<?php
include_once __DIR__ . '/../../../database/db.php';

class Medication {
    private $conn;

    public const FORMS = ['Tablet', 'Capsule', 'Syrup', 'Ointment', 'Injection', 'Drops', 'Other'];

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /* ---------- Queries ---------- */

    private function buildWhere(string $search, string $filter): array {
        $where  = ['is_archived = 0'];
        $params = [];

        if ($search !== '') {
            $where[] = '(medicine_name LIKE :s1 OR generic_name LIKE :s2 OR category LIKE :s3)';
            $like = '%' . $search . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
        }

        switch ($filter) {
            case 'low':
                $where[] = 'quantity <= reorder_level AND quantity > 0';
                break;
            case 'out':
                $where[] = 'quantity = 0';
                break;
            case 'expiring':
                $where[] = 'expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)';
                break;
            case 'expired':
                $where[] = 'expiry_date < CURDATE()';
                break;
        }

        return ['WHERE ' . implode(' AND ', $where), $params];
    }

    public function getAll(string $search = '', string $filter = 'all', int $limit = 10, int $offset = 0): array {
        [$where, $params] = $this->buildWhere($search, $filter);
        $stmt = $this->conn->prepare(
            "SELECT * FROM clinic_medications $where ORDER BY medicine_name ASC LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(string $search = '', string $filter = 'all'): int {
        [$where, $params] = $this->buildWhere($search, $filter);
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM clinic_medications $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getSummary(): array {
        $sql = "SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(quantity <= reorder_level AND quantity > 0), 0) AS low,
                    COALESCE(SUM(quantity = 0), 0) AS out_of_stock,
                    COALESCE(SUM(expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)), 0) AS expiring,
                    COALESCE(SUM(expiry_date < CURDATE()), 0) AS expired
                FROM clinic_medications WHERE is_archived = 0";
        $row = $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC);
        return array_map('intval', $row);
    }

    /* ---------- Create / Update / Archive ---------- */

    public function validate(array $d): array {
        $errors = [];
        if (trim($d['medicine_name'] ?? '') === '') {
            $errors[] = 'Medicine name is required.';
        }
        if (!is_numeric($d['quantity'] ?? null) || (int) $d['quantity'] < 0) {
            $errors[] = 'Quantity must be 0 or more.';
        }
        if (!is_numeric($d['reorder_level'] ?? null) || (int) $d['reorder_level'] < 0) {
            $errors[] = 'Reorder level must be 0 or more.';
        }
        if (!in_array($d['dosage_form'] ?? '', self::FORMS, true)) {
            $errors[] = 'Choose a valid dosage form.';
        }
        if (!empty($d['expiry_date']) && !strtotime($d['expiry_date'])) {
            $errors[] = 'Expiry date is not valid.';
        }
        return $errors;
    }

    private function bindData(PDOStatement $stmt, array $d): void {
        $nullable = fn($k) => (isset($d[$k]) && trim($d[$k]) !== '') ? trim($d[$k]) : null;

        $stmt->bindValue(':medicine_name', trim($d['medicine_name']));
        $stmt->bindValue(':generic_name',  $nullable('generic_name'));
        $stmt->bindValue(':category',      $nullable('category'));
        $stmt->bindValue(':dosage_form',   $d['dosage_form']);
        $stmt->bindValue(':strength',      $nullable('strength'));
        $stmt->bindValue(':unit',          trim($d['unit'] ?? '') ?: 'pcs');
        $stmt->bindValue(':quantity',      (int) $d['quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':reorder_level', (int) $d['reorder_level'], PDO::PARAM_INT);
        $stmt->bindValue(':batch_no',      $nullable('batch_no'));
        $stmt->bindValue(':expiry_date',   $nullable('expiry_date'));
        $stmt->bindValue(':supplier',      $nullable('supplier'));
        $stmt->bindValue(':notes',         $nullable('notes'));
    }

    public function create(array $d): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO clinic_medications
                (medicine_name, generic_name, category, dosage_form, strength, unit,
                 quantity, reorder_level, batch_no, expiry_date, supplier, notes)
             VALUES
                (:medicine_name, :generic_name, :category, :dosage_form, :strength, :unit,
                 :quantity, :reorder_level, :batch_no, :expiry_date, :supplier, :notes)"
        );
        $this->bindData($stmt, $d);
        return $stmt->execute();
    }

    public function update(int $id, array $d): bool {
        $stmt = $this->conn->prepare(
            "UPDATE clinic_medications SET
                medicine_name = :medicine_name, generic_name = :generic_name, category = :category,
                dosage_form = :dosage_form, strength = :strength, unit = :unit,
                quantity = :quantity, reorder_level = :reorder_level, batch_no = :batch_no,
                expiry_date = :expiry_date, supplier = :supplier, notes = :notes
             WHERE medication_id = :id AND is_archived = 0"
        );
        $this->bindData($stmt, $d);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function archive(int $id): bool {
        $stmt = $this->conn->prepare("UPDATE clinic_medications SET is_archived = 1 WHERE medication_id = ?");
        return $stmt->execute([$id]);
    }

    /* ---------- Stock status (for the badge) ---------- */

    public static function getStatus(array $row): array {
        $days = null;
        if (!empty($row['expiry_date'])) {
            $days = (int) (new DateTime('today'))->diff(new DateTime($row['expiry_date']))->format('%r%a');
            if ($days < 0) return ['Expired', 'badge-danger'];
        }
        if ((int) $row['quantity'] === 0)                          return ['Out of stock', 'badge-danger'];
        if ((int) $row['quantity'] <= (int) $row['reorder_level']) return ['Low stock', 'badge-warning'];
        if ($days !== null && $days <= 30)                         return ['Expiring soon', 'badge-warning'];
        return ['In stock', 'badge-success'];
    }
}