<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';

$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$type     = trim($_GET['type']     ?? '');
$sort     = trim($_GET['sort']     ?? 'newest');

// Build query
$where  = ["s.status = 'active'"];
$params = [];

if ($search) {
    $where[]  = "(s.title LIKE ? OR s.description LIKE ? OR s.tags LIKE ?)";
    $params   = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($category) {
    $where[]  = "s.category = ?";
    $params[] = $category;
}
if ($type) {
    $where[]  = "s.skill_type = ?";
    $params[] = $type;
}

$orderBy = match($sort) {
    'views'  => 's.views DESC',
    'oldest' => 's.created_at ASC',
    default  => 's.created_at DESC',
};

$sql = "SELECT s.*, u.name AS uname, u.department
        FROM skills s JOIN users u ON s.user_id=u.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$skills = $stmt->fetchAll();

// All categories
$cats = $pdo->query("SELECT DISTINCT category FROM skills WHERE status='active' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

require_once '../../includes/header.php';
?>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-4">
        <div class="skill-card p-3">
            <div class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">🔎 Filter Skills</div>
            <form method="GET">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?= sanitize($search) ?>" placeholder="Keyword...">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($cats as $c): ?>
                        <option value="<?= sanitize($c) ?>" <?= $category===$c?'selected':'' ?>><?= getCategoryIcon($c) ?> <?= sanitize($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="offer"   <?= $type==='offer'  ?'selected':'' ?>>✅ Offering</option>
                        <option value="request" <?= $type==='request'?'selected':'' ?>>🔍 Wanted</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest First</option>
                        <option value="views"  <?= $sort==='views' ?'selected':'' ?>>Most Viewed</option>
                        <option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Oldest First</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm w-100" style="background:var(--primary);color:#fff;border-radius:8px;">Apply Filters</button>
                <?php if ($search||$category||$type): ?>
                <a href="browse.php" class="btn btn-sm btn-outline-secondary w-100 mt-2">Clear All</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Skills Grid -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="fw-bold"><?= count($skills) ?> skill<?= count($skills)!=1?'s':'' ?> found</span>
                <?php if ($search): ?><span class="text-muted ms-2">for "<?= sanitize($search) ?>"</span><?php endif; ?>
                <?php if ($category): ?><span class="badge" style="background:var(--accent);margin-left:6px;"><?= sanitize($category) ?></span><?php endif; ?>
            </div>
            <?php if (isLoggedIn()): ?>
            <a href="../skills/create.php" class="btn btn-accent btn-sm"><i class="bi bi-plus me-1"></i>Share a Skill</a>
            <?php endif; ?>
        </div>

        <?php if (empty($skills)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h5>No skills found</h5>
            <p>Try different keywords or filters</p>
        </div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($skills as $sk): ?>
            <div class="col-md-6">
                <div class="skill-card">
                    <div class="skill-card-header">
                        <div class="d-flex gap-2 mb-2">
                            <span class="category-pill"><?= getCategoryIcon($sk['category']) ?> <?= sanitize($sk['category']) ?></span>
                            <span class="category-pill type-<?= $sk['skill_type'] ?>"><?= $sk['skill_type']==='offer'?'✅ Offering':'🔍 Wanted' ?></span>
                        </div>
                        <div class="skill-title"><?= sanitize($sk['title']) ?></div>
                        <p class="skill-desc mb-0"><?= sanitize(substr($sk['description'],0,110)) ?>...</p>
                    </div>
                    <div class="skill-card-footer d-flex justify-content-between align-items-center">
                        <div class="user-mini">
                            <div class="avatar-xs"><?= strtoupper(substr($sk['uname'],0,1)) ?></div>
                            <div>
                                <div class="user-mini-name"><?= sanitize($sk['uname']) ?></div>
                                <div class="user-mini-dept"><?= sanitize($sk['department']) ?> · <i class="bi bi-eye"></i> <?= $sk['views'] ?></div>
                            </div>
                        </div>
                        <a href="view.php?id=<?= $sk['id'] ?>" class="btn btn-sm btn-accent">View →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
