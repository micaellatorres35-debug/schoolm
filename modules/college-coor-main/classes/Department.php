<?php
include_once __DIR__ . '/../../../database/db.php';

/**
 * Expected, user-correctable problems (validation, duplicates, blocked status changes).
 * Shared with Unit.php; the controller turns these into clean JSON responses.
 */
if (!class_exists('OrgException')) {
    class OrgException extends \RuntimeException {
        public $status;
        public $errors;

        public function __construct($message, $status = 422, array $errors = []) {
            parent::__construct($message);
            $this->status = (int) $status;
            $this->errors = $errors;
        }
    }
}

class Department {
    // "Assigned employees" = not archived and Active/Probationary (alias must be `e`)
    const EMP_ACTIVE_SQL = "(e.is_archived = 0 OR e.is_archived IS NULL) AND (e.employment_status IS NULL OR e.employment_status IN ('Active','Probationary'))";
    const NAME_MAX = 100;
    const DESC_MAX = 1000;

    private $conn;
    private $department_id;
    private $department_name;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ── Existing methods (unchanged) ───────────────────────── */

    public function getAllDepartments() {
        $stmt = $this->conn->prepare("SELECT department_id, department_name FROM em_departments");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDepartmentEmployees($department_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM em_employees WHERE department_id = :department_id");
        $stmt->execute([':department_id' => $department_id]);
        return $stmt->fetchColumn();
    }

    public function getDepartmentsWithDetails() {
        $sql = "SELECT 
                    d.department_id,
                    d.department_name,
                    CONCAT(e.first_name, ' ', e.last_name) AS department_head_name,
                    COUNT(emp.employee_id) AS employee_count
                    
                FROM em_departments d
                LEFT JOIN em_employees e 
                    ON d.department_head = e.employee_id
                LEFT JOIN em_employees emp 
                    ON emp.department_id = d.department_id
                GROUP BY d.department_id, d.department_name, e.first_name, e.last_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDepartment($department_name) {
        $stmt = $this->conn->prepare("INSERT INTO em_departments (department_name) VALUES (:department_name)");
        return $stmt->execute([':department_name' => $department_name]);
    }

    /* ── Overview ───────────────────────────────────────────── */

    private function selectSql() {
        return "SELECT d.department_id, d.department_code, d.department_name, d.description,
                       COALESCE(d.status, 'Active') AS status,
                       TRIM(CONCAT_WS(' ', h.first_name, h.last_name)) AS head_name,
                       (SELECT COUNT(*) FROM em_units u WHERE u.department_id = d.department_id) AS unit_count,
                       (SELECT COUNT(*) FROM em_employees e
                         WHERE e.department_id = d.department_id AND " . self::EMP_ACTIVE_SQL . ") AS employee_count
                FROM em_departments d
                LEFT JOIN em_employees h ON h.employee_id = d.department_head ";
    }

    private function castCounts(array $row) {
        $row['unit_count']     = (int) $row['unit_count'];
        $row['employee_count'] = (int) $row['employee_count'];
        return $row;
    }

    // A department matches if its own name matches or any of its units' names do
    public function getDepartmentOverview(array $f) {
        $where  = [];
        $params = [];

        $search = trim((string) ($f['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $where[] = "(d.department_name LIKE :q1
                         OR EXISTS (SELECT 1 FROM em_units u2
                                    WHERE u2.department_id = d.department_id AND u2.unit_name LIKE :q2))";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }

        $status = (string) ($f['status'] ?? '');
        if ($status === 'active' || $status === 'inactive') {
            $where[] = "COALESCE(d.status, 'Active') = :st";
            $params[':st'] = $status === 'active' ? 'Active' : 'Inactive';
        }

        $sql = $this->selectSql() . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY d.department_name';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return array_map([$this, 'castCounts'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Unit names that matched the search, per department (so the card can say why it matched)
    public function getMatchingUnits(array $departmentIds, $search) {
        $search = trim((string) $search);
        if ($search === '' || !$departmentIds) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($departmentIds), '?'));
        $stmt = $this->conn->prepare(
            "SELECT department_id, unit_name FROM em_units
             WHERE department_id IN ($marks) AND unit_name LIKE ?
             ORDER BY unit_name"
        );
        $stmt->execute(array_merge(array_map('intval', $departmentIds), ['%' . addcslashes($search, '%_\\') . '%']));

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $out[(int) $r['department_id']][] = $r['unit_name'];
        }
        return $out;
    }

    public function getOrgStats() {
        $row = $this->conn->query(
            "SELECT
                (SELECT COUNT(*) FROM em_departments) AS departments,
                (SELECT COUNT(*) FROM em_departments WHERE COALESCE(status,'Active') = 'Active') AS active_departments,
                (SELECT COUNT(*) FROM em_units) AS units,
                (SELECT COUNT(*) FROM em_units WHERE COALESCE(status,'Active') = 'Active') AS active_units,
                (SELECT COUNT(*) FROM em_employees e WHERE " . self::EMP_ACTIVE_SQL . ") AS employees"
        )->fetch(PDO::FETCH_ASSOC);

        return array_map('intval', $row);
    }

    public function getDepartment($id) {
        $stmt = $this->conn->prepare($this->selectSql() . ' WHERE d.department_id = :id');
        $stmt->execute([':id' => (int) $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->castCounts($row) : null;
    }

    // For the parent-department dropdown on the unit form
    public function getDepartmentOptions() {
        return $this->conn->query(
            "SELECT department_id, department_name, COALESCE(status,'Active') AS status
             FROM em_departments ORDER BY department_name"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Create / update / status ───────────────────────────── */

    private function validateInput($name, $description, $status, $excludeId) {
        $errors = [];

        $name = trim((string) $name);
        if ($name === '') {
            $errors['name'] = 'Department name is required.';
        } elseif (mb_strlen($name) > self::NAME_MAX) {
            $errors['name'] = 'Department name must be ' . self::NAME_MAX . ' characters or fewer.';
        } else {
            $s = $this->conn->prepare(
                'SELECT department_id FROM em_departments
                 WHERE LOWER(department_name) = LOWER(:n) AND department_id <> :id LIMIT 1'
            );
            $s->execute([':n' => $name, ':id' => (int) $excludeId]);
            if ($s->fetchColumn()) {
                $errors['name'] = 'A department with this name already exists.';
            }
        }

        $description = trim((string) $description);
        if (mb_strlen($description) > self::DESC_MAX) {
            $errors['description'] = 'Description must be ' . self::DESC_MAX . ' characters or fewer.';
        }
        if ($description === '') {
            $description = null;
        }

        if (!in_array($status, ['Active', 'Inactive'], true)) {
            $errors['status'] = 'Choose a valid status.';
        }

        if ($errors) {
            throw new OrgException('Please correct the highlighted fields.', 422, $errors);
        }
        return [$name, $description, $status];
    }

    private function plural($n, $word) {
        return $n . ' ' . $word . ($n === 1 ? '' : 's');
    }

    // A department can't go inactive while it still has working units or staff
    private function assertCanDeactivate($id) {
        $u = $this->conn->prepare(
            "SELECT COUNT(*) FROM em_units WHERE department_id = :id AND COALESCE(status,'Active') = 'Active'"
        );
        $u->execute([':id' => $id]);
        $units = (int) $u->fetchColumn();

        $e = $this->conn->prepare(
            'SELECT COUNT(*) FROM em_employees e WHERE e.department_id = :id AND ' . self::EMP_ACTIVE_SQL
        );
        $e->execute([':id' => $id]);
        $emps = (int) $e->fetchColumn();

        if ($units > 0 || $emps > 0) {
            $msg = 'This department still has ' . $this->plural($units, 'active unit')
                 . ' and ' . $this->plural($emps, 'assigned employee')
                 . '. Reassign or deactivate them first.';
            throw new OrgException($msg, 409, ['status' => $msg]);
        }
    }

    public function createDepartment($name, $description, $status) {
        list($name, $description, $status) = $this->validateInput($name, $description, $status, 0);

        $stmt = $this->conn->prepare(
            'INSERT INTO em_departments (department_name, description, status) VALUES (:n, :d, :s)'
        );
        $stmt->execute([':n' => $name, ':d' => $description, ':s' => $status]);

        return ['id' => (int) $this->conn->lastInsertId(), 'name' => $name];
    }

    public function updateDepartment($id, $name, $description, $status) {
        $id = (int) $id;

        $this->conn->beginTransaction();
        try {
            $s = $this->conn->prepare(
                "SELECT department_id, COALESCE(status,'Active') AS status
                 FROM em_departments WHERE department_id = :id FOR UPDATE"
            );
            $s->execute([':id' => $id]);
            $cur = $s->fetch(PDO::FETCH_ASSOC);
            if (!$cur) {
                throw new OrgException('Department not found.', 404);
            }

            list($name, $description, $status) = $this->validateInput($name, $description, $status, $id);

            if ($status === 'Inactive' && $cur['status'] !== 'Inactive') {
                $this->assertCanDeactivate($id);
            }

            $u = $this->conn->prepare(
                'UPDATE em_departments SET department_name = :n, description = :d, status = :s WHERE department_id = :id'
            );
            $u->execute([':n' => $name, ':d' => $description, ':s' => $status, ':id' => $id]);

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        return ['id' => $id, 'name' => $name];
    }

    public function setDepartmentStatus($id, $status) {
        $id = (int) $id;
        if (!in_array($status, ['Active', 'Inactive'], true)) {
            throw new OrgException('Choose a valid status.', 422, ['status' => 'Choose a valid status.']);
        }

        $this->conn->beginTransaction();
        try {
            $s = $this->conn->prepare(
                "SELECT department_id, department_name, COALESCE(status,'Active') AS status
                 FROM em_departments WHERE department_id = :id FOR UPDATE"
            );
            $s->execute([':id' => $id]);
            $cur = $s->fetch(PDO::FETCH_ASSOC);
            if (!$cur) {
                throw new OrgException('Department not found.', 404);
            }
            if ($cur['status'] === $status) {
                throw new OrgException('This department is already ' . strtolower($status) . '.', 409);
            }
            if ($status === 'Inactive') {
                $this->assertCanDeactivate($id);
            }

            $u = $this->conn->prepare('UPDATE em_departments SET status = :s WHERE department_id = :id');
            $u->execute([':s' => $status, ':id' => $id]);

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        return ['id' => $id, 'name' => $cur['department_name']];
    }
}