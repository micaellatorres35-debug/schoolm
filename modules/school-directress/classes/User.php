<?php

include_once __DIR__ . '/../../../database/db.php';

/**
 * Thrown for expected, user-correctable problems (validation, duplicates, not found).
 * The controller turns these into clean JSON responses.
 */
if (!class_exists('AccountException')) {
    class AccountException extends \RuntimeException {
        public $status;
        public $errors;

        public function __construct($message, $status = 422, array $errors = []) {
            parent::__construct($message);
            $this->status = (int) $status;
            $this->errors = $errors;
        }
    }
}

class User {
    const STATUS_ACTIVE      = 'Active';
    const STATUS_DEACTIVATED = 'Deactivated';
    const MIN_PASSWORD       = 12;
    const MAX_PASSWORD       = 72;   // bcrypt ignores anything past 72 bytes
    const LOG_MODULE         = 'User Management';

    private $conn;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ── Existing: session user (unchanged) ─────────────────── */

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

    /* ── Dropdown data for forms and filters ────────────────── */

    public function getFormOptions() {
        $roles = $this->conn->query(
            "SELECT role_id, role_name FROM em_roles WHERE status = 'Active' ORDER BY role_name"
        )->fetchAll(PDO::FETCH_ASSOC);

        $departments = $this->conn->query(
            "SELECT department_id, department_name FROM em_departments WHERE status = 'Active' ORDER BY department_name"
        )->fetchAll(PDO::FETCH_ASSOC);

        $units = $this->conn->query(
            "SELECT unit_id, department_id, unit_name FROM em_units WHERE status = 'Active' ORDER BY unit_name"
        )->fetchAll(PDO::FETCH_ASSOC);

        return ['roles' => $roles, 'departments' => $departments, 'units' => $units];
    }

    /* ── Directory: list, count, stats ──────────────────────── */

    private function accountFrom() {
        return " FROM user_account ua
                 INNER JOIN em_employees e ON e.employee_id = ua.employee_id
                 LEFT JOIN em_roles r        ON r.role_id = e.role_id
                 LEFT JOIN em_departments d  ON d.department_id = e.department_id
                 LEFT JOIN em_units un       ON un.unit_id = e.unit_id
                 LEFT JOIN em_positions p    ON p.position_id = e.position_id ";
    }

