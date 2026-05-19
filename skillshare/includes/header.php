<?php
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_URL . 'config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_URL . 'config/functions.php';

// Unread notification count
$notifCount = 0;
if (isLoggedIn()) {
    $ns = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $ns->execute([currentUserId()]);
    $notifCount = $ns->fetchColumn();
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
            <span class="brand-icon">⚡</span> <?= SITE_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>pages/skills/browse.php">Browse Skills</a>
                </li>
                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>pages/skills/create.php">
                        <i class="bi bi-plus-circle me-1"></i>Share Skill
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Search -->
            <form class="d-flex me-3" method="GET" action="<?= BASE_URL ?>pages/skills/browse.php">
                <div class="search-wrap">
                    <input class="form-control search-input" type="search" name="search" placeholder="Search skills...">
                    <i class="bi bi-search search-icon"></i>
                </div>
            </form>

            <ul class="navbar-nav align-items-center">
                <?php if (isLoggedIn()): ?>
                    <!-- Notifications -->
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($notifCount > 0): ?>
                            <span class="notif-badge"><?= $notifCount ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0">
                            <div class="notif-header">Notifications</div>
                            <?php
                            $notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 6");
                            $notifs->execute([currentUserId()]);
                            $notifList = $notifs->fetchAll();
                            if (empty($notifList)):
                            ?>
                            <div class="p-3 text-muted text-center small">No notifications yet</div>
                            <?php else: foreach ($notifList as $n): ?>
                            <a href="<?= BASE_URL . ltrim($n['link'], '/') ?>" class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                                <i class="bi bi-bell-fill me-2 text-warning"></i>
                                <?= sanitize($n['message']) ?>
                                <div class="notif-time"><?= timeAgo($n['created_at']) ?></div>
                            </a>
                            <?php endforeach; endif; ?>
                            <a href="<?= BASE_URL ?>pages/profile/notifications.php" class="notif-footer">View all</a>
                        </div>
                    </li>
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            <div class="avatar-sm"><?= strtoupper(substr(currentUser()['name'], 0, 1)) ?></div>
                            <span class="d-none d-lg-inline"><?= sanitize(explode(' ', currentUser()['name'])[0]) ?></span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>pages/profile/view.php?id=<?= currentUserId() ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>pages/profile/my_skills.php"><i class="bi bi-collection me-2"></i>My Skills</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>pages/profile/requests.php"><i class="bi bi-envelope me-2"></i>Requests</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>pages/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>pages/auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-accent ms-2" href="<?= BASE_URL ?>pages/auth/register.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
    <?= sanitize($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
