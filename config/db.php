<?php
// Enable error reporting for debugging live server issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$is_local = (php_sapi_name() == 'cli' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1');

if ($is_local) {
    $host = '127.0.0.1';
    $db   = 'flight_booking_system';
    $user = 'root';
    $pass = '';
} else {
    // InfinityFree Credentials
    $host = 'sql208.infinityfree.com';
    $db   = 'if0_41895417_Booking';
    $user = 'if0_41895417'; 
    $pass = 'Yv0NUcZUFgp';
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage() . " (Check your Host, User, and Password in config/db.php)");
}
?>
