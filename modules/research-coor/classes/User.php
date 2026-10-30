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
            $sql = "SELECT e.employee_id, e.first_name, e.last_name, e.middle_name, e.department_id, r.role_name AS role, e.employment_status AS status FROM `em_employees` e LEFT JOIN `em_roles` r ON e.role_id = r.role_id WHERE e.employee_id = :employee_id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':employee_id' => $_SESSION['employee_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('userSession error: ' . $e->getMessage());
            return null;
        }
    }

    public function registerEmployee($employee_id, $department_id, $position_id, $password) {
        // Check if employee already has a user account
        $checkStmt = $this->conn->prepare("SELECT user_id FROM user_account WHERE employee_id = :employee_id");
        $checkStmt->execute([':employee_id' => $employee_id]);
        if ($checkStmt->fetchColumn()) {
            return ['success' => false, 'message' => 'Employee already has a user account.'];
        }

        // Get the role tied to this department (null if not found)
        $role_id = null;
        try {
            $roleStmt = $this->conn->prepare("SELECT role_id FROM em_roles WHERE department_id = :dept_id LIMIT 1");
            $roleStmt->execute([':dept_id' => $department_id]);
            $role_id = $roleStmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            error_log('registerEmployee role lookup error: ' . $e->getMessage());
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $this->conn->beginTransaction();

            // 1. INSERT into user_account
            $insertSql  = "INSERT INTO user_account (password, employee_id, created_at) VALUES (:password, :employee_id, NOW())";
            $insertStmt = $this->conn->prepare($insertSql);
            $insertStmt->execute([
                ':password'    => $hashed_password,
                ':employee_id' => $employee_id,
            ]);

            $user_id = $this->conn->lastInsertId();

            // 2. UPDATE em_employees
            $updateSql  = "UPDATE em_employees SET department_id = :department_id, position_id = :position_id, role_id = :role_id, user_id = :user_id, employment_status = 'Active' WHERE employee_id = :employee_id";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->execute([
                ':department_id' => $department_id,
                ':position_id'   => $position_id,
                ':role_id'       => $role_id,
                ':user_id'       => $user_id,
                ':employee_id'   => $employee_id,
            ]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Employee registered successfully.'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}