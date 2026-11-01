<?php
include_once __DIR__ . '/../../../database/db.php';

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

class Unit { 
    // "Assigned employees" = not archived and Active/Probationary (alias must be `e`)
    const EMP_ACTIVE_SQL = "(e.is_archived = 0 OR e.is_archived IS NULL) AND (e.employment_status IS NULL OR e.employment_status IN ('Active','Probationary'))";
    const NAME_MAX = 100;
    const DESC_MAX = 1000;

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
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ── Existing methods (unchanged) ───────────────────────── */

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

    /* ── Read ───────────────────────────────────────────────── */

    private function selectSql() {
        return "SELECT u.unit_id, u.department_id, u.unit_code, u.unit_name, u.description,
                       COALESCE(u.status, 'Active') AS status,
                       d.department_name,
                       COALESCE(d.status, 'Active') AS department_status,
                       TRIM(CONCAT_WS(' ', h.first_name, h.last_name)) AS head_name,
                       (SELECT COUNT(*) FROM em_employees e
                         WHERE e.unit_id = u.unit_id AND " . self::EMP_ACTIVE_SQL . ") AS employee_count
                FROM em_units u
                INNER JOIN em_departments d ON d.department_id = u.department_id
                LEFT JOIN em_employees h ON h.employee_id = u.unit_head ";
    }

