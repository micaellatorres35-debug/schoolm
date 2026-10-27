<?php

class Unit { 
    private $conn;
    private $unit_id;
    private $unit_name;
    private $department_id;
    private $unit_head_id;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getAllUnits() {
        $stmt = $this->conn->prepare("SELECT unit_id, unit_name FROM em_units");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnitsByDepartment($department_id) {
        $sql = "SELECT unit_id, unit_name 
                FROM em_units 
                WHERE department_id = :dept_id
                ORDER BY unit_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* All units with their department, status and head name — one query for the whole list */
    public function getAllUnitsWithHeads() {
        $sql = "SELECT 
                    u.unit_id,
                    u.unit_name,
                    u.department_id,
                    u.status,
                    CONCAT(e.first_name, ' ', e.last_name) AS unit_head_name
                FROM em_units u
                LEFT JOIN em_employees e 
                    ON u.unit_head = e.employee_id
                ORDER BY u.unit_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}