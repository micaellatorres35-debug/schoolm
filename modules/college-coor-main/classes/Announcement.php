<?php

include_once __DIR__ . '/../../../database/db.php';
require_once __DIR__ . '/FileStorage.php';

/** Expected, user-correctable problems (not found, wrong status, invalid input). */
if (!class_exists('AnnouncementException')) {
    class AnnouncementException extends \RuntimeException {
        public $status;

        public function __construct($message, $status = 422) {
            parent::__construct($message);
            $this->status = (int) $status;
        }
    }
}

/**
 * Announcements / official memos.
 *
 * Lifecycle (stored in cc_announcements.status):
 *   Draft      -> Scheduled | Published
 *   Scheduled  -> Published (automatic) | Draft (unschedule) | Withdrawn
 *   Published  -> Withdrawn | Archived
 *   Archived   -> Published (restore)
 *   Withdrawn  -> Draft (reopen)
 * "Expired" is not stored: it is Published + expires_at in the past.
 *
 * All "now" comparisons use PHP's clock (date()), so make sure the app sets
 * one timezone. Scheduled posts go live in processScheduled(), which runs on
 * feed/summary requests and from cron/process_scheduled.php.
 */
class Announcement {

    const TYPES = [
        'General Announcement', 'Official Memorandum', 'Urgent Notice',
        'Administrative Advisory', 'Academic Announcement', 'Event Announcement',
    ];
    const PRIORITIES      = ['Normal', 'Important', 'Urgent'];
    const EDITABLE        = ['Draft', 'Scheduled', 'Published'];
    const MAX_ATTACHMENTS = 10;
    const ATTACHMENT_DIR  = 'announcements/attachments';

