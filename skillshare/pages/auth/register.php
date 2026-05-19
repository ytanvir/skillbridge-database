<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';

if (isLoggedIn()) redirect(BASE_URL . 'index.php');

$errors = [];
$name = $email = $department = $semester = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm'];
    $department = trim($_POST['department']);
    $semester   = trim($_POST['semester']);
    $bio        = trim($_POST['bio']);

    if (empty($name))       $errors[] = 'Full name is required.';
    if (empty($email))      $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)  $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $chk->execute([$email]);
        if ($chk->fetch()) $errors[] = 'Email already registered.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins  = $pdo->prepare("INSERT INTO users (name,email,password,bio,department,semester) VALUES (?,?,?,?,?,?)");
        $ins->execute([$name, $email, $hash, $bio, $department, $semester]);
        $newId = $pdo->lastInsertId();

        // Auto login
        $user = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $user->execute([$newId]);
        $userData = $user->fetch();
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user']    = $userData;

        setFlash('success', 'Welcome to SkillBridge, ' . $name . '!');
        redirect(BASE_URL . 'index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register - SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrap py-5">
    <div style="width:100%;max-width:520px;margin:0 auto;padding:0 16px;">
        <div class="auth-logo">
            <a href="<?= BASE_URL ?>index.php" style="text-decoration:none;">
                <span style="font-size:1.8rem;">⚡</span>
                <span>SkillBridge</span>
            </a>
        </div>
        <div class="form-card">
            <h4 class="fw-bold mb-1" style="font-family:'Syne',sans-serif;">Create your account</h4>
            <p class="text-muted mb-4 small">Join thousands of students sharing skills</p>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2">
                <?php foreach ($errors as $e): ?><div>• <?= sanitize($e) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= sanitize($name) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" value="<?= sanitize($email) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Min 6 chars" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm" class="form-control" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" placeholder="e.g. Computer Science" value="<?= sanitize($department) ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['1st','2nd','3rd','4th','5th','6th','7th','8th'] as $s): ?>
                            <option value="<?= $s ?>" <?= $semester===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Short Bio <span class="text-muted">(optional)</span></label>
                        <textarea name="bio" class="form-control" rows="2" placeholder="Tell others about yourself..."><?= sanitize($bio ?? '') ?></textarea>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn-submit">Create Account</button>
                    </div>
                </div>
            </form>
            <p class="text-center mt-3 mb-0 small text-muted">
                Already have an account? <a href="login.php" class="fw-bold" style="color:var(--accent);">Sign in</a>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
