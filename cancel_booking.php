<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/User.php';

session_start();
$userAuth = new User($pdo);
$userAuth->requireRole('Passenger');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ref'])) {
    $ref = $_POST['ref'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Ensure the booking belongs to the user and is Pending
        $stmt = $pdo->prepare("SELECT id, seat_id, status FROM bookings WHERE booking_reference = ? AND user_id = ? FOR UPDATE");
        $stmt->execute([$ref, $user_id]);
        $booking = $stmt->fetch();

        if ($booking && $booking['status'] === 'Pending') {
            // Update booking to Cancelled
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
            $stmt->execute([$booking['id']]);

            // Free up the seat
            $stmt = $pdo->prepare("UPDATE seats SET status = 'Available' WHERE id = ?");
            $stmt->execute([$booking['seat_id']]);

            $pdo->commit();
            header("Location: dashboard.php?msg=cancelled");
            exit;
        } else {
            $pdo->rollBack();
            die("Invalid booking or booking cannot be cancelled.");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Error cancelling booking.");
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>
