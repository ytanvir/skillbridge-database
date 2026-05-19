<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once 'config/db.php';
require_once 'config/functions.php';

// Stats
$totalSkills = $pdo->query("SELECT COUNT(*) FROM skills WHERE status='active'")->fetchColumn();
$totalUsers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalCats   = $pdo->query("SELECT COUNT(DISTINCT category) FROM skills")->fetchColumn();

// Latest skills
$latest = $pdo->query("
    SELECT s.*, u.name AS uname, u.department
    FROM skills s JOIN users u ON s.user_id=u.id
    WHERE s.status='active'
    ORDER BY s.created_at DESC LIMIT 6
")->fetchAll();

// Categories with count
$categories = $pdo->query("
    SELECT category, COUNT(*) as cnt
    FROM skills WHERE status='active'
    GROUP BY category ORDER BY cnt DESC LIMIT 8
")->fetchAll();

// Top rated users
$topUsers = $pdo->query("
    SELECT u.id, u.name, u.department,
           ROUND(AVG(r.rating),1) AS avg_rating,
           COUNT(r.id) AS total_reviews
    FROM users u
    JOIN reviews r ON r.reviewed_id = u.id
    GROUP BY u.id
    ORDER BY avg_rating DESC LIMIT 4
")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Hero -->
<div class="hero">
    <div class="container position-relative" style="z-index:1;">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-title">Learn, Teach &<br><span>Grow Together</span></div>
                <p class="hero-sub">A platform where students share their skills, find mentors, and build connections across campus.</p>
                <form class="hero-search d-flex" method="GET" action="pages/skills/browse.php">
                    <input class="form-control" name="search" placeholder="e.g. Python, Graphic Design, Flutter...">
                    <button type="submit" class="btn"><i class="bi bi-search me-1"></i>Search</button>
                </form>
                <div class="hero-stats">
                    <div>
                        <div class="hero-stat-num"><?= $totalSkills ?>+</div>
                        <div class="hero-stat-lbl">Skills Shared</div>
                    </div>
                    <div>
                        <div class="hero-stat-num"><?= $totalUsers ?>+</div>
                        <div class="hero-stat-lbl">Students</div>
                    </div>
                    <div>
                        <div class="hero-stat-num"><?= $totalCats ?>+</div>
                        <div class="hero-stat-lbl">Categories</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <div style="font-size:8rem; opacity:0.3;">🎓</div>
            </div>
        </div>
    </div>
</div>

<!-- Categories -->
<div class="mt-5">
    <div class="section-heading">Browse by Category</div>
    <p class="section-sub">Find skills in the areas you're passionate about</p>
    <div class="row g-3">
        <?php foreach ($categories as $cat): ?>
        <div class="col-6 col-md-3">
            <a href="pages/skills/browse.php?category=<?= urlencode($cat['category']) ?>" class="cat-card">
                <div class="cat-icon"><?= getCategoryIcon($cat['category']) ?></div>
                <div class="cat-name"><?= sanitize($cat['category']) ?></div>
                <div class="cat-count"><?= $cat['cnt'] ?> skill<?= $cat['cnt'] != 1 ? 's' : '' ?></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Latest Skills -->
<div class="mt-5">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <div class="section-heading">Latest Skills</div>
            <p class="section-sub mb-0">Recently shared by students</p>
        </div>
        <a href="pages/skills/browse.php" class="btn btn-outline-secondary btn-sm">View All →</a>
    </div>
    <div class="row g-3">
        <?php foreach ($latest as $sk): ?>
        <div class="col-md-6 col-lg-4">
            <div class="skill-card">
                <div class="skill-card-header">
                    <span class="category-pill"><?= getCategoryIcon($sk['category']) ?> <?= sanitize($sk['category']) ?></span>
                    <span class="ms-1 type-<?= $sk['skill_type'] ?> category-pill"><?= $sk['skill_type'] === 'offer' ? '✅ Offering' : '🔍 Wanted' ?></span>
                    <div class="skill-title mt-2"><?= sanitize($sk['title']) ?></div>
                    <p class="skill-desc mb-0"><?= sanitize(substr($sk['description'], 0, 100)) ?>...</p>
                </div>
                <div class="skill-card-footer d-flex justify-content-between align-items-center">
                    <div class="user-mini">
                        <div class="avatar-xs"><?= strtoupper(substr($sk['uname'],0,1)) ?></div>
                        <div>
                            <div class="user-mini-name"><?= sanitize($sk['uname']) ?></div>
                            <div class="user-mini-dept"><?= sanitize($sk['department']) ?></div>
                        </div>
                    </div>
                    <a href="pages/skills/view.php?id=<?= $sk['id'] ?>" class="btn btn-sm btn-accent">View</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Top Rated Students -->
<?php if (!empty($topUsers)): ?>
<div class="mt-5 mb-4">
    <div class="section-heading">Top Rated Students</div>
    <p class="section-sub">Highly rated by their peers</p>
    <div class="row g-3">
        <?php foreach ($topUsers as $u): ?>
        <div class="col-sm-6 col-lg-3">
            <div class="skill-card text-center p-4">
                <div class="avatar-lg mx-auto mb-3"><?= strtoupper(substr($u['name'],0,1)) ?></div>
                <div class="fw-bold" style="font-family:'Syne',sans-serif;"><?= sanitize($u['name']) ?></div>
                <div class="text-muted small mb-2"><?= sanitize($u['department']) ?></div>
                <div class="stars">
                    <?= str_repeat('★', round($u['avg_rating'])) ?><?= str_repeat('☆', 5-round($u['avg_rating'])) ?>
                </div>
                <div class="small text-muted"><?= $u['avg_rating'] ?> · <?= $u['total_reviews'] ?> reviews</div>
                <a href="pages/profile/view.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary mt-3 w-100">View Profile</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
