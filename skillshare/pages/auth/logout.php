<?php
define('BASE_URL', '/skillshare/');
require_once '../../config/functions.php';
session_destroy();
header('Location: ' . BASE_URL . 'pages/auth/login.php');
exit;
