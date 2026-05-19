<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';
requireLogin();

$user   = currentUser();
$errors = [];

// Reload fresh from DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $bio        = trim($_POST['bio']);
    $department = trim($_POST['department']);
    $semester   = trim($_POST['semester']);
    $newPass    = $_POST['new_password'];
    $confirm    = $_POST['confirm_password'];

    if (empty($name)) $errors[] = 'Name is required.';

    if (!empty($newPass)) {
        if (strlen($newPass) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($newPass !== $confirm) $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        if (!empty($newPass)) {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET name=?,bio=?,department=?,semester=?,password=? WHERE id=?")
                ->execute([$name,$bio,$department,$semester,$hash,currentUserId()]);
        } else {
            $pdo->prepare("UPDATE users SET name=?,bio=?,department=?,semester=? WHERE id=?")
                ->execute([$name,$bio,$department,$semester,currentUserId()]);
        }
        // Refresh session
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([currentUserId()]);
        $_SESSION['user'] = $stmt->fetch();

        setFlash('success','Profile updated successfully!');
        redirect(BASE_URL.'pages/profile/view.php?id='.currentUserId());
    }
}

require_once '../../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold" style="font-family:'Syne',sans-serif;">Edit Profile</h2>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $e): ?><div>• <?= sanitize($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Tell others about yourself..."><?= sanitize($user['bio'] ?? '') ?></textarea>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-7">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="<?= sanitize($user['department'] ?? '') ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select">
                    <option value="">Select</option>
                    <?php foreach (['1st','2nd','3rd','4th','5th','6th','7th','8th'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($user['semester']??'')===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <hr>
        <div class="fw-bold mb-3 small text-muted text-uppercase">Change Password (leave blank to keep current)</div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters">
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn-submit" style="width:auto;padding:11px 32px;">Save Changes</button>
            <a href="view.php?id=<?= currentUserId() ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