    public function getUnitsDetailed($departmentId) {
        $stmt = $this->conn->prepare($this->selectSql() . ' WHERE u.department_id = :d ORDER BY u.unit_name');
        $stmt->execute([':d' => (int) $departmentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['employee_count'] = (int) $r['employee_count'];
        }
        unset($r);
        return $rows;
    }

    public function getUnit($id) {
        $stmt = $this->conn->prepare($this->selectSql() . ' WHERE u.unit_id = :id');
        $stmt->execute([':id' => (int) $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['employee_count'] = (int) $row['employee_count'];
        }
        return $row ?: null;
    }

    public function getUnitEmployees($id, $limit = 100) {
        $stmt = $this->conn->prepare(
            "SELECT e.employee_id, e.employee_code,
                    TRIM(CONCAT_WS(' ', e.first_name, e.last_name)) AS full_name,
                    p.position_name
             FROM em_employees e
             LEFT JOIN em_positions p ON p.position_id = e.position_id
             WHERE e.unit_id = :id AND " . self::EMP_ACTIVE_SQL . "
             ORDER BY e.last_name, e.first_name
             LIMIT :limit"
        );
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countAssigned($id) {
        $s = $this->conn->prepare('SELECT COUNT(*) FROM em_employees e WHERE e.unit_id = :id AND ' . self::EMP_ACTIVE_SQL);
        $s->execute([':id' => (int) $id]);
        return (int) $s->fetchColumn();
    }

    /* ── Validation ─────────────────────────────────────────── */

    // $requireActiveParent: creating, moving, or re-activating a unit needs an Active parent
    private function validateInput($departmentId, $name, $description, $status, $excludeId, $requireActiveParent) {
        $errors = [];
        $departmentId = (int) $departmentId;

        if ($departmentId <= 0) {
            $errors['department_id'] = 'Select a parent department.';
        } else {
            $s = $this->conn->prepare("SELECT COALESCE(status,'Active') FROM em_departments WHERE department_id = :id");
            $s->execute([':id' => $departmentId]);
            $deptStatus = $s->fetchColumn();
            if ($deptStatus === false) {
                $errors['department_id'] = 'The selected department does not exist.';
            } elseif ($requireActiveParent && $deptStatus !== 'Active') {
                $errors['department_id'] = 'The selected department is inactive.';
            }
        }

        $name = trim((string) $name);
        if ($name === '') {
            $errors['name'] = 'Unit name is required.';
        } elseif (mb_strlen($name) > self::NAME_MAX) {
            $errors['name'] = 'Unit name must be ' . self::NAME_MAX . ' characters or fewer.';
        } elseif (!isset($errors['department_id'])) {
            $s = $this->conn->prepare(
                'SELECT unit_id FROM em_units
                 WHERE department_id = :d AND LOWER(unit_name) = LOWER(:n) AND unit_id <> :id LIMIT 1'
            );
            $s->execute([':d' => $departmentId, ':n' => $name, ':id' => (int) $excludeId]);
            if ($s->fetchColumn()) {
                $errors['name'] = 'This department already has a unit with that name.';
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
        return [$departmentId, $name, $description, $status];
    }

    private function assignedMessage($n) {
        return 'This unit still has ' . $n . ' assigned employee' . ($n === 1 ? '' : 's') . '. Reassign them first.';
    }

    /* ── Create / update / status ───────────────────────────── */

    public function createUnit($departmentId, $name, $description, $status) {
        list($departmentId, $name, $description, $status) =
            $this->validateInput($departmentId, $name, $description, $status, 0, true);

        $stmt = $this->conn->prepare(
            'INSERT INTO em_units (department_id, unit_name, description, status) VALUES (:d, :n, :ds, :s)'
        );
        $stmt->execute([':d' => $departmentId, ':n' => $name, ':ds' => $description, ':s' => $status]);

        return ['id' => (int) $this->conn->lastInsertId(), 'name' => $name, 'department_id' => $departmentId];
    }

    public function updateUnit($id, $departmentId, $name, $description, $status) {
        $id = (int) $id;

        $this->conn->beginTransaction();
        try {
            $s = $this->conn->prepare(
                "SELECT unit_id, department_id, COALESCE(status,'Active') AS status
                 FROM em_units WHERE unit_id = :id FOR UPDATE"
            );
            $s->execute([':id' => $id]);
            $cur = $s->fetch(PDO::FETCH_ASSOC);
            if (!$cur) {
                throw new OrgException('Unit not found.', 404);
            }

            $deptChanged = (int) $departmentId !== (int) $cur['department_id'];
            $reactivating = ($status === 'Active' && $cur['status'] === 'Inactive');

            list($departmentId, $name, $description, $status) =
                $this->validateInput($departmentId, $name, $description, $status, $id, $deptChanged || $reactivating);

            $assigned = $this->countAssigned($id);
            if ($deptChanged && $assigned > 0) {
                $msg = 'This unit has ' . $assigned . ' assigned employee' . ($assigned === 1 ? '' : 's')
                     . ', so it can\'t be moved to another department. Reassign them first.';
                throw new OrgException($msg, 409, ['department_id' => $msg]);
            }
            if ($status === 'Inactive' && $cur['status'] !== 'Inactive' && $assigned > 0) {
                $msg = $this->assignedMessage($assigned);
                throw new OrgException($msg, 409, ['status' => $msg]);
            }

            $u = $this->conn->prepare(
                'UPDATE em_units SET department_id = :d, unit_name = :n, description = :ds, status = :s WHERE unit_id = :id'
            );
            $u->execute([':d' => $departmentId, ':n' => $name, ':ds' => $description, ':s' => $status, ':id' => $id]);

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        return ['id' => $id, 'name' => $name, 'department_id' => $departmentId];
    }

    public function setUnitStatus($id, $status) {
        $id = (int) $id;
        if (!in_array($status, ['Active', 'Inactive'], true)) {
            throw new OrgException('Choose a valid status.', 422, ['status' => 'Choose a valid status.']);
        }

        $this->conn->beginTransaction();
        try {
            $s = $this->conn->prepare(
                "SELECT u.unit_id, u.unit_name, u.department_id, COALESCE(u.status,'Active') AS status,
                        COALESCE(d.status,'Active') AS department_status
                 FROM em_units u
                 INNER JOIN em_departments d ON d.department_id = u.department_id
                 WHERE u.unit_id = :id FOR UPDATE"
            );
            $s->execute([':id' => $id]);
            $cur = $s->fetch(PDO::FETCH_ASSOC);
            if (!$cur) {
                throw new OrgException('Unit not found.', 404);
            }
            if ($cur['status'] === $status) {
                throw new OrgException('This unit is already ' . strtolower($status) . '.', 409);
            }

            if ($status === 'Inactive') {
                $assigned = $this->countAssigned($id);
                if ($assigned > 0) {
                    $msg = $this->assignedMessage($assigned);
                    throw new OrgException($msg, 409, ['status' => $msg]);
                }
            } elseif ($cur['department_status'] !== 'Active') {
                throw new OrgException('Reactivate the parent department before reactivating this unit.', 409);
            }

            $u = $this->conn->prepare('UPDATE em_units SET status = :s WHERE unit_id = :id');
            $u->execute([':s' => $status, ':id' => $id]);

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        return ['id' => $id, 'name' => $cur['unit_name'], 'department_id' => (int) $cur['department_id']];
    }
}