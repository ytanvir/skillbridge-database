<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';
requireLogin();

$errors = [];
$title = $description = $category = $tags = '';
$skill_type = 'offer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category    = trim($_POST['category']);
    $tags        = trim($_POST['tags']);
    $skill_type  = $_POST['skill_type'] === 'request' ? 'request' : 'offer';

    if (empty($title))       $errors[] = 'Title is required.';
    if (strlen($title) < 10) $errors[] = 'Title must be at least 10 characters.';
    if (empty($description)) $errors[] = 'Description is required.';
    if (strlen($description) < 30) $errors[] = 'Please write a more detailed description (min 30 chars).';
    if (empty($category))    $errors[] = 'Category is required.';

    if (empty($errors)) {
        $pdo->prepare("INSERT INTO skills (user_id,title,description,category,tags,skill_type) VALUES (?,?,?,?,?,?)")
            ->execute([currentUserId(), $title, $description, $category, $tags, $skill_type]);
        setFlash('success','Skill posted successfully!');
        redirect(BASE_URL . 'pages/profile/my_skills.php');
    }
}

$categories = ['Programming','Data Science','Design','Mobile','Language','Photography','Mathematics','Music','Business','Science','Other'];
require_once '../../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold" style="font-family:'Syne',sans-serif;">Share a Skill</h2>
    <p class="text-muted">Post a skill you can teach, or something you want to learn.</p>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $e): ?><div>• <?= sanitize($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="form-card" style="max-width:640px;">
    <form method="POST">
        <!-- Type Toggle -->
        <div class="mb-4">
            <label class="form-label fw-bold">I want to...</label>
            <div class="d-flex gap-3">
                <label class="flex-fill">
                    <input type="radio" name="skill_type" value="offer" <?= $skill_type==='offer'?'checked':'' ?> class="d-none type-radio" id="typeOffer">
                    <div class="type-toggle-btn" id="labelOffer" onclick="setType('offer')" style="border:2px solid var(--border);border-radius:12px;padding:14px;text-align:center;cursor:pointer;transition:all 0.2s;">
                        <div style="font-size:1.5rem;">✅</div>
                        <div class="fw-bold small">Offer a Skill</div>
                        <div class="text-muted" style="font-size:0.75rem;">I can teach this</div>
                    </div>
                </label>
                <label class="flex-fill">
                    <input type="radio" name="skill_type" value="request" <?= $skill_type==='request'?'checked':'' ?> class="d-none type-radio" id="typeRequest">
                    <div class="type-toggle-btn" id="labelRequest" onclick="setType('request')" style="border:2px solid var(--border);border-radius:12px;padding:14px;text-align:center;cursor:pointer;transition:all 0.2s;">
                        <div style="font-size:1.5rem;">🔍</div>
                        <div class="fw-bold small">Request a Skill</div>
                        <div class="text-muted" style="font-size:0.75rem;">I want to learn this</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" value="<?= sanitize($title) ?>"
                   placeholder="e.g. Web Development with HTML, CSS & JavaScript" required>
            <div class="form-text">Be specific and descriptive (min 10 chars)</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Category *</label>
            <select name="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c ?>" <?= $category===$c?'selected':'' ?>><?= getCategoryIcon($c) ?> <?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Description *</label>
            <textarea name="description" class="form-control" rows="5" required placeholder="Describe the skill in detail — what topics you'll cover, who it's for, your experience level, etc."><?= sanitize($description) ?></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label">Tags <span class="text-muted">(optional)</span></label>
            <input type="text" name="tags" class="form-control" value="<?= sanitize($tags) ?>" placeholder="e.g. html,css,javascript (comma separated)">
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn-submit" style="width:auto;padding:11px 32px;">Post Skill</button>
            <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function setType(type) {
    const offerEl   = document.getElementById('labelOffer');
    const requestEl = document.getElementById('labelRequest');
    document.getElementById('typeOffer').checked   = type === 'offer';
    document.getElementById('typeRequest').checked = type === 'request';
    offerEl.style.borderColor   = type === 'offer'   ? 'var(--accent)' : 'var(--border)';
    requestEl.style.borderColor = type === 'request' ? 'var(--accent)' : 'var(--border)';
    offerEl.style.background    = type === 'offer'   ? '#fff7ed' : '#fff';
    requestEl.style.background  = type === 'request' ? '#fff7ed' : '#fff';
}
setType('<?= $skill_type ?>');
</script>

<?php require_once '../../includes/footer.php'; ?>
