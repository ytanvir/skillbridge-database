<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';
requireLogin();

$skills = $pdo->prepare("SELECT * FROM skills WHERE user_id=? ORDER BY created_at DESC");
$skills->execute([currentUserId()]);
$mySkills = $skills->fetchAll();

require_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-family:'Syne',sans-serif;">My Skills</h2>
        <p class="text-muted mb-0"><?= count($mySkills) ?> skill<?= count($mySkills)!=1?'s':'' ?> posted</p>
    </div>
    <a href="<?= BASE_URL ?>pages/skills/create.php" class="btn btn-accent">
        <i class="bi bi-plus-lg me-1"></i>Add New Skill
    </a>
</div>

<?php if (empty($mySkills)): ?>
<div class="empty-state">
    <div class="icon">📚</div>
    <h5>No skills yet</h5>
    <p>Share your first skill with the community!</p>
    <a href="<?= BASE_URL ?>pages/skills/create.php" class="btn btn-accent mt-2">Share a Skill</a>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($mySkills as $sk): ?>
    <div class="col-md-6">
        <div class="skill-card">
            <div class="skill-card-header">
                <div class="d-flex gap-2 mb-2 align-items-center justify-content-between">
                    <div>
                        <span class="category-pill"><?= getCategoryIcon($sk['category']) ?> <?= sanitize($sk['category']) ?></span>
                        <span class="category-pill type-<?= $sk['skill_type'] ?> ms-1"><?= $sk['skill_type']==='offer'?'✅ Offering':'🔍 Wanted' ?></span>
                    </div>
                    <span class="badge" style="background:<?= $sk['status']==='active'?'#dcfce7;color:#166534':'#fee2e2;color:#991b1b' ?>;">
                        <?= $sk['status']==='active'?'Active':'Closed' ?>
                    </span>
                </div>
                <div class="skill-title"><?= sanitize($sk['title']) ?></div>
                <p class="skill-desc mb-0"><?= sanitize(substr($sk['description'],0,100)) ?>...</p>
            </div>
            <div class="skill-card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <i class="bi bi-eye me-1"></i><?= $sk['views'] ?> views &nbsp;
                    <i class="bi bi-clock me-1"></i><?= timeAgo($sk['created_at']) ?>
                </div>
                <div class="d-flex gap-1">
                    <a href="<?= BASE_URL ?>pages/skills/view.php?id=<?= $sk['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                    <a href="<?= BASE_URL ?>pages/skills/edit.php?id=<?= $sk['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="<?= BASE_URL ?>pages/skills/delete.php?id=<?= $sk['id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this skill? This cannot be undone.')">Del</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>
