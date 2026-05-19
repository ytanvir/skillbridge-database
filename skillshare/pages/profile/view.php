<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) { setFlash('danger','User not found.'); redirect(BASE_URL.'index.php'); }

// Skills
$skills = $pdo->prepare("SELECT * FROM skills WHERE user_id=? AND status='active' ORDER BY created_at DESC");
$skills->execute([$id]);
$skillList = $skills->fetchAll();

// Reviews received
$reviews = $pdo->prepare("
    SELECT r.*, u.name AS rname, u.department AS rdept, s.title AS skill_title
    FROM reviews r
    JOIN users u ON r.reviewer_id=u.id
    JOIN skills s ON r.skill_id=s.id
    WHERE r.reviewed_id=?
    ORDER BY r.created_at DESC
");
$reviews->execute([$id]);
$reviewList = $reviews->fetchAll();
$avgRating  = count($reviewList) ? round(array_sum(array_column($reviewList,'rating'))/count($reviewList),1) : 0;

require_once '../../includes/header.php';
?>

<!-- Profile Header -->
<div class="profile-header mb-4">
    <div class="row align-items-center">
        <div class="col-md-8 d-flex align-items-center gap-4">
            <div class="avatar-lg flex-shrink-0"><?= strtoupper(substr($user['name'],0,1)) ?></div>
            <div>
                <h2 class="mb-1" style="font-family:'Syne',sans-serif;font-weight:800;"><?= sanitize($user['name']) ?></h2>
                <div style="opacity:0.8;"><?= sanitize($user['department']) ?> · <?= sanitize($user['semester']) ?> Semester</div>
                <?php if ($user['bio']): ?>
                <p class="mt-2 mb-0" style="opacity:0.75;font-size:0.9rem;"><?= sanitize($user['bio']) ?></p>
                <?php endif; ?>
                <div class="mt-2 d-flex gap-3 flex-wrap" style="font-size:0.85rem;opacity:0.7;">
                    <span><i class="bi bi-collection me-1"></i><?= count($skillList) ?> skills</span>
                    <span><i class="bi bi-star me-1"></i><?= $avgRating ?> avg rating</span>
                    <span><i class="bi bi-chat me-1"></i><?= count($reviewList) ?> reviews</span>
                    <span><i class="bi bi-calendar me-1"></i>Joined <?= date('M Y', strtotime($user['created_at'])) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <?php if (isLoggedIn() && currentUserId() === $id): ?>
            <a href="edit_profile.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit Profile
            </a>
            <?php endif; ?>
            <?php if ($avgRating > 0): ?>
            <div class="mt-2">
                <span class="rating-avg"><?= $avgRating ?></span>
                <div class="stars"><?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆',5-round($avgRating)) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Skills -->
        <h5 class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">
            <?= sanitize(explode(' ',$user['name'])[0]) ?>'s Skills (<?= count($skillList) ?>)
        </h5>
        <?php if (empty($skillList)): ?>
        <div class="empty-state">
            <div class="icon">📭</div>
            <p>No skills posted yet.</p>
        </div>
        <?php else: ?>
        <div class="row g-3 mb-5">
            <?php foreach ($skillList as $sk): ?>
            <div class="col-sm-6">
                <div class="skill-card">
                    <div class="skill-card-header">
                        <span class="category-pill"><?= getCategoryIcon($sk['category']) ?> <?= sanitize($sk['category']) ?></span>
                        <span class="category-pill type-<?= $sk['skill_type'] ?> ms-1"><?= $sk['skill_type']==='offer'?'✅ Offer':'🔍 Want' ?></span>
                        <div class="skill-title mt-2"><?= sanitize($sk['title']) ?></div>
                    </div>
                    <div class="skill-card-footer">
                        <a href="<?= BASE_URL ?>pages/skills/view.php?id=<?= $sk['id'] ?>" class="btn btn-sm btn-accent">View Skill →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <h5 class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">
            Reviews Received (<?= count($reviewList) ?>)
        </h5>
        <?php if (empty($reviewList)): ?>
        <p class="text-muted">No reviews yet.</p>
        <?php else: ?>
        <?php foreach ($reviewList as $rv): ?>
        <div class="skill-card mb-3 p-3">
            <div class="d-flex gap-3">
                <div class="avatar-xs flex-shrink-0"><?= strtoupper(substr($rv['rname'],0,1)) ?></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-bold small"><?= sanitize($rv['rname']) ?></span>
                            <span class="text-muted small ms-1"><?= sanitize($rv['rdept']) ?></span>
                        </div>
                        <span class="stars small"><?= str_repeat('★',$rv['rating']) ?><?= str_repeat('☆',5-$rv['rating']) ?></span>
                    </div>
                    <div class="text-muted" style="font-size:0.75rem;">On: <?= sanitize($rv['skill_title']) ?></div>
                    <p class="mb-0 small mt-1"><?= sanitize($rv['comment']) ?></p>
                    <div class="text-muted" style="font-size:0.72rem;"><?= timeAgo($rv['created_at']) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar Stats -->
    <div class="col-lg-4">
        <div class="skill-card p-4 mb-3">
            <div class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">Stats</div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted small">Skills Shared</span>
                <span class="fw-bold"><?= count($skillList) ?></span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted small">Reviews Received</span>
                <span class="fw-bold"><?= count($reviewList) ?></span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted small">Avg Rating</span>
                <span class="fw-bold"><?= $avgRating ?> / 5</span>
            </div>
            <div class="d-flex justify-content-between py-2">
                <span class="text-muted small">Member Since</span>
                <span class="fw-bold"><?= date('M Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>

        <?php
        // Category breakdown
        $catStats = $pdo->prepare("SELECT category, COUNT(*) as cnt FROM skills WHERE user_id=? AND status='active' GROUP BY category");
        $catStats->execute([$id]);
        $catList = $catStats->fetchAll();
        if (!empty($catList)):
        ?>
        <div class="skill-card p-4">
            <div class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">Skill Categories</div>
            <?php foreach ($catList as $c): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small"><?= getCategoryIcon($c['category']) ?> <?= sanitize($c['category']) ?></span>
                <span class="badge" style="background:var(--primary);color:#fff;"><?= $c['cnt'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
