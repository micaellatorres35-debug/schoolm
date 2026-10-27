<?php
include_once __DIR__ . '/../../../database/db.php';

class Announcement {
    private $conn;

    public const CATEGORIES = ['General', 'Health Advisory', 'Schedule', 'Urgent'];

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /** Active = not archived and not past its expiry date. Pinned first, then newest. */
    public function getActive(int $limit = 5): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM clinic_announcements
             WHERE is_archived = 0 AND (expires_at IS NULL OR expires_at >= CURDATE())
             ORDER BY is_pinned DESC, created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validate(array $d): array {
        $errors = [];
        if (trim($d['title'] ?? '') === '')   $errors[] = 'Title is required.';
        if (trim($d['message'] ?? '') === '') $errors[] = 'Message is required.';
        if (!in_array($d['category'] ?? '', self::CATEGORIES, true)) $errors[] = 'Choose a valid category.';
        if (!empty($d['expires_at']) && !strtotime($d['expires_at'])) $errors[] = 'Expiry date is not valid.';
        return $errors;
    }

    public function create(array $d, ?string $postedBy = null): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO clinic_announcements (title, message, category, is_pinned, posted_by, expires_at)
             VALUES (:title, :message, :category, :pinned, :posted_by, :expires_at)"
        );
        $stmt->bindValue(':title', trim($d['title']));
        $stmt->bindValue(':message', trim($d['message']));
        $stmt->bindValue(':category', $d['category']);
        $stmt->bindValue(':pinned', !empty($d['is_pinned']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':posted_by', $postedBy);
        $stmt->bindValue(':expires_at', !empty($d['expires_at']) ? $d['expires_at'] : null);
        return $stmt->execute();
    }

    public function archive(int $id): bool {
        $stmt = $this->conn->prepare("UPDATE clinic_announcements SET is_archived = 1 WHERE announcement_id = ?");
        return $stmt->execute([$id]);
    }

    public function togglePin(int $id): bool {
        $stmt = $this->conn->prepare("UPDATE clinic_announcements SET is_pinned = 1 - is_pinned WHERE announcement_id = ?");
        return $stmt->execute([$id]);
    }
}