<?php
define('BASE_URL', '/skillshare/');
require_once '../../config/db.php';
require_once '../../config/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM skills WHERE id=? AND user_id=?");
$stmt->execute([$id, currentUserId()]);
$skill = $stmt->fetch();

if ($skill) {
    $pdo->prepare("DELETE FROM skills WHERE id=?")->execute([$id]);
    setFlash('success', 'Skill deleted successfully.');
} else {
    setFlash('danger', 'Skill not found or access denied.');
}

redirect(BASE_URL . 'pages/profile/my_skills.php');
