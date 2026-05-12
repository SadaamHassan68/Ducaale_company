<?php
require_once __DIR__ . '/../config/db.php';
try {
    $pdo->exec("ALTER TABLE flights ADD COLUMN image_url VARCHAR(255) DEFAULT NULL");
    echo "Column image_url added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