    private $conn;
    private $uploadDir = __DIR__ . '/../uploads/announcements/'; // legacy header image

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database   = new Database();
            $this->conn = $database->getConnection();
        }
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /* ══════════════════════════════════════════════════════════
       Small helpers
       ══════════════════════════════════════════════════════════ */

    private function now() {
        return date('Y-m-d H:i:s');
    }

    private function fail($message, $status = 422) {
        throw new AnnouncementException($message, $status);
    }

    private function scalar($sql, array $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function fetchRow($id, $lock = false) {
        $stmt = $this->conn->prepare(
            'SELECT * FROM cc_announcements WHERE announcement_id = :id' . ($lock ? ' FOR UPDATE' : '')
        );
        $stmt->execute([':id' => (int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function titleOf($id) {
        $t = trim((string) $this->scalar('SELECT title FROM cc_announcements WHERE announcement_id = :id', [':id' => (int) $id]));
        return $t !== '' ? $t : 'Announcement #' . (int) $id;
    }

    private function parseDate($value, $label) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        if ($ts === false) {
            $this->fail('Invalid ' . $label . '.');
        }
        return date('Y-m-d H:i:s', $ts);
    }

    private function cleanIds($v) {
        if (!is_array($v)) {
            $v = ($v === null || $v === '') ? [] : [$v];
        }
        $out = [];
        foreach ($v as $x) {
            $i = filter_var($x, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($i !== false) {
                $out[$i] = $i;
            }
        }
        return array_values($out);
    }

    private function countExisting($table, $col, array $ids, $extra = '') {
        if (!$ids) {
            return 0;
        }
        $ph   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM `$table` WHERE `$col` IN ($ph)" . $extra);
        $stmt->execute(array_values($ids));
        return (int) $stmt->fetchColumn();
    }

    private function preview($text, $max = 200) {
        $text = trim(strip_tags((string) $text));
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '…' : $text;
    }

    /**
     * Audit trail in cc_activity_logs (module = 'communications').
     * Best-effort: a logging problem is written to the error log and never
     * blocks the announcement action itself.
     */
    private function log($employeeId, $action, $description, $announcementId) {
        try {
            if (!$employeeId) {
                return;
            }
            $uid = $this->scalar('SELECT user_id FROM cc_user_account WHERE employee_id = :e LIMIT 1', [':e' => (int) $employeeId]);
            if (!$uid) {
                return;
            }
            $stmt = $this->conn->prepare(
                "INSERT INTO cc_activity_logs (user_id, action, module, description, record_id, created_at)
                 VALUES (:u, :a, 'communications', :d, :r, :c)"
            );
            $stmt->execute([
                ':u' => (int) $uid,
                ':a' => $action,
                ':d' => mb_substr((string) $description, 0, 500),
                ':r' => (int) $announcementId,
                ':c' => $this->now(),
            ]);
        } catch (\Throwable $e) {
            error_log('Announcement audit log failed: ' . $e->getMessage());
        }
    }

    /* ══════════════════════════════════════════════════════════
       Visibility (audience enforcement lives here, in SQL)
       ══════════════════════════════════════════════════════════ */

    // $viewer = ['employee_id' => int, 'department_id' => int, 'unit_id' => int]
    private function visibilitySql(array $viewer) {
        $sql = "(a.audience_scope = 'Campus' OR EXISTS (
                    SELECT 1 FROM cc_announcement_audiences vau
                    WHERE vau.announcement_id = a.announcement_id
                      AND (vau.department_id = :v_dept OR vau.unit_id = :v_unit OR vau.employee_id = :v_emp)))";
        return [$sql, [
            ':v_dept' => (int) ($viewer['department_id'] ?? 0),
            ':v_unit' => (int) ($viewer['unit_id'] ?? 0),
            ':v_emp'  => (int) ($viewer['employee_id'] ?? 0),
        ]];
    }

    private function isVisibleTo($id, array $viewer) {
        list($vis, $vp) = $this->visibilitySql($viewer);
        $stmt = $this->conn->prepare(
            "SELECT 1 FROM cc_announcements a
             WHERE a.announcement_id = :id
               AND a.status = 'Published'
               AND (a.expires_at IS NULL OR a.expires_at > :i_now)
               AND $vis"
        );
        $stmt->execute([':id' => (int) $id, ':i_now' => $this->now()] + $vp);
        return (bool) $stmt->fetchColumn();
    }

    /* ══════════════════════════════════════════════════════════
       Legacy: simple post (old modal form) — publishes immediately
       to the entire campus. Kept so the current page keeps working.
       ══════════════════════════════════════════════════════════ */

    public function postAnnouncement($title, $message, $publishDate, $createdBy, $imageFile = null) {
        $imageFilename = null;

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType     = (new finfo(FILEINFO_MIME_TYPE))->file($imageFile['tmp_name']);

            if (!in_array($mimeType, $allowedTypes, true)) {
                return ['success' => false, 'message' => 'Invalid image type.'];
            }
            if ($imageFile['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'Image must be 5 MB or smaller.'];
            }

            $imageFilename = uniqid('ann_', true) . '.' . strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
            if (!move_uploaded_file($imageFile['tmp_name'], $this->uploadDir . $imageFilename)) {
                return ['success' => false, 'message' => 'The image could not be saved. Check the uploads folder permissions.'];
            }
        }

        $now = $this->now();
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO cc_announcements
                    (title, message, publish_date, created_by, image_file, status, audience_scope, published_at)
                 VALUES (:title, :message, :publish_date, :created_by, :image_file, 'Published', 'Campus', :published_at)"
            );
            $stmt->execute([
                ':title'        => $title,
                ':message'      => trim((string) $message) !== '' ? $message : null,
                ':publish_date' => $publishDate,
                ':created_by'   => $createdBy,
                ':image_file'   => $imageFilename,
                ':published_at' => $now,
            ]);
            $id = (int) $this->conn->lastInsertId();

            $this->deliver($id);
            $this->log($createdBy, 'published', 'Published "' . $this->titleOf($id) . '" to the entire campus.', $id);
            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($imageFilename) {
                @unlink($this->uploadDir . $imageFilename);
            }
            throw $e;
        }

        return $id;
    }

    /* ══════════════════════════════════════════════════════════
       Reading: legacy active list, feed, manage list, detail
       ══════════════════════════════════════════════════════════ */

    // Legacy list used by the existing view: every live (published, unexpired) announcement.
    public function getAnnouncement() {
        $stmt = $this->conn->prepare(
            "SELECT a.announcement_id, a.title, a.message, a.publish_date, a.image_file,
                    a.announcement_type, a.priority, a.expires_at,
                    CONCAT(e.first_name, ' ', e.last_name) AS created_by
             FROM cc_announcements a
             LEFT JOIN em_employees e ON a.created_by = e.employee_id
             WHERE a.status = 'Published' AND (a.expires_at IS NULL OR a.expires_at > :now)
             ORDER BY a.publish_date DESC"
        );
        $stmt->execute([':now' => $this->now()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeed(array $viewer, array $filters, $limit, $offset) {
        return $this->listAnnouncements('feed', $viewer, $filters, $limit, $offset);
    }

    public function getManageList(array $filters, $limit, $offset) {
        return $this->listAnnouncements('manage', [], $filters, $limit, $offset);
    }

    // Cheap fingerprint of what this viewer can see; the page polls this and reloads the list only when it changes.
    public function getFeedState(array $viewer) {
        list($vis, $vp) = $this->visibilitySql($viewer);
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS c, COALESCE(MAX(a.updated_at), '') AS latest
             FROM cc_announcements a
             WHERE a.status = 'Published' AND (a.expires_at IS NULL OR a.expires_at > :f_now) AND $vis"
        );
        $stmt->execute([':f_now' => $this->now()] + $vp);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ['count' => (int) $row['c'], 'latest' => (string) $row['latest'], 'server_time' => $this->now()];
    }

    public function getSummary() {
        $now        = $this->now();
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd   = date('Y-m-01 00:00:00', strtotime('+1 month', strtotime($monthStart)));

        $stmt = $this->conn->prepare(
            "SELECT
                COALESCE(SUM(a.status = 'Published' AND (a.expires_at IS NULL OR a.expires_at > :n1)), 0) AS active,
                COALESCE(SUM(a.status IN ('Published','Archived') AND a.published_at >= :ms AND a.published_at < :me), 0) AS published_month,
                COALESCE(SUM(a.status = 'Scheduled'), 0) AS scheduled,
                COALESCE(SUM(a.status = 'Archived'), 0) AS archived
             FROM cc_announcements a"
        );
        $stmt->execute([':n1' => $now, ':ms' => $monthStart, ':me' => $monthEnd]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'active'          => (int) $r['active'],
            'published_month' => (int) $r['published_month'],
            'scheduled'       => (int) $r['scheduled'],
            'archived'        => (int) $r['archived'],
        ];
    }

    private function listAnnouncements($mode, array $viewer, array $f, $limit, $offset) {
        $now   = $this->now();
        $where = [];
        $p     = [];

        if ($mode === 'feed') {
            $where[]       = "a.status = 'Published' AND (a.expires_at IS NULL OR a.expires_at > :w_now)";
            $p[':w_now']   = $now;
            list($vis, $vp) = $this->visibilitySql($viewer);
            $where[]       = $vis;
            $p            += $vp;
        } else {
            $status = (string) ($f['status'] ?? '');
            if ($status === 'Published') {
                $where[]     = "a.status = 'Published' AND (a.expires_at IS NULL OR a.expires_at > :m_now)";
                $p[':m_now'] = $now;
            } elseif ($status === 'Expired') {
                $where[]     = "a.status = 'Published' AND a.expires_at IS NOT NULL AND a.expires_at <= :m_now";
                $p[':m_now'] = $now;
            } elseif (in_array($status, ['Draft', 'Scheduled', 'Archived', 'Withdrawn'], true)) {
                $where[]       = 'a.status = :m_status';
                $p[':m_status'] = $status;
            }
        }

        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $where[] = "(a.title LIKE :q1 OR a.message LIKE :q2 OR CONCAT_WS(' ', c.first_name, c.last_name) LIKE :q3)";
            $p[':q1'] = $like;
            $p[':q2'] = $like;
            $p[':q3'] = $like;
        }

        $type = (string) ($f['type'] ?? '');
        if (in_array($type, self::TYPES, true)) {
            $where[]    = 'a.announcement_type = :f_type';
            $p[':f_type'] = $type;
        }

        $priority = (string) ($f['priority'] ?? '');
        if (in_array($priority, self::PRIORITIES, true)) {
            $where[]        = 'a.priority = :f_prio';
            $p[':f_prio']   = $priority;
        }

        $dateExpr = 'COALESCE(a.published_at, a.publish_date, a.created_at)';
        $from     = (string) ($f['date_from'] ?? '');
        $to       = (string) ($f['date_to'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $where[]       = "$dateExpr >= :d_from";
            $p[':d_from']  = $from . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $where[]     = "$dateExpr <= :d_to";
            $p[':d_to']  = $to . ' 23:59:59';
        }

        $dept = (int) ($f['department_id'] ?? 0);
        if ($dept > 0) {
            $where[]      = "EXISTS (SELECT 1 FROM cc_announcement_audiences fau
                                     WHERE fau.announcement_id = a.announcement_id AND fau.department_id = :f_dept)";
            $p[':f_dept'] = $dept;
        }

        $whereSql = $where ? implode(' AND ', $where) : '1=1';

        switch ((string) ($f['sort'] ?? 'newest')) {
            case 'oldest':
                $order = "$dateExpr ASC, a.announcement_id ASC";
                break;
            case 'priority': // ENUM order: Normal < Important < Urgent
                $order = "a.priority DESC, $dateExpr DESC, a.announcement_id DESC";
                break;
            case 'expiring':
                $order = "(a.expires_at IS NULL) ASC, a.expires_at ASC, $dateExpr DESC";
                break;
            default:
                $order = "$dateExpr DESC, a.announcement_id DESC";
        }

        $countStmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM cc_announcements a
             LEFT JOIN em_employees c ON c.employee_id = a.created_by
             WHERE $whereSql"
        );
        $countStmt->execute($p);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->conn->prepare(
            "SELECT a.announcement_id, a.title, a.message, a.announcement_type, a.priority, a.status,
                    a.audience_scope, a.publish_date, a.published_at, a.expires_at, a.image_file, a.updated_at,
                    TRIM(CONCAT_WS(' ', c.first_name, c.last_name)) AS created_by_name,
                    d.department_name AS issuing_office,
                    (SELECT COUNT(*) FROM cc_announcement_audiences xa WHERE xa.announcement_id = a.announcement_id) AS audience_count,
                    (SELECT COUNT(*) FROM cc_announcement_attachments xt WHERE xt.announcement_id = a.announcement_id) AS attachment_count,
                    (a.status = 'Published' AND a.expires_at IS NOT NULL AND a.expires_at <= :s_now) AS is_expired
             FROM cc_announcements a
             LEFT JOIN em_employees c ON c.employee_id = a.created_by
             LEFT JOIN em_departments d ON d.department_id = a.issuing_department_id
             WHERE $whereSql
             ORDER BY $order
             LIMIT :lim OFFSET :off"
        );
        foreach ($p as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':s_now', $now);
        $stmt->bindValue(':lim', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['preview']          = $this->preview($r['message']);
            unset($r['message']);
            $r['is_expired']       = (bool) $r['is_expired'];
            $r['display_status']   = $r['is_expired'] ? 'Expired' : $r['status'];
            $r['audience_count']   = (int) $r['audience_count'];
            $r['attachment_count'] = (int) $r['attachment_count'];
        }
        unset($r);

        return ['rows' => $rows, 'total' => $total];
    }

    public function getDetail($id, array $viewer, $canManage) {
        $stmt = $this->conn->prepare(
            "SELECT a.announcement_id, a.title, a.message, a.announcement_type, a.priority, a.status,
                    a.audience_scope, a.publish_date, a.published_at, a.expires_at, a.image_file,
                    a.created_at, a.updated_at, a.archived_at, a.withdrawn_at, a.withdraw_reason,
                    a.issuing_department_id,
                    TRIM(CONCAT_WS(' ', c.first_name, c.last_name))  AS created_by_name,
                    TRIM(CONCAT_WS(' ', ab.first_name, ab.last_name)) AS archived_by_name,
                    TRIM(CONCAT_WS(' ', wb.first_name, wb.last_name)) AS withdrawn_by_name,
                    d.department_name AS issuing_office,
                    (a.status = 'Published' AND a.expires_at IS NOT NULL AND a.expires_at <= :d_now) AS is_expired
             FROM cc_announcements a
             LEFT JOIN em_employees c  ON c.employee_id  = a.created_by
             LEFT JOIN em_employees ab ON ab.employee_id = a.archived_by
             LEFT JOIN em_employees wb ON wb.employee_id = a.withdrawn_by
             LEFT JOIN em_departments d ON d.department_id = a.issuing_department_id
             WHERE a.announcement_id = :id"
        );
        $stmt->execute([':d_now' => $this->now(), ':id' => (int) $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Same answer for "missing" and "not yours", so ids cannot be probed
        if (!$row || (!$canManage && !$this->isVisibleTo($id, $viewer))) {
            $this->fail('Announcement not found.', 404);
        }

        $row['is_expired']     = (bool) $row['is_expired'];
        $row['display_status'] = $row['is_expired'] ? 'Expired' : $row['status'];

        // Audience
        $aStmt = $this->conn->prepare(
            "SELECT au.audience_type, au.department_id, au.unit_id, au.employee_id,
                    d.department_name, u.unit_name, ud.department_name AS unit_department_name,
                    TRIM(CONCAT_WS(' ', e.first_name, e.last_name)) AS employee_name
             FROM cc_announcement_audiences au
             LEFT JOIN em_departments d  ON d.department_id  = au.department_id
             LEFT JOIN em_units u        ON u.unit_id        = au.unit_id
             LEFT JOIN em_departments ud ON ud.department_id = u.department_id
             LEFT JOIN em_employees e    ON e.employee_id    = au.employee_id
             WHERE au.announcement_id = :id
             ORDER BY au.audience_id"
        );
        $aStmt->execute([':id' => (int) $id]);

        $audience = ['departments' => [], 'units' => [], 'employees' => [], 'employee_count' => 0];
        foreach ($aStmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
            if ($a['audience_type'] === 'Department') {
                $audience['departments'][] = ['id' => (int) $a['department_id'], 'name' => $a['department_name']];
            } elseif ($a['audience_type'] === 'Unit') {
                $audience['units'][] = ['id' => (int) $a['unit_id'], 'name' => $a['unit_name'], 'department' => $a['unit_department_name']];
            } else {
                $audience['employee_count']++;
                if ($canManage) { // recipients do not get to see who else was addressed by name
                    $audience['employees'][] = ['id' => (int) $a['employee_id'], 'name' => $a['employee_name']];
                }
            }
        }
        $row['audience'] = $audience;

        if ($row['audience_scope'] === 'Campus') {
            $row['audience_summary'] = 'Entire campus';
        } else {
            $parts = [];
            if ($audience['departments']) {
                $parts[] = implode(', ', array_column($audience['departments'], 'name'));
            }
            if ($audience['units']) {
                $parts[] = implode(', ', array_column($audience['units'], 'name'));
            }
            if ($audience['employee_count']) {
                $n = $audience['employee_count'];
                $parts[] = $n . ($n === 1 ? ' individual' : ' individuals');
            }
            $row['audience_summary'] = implode(' · ', $parts);
        }

        // Attachments (never expose storage paths)
        $tStmt = $this->conn->prepare(
            'SELECT attachment_id, original_name, file_size FROM cc_announcement_attachments
             WHERE announcement_id = :id ORDER BY attachment_id'
        );
        $tStmt->execute([':id' => (int) $id]);
        $row['attachments'] = $tStmt->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }

    public function getHistory($id) {
        $stmt = $this->conn->prepare(
            "SELECT l.action, l.description, l.created_at,
                    TRIM(CONCAT_WS(' ', e.first_name, e.last_name)) AS actor
             FROM cc_activity_logs l
             LEFT JOIN cc_user_account ua ON ua.user_id = l.user_id
             LEFT JOIN em_employees e  ON e.employee_id = ua.employee_id
             WHERE l.module = 'communications' AND l.record_id = :id
             ORDER BY l.created_at DESC
             LIMIT 100"
        );
        $stmt->execute([':id' => (int) $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ══════════════════════════════════════════════════════════
       Audience pickers (from the existing org tables)
       ══════════════════════════════════════════════════════════ */

    public function getAudienceOptions() {
        $depts = $this->conn->query(
            "SELECT department_id, department_name FROM em_departments WHERE status = 'Active' ORDER BY department_name"
        )->fetchAll(PDO::FETCH_ASSOC);

        $units = $this->conn->query(
            "SELECT unit_id, department_id, unit_name FROM em_units WHERE status = 'Active' ORDER BY unit_name"
        )->fetchAll(PDO::FETCH_ASSOC);

        return ['departments' => $depts, 'units' => $units];
    }

    // Only employees who can actually sign in and receive notifications.
    public function searchEmployees($q, $limit = 20) {
        $q = trim((string) $q);
        if (mb_strlen($q) < 2) {
            return [];
        }
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $stmt = $this->conn->prepare(
            "SELECT e.employee_id, TRIM(CONCAT_WS(' ', e.first_name, e.last_name)) AS name,
                    d.department_name, u.unit_name
             FROM em_employees e
             JOIN cc_user_account ua ON ua.employee_id = e.employee_id
             LEFT JOIN em_departments d ON d.department_id = e.department_id
             LEFT JOIN em_units u       ON u.unit_id       = e.unit_id
             WHERE e.is_archived = 0 AND e.employment_status IN ('Active','Probationary')
               AND CONCAT_WS(' ', e.first_name, e.last_name) LIKE :q
             ORDER BY e.last_name, e.first_name
             LIMIT :lim"
        );
        $stmt->bindValue(':q', $like);
        $stmt->bindValue(':lim', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ══════════════════════════════════════════════════════════
       Create / edit (drafts and live edits)
       ══════════════════════════════════════════════════════════ */

    private function normalize(array $in) {
        $title   = trim((string) ($in['title'] ?? ''));
        $message = trim((string) ($in['message'] ?? ''));

        if ($title === '') {
            $this->fail('Announcement title is required.');
        }
        if (mb_strlen($title) > 255) {
            $this->fail('The title must be 255 characters or fewer.');
        }
        if ($message === '') {
            $this->fail('Announcement content is required.');
        }
        if (mb_strlen($message) > 10000) {
            $this->fail('The content must be 10,000 characters or fewer.');
        }

        $type = (string) ($in['announcement_type'] ?? 'General Announcement');
        if (!in_array($type, self::TYPES, true)) {
            $this->fail('Choose a valid announcement type.');
        }
        $priority = (string) ($in['priority'] ?? 'Normal');
        if (!in_array($priority, self::PRIORITIES, true)) {
            $this->fail('Choose a valid priority.');
        }

        $scope = ((string) ($in['audience_scope'] ?? 'Campus')) === 'Targeted' ? 'Targeted' : 'Campus';
        $dept = $unit = $emp = [];

        if ($scope === 'Targeted') {
            $dept = $this->cleanIds($in['department_ids'] ?? []);
            $unit = $this->cleanIds($in['unit_ids'] ?? []);
            $emp  = $this->cleanIds($in['employee_ids'] ?? []);

            if (!$dept && !$unit && !$emp) {
                $this->fail('Select at least one department, unit or person, or choose Entire Campus.');
            }
            if ($this->countExisting('em_departments', 'department_id', $dept, " AND status = 'Active'") !== count($dept)) {
                $this->fail('One of the selected departments is not available.');
            }
            if ($this->countExisting('em_units', 'unit_id', $unit, " AND status = 'Active'") !== count($unit)) {
                $this->fail('One of the selected units is not available.');
            }
            if ($this->countExisting('em_employees', 'employee_id', $emp, " AND is_archived = 0 AND employment_status IN ('Active','Probationary')") !== count($emp)) {
                $this->fail('One of the selected people is not available.');
            }
        }

        $issuing = (int) ($in['issuing_department_id'] ?? 0);

        return [
            'title'        => $title,
            'message'      => $message,
            'type'         => $type,
            'priority'     => $priority,
            'scope'        => $scope,
            'dept'         => $dept,
            'unit'         => $unit,
            'emp'          => $emp,
            'publish_date' => $this->parseDate($in['publish_date'] ?? '', 'publication date'),
            'expires_at'   => $this->parseDate($in['expires_at'] ?? '', 'expiration date'),
            'issuing'      => $issuing > 0 ? $issuing : null,
        ];
    }

    /**
     * Create a draft (no $id) or edit an announcement that is Draft, Scheduled or Published.
     * Editing never re-sends notifications. Returns the announcement id.
     *
     * $files  = list of $_FILES-style single-file arrays
     * $removeIds = attachment ids to remove (must belong to this announcement)
     */
    public function save(array $in, $employeeId, array $files = [], array $removeIds = [], $id = null) {
        $id  = (int) $id;
        $d   = $this->normalize($in);
        $now = $this->now();

        // Validate every upload before touching the database
        $valid = [];
        foreach ($files as $f) {
            if (!FileStorage::hasUpload($f)) {
                continue;
            }
            $err = FileStorage::validateUpload($f);
            if ($err !== null) {
                $this->fail(FileStorage::cleanName($f['name'] ?? 'File') . ': ' . $err);
            }
            $valid[] = $f;
        }
        $removeIds = $this->cleanIds($removeIds);

        $stored   = [];
        $toDelete = [];

        $this->conn->beginTransaction();
        try {
            $existing = null;
            if ($id > 0) {
                $existing = $this->fetchRow($id, true);
                if (!$existing) {
                    $this->fail('Announcement not found.', 404);
                }
                if (!in_array($existing['status'], self::EDITABLE, true)) {
                    $this->fail('This announcement is ' . strtolower($existing['status']) . ' and cannot be edited. '
                        . ($existing['status'] === 'Archived' ? 'Restore it first.' : 'Reopen it as a draft first.'), 409);
                }
            }

            // Date rules
            $publishDate = $d['publish_date'];
            $status      = $existing ? $existing['status'] : 'Draft';

            if ($status === 'Published') {
                $publishDate = $existing['publish_date']; // a live announcement keeps its publication time
            } elseif ($status === 'Scheduled') {
                if (!$publishDate) {
                    $this->fail('A scheduled announcement needs a publication time.');
                }
                if ($publishDate <= $now) {
                    $this->fail('The scheduled time must be in the future. Use Publish Now instead.');
                }
            }

            if ($d['expires_at'] !== null) {
                $basis = $status === 'Published'
                    ? ($existing['published_at'] ?: ($publishDate ?: $now))
                    : ($publishDate ?: $now);
                if ($d['expires_at'] <= $basis) {
                    $this->fail('The expiration must be later than the publication time.');
                }
            }

            // Announcement row
            if ($id === 0) {
                $stmt = $this->conn->prepare(
                    "INSERT INTO cc_announcements
                        (title, message, announcement_type, priority, status, audience_scope,
                         issuing_department_id, publish_date, expires_at, created_by)
                     VALUES (:t, :m, :ty, :pr, 'Draft', :sc, :iss, :pd, :ex, :cb)"
                );
                $stmt->execute([
                    ':t' => $d['title'], ':m' => $d['message'], ':ty' => $d['type'], ':pr' => $d['priority'],
                    ':sc' => $d['scope'], ':iss' => $d['issuing'], ':pd' => $publishDate, ':ex' => $d['expires_at'],
                    ':cb' => (int) $employeeId,
                ]);
                $id = (int) $this->conn->lastInsertId();
                $this->log($employeeId, 'created', 'Created draft "' . $d['title'] . '".', $id);
            } else {
                $stmt = $this->conn->prepare(
                    "UPDATE cc_announcements
                     SET title = :t, message = :m, announcement_type = :ty, priority = :pr,
                         audience_scope = :sc, publish_date = :pd, expires_at = :ex
                     WHERE announcement_id = :id"
                );
                $stmt->execute([
                    ':t' => $d['title'], ':m' => $d['message'], ':ty' => $d['type'], ':pr' => $d['priority'],
                    ':sc' => $d['scope'], ':pd' => $publishDate, ':ex' => $d['expires_at'], ':id' => $id,
                ]);
                $this->log($employeeId, 'updated', 'Edited "' . $d['title'] . '" (' . $status . ').', $id);
            }

            // Audiences: replace the set (cascade-safe, duplicates blocked by unique keys)
            $del = $this->conn->prepare('DELETE FROM cc_announcement_audiences WHERE announcement_id = :id');
            $del->execute([':id' => $id]);

            if ($d['scope'] === 'Targeted') {
                $ins = $this->conn->prepare(
                    'INSERT INTO cc_announcement_audiences (announcement_id, audience_type, department_id, unit_id, employee_id)
                     VALUES (:a, :ty, :d, :u, :e)'
                );
                foreach ($d['dept'] as $x) {
                    $ins->execute([':a' => $id, ':ty' => 'Department', ':d' => $x, ':u' => null, ':e' => null]);
                }
                foreach ($d['unit'] as $x) {
                    $ins->execute([':a' => $id, ':ty' => 'Unit', ':d' => null, ':u' => $x, ':e' => null]);
                }
                foreach ($d['emp'] as $x) {
                    $ins->execute([':a' => $id, ':ty' => 'Employee', ':d' => null, ':u' => null, ':e' => $x]);
                }
            }

            // Attachment removals (ids must belong to this announcement)
            foreach ($removeIds as $attId) {
                $path = $this->scalar(
                    'SELECT file_path FROM cc_announcement_attachments WHERE attachment_id = :a AND announcement_id = :id',
                    [':a' => $attId, ':id' => $id]
                );
                if ($path !== false) {
                    $this->conn->prepare('DELETE FROM cc_announcement_attachments WHERE attachment_id = :a')
                               ->execute([':a' => $attId]);
                    $toDelete[] = $path;
                    $this->log($employeeId, 'attachment_removed', 'Removed an attachment from "' . $d['title'] . '".', $id);
                }
            }

            // New attachments
            if ($valid) {
                $remaining = (int) $this->scalar(
                    'SELECT COUNT(*) FROM cc_announcement_attachments WHERE announcement_id = :id', [':id' => $id]
                );
                if ($remaining + count($valid) > self::MAX_ATTACHMENTS) {
                    $this->fail('An announcement can have at most ' . self::MAX_ATTACHMENTS . ' attachments.');
                }

                $insAtt = $this->conn->prepare(
                    'INSERT INTO cc_announcement_attachments (announcement_id, file_path, original_name, file_size, uploaded_by)
                     VALUES (:a, :p, :n, :s, :u)'
                );
                foreach ($valid as $f) {
                    $rel      = FileStorage::store($f, self::ATTACHMENT_DIR, 'ann' . $id);
                    $stored[] = $rel;
                    $insAtt->execute([
                        ':a' => $id, ':p' => $rel, ':n' => FileStorage::cleanName($f['name']),
                        ':s' => (int) $f['size'], ':u' => (int) $employeeId,
                    ]);
                }
                $this->log($employeeId, 'attachment_added', 'Attached ' . count($valid) . ' file(s) to "' . $d['title'] . '".', $id);
            }

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            foreach ($stored as $rel) { // do not leave orphaned files behind
                FileStorage::delete($rel);
            }
            throw $e;
        }

        foreach ($toDelete as $rel) {
            FileStorage::delete($rel);
        }
        return $id;
    }

    /* ══════════════════════════════════════════════════════════
       Lifecycle
       ══════════════════════════════════════════════════════════ */

    /**
     * Publish now (no $scheduleAt) or schedule for a future time.
     * Returns ['status' => 'Published'|'Scheduled', 'notified' => int, 'publish_date' => string].
     */
    public function publish($id, $employeeId, $scheduleAt = null) {
        $id  = (int) $id;
        $now = $this->now();
        $notified = 0;

        $this->conn->beginTransaction();
        try {
            $a = $this->fetchRow($id, true);
            if (!$a) {
                $this->fail('Announcement not found.', 404);
            }
            if (!in_array($a['status'], ['Draft', 'Scheduled'], true)) {
                $this->fail('Only draft or scheduled announcements can be published (this one is ' . strtolower($a['status']) . ').', 409);
            }
            if (trim((string) $a['title']) === '' || trim((string) $a['message']) === '') {
                $this->fail('The announcement needs a title and content before it can be published.');
            }
            if ($a['audience_scope'] === 'Targeted'
                && (int) $this->scalar('SELECT COUNT(*) FROM cc_announcement_audiences WHERE announcement_id = :id', [':id' => $id]) === 0) {
                $this->fail('Select at least one audience, or choose Entire Campus.');
            }

            $schedule = $this->parseDate($scheduleAt, 'scheduled time');
            if ($schedule !== null && $schedule <= $now) {
                $this->fail('The scheduled time must be in the future.');
            }

            $effective = $schedule ?: $now;
            if ($a['expires_at'] !== null && $a['expires_at'] <= $effective) {
                $this->fail('The expiration must be later than the publication time.');
            }

            if ($schedule !== null) {
                $stmt = $this->conn->prepare(
                    "UPDATE cc_announcements SET status = 'Scheduled', publish_date = :pd
                     WHERE announcement_id = :id AND status IN ('Draft','Scheduled')"
                );
                $stmt->execute([':pd' => $schedule, ':id' => $id]);
                $status = 'Scheduled';
                $this->log($employeeId, 'scheduled', 'Scheduled "' . $a['title'] . '" for ' . $schedule . '.', $id);
            } else {
                $stmt = $this->conn->prepare(
                    "UPDATE cc_announcements
                     SET status = 'Published', publish_date = :pd, published_at = :pa, is_archived = 0
                     WHERE announcement_id = :id AND status IN ('Draft','Scheduled')"
                );
                $stmt->execute([':pd' => $now, ':pa' => $now, ':id' => $id]);
                $status   = 'Published';
                $notified = $this->deliver($id);
                $this->log($employeeId, 'published', 'Published "' . $a['title'] . '" (' . $a['audience_scope'] . ').', $id);
            }

            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        return ['status' => $status, 'notified' => $notified, 'publish_date' => $schedule ?: $now];
    }

    /**
     * Moves due Scheduled announcements to Published and notifies recipients.
     * Safe to run concurrently and repeatedly: the conditional UPDATE lets only
     * one run flip each row, and the delivery ledger blocks duplicate notifications.
     * Returns how many announcements went live.
     */
    public function processScheduled($limit = 25) {
        $now  = $this->now();
        $stmt = $this->conn->prepare(
            "SELECT announcement_id, created_by FROM cc_announcements
             WHERE status = 'Scheduled' AND publish_date <= :now
             ORDER BY publish_date LIMIT :lim"
        );
        $stmt->bindValue(':now', $now);
        $stmt->bindValue(':lim', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $due = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $published = 0;
        foreach ($due as $row) {
            try {
                $this->conn->beginTransaction();
                $up = $this->conn->prepare(
                    "UPDATE cc_announcements SET status = 'Published', published_at = :pa, is_archived = 0
                     WHERE announcement_id = :id AND status = 'Scheduled' AND publish_date <= :pd"
                );
                $up->execute([':pa' => $now, ':id' => (int) $row['announcement_id'], ':pd' => $now]);

                if ($up->rowCount() === 1) {
                    $this->deliver((int) $row['announcement_id']);
                    $this->log($row['created_by'], 'published',
                        'Scheduled announcement "' . $this->titleOf($row['announcement_id']) . '" went live automatically.',
                        $row['announcement_id']);
                    $published++;
                }
                $this->conn->commit();
            } catch (\Throwable $e) {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                error_log('Announcement scheduled publish failed (#' . $row['announcement_id'] . '): ' . $e->getMessage());
            }
        }
        return $published;
    }

    // One conditional UPDATE per transition keeps two clicks / two admins from racing.
    private function transition($id, array $from, $setSql, array $params, $verb) {
        $in = implode(',', array_map(function ($s) {
            return $this->conn->quote($s);
        }, $from));

        $stmt = $this->conn->prepare("UPDATE cc_announcements SET $setSql WHERE announcement_id = :tid AND status IN ($in)");
        $stmt->execute($params + [':tid' => (int) $id]);

        if ($stmt->rowCount() === 0) {
            $row = $this->fetchRow($id);
            if (!$row) {
                $this->fail('Announcement not found.', 404);
            }
            $this->fail('This announcement cannot be ' . $verb . ' while it is ' . strtolower($row['status']) . '.', 409);
        }
    }

    public function archiveAnnouncement($id, $employeeId) {
        $this->transition($id, ['Published'],
            "status = 'Archived', is_archived = 1, archived_at = :at, archived_by = :by",
            [':at' => $this->now(), ':by' => (int) $employeeId], 'archived');
        $this->log($employeeId, 'archived', 'Archived "' . $this->titleOf($id) . '".', $id);
    }

    // Restore returns it to Published (no new notifications: the delivery ledger already has the recipients).
    public function restoreAnnouncement($id, $employeeId = null) {
        $this->transition($id, ['Archived'],
            "status = 'Published', is_archived = 0, archived_at = NULL, archived_by = NULL",
            [], 'restored');
        $this->log($employeeId, 'restored', 'Restored "' . $this->titleOf($id) . '" from the archive.', $id);
    }

    public function withdraw($id, $employeeId, $reason = '') {
        $reason = mb_substr(trim((string) $reason), 0, 255);
        $this->transition($id, ['Published', 'Scheduled'],
            "status = 'Withdrawn', is_archived = 0, withdrawn_at = :at, withdrawn_by = :by, withdraw_reason = :r",
            [':at' => $this->now(), ':by' => (int) $employeeId, ':r' => $reason !== '' ? $reason : null], 'withdrawn');
        $this->log($employeeId, 'withdrawn', 'Withdrew "' . $this->titleOf($id) . '"' . ($reason !== '' ? ': ' . $reason : '.'), $id);
    }

    public function unschedule($id, $employeeId) {
        $this->transition($id, ['Scheduled'], "status = 'Draft'", [], 'unscheduled');
        $this->log($employeeId, 'unscheduled', 'Moved "' . $this->titleOf($id) . '" back to draft.', $id);
    }

    public function reopen($id, $employeeId) {
        $this->transition($id, ['Withdrawn'],
            "status = 'Draft', withdrawn_at = NULL, withdrawn_by = NULL, withdraw_reason = NULL", [], 'reopened');
        $this->log($employeeId, 'reopened', 'Reopened "' . $this->titleOf($id) . '" as a draft.', $id);
    }

    // Only drafts can be deleted; anything that was ever live is kept for the record.
    public function deleteDraft($id, $employeeId) {
        $id = (int) $id;
        $paths = [];

        $this->conn->beginTransaction();
        try {
            $a = $this->fetchRow($id, true);
            if (!$a) {
                $this->fail('Announcement not found.', 404);
            }
            if ($a['status'] !== 'Draft') {
                $this->fail('Only drafts can be deleted. Withdraw or archive this announcement instead.', 409);
            }

            $s = $this->conn->prepare('SELECT file_path FROM cc_announcement_attachments WHERE announcement_id = :id');
            $s->execute([':id' => $id]);
            $paths = $s->fetchAll(PDO::FETCH_COLUMN);

            $this->conn->prepare('DELETE FROM cc_announcements WHERE announcement_id = :id')->execute([':id' => $id]);
            $this->log($employeeId, 'deleted', 'Deleted draft "' . $a['title'] . '".', $id);
            $this->conn->commit();
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }

        foreach ($paths as $p) {
            FileStorage::delete($p);
        }
    }

    /* ══════════════════════════════════════════════════════════
       Notifications (call inside the publishing transaction)
       ══════════════════════════════════════════════════════════ */

    /**
     * Creates one cc_notifications row per eligible recipient who has not already
     * been delivered this announcement, then records them in the delivery ledger.
     * Recipients = active employees with a user account in the targeted audience
     * (campus-wide, or department / unit / individually selected), minus the author,
     * counted once even if they match several groups.
     */
    private function deliver($id) {
        $a = $this->fetchRow($id);
        if (!$a) {
            return 0;
        }

        $recipients =
            "SELECT DISTINCT ua.user_id
             FROM em_employees e
             JOIN cc_user_account ua ON ua.employee_id = e.employee_id
             WHERE e.is_archived = 0
               AND e.employment_status IN ('Active','Probationary')
               AND e.employee_id <> :author
               AND (:camp = 1
                    OR e.department_id IN (SELECT au1.department_id FROM cc_announcement_audiences au1
                                           WHERE au1.announcement_id = :aid_d AND au1.department_id IS NOT NULL)
                    OR e.unit_id IN (SELECT au2.unit_id FROM cc_announcement_audiences au2
                                     WHERE au2.announcement_id = :aid_u AND au2.unit_id IS NOT NULL)
                    OR e.employee_id IN (SELECT au3.employee_id FROM cc_announcement_audiences au3
                                         WHERE au3.announcement_id = :aid_e AND au3.employee_id IS NOT NULL))";

        $rp = [
            ':author' => (int) $a['created_by'],
            ':camp'   => $a['audience_scope'] === 'Campus' ? 1 : 0,
            ':aid_d'  => (int) $id,
            ':aid_u'  => (int) $id,
            ':aid_e'  => (int) $id,
        ];

        $title = trim((string) $a['title']);
        $title = mb_substr($title !== '' ? $title : 'New announcement', 0, 150);

        $n = $this->conn->prepare(
            "INSERT INTO cc_notifications (user_id, title, message, notification_type, module, record_id)
             SELECT r.user_id, :nt, :nm, :ntype, 'communications', :rid
             FROM ($recipients) r
             LEFT JOIN cc_announcement_deliveries dl ON dl.announcement_id = :did AND dl.user_id = r.user_id
             WHERE dl.user_id IS NULL"
        );
        $n->execute($rp + [
            ':nt'    => $title,
            ':nm'    => $this->preview($a['message'], 200) ?: 'A new announcement has been posted.',
            ':ntype' => $a['priority'] === 'Urgent' ? 'URGENT' : 'ANNOUNCEMENT',
            ':rid'   => (int) $id,
            ':did'   => (int) $id,
        ]);
        $sent = $n->rowCount();

        $l = $this->conn->prepare(
            "INSERT IGNORE INTO cc_announcement_deliveries (announcement_id, user_id)
             SELECT :aid_s, r.user_id FROM ($recipients) r"
        );
        $l->execute($rp + [':aid_s' => (int) $id]);

        return $sent;
    }

    /* ══════════════════════════════════════════════════════════
       Attachments (stored through FileStorage, served by the controller)
       ══════════════════════════════════════════════════════════ */

    public function getAttachmentForViewer($attachmentId, array $viewer, $canManage) {
        $stmt = $this->conn->prepare(
            'SELECT attachment_id, announcement_id, file_path, original_name
             FROM cc_announcement_attachments WHERE attachment_id = :id'
        );
        $stmt->execute([':id' => (int) $attachmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || (!$canManage && !$this->isVisibleTo($row['announcement_id'], $viewer))) {
            $this->fail('Attachment not found.', 404);
        }
        return $row;
    }

    /* ══════════════════════════════════════════════════════════
       Archived list (used by the existing Archived tab)
       ══════════════════════════════════════════════════════════ */

    private function archivedWhere($search) {
        $where  = "a.status = 'Archived'";
        $params = [];

        $search = trim((string) $search);
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $where .= " AND (a.title LIKE :q1
                             OR a.message LIKE :q2
                             OR CONCAT_WS(' ', c.first_name, c.last_name) LIKE :q3)";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }
        return [$where, $params];
    }

    public function countArchived($search = '') {
        list($where, $params) = $this->archivedWhere($search);
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM cc_announcements a
             LEFT JOIN em_employees c ON c.employee_id = a.created_by
             WHERE $where"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getArchived($search = '', $limit = 10, $offset = 0) {
        list($where, $params) = $this->archivedWhere($search);

        $stmt = $this->conn->prepare(
            "SELECT a.announcement_id, a.title, a.message, a.publish_date, a.image_file, a.archived_at,
                    TRIM(CONCAT_WS(' ', c.first_name, c.last_name))   AS created_by_name,
                    TRIM(CONCAT_WS(' ', ab.first_name, ab.last_name)) AS archived_by_name
             FROM cc_announcements a
             LEFT JOIN em_employees c  ON c.employee_id  = a.created_by
             LEFT JOIN em_employees ab ON ab.employee_id = a.archived_by
             WHERE $where
             ORDER BY a.archived_at DESC, a.announcement_id DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['preview'] = $this->preview($r['message'], 160);
            unset($r['message']);
        }
        unset($r);
        return $rows;
    }
}