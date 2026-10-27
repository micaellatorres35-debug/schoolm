<?php
// Announcements section for the dashboard. Included by pages/ClinicDashboard.php
require_once __DIR__ . '/../classes/Announcement.php';
require_once __DIR__ . '/../classes/Employee.php';

$annMessage = '';
$annType = '';
$annLoadError = '';
$announcementClass = null;
try {
    $announcementClass = new Announcement();
} catch (Throwable $e) {
    $annLoadError = 'Could not connect: ' . $e->getMessage();
}

if ($announcementClass && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $annAction = $_POST['ann_action'] ?? '';
    $annId = (int) ($_POST['announcement_id'] ?? 0);
    try {
        if ($annAction === 'save') {
            $errors = $announcementClass->validate($_POST);
            if ($errors) {
                $annMessage = implode(' ', $errors);
                $annType = 'error';
            } else {
                $postedBy = null;
                try { $postedBy = (new Employee())->getEmployeeName(); } catch (Throwable $e) {}
                $announcementClass->create($_POST, $postedBy);
                $annMessage = 'Announcement posted.';
                $annType = 'success';
            }
        } elseif ($annAction === 'archive' && $annId > 0) {
            $announcementClass->archive($annId);
            $annMessage = 'Announcement removed.';
            $annType = 'success';
        } elseif ($annAction === 'pin' && $annId > 0) {
            $announcementClass->togglePin($annId);
            $annMessage = 'Announcement updated.';
            $annType = 'success';
        }
    } catch (Throwable $e) {
        $annMessage = 'Could not save the announcement: ' . $e->getMessage();
        $annType = 'error';
    }
}

$announcements = [];
if ($announcementClass) {
    try {
        $announcements = $announcementClass->getActive(5);
    } catch (Throwable $e) {
        $annLoadError = 'Could not load announcements: ' . $e->getMessage();
    }
}

function announcementBadge(string $category): string {
    $class = $category === 'Urgent' ? 'badge-danger' : ($category === 'Health Advisory' ? 'badge-warning' : 'badge-success');
    return '<span class="badge ' . $class . '">' . htmlspecialchars($category) . '</span>';
}
?>

<div class="student-info">
    <h2>Announcements</h2>

    <?php if ($annLoadError): ?>
        <div class="message error"><?= htmlspecialchars($annLoadError) ?></div>
    <?php endif; ?>

    <?php if ($annMessage): ?>
        <div class="message <?= $annType ?>"><?= htmlspecialchars($annMessage) ?></div>
    <?php endif; ?>

    <?php if (!$announcements): ?>
        <p class="pagination-info">No announcements right now.</p>
    <?php else: foreach ($announcements as $a): ?>
        <div style="background:#fff; border-radius:8px; padding:14px 16px; margin-bottom:12px; border-left:4px solid <?= $a['category'] === 'Urgent' ? '#dc3545' : '#667eea' ?>;">
            <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
                <strong><?= $a['is_pinned'] ? '[Pinned] ' : '' ?><?= htmlspecialchars($a['title']) ?></strong>
                <?= announcementBadge($a['category']) ?>
            </div>
            <p style="margin:8px 0; color:#495057; white-space:pre-line;"><?= htmlspecialchars($a['message']) ?></p>
            <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
                <small class="pagination-info">
                    <?= date('M d, Y', strtotime($a['created_at'])) ?>
                    <?= $a['posted_by'] ? ' by ' . htmlspecialchars($a['posted_by']) : '' ?>
                    <?= $a['expires_at'] ? ' | Until ' . date('M d, Y', strtotime($a['expires_at'])) : '' ?>
                </small>
                <span class="action-cell">
                    <form method="post">
                        <input type="hidden" name="ann_action" value="pin">
                        <input type="hidden" name="announcement_id" value="<?= (int) $a['announcement_id'] ?>">
                        <button type="submit" class="btn-edit"><?= $a['is_pinned'] ? 'Unpin' : 'Pin' ?></button>
                    </form>
                    <form method="post" onsubmit="return confirm('Remove this announcement?');">
                        <input type="hidden" name="ann_action" value="archive">
                        <input type="hidden" name="announcement_id" value="<?= (int) $a['announcement_id'] ?>">
                        <button type="submit" class="btn-delete">Remove</button>
                    </form>
                </span>
            </div>
        </div>
    <?php endforeach; endif; ?>

    <details style="margin-top:15px;">
        <summary class="btn-primary" style="display:inline-block; cursor:pointer;">Post announcement</summary>
        <form method="post" style="margin-top:15px;">
            <input type="hidden" name="ann_action" value="save">
            <div class="form-row">
                <div class="form-col">
                    <label for="ann_title">Title<span class="required">*</span></label>
                    <input type="text" name="title" id="ann_title" maxlength="150" required>
                </div>
                <div class="form-col">
                    <label for="ann_category">Category<span class="required">*</span></label>
                    <select name="category" id="ann_category">
                        <?php foreach (Announcement::CATEGORIES as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label for="ann_expires">Show until (optional)</label>
                    <input type="date" name="expires_at" id="ann_expires">
                </div>
            </div>
            <div class="form-row">
                <div class="form-col form-col-full">
                    <label for="ann_message">Message<span class="required">*</span></label>
                    <textarea name="message" id="ann_message" rows="3" required></textarea>
                </div>
            </div>
            <label style="font-size:14px;"><input type="checkbox" name="is_pinned" value="1"> Pin to the top</label>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Post announcement</button>
            </div>
        </form>
    </details>
</div>

<script>
if (window.history.replaceState) { window.history.replaceState(null, null, window.location.href); }
</script>