    private function buildAccountFilter(array $f) {
        $where  = [];
        $params = [];

        $search = trim((string) ($f['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $where[] = "(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) LIKE :q1
                         OR e.employee_code LIKE :q2
                         OR e.email LIKE :q3
                         OR r.role_name LIKE :q4)";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        $role = (int) ($f['role'] ?? 0);
        if ($role > 0) {
            $where[] = 'e.role_id = :role';
            $params[':role'] = $role;
        }

        $dept = (int) ($f['department'] ?? 0);
        if ($dept > 0) {
            $where[] = 'e.department_id = :dept';
            $params[':dept'] = $dept;
        }

        $unit = (int) ($f['unit'] ?? 0);
        if ($unit > 0) {
            $where[] = 'e.unit_id = :unit';
            $params[':unit'] = $unit;
        }

        $status = (string) ($f['status'] ?? '');
        if ($status === 'active') {
            $where[] = 'ua.status = :status';
            $params[':status'] = self::STATUS_ACTIVE;
        } elseif ($status === 'deactivated') {
            $where[] = 'ua.status = :status';
            $params[':status'] = self::STATUS_DEACTIVATED;
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    public function countAccounts(array $filters) {
        list($whereSql, $params) = $this->buildAccountFilter($filters);
        $stmt = $this->conn->prepare('SELECT COUNT(*)' . $this->accountFrom() . $whereSql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // $limit = null returns every matching row (used by export)
    public function getAccounts(array $filters, $limit = null, $offset = 0) {
        list($whereSql, $params) = $this->buildAccountFilter($filters);

        $sql = "SELECT ua.user_id,
                       ua.status AS account_status,
                       ua.last_login,
                       (ua.locked_until IS NOT NULL AND ua.locked_until > NOW()) AS is_locked,
                       e.employee_id, e.employee_code,
                       e.first_name, e.middle_name, e.last_name, e.suffix,
                       e.email, e.role_id, r.role_name,
                       e.department_id, d.department_name,
                       e.unit_id, un.unit_name,
                       p.position_name"
              . $this->accountFrom() . $whereSql
              . ' ORDER BY e.last_name, e.first_name, ua.user_id';

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

    public function getAccountStats() {
        $row = $this->conn->query(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'Active') AS active,
                    SUM(status = 'Deactivated') AS deactivated
             FROM user_account"
        )->fetch(PDO::FETCH_ASSOC);

        return [
            'total'       => (int) ($row['total'] ?? 0),
            'active'      => (int) ($row['active'] ?? 0),
            'deactivated' => (int) ($row['deactivated'] ?? 0),
        ];
    }

    /* ── Details + activity ─────────────────────────────────── */

    public function getAccountDetails($userId) {
        $sql = "SELECT ua.user_id,
                       ua.status AS account_status,
                       ua.created_at AS account_created_at,
                       ua.last_login,
                       ua.password_changed_at,
                       ua.failed_login_attempts,
                       ua.locked_until,
                       (ua.locked_until IS NOT NULL AND ua.locked_until > NOW()) AS is_locked,
                       ua.deactivated_at,
                       ua.deactivation_reason,
                       TRIM(CONCAT_WS(' ', dbe.first_name, dbe.last_name)) AS deactivated_by_name,
                       e.employee_id, e.employee_code,
                       e.first_name, e.middle_name, e.last_name, e.suffix,
                       e.email, e.mobile_no, e.phone_no, e.employment_status,
                       e.role_id, r.role_name,
                       e.department_id, d.department_name,
                       e.unit_id, un.unit_name,
                       p.position_name,
                       (SELECT MAX(l.created_at) FROM sd_activity_logs l WHERE l.user_id = ua.user_id) AS last_activity_at
                FROM user_account ua
                INNER JOIN em_employees e ON e.employee_id = ua.employee_id
                LEFT JOIN em_employees dbe ON dbe.employee_id = ua.deactivated_by
                LEFT JOIN em_roles r       ON r.role_id = e.role_id
                LEFT JOIN em_departments d ON d.department_id = e.department_id
                LEFT JOIN em_units un      ON un.unit_id = e.unit_id
                LEFT JOIN em_positions p   ON p.position_id = e.position_id
                WHERE ua.user_id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => (int) $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Activity performed BY the account, plus user-management actions done TO it.
    public function getActivity($userId, $limit = 15, $offset = 0) {
        $sql = "SELECT l.log_id, l.action, l.module, l.description, l.record_id, l.created_at,
                       l.user_id AS actor_user_id,
                       TRIM(CONCAT_WS(' ', ae.first_name, ae.last_name)) AS actor_name
                FROM sd_activity_logs l
                LEFT JOIN user_account au ON au.user_id = l.user_id
                LEFT JOIN em_employees ae ON ae.employee_id = au.employee_id
                WHERE l.user_id = :uid1
                   OR (l.module = :mod AND l.record_id = :uid2)
                ORDER BY l.created_at DESC, l.log_id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':uid1', (int) $userId, PDO::PARAM_INT);
        $stmt->bindValue(':mod', self::LOG_MODULE);
        $stmt->bindValue(':uid2', (string) (int) $userId);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActivity($userId) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM sd_activity_logs l
             WHERE l.user_id = :uid1 OR (l.module = :mod AND l.record_id = :uid2)"
        );
        $stmt->execute([
            ':uid1' => (int) $userId,
            ':mod'  => self::LOG_MODULE,
            ':uid2' => (string) (int) $userId,
        ]);
        return (int) $stmt->fetchColumn();
    }

    // Audit log write. Best-effort: a logging failure never blocks the account change itself.
    public function logActivity($actorEmployeeId, $action, $description, $recordId = null) {
        try {
            $stmt = $this->conn->prepare('SELECT user_id FROM user_account WHERE employee_id = :e LIMIT 1');
            $stmt->execute([':e' => (int) $actorEmployeeId]);
            $actorUserId = $stmt->fetchColumn();
            if (!$actorUserId) {
                return;
            }

            $ins = $this->conn->prepare(
                'INSERT INTO sd_activity_logs (user_id, action, module, description, record_id, created_at)
                 VALUES (:u, :a, :m, :d, :r, NOW())'
            );
            $ins->execute([
                ':u' => (int) $actorUserId,
                ':a' => $action,
                ':m' => self::LOG_MODULE,
                ':d' => mb_substr($description, 0, 500),
                ':r' => $recordId,
            ]);
        } catch (\Throwable $e) {
            error_log('logActivity failed: ' . $e->getMessage());
        }
    }

    /* ── Employee picker for the Create modal ───────────────── */

    public function lookupEmployees($query, $limit = 20) {
        $like = '%' . addcslashes(trim((string) $query), '%_\\') . '%';

        $sql = "SELECT e.employee_id, e.employee_code,
                       TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) AS full_name,
                       e.email, e.role_id, e.department_id, e.unit_id, e.employment_status,
                       d.department_name,
                       (ua.user_id IS NOT NULL OR e.user_id IS NOT NULL) AS has_account
                FROM em_employees e
                LEFT JOIN user_account ua  ON ua.employee_id = e.employee_id
                LEFT JOIN em_departments d ON d.department_id = e.department_id
                WHERE (e.is_archived = 0 OR e.is_archived IS NULL)
                  AND (CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) LIKE :q1
                       OR e.employee_code LIKE :q2
                       OR e.email LIKE :q3)
                ORDER BY has_account ASC, e.last_name, e.first_name
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':q1', $like);
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':q3', $like);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['has_account'] = (int) $r['has_account'];
            $r['eligible']    = in_array($r['employment_status'], ['Active', 'Probationary'], true) ? 1 : 0;
        }
        unset($r);
        return $rows;
    }

