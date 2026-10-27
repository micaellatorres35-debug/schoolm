<?php
include_once __DIR__ . '/../../../database/db.php';
include_once __DIR__ . '/Medication.php';
include_once __DIR__ . '/student.php';

class ClinicDashboard {
    private $conn;
    private $medication;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->medication = new Medication($this->conn);
    }

    /** Patient Information module: counts the same records it lists. */
    public function getPatientCount(): ?int {
        try {
            $students = (new Student($this->conn))->getAllStudents();
            return is_array($students) ? count($students) : 0;
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Medication & Inventory module. */
    public function getMedicationSummary(): array {
        try {
            return $this->medication->getSummary();
        } catch (PDOException $e) {
            return ['total' => 0, 'low' => 0, 'out_of_stock' => 0, 'expiring' => 0, 'expired' => 0];
        }
    }

    public function getMedicationAlerts(int $limit = 6): array {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM clinic_medications
                 WHERE is_archived = 0
                   AND (quantity <= reorder_level OR expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
                 ORDER BY (quantity = 0) DESC, expiry_date ASC, quantity ASC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}