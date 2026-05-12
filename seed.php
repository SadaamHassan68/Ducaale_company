<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->beginTransaction();

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DELETE FROM activity_logs");
    $pdo->exec("DELETE FROM bookings");
    $pdo->exec("DELETE FROM seats");
    $pdo->exec("DELETE FROM flights");
    $pdo->exec("DELETE FROM users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    
    $stmt->execute(['John Doe (Passenger)', 'john@example.com', $password_hash, 'Passenger']);
    $user_id = $pdo->lastInsertId();
    
    $stmt->execute(['Admin User', 'admin@example.com', $password_hash, 'Admin']);
    $stmt->execute(['Staff User', 'staff@example.com', $password_hash, 'Staff']);

    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    $flightsData = [
        ['AERO-101', 'JFK', 'LHR', "$tomorrow 08:00:00", "$tomorrow 20:00:00", 500.00, 60, 'Boeing 777', 'Scheduled'],
        ['AERO-202', 'JFK', 'LAX', "$tomorrow 10:30:00", "$tomorrow 13:45:00", 350.00, 60, 'Airbus A320', 'Scheduled']
    ];

    $stmt = $pdo->prepare("INSERT INTO flights (flight_number, origin, destination, departure_time, arrival_time, base_price, total_seats, aircraft_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $flightIds = [];
    foreach ($flightsData as $f) {
        $stmt->execute($f);
        $flightIds[] = $pdo->lastInsertId();
    }

    $seatColumns = ['A', 'B', 'C', 'D', 'E', 'F'];
    
    $stmt = $pdo->prepare("INSERT INTO seats (flight_id, seat_number, seat_class, seat_type, status) VALUES (?, ?, ?, ?, ?)");

    foreach ($flightIds as $fid) {
        for ($row = 1; $row <= 10; $row++) {
            
            $seat_class = 'Economy';
            if ($row <= 2) $seat_class = 'First Class';
            elseif ($row <= 4) $seat_class = 'Business';

            foreach ($seatColumns as $col) {
                $seat_number = $row . $col;
                
                $seat_type = 'Middle';
                if ($col === 'A' || $col === 'F') $seat_type = 'Window';
                if ($col === 'C' || $col === 'D') $seat_type = 'Aisle';
                if ($row === 4 || $row === 5) $seat_type = 'Emergency Exit';
                
                $status = (rand(1, 100) > 85) ? 'Booked' : 'Available';

                $stmt->execute([$fid, $seat_number, $seat_class, $seat_type, $status]);
            }
        }
    }

    $pdo->commit();
    echo "<h1>Database seeded successfully!</h1>";
    echo "<p><a href='index.php'>Go back to Search</a></p>";

} catch (Exception $e) {
    echo "<h1>Error seeding database:</h1><p>" . $e->getMessage() . "</p>";
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
?>