    /* ── Validation helpers ─────────────────────────────────── */

    private function validatePassword($password, $required) {
        $password = (string) $password;
        if ($password === '' && !$required) {
            return;
        }
        $len = strlen($password);
        if ($len < self::MIN_PASSWORD || $len > self::MAX_PASSWORD) {
            throw new AccountException(
                'Please correct the highlighted fields.',
                422,
                ['password' => 'Password must be between ' . self::MIN_PASSWORD . ' and ' . self::MAX_PASSWORD . ' characters.']
            );
        }
    }

    // $keep holds the values the record already has, so an inactive role/department/unit
    // or an unchanged email is not rejected just because it is already assigned.
    private function validateAssignment($roleId, $deptId, $unitId, $email, $employeeId, array $keep = []) {
        $errors = [];

        if ($roleId <= 0) {
            $errors['role_id'] = 'Select a role.';
        } else {
            $s = $this->conn->prepare('SELECT status FROM em_roles WHERE role_id = :id');
            $s->execute([':id' => $roleId]);
            $st = $s->fetchColumn();
            if ($st === false) {
                $errors['role_id'] = 'The selected role does not exist.';
            } elseif ($st !== 'Active' && $roleId !== (int) ($keep['role_id'] ?? 0)) {
                $errors['role_id'] = 'The selected role is inactive.';
            }
        }

        if ($deptId <= 0) {
            $errors['department_id'] = 'Select a department.';
        } else {
            $s = $this->conn->prepare('SELECT status FROM em_departments WHERE department_id = :id');
            $s->execute([':id' => $deptId]);
            $st = $s->fetchColumn();
            if ($st === false) {
                $errors['department_id'] = 'The selected department does not exist.';
            } elseif ($st !== 'Active' && $deptId !== (int) ($keep['department_id'] ?? 0)) {
                $errors['department_id'] = 'The selected department is inactive.';
            }
        }

        if ($unitId > 0 && !isset($errors['department_id'])) {
            $s = $this->conn->prepare('SELECT department_id, status FROM em_units WHERE unit_id = :id');
            $s->execute([':id' => $unitId]);
            $u = $s->fetch(PDO::FETCH_ASSOC);
            if (!$u) {
                $errors['unit_id'] = 'The selected unit does not exist.';
            } elseif ((int) $u['department_id'] !== $deptId) {
                $errors['unit_id'] = 'The selected unit does not belong to that department.';
            } elseif ($u['status'] !== 'Active' && $unitId !== (int) ($keep['unit_id'] ?? 0)) {
                $errors['unit_id'] = 'The selected unit is inactive.';
            }
        }

        $email = trim((string) $email);
        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address (100 characters max).';
        } elseif (strcasecmp($email, (string) ($keep['email'] ?? '')) !== 0) {
            $s = $this->conn->prepare(
                'SELECT employee_id FROM em_employees WHERE LOWER(email) = LOWER(:e) AND employee_id <> :id LIMIT 1'
            );
            $s->execute([':e' => $email, ':id' => $employeeId]);
            if ($s->fetchColumn()) {
                $errors['email'] = 'This email address is already used by another employee.';
            }
        }

