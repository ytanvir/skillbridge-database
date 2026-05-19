<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM skills WHERE id=? AND user_id=?");
$stmt->execute([$id, currentUserId()]);
$skill = $stmt->fetch();

if (!$skill) { setFlash('danger','Skill not found or access denied.'); redirect(BASE_URL.'pages/profile/my_skills.php'); }

$errors = [];
$title       = $skill['title'];
$description = $skill['description'];
$category    = $skill['category'];
$tags        = $skill['tags'];
$skill_type  = $skill['skill_type'];
$status      = $skill['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category    = trim($_POST['category']);
    $tags        = trim($_POST['tags']);
    $skill_type  = $_POST['skill_type'] === 'request' ? 'request' : 'offer';
    $status      = $_POST['status'] === 'closed' ? 'closed' : 'active';

    if (empty($title))       $errors[] = 'Title is required.';
    if (empty($description)) $errors[] = 'Description is required.';
    if (empty($category))    $errors[] = 'Category is required.';

    if (empty($errors)) {
        $pdo->prepare("UPDATE skills SET title=?,description=?,category=?,tags=?,skill_type=?,status=? WHERE id=?")
            ->execute([$title, $description, $category, $tags, $skill_type, $status, $id]);
        setFlash('success','Skill updated successfully!');
        redirect(BASE_URL.'pages/profile/my_skills.php');
    }
}

$categories = ['Programming','Data Science','Design','Mobile','Language','Photography','Mathematics','Music','Business','Science','Other'];
require_once '../../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold" style="font-family:'Syne',sans-serif;">Edit Skill</h2>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><?php foreach ($errors as $e): ?><div>• <?= sanitize($e) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="form-card" style="max-width:640px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="skill_type" class="form-select">
                <option value="offer"   <?= $skill_type==='offer'  ?'selected':'' ?>>✅ Offering (I can teach)</option>
                <option value="request" <?= $skill_type==='request'?'selected':'' ?>>🔍 Requesting (I want to learn)</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" value="<?= sanitize($title) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category *</label>
            <select name="category" class="form-select" required>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c ?>" <?= $category===$c?'selected':'' ?>><?= getCategoryIcon($c) ?> <?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Description *</label>
            <textarea name="description" class="form-control" rows="5" required><?= sanitize($description) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Tags</label>
            <input type="text" name="tags" class="form-control" value="<?= sanitize($tags) ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= $status==='active'?'selected':'' ?>>✅ Active</option>
                <option value="closed" <?= $status==='closed'?'selected':'' ?>>🔒 Closed</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn-submit" style="width:auto;padding:11px 32px;">Update Skill</button>
            <a href="<?= BASE_URL ?>pages/profile/my_skills.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
