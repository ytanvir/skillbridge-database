<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';

if (isLoggedIn()) redirect(BASE_URL . 'index.php');

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $loginOk = false;

        if ($user) {
            // Check 1: bcrypt hash — for users who registered via the app
            if (password_verify($password, $user['password'])) {
                $loginOk = true;
            }
            // Check 2: plain text — for passwords set directly in phpMyAdmin
            elseif ($user['password'] === $password) {
                $loginOk = true;
            }
            // Check 3: MD5 — extra fallback
            elseif ($user['password'] === md5($password)) {
                $loginOk = true;
            }
        }

        if ($loginOk) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = $user;
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect(BASE_URL . 'index.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
    <div style="width:100%;max-width:420px;padding:0 16px;">
        <div class="auth-logo">
            <a href="<?= BASE_URL ?>index.php" style="text-decoration:none;">
                <span style="font-size:1.8rem;">⚡</span>
                <span>SkillBridge</span>
            </a>
        </div>
        <div class="form-card">
            <h4 class="fw-bold mb-1" style="font-family:'Syne',sans-serif;">Sign in</h4>
            <p class="text-muted small mb-4">Welcome back! Enter your details below.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <!-- Demo credentials hint -->
            <div class="alert alert-info py-2 small mb-3" style="background:#e0f2fe;border:none;color:#0369a1;border-radius:8px;">
                <strong>Demo login:</strong> anika@example.com / password
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize($email) ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-submit">Sign In</button>
            </form>
            <p class="text-center mt-3 mb-0 small text-muted">
                Don't have an account? <a href="register.php" class="fw-bold" style="color:var(--accent);">Register</a>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