        if ($errors) {
            throw new AccountException('Please correct the highlighted fields.', 422, $errors);
        }
        return $email;
    }

    private function fullName(array $row) {
        return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    }

    /* ── Create ─────────────────────────────────────────────── */

    public function createAccount($employeeId, $roleId, $deptId, $unitId, $email, $password, $actorEmployeeId) {
        $employeeId = (int) $employeeId;
        $roleId     = (int) $roleId;
        $deptId     = (int) $deptId;
        $unitId     = (int) $unitId;

        if ($employeeId <= 0) {
            throw new AccountException('Select an employee.', 422, ['employee_id' => 'Select an employee.']);
        }

        $duplicate = new AccountException(
            'This employee already has a user account.',
            409,
            ['employee_id' => 'This employee already has a user account.']
        );

        $this->conn->beginTransaction();
        try {
            $s = $this->conn->prepare(
                'SELECT employee_id, employee_code, user_id, first_name, last_name, email, employment_status, is_archived
                 FROM em_employees WHERE employee_id = :id FOR UPDATE'
            );
            $s->execute([':id' => $employeeId]);
            $emp = $s->fetch(PDO::FETCH_ASSOC);

            if (!$emp) {
                throw new AccountException('Employee not found.', 404, ['employee_id' => 'Employee not found.']);
            }
            if (!empty($emp['is_archived']) || !in_array($emp['employment_status'], ['Active', 'Probationary'], true)) {
                throw new AccountException(
                    'Only active or probationary employees can receive an account.',
                    422,
                    ['employee_id' => 'This employee is not eligible for an account.']
                );
            }

            // Already-has-an-account check, both directions of the link
            $s = $this->conn->prepare('SELECT user_id FROM user_account WHERE employee_id = :id LIMIT 1 FOR UPDATE');
            $s->execute([':id' => $employeeId]);
            if ($s->fetchColumn() || !empty($emp['user_id'])) {
                throw $duplicate;
            }

            $email = $this->validateAssignment($roleId, $deptId, $unitId, $email, $employeeId, ['email' => $emp['email']]);
            $this->validatePassword($password, true);

            $u = $this->conn->prepare(
                'UPDATE em_employees SET role_id = :r, department_id = :d, unit_id = :u, email = :e WHERE employee_id = :id'
            );
            $u->execute([
                ':r'  => $roleId,
                ':d'  => $deptId,
                ':u'  => $unitId > 0 ? $unitId : null,
                ':e'  => $email,
                ':id' => $employeeId,
            ]);

            $i = $this->conn->prepare(
                "INSERT INTO user_account (employee_id, password, status, password_changed_at)
                 VALUES (:e, :p, 'Active', NOW())"
            );
            $i->execute([':e' => $employeeId, ':p' => password_hash((string) $password, PASSWORD_DEFAULT)]);
            $userId = (int) $this->conn->lastInsertId();

            $l = $this->conn->prepare('UPDATE em_employees SET user_id = :u WHERE employee_id = :id');
            $l->execute([':u' => $userId, ':id' => $employeeId]);

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            // Two admins racing: the unique key on user_account.employee_id is the final guard
            if ($e instanceof \PDOException && (int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw $duplicate;
            }
            throw $e;
        }

        $name = $this->fullName($emp);
        $this->logActivity(
            $actorEmployeeId,
            'Account created',
            'Created user account for ' . $name . ' (' . $emp['employee_code'] . ')',
            $userId
        );

        return ['user_id' => $userId, 'name' => $name];
    }

    /* ── Update ─────────────────────────────────────────────── */

    public function updateAccount($userId, $roleId, $deptId, $unitId, $email, $password, $actorEmployeeId) {
        $userId = (int) $userId;
        $roleId = (int) $roleId;
        $deptId = (int) $deptId;
        $unitId = (int) $unitId;
        $password = (string) $password;

        $changes = [];
        $oldRole = null;

        $this->conn->beginTransaction();
        try {
            $s = $this->conn->prepare(
                'SELECT ua.user_id, ua.employee_id,
                        e.employee_code, e.first_name, e.last_name, e.email,
                        e.role_id, e.department_id, e.unit_id, r.role_name
                 FROM user_account ua
                 INNER JOIN em_employees e ON e.employee_id = ua.employee_id
                 LEFT JOIN em_roles r ON r.role_id = e.role_id
                 WHERE ua.user_id = :id FOR UPDATE'
            );
            $s->execute([':id' => $userId]);
            $cur = $s->fetch(PDO::FETCH_ASSOC);

            if (!$cur) {
                throw new AccountException('Account not found.', 404);
            }

            if ((int) $cur['employee_id'] === (int) $actorEmployeeId && $roleId !== (int) $cur['role_id']) {
                throw new AccountException(
                    'You cannot change your own role.',
                    422,
                    ['role_id' => 'You cannot change your own role.']
                );
            }

            $email = $this->validateAssignment($roleId, $deptId, $unitId, $email, (int) $cur['employee_id'], $cur);
            $this->validatePassword($password, false);

            $roleChanged = $roleId !== (int) $cur['role_id'];
            if ($roleChanged) {
                $oldRole = $cur['role_name'];
            }
            if ($deptId !== (int) $cur['department_id']) { $changes[] = 'department'; }
            if ($unitId !== (int) $cur['unit_id'])        { $changes[] = 'unit'; }
            if (strcasecmp($email, (string) $cur['email']) !== 0) { $changes[] = 'email'; }

            if ($roleChanged || $changes) {
                $u = $this->conn->prepare(
                    'UPDATE em_employees SET role_id = :r, department_id = :d, unit_id = :u, email = :e WHERE employee_id = :id'
                );
                $u->execute([
                    ':r'  => $roleId,
                    ':d'  => $deptId,
                    ':u'  => $unitId > 0 ? $unitId : null,
                    ':e'  => $email,
                    ':id' => (int) $cur['employee_id'],
                ]);
            }

            $passwordReset = ($password !== '');
            if ($passwordReset) {
                // A reset also clears any lockout so the user can sign in with the new password
                $p = $this->conn->prepare(
                    'UPDATE user_account
                     SET password = :p, password_changed_at = NOW(), failed_login_attempts = 0, locked_until = NULL
                     WHERE user_id = :id'
                );
                $p->execute([':p' => password_hash($password, PASSWORD_DEFAULT), ':id' => $userId]);
            }

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        $name = $this->fullName($cur) . ' (' . $cur['employee_code'] . ')';

        if ($roleChanged) {
            $n = $this->conn->prepare('SELECT role_name FROM em_roles WHERE role_id = :id');
            $n->execute([':id' => $roleId]);
            $this->logActivity(
                $actorEmployeeId,
                'Role changed',
                'Role for ' . $name . ' changed from ' . ($oldRole ?: 'none') . ' to ' . $n->fetchColumn(),
                $userId
            );
        }
        if ($changes) {
            $this->logActivity($actorEmployeeId, 'Account updated', 'Updated ' . implode(', ', $changes) . ' for ' . $name, $userId);
        }
        if ($passwordReset) {
            $this->logActivity($actorEmployeeId, 'Password reset', 'Password reset for ' . $name, $userId);
        }

        return [
            'changed' => ($roleChanged || $changes || $passwordReset),
            'name'    => $this->fullName($cur),
        ];
    }

    /* ── Deactivate / reactivate / unlock ───────────────────── */

    public function setAccountStatus($userId, $deactivate, $reason, $actorEmployeeId) {
        $userId = (int) $userId;
        $reason = $reason === null ? null : mb_substr(trim((string) $reason), 0, 255);
        if ($reason === '') {
            $reason = null;
        }

        $this->conn->beginTransaction();
        try {
            $s = $this->conn->prepare(
                'SELECT ua.user_id, ua.employee_id, ua.status, e.first_name, e.last_name, e.employee_code
                 FROM user_account ua
                 INNER JOIN em_employees e ON e.employee_id = ua.employee_id
                 WHERE ua.user_id = :id FOR UPDATE'
            );
            $s->execute([':id' => $userId]);
            $row = $s->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new AccountException('Account not found.', 404);
            }
            if ($deactivate && (int) $row['employee_id'] === (int) $actorEmployeeId) {
                throw new AccountException('You cannot deactivate your own account.', 422);
            }

            $target = $deactivate ? self::STATUS_DEACTIVATED : self::STATUS_ACTIVE;
            if ($row['status'] === $target) {
                throw new AccountException('This account is already ' . strtolower($target) . '.', 409);
            }

            if ($deactivate) {
                $u = $this->conn->prepare(
                    "UPDATE user_account
                     SET status = 'Deactivated', deactivated_at = NOW(), deactivated_by = :by, deactivation_reason = :reason
                     WHERE user_id = :id"
                );
                $u->execute([':by' => (int) $actorEmployeeId, ':reason' => $reason, ':id' => $userId]);
            } else {
                $u = $this->conn->prepare(
                    "UPDATE user_account
                     SET status = 'Active', deactivated_at = NULL, deactivated_by = NULL, deactivation_reason = NULL,
                         failed_login_attempts = 0, locked_until = NULL
                     WHERE user_id = :id"
                );
                $u->execute([':id' => $userId]);
            }

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        $name = $this->fullName($row) . ' (' . $row['employee_code'] . ')';
        if ($deactivate) {
            $this->logActivity(
                $actorEmployeeId,
                'Account deactivated',
                'Deactivated account of ' . $name . ($reason ? '. Reason: ' . $reason : ''),
                $userId
            );
        } else {
            $this->logActivity($actorEmployeeId, 'Account reactivated', 'Reactivated account of ' . $name, $userId);
        }

        return ['name' => $this->fullName($row)];
    }

    public function unlockAccount($userId, $actorEmployeeId) {
        $userId = (int) $userId;

        $s = $this->conn->prepare(
            'SELECT e.first_name, e.last_name, e.employee_code
             FROM user_account ua
             INNER JOIN em_employees e ON e.employee_id = ua.employee_id
             WHERE ua.user_id = :id'
        );
        $s->execute([':id' => $userId]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new AccountException('Account not found.', 404);
        }

        $u = $this->conn->prepare('UPDATE user_account SET failed_login_attempts = 0, locked_until = NULL WHERE user_id = :id');
        $u->execute([':id' => $userId]);

        $this->logActivity(
            $actorEmployeeId,
            'Account unlocked',
            'Unlocked account of ' . $this->fullName($row) . ' (' . $row['employee_code'] . ')',
            $userId
        );

        return ['name' => $this->fullName($row)];
    }
}