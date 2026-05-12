<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$userAuth = new User($pdo);
$userAuth->logout();
?>
