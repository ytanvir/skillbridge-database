<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';
requireLogin();

// Handle accept/decline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $rqId   = (int)$_POST['request_id'];
    $action = $_POST['action'] === 'accepted' ? 'accepted' : 'declined';
    // Make sure current user owns the skill
    $check = $pdo->prepare("SELECT r.id, r.sender_id, s.title FROM requests r JOIN skills s ON r.skill_id=s.id WHERE r.id=? AND s.user_id=?");
    $check->execute([$rqId, currentUserId()]);
    $rq = $check->fetch();
    if ($rq) {
        $pdo->prepare("UPDATE requests SET status=? WHERE id=?")->execute([$action, $rqId]);
        // Notify sender
        $msg = $action === 'accepted'
            ? currentUser()['name'] . ' accepted your interest in "' . $rq['title'] . '"! 🎉'
            : currentUser()['name'] . ' declined your interest in "' . $rq['title'] . '".';
        $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")
            ->execute([$rq['sender_id'], $msg, 'pages/profile/requests.php']);
        setFlash('success', 'Request ' . $action . '.');
    }
    redirect(BASE_URL . 'pages/profile/requests.php');
}

// Requests received (on my skills)
$received = $pdo->query("
    SELECT r.*, s.title AS skill_title, u.name AS sender_name, u.department AS sender_dept, u.id AS sender_id
    FROM requests r
    JOIN skills s ON r.skill_id=s.id
    JOIN users  u ON r.sender_id=u.id
    WHERE s.user_id=" . currentUserId() . "
    ORDER BY r.created_at DESC
")->fetchAll();

// Requests sent by me
$sent = $pdo->query("
    SELECT r.*, s.title AS skill_title, u.name AS owner_name
    FROM requests r
    JOIN skills s ON r.skill_id=s.id
    JOIN users  u ON s.user_id=u.id
    WHERE r.sender_id=" . currentUserId() . "
    ORDER BY r.created_at DESC
")->fetchAll();

require_once '../../includes/header.php';
?>

<h2 class="fw-bold mb-4" style="font-family:'Syne',sans-serif;">Connection Requests</h2>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="reqTab">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#received">
            Received <span class="badge bg-danger ms-1"><?= count(array_filter($received, fn($r)=>$r['status']==='pending')) ?></span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sent">
            Sent <span class="badge bg-secondary ms-1"><?= count($sent) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Received Requests -->
    <div class="tab-pane fade show active" id="received">
        <?php if (empty($received)): ?>
        <div class="empty-state"><div class="icon">📭</div><p>No requests received yet.</p></div>
        <?php else: ?>
        <?php foreach ($received as $rq): ?>
        <div class="skill-card mb-3 p-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex gap-3 align-items-center mb-2">
                        <div class="avatar-xs flex-shrink-0"><?= strtoupper(substr($rq['sender_name'],0,1)) ?></div>
                        <div>
                            <a href="<?= BASE_URL ?>pages/profile/view.php?id=<?= $rq['sender_id'] ?>" class="fw-bold text-decoration-none"><?= sanitize($rq['sender_name']) ?></a>
                            <div class="text-muted small"><?= sanitize($rq['sender_dept']) ?></div>
                        </div>
                    </div>
                    <div class="small text-muted mb-1">Interested in: <strong><?= sanitize($rq['skill_title']) ?></strong></div>
                    <?php if ($rq['message']): ?>
                    <div class="small p-2 rounded" style="background:#f8fafc;border-left:3px solid var(--accent);">
                        "<?= sanitize($rq['message']) ?>"
                    </div>
                    <?php endif; ?>
                    <div class="text-muted mt-1" style="font-size:0.72rem;"><?= timeAgo($rq['created_at']) ?></div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <?php if ($rq['status'] === 'pending'): ?>
                    <form method="POST" class="d-flex gap-2 justify-content-md-end">
                        <input type="hidden" name="request_id" value="<?= $rq['id'] ?>">
                        <button name="action" value="accepted" class="btn btn-sm btn-success">✅ Accept</button>
                        <button name="action" value="declined" class="btn btn-sm btn-outline-danger">❌ Decline</button>
                    </form>
                    <?php else: ?>
                    <span class="badge-status-<?= $rq['status'] ?>"><?= ucfirst($rq['status']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sent Requests -->
    <div class="tab-pane fade" id="sent">
        <?php if (empty($sent)): ?>
        <div class="empty-state"><div class="icon">📤</div><p>You haven't sent any requests yet.</p></div>
        <?php else: ?>
        <?php foreach ($sent as $rq): ?>
        <div class="skill-card mb-3 p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="fw-semibold"><?= sanitize($rq['skill_title']) ?></div>
                <div class="text-muted small">By <?= sanitize($rq['owner_name']) ?> · <?= timeAgo($rq['created_at']) ?></div>
                <?php if ($rq['message']): ?>
                <div class="small text-muted mt-1 fst-italic">"<?= sanitize(substr($rq['message'],0,80)) ?>..."</div>
                <?php endif; ?>
            </div>
            <span class="badge-status-<?= $rq['status'] ?>"><?= ucfirst($rq['status']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
