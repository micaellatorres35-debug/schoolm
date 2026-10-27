<?php

include_once __DIR__ . '/../../../database/db.php';

class User {
    private $conn;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function userSession() {
        if (!isset($_SESSION['employee_id'])) {
            return null;
        }

        try {
            $sql = "SELECT
                e.employee_id,
                e.first_name,
                e.last_name,
                e.middle_name,
                e.department_id,
                d.department_name,
                e.unit_id,
                u.unit_name,
                r.role_name AS role,
                e.employment_status AS status
            FROM em_employees e
            LEFT JOIN em_units u
                ON e.unit_id = u.unit_id
            LEFT JOIN em_departments d
                ON e.department_id = d.department_id
            LEFT JOIN em_roles r
                ON e.role_id = r.role_id
            WHERE e.employee_id = :employee_id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':employee_id' => $_SESSION['employee_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('userSession error: ' . $e->getMessage());
            return null;
        }
    }

}