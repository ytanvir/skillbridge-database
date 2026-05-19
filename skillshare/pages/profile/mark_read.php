<?php
define('BASE_URL', '/skillshare/');
require_once '../../config/db.php';
require_once '../../config/functions.php';
if (isLoggedIn()) {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([currentUserId()]);
}
http_response_code(200);
