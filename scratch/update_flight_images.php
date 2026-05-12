<?php
require_once __DIR__ . '/../config/db.php';

$images = [
    'DXB' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?q=80&w=2070&auto=format&fit=crop',
    'DUB' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?q=80&w=2070&auto=format&fit=crop',
    'MOG' => 'https://images.unsplash.com/photo-1596492784531-6e6eb5ea9993?q=80&w=1974&auto=format&fit=crop',
    'LHR' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?q=80&w=2070&auto=format&fit=crop',
    'JFK' => 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?q=80&w=2070&auto=format&fit=crop',
    'IST' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?q=80&w=2071&auto=format&fit=crop',
    'NBO' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?q=80&w=2064&auto=format&fit=crop'
];

try {
    foreach ($images as $code => $url) {
        $stmt = $pdo->prepare("UPDATE flights SET image_url = :url WHERE destination LIKE :code");
        $stmt->execute(['url' => $url, 'code' => "%$code%"]);
    }
    echo "Flight images updated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
