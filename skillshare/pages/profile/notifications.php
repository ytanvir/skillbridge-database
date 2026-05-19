<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';
requireLogin();

// Mark all as read
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([currentUserId()]);

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$notifs->execute([currentUserId()]);
$list = $notifs->fetchAll();

require_once '../../includes/header.php';
?>

<h2 class="fw-bold mb-4" style="font-family:'Syne',sans-serif;">All Notifications</h2>

<?php if (empty($list)): ?>
<div class="empty-state">
    <div class="icon">🔔</div>
    <h5>No notifications yet</h5>
    <p>You'll see updates here when someone interacts with your skills.</p>
</div>
<?php else: ?>
<div class="skill-card overflow-hidden">
    <?php foreach ($list as $n): ?>
    <a href="<?= BASE_URL . ltrim($n['link'],'/') ?>" class="notif-item d-flex gap-3 align-items-start" style="padding:16px 20px;">
        <div style="font-size:1.4rem;flex-shrink:0;">🔔</div>
        <div>
            <div class="small"><?= sanitize($n['message']) ?></div>
            <div class="text-muted" style="font-size:0.72rem;"><?= timeAgo($n['created_at']) ?></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>
