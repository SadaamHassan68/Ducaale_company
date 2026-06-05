<?php
// Enable error reporting for debugging live server issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Local XAMPP configuration
$host = 'localhost';
$db   = 'flight_booking_system'; // Using your existing database
$user = 'root'; 
$pass = '';

// Live Server configuration (Commented out for local testing)
// $host = 'sql102.infinityfree.com';
// $db   = 'if0_42086954_booking';
// $user = 'if0_42086954'; 
// $pass = 'OJntmcuzNxicFS';

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
