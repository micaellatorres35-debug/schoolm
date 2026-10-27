<?php
include_once __DIR__ . '/../../../database/db.php';

Class Employee {
    private $conn;
    private $employeeid;
    private $firstname;
    private $lastname;
    private $middlename;
    private $department;
    private $position;
    private $status; 


    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getEmployees() {
    $sql = "SELECT 
                em.employee_id,
                em.employee_code,
                em.first_name,
                em.middle_name,
                em.last_name,
                em.email,
                em.mobile_no,
                em.hire_date,
                em.employment_type,
                em.employment_status,
                em.department_id,
                em.position_id,
                sd.department_name,
                sp.position_name
            FROM em_employees em
            LEFT JOIN em_departments sd 
                ON em.department_id = sd.department_id
            LEFT JOIN em_positions sp
                ON em.position_id = sp.position_id
            ORDER BY em.employee_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── AJAX list support (User Management) ─────────────── */

    private function buildEmployeeFilter(array $f) {
        $where  = [];
        $params = [];

        $search = trim((string) ($f['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $where[] = "(CONCAT_WS(' ', em.first_name, em.middle_name, em.last_name) LIKE :q1
                         OR sd.department_name LIKE :q2
                         OR sp.position_name LIKE :q3)";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $dept = trim((string) ($f['department'] ?? ''));
        if ($dept !== '') {
            $where[] = 'sd.department_name = :dept';
            $params[':dept'] = $dept;
        }

        $pos = trim((string) ($f['position'] ?? ''));
        if ($pos !== '') {
            $where[] = 'sp.position_name = :pos';
            $params[':pos'] = $pos;
        }

        $status = trim((string) ($f['status'] ?? ''));
        if ($status !== '') {
            $where[] = 'em.employment_status = :status';
            $params[':status'] = $status;
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    public function countEmployeesFiltered(array $filters) {
        list($whereSql, $params) = $this->buildEmployeeFilter($filters);

        $sql = "SELECT COUNT(*)
                FROM em_employees em
                LEFT JOIN em_departments sd ON em.department_id = sd.department_id
                LEFT JOIN em_positions sp   ON em.position_id = sp.position_id
                $whereSql";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // $limit = null returns every matching row (used by export)
    public function getEmployeesFiltered(array $filters, $limit = null, $offset = 0) {
        list($whereSql, $params) = $this->buildEmployeeFilter($filters);

        $sql = "SELECT 
                    em.employee_id,
                    em.employee_code,
                    em.first_name,
                    em.middle_name,
                    em.last_name,
                    em.email,
                    em.mobile_no,
                    em.hire_date,
                    em.employment_type,
                    em.employment_status,
                    sd.department_name,
                    u.unit_name,
                    sp.position_name
                FROM em_employees em
                LEFT JOIN em_departments sd ON em.department_id = sd.department_id
                LEFT JOIN em_units u ON em.unit_id = u.unit_id
                LEFT JOIN em_positions sp   ON em.position_id = sp.position_id
                $whereSql
                ORDER BY em.employee_id";

        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeId() {
        return $_SESSION['employee_id'] ?? null;
    }

    public function getEmployeeName() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $employeeId = $_SESSION['employee_id'] ?? null;

        if ($employeeId) {
            $sql = "SELECT first_name, last_name FROM em_employees WHERE employee_id = :employee_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':employee_id', $employeeId);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                return htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']);
            }
        }
        return 'Unknown User';
    }

    public function getEmployeeUnit () {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $employeeId = $_SESSION['employee_id'] ?? null;

        if ($employeeId) {
            $sql = "SELECT su.unit_name 
                    FROM em_employees em
                    LEFT JOIN em_units su ON em.unit_id = su.unit_id
                    WHERE em.employee_id = :employee_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':employee_id', $employeeId);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                return htmlspecialchars($employee['unit_name']);
            }
        }
        return 'Unknown Unit';
    }
    
    public function getEmployeePosition() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $employeeId = $_SESSION['employee_id'] ?? null;

        if ($employeeId) {
            $sql = "SELECT sp.position_name 
                    FROM em_employees em
                    LEFT JOIN em_positions sp ON em.position_id = sp.position_id
                    WHERE em.employee_id = :employee_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':employee_id', $employeeId);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                return htmlspecialchars($employee['position_name']);
            }
        }
        return 'Unknown Position';
    }
}