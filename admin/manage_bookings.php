<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../includes/admin_header.php';

$error = '';
$success = '';

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $booking_id = $_POST['booking_id'];
    $seat_id = $_POST['seat_id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        $stmt = $pdo->prepare("UPDATE seats SET status = 'Available' WHERE id = ?");
        $stmt->execute([$seat_id]);
        
        $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'Cancel Booking', "Cancelled booking ID $booking_id"]);
        
        $pdo->commit();
        $success = "Booking has been successfully cancelled.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error cancelling booking: " . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
    $booking_ref = $_POST['booking_ref'];
    
    require_once __DIR__ . '/../classes/Booking.php';
    $bookingManager = new Booking($pdo);
    $result = $bookingManager->confirmPayment($booking_ref);
    
    if ($result['success']) {
        // Fetch passenger details for notification - with fallback to user account email
        $stmt = $pdo->prepare("
            SELECT COALESCE(NULLIF(b.passenger_email, ''), u.email) as final_email, 
                   COALESCE(NULLIF(b.passenger_name, ''), u.name) as final_name, 
                   f.destination 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id
            JOIN flights f ON b.flight_id = f.id 
            WHERE b.booking_reference = ?
        ");
        $stmt->execute([$booking_ref]);
        $details = $stmt->fetch();

        if ($details && !empty($details['final_email'])) {
            $emailSubject = "Ticket Confirmed! Your Ducaale Airline Pass - $booking_ref";
            $emailMessage = "Dear " . $details['final_name'] . ",\n\nYour payment has been verified and your booking to " . $details['destination'] . " is now CONFIRMED.\nBooking Reference: $booking_ref\n\nYou can now download and print your Boarding Pass here: " . base_url('ticket.php?pnr=' . $booking_ref) . "\n\nFly Elite with Ducaale Airline.";
            sendEmailNotification($details['final_email'], $emailSubject, $emailMessage);
        }

        $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'Confirm Payment', "Confirmed offline payment for booking $booking_ref"]);
        $success = "Payment confirmed. Booking is now active and notification sent.";
    } else {
        $error = "Error confirming payment: " . $result['message'];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $booking_id = $_POST['booking_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'Delete Booking', "Permanently deleted booking ID $booking_id"]);
        
        $success = "Booking has been permanently deleted.";
    } catch (Exception $e) {
        $error = "Error deleting booking: " . $e->getMessage();
    }
}

// Fetch recent bookings
$stmt = $pdo->query("
    SELECT b.id, b.booking_reference, b.status as booking_status, b.final_price, b.created_at, b.seat_id,
           b.passenger_name, b.passenger_email, b.passenger_phone,
           f.flight_number, f.origin, f.destination, f.departure_time,
           s.seat_number, s.seat_class
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN seats s ON b.seat_id = s.id
    ORDER BY b.created_at DESC
    LIMIT 100
");
$bookings = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Bookings</h2>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card admin-card">
    <div class="card-header bg-transparent border-bottom p-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">Recent Bookings</h5>
        <div class="input-group" style="width: 250px;">
            <span class="input-group-text bg-light border-end-0 border-light shadow-sm"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control bg-light border-start-0 border-light shadow-sm" id="bookingSearch" placeholder="Search...">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0" id="bookingsTable">
                <thead>
                    <tr>
                        <th class="ps-4">Booking Ref</th>
                        <th>Passenger</th>
                        <th>Flight Info</th>
                        <th>Seat</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No bookings found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): 
                            $statusClass = 'bg-secondary';
                            if ($b['booking_status'] == 'Confirmed') $statusClass = 'bg-success';
                            if ($b['booking_status'] == 'Pending') $statusClass = 'bg-warning text-dark';
                            if ($b['booking_status'] == 'Cancelled') $statusClass = 'bg-danger';
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="font-monospace fw-bold text-primary"><?= htmlspecialchars($b['booking_reference']) ?></span>
                                    <div class="small text-muted"><?= date('M d, Y', strtotime($b['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($b['passenger_name']) ?></div>
                                    <div class="small text-muted mb-1"><?= htmlspecialchars($b['passenger_email']) ?></div>
                                    <?php if (!empty($b['passenger_phone'])): 
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $b['passenger_phone']);
                                        $waMessage = urlencode("Haye " . $b['passenger_name'] . ",\n\nTikidhkaaga Ducaale Airline waa diyaar! \nPNR: " . $b['booking_reference'] . "\nU socda: " . $b['destination'] . "\n\nKa soo dejiso halkan: " . base_url('ticket.php?pnr=' . $b['booking_reference']));
                                    ?>
                                        <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waMessage ?>" target="_blank" class="badge bg-success bg-opacity-10 text-success text-decoration-none border-0 py-2 px-3">
                                            <i class="bi bi-whatsapp me-2"></i><?= htmlspecialchars($b['passenger_phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border-0 py-2 px-3">
                                            <i class="bi bi-telephone-x me-2"></i>No Contact
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($b['flight_number']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($b['origin']) ?> → <?= htmlspecialchars($b['destination']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($b['seat_number']) ?></span>
                                    <div class="small text-muted"><?= htmlspecialchars($b['seat_class']) ?></div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">$<?= number_format($b['final_price'], 2) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?> rounded-pill"><?= $b['booking_status'] ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <?php if ($b['booking_status'] === 'Pending'): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Confirm offline payment for this booking?');">
                                                <input type="hidden" name="action" value="confirm">
                                                <input type="hidden" name="booking_ref" value="<?= htmlspecialchars($b['booking_reference']) ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Confirm Payment">
                                                    <i class="bi bi-check-circle"></i> Confirm
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($b['booking_status'] !== 'Cancelled'): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                <input type="hidden" name="seat_id" value="<?= $b['seat_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Cancel Booking">
                                                    <i class="bi bi-x-circle"></i> Cancel
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light text-muted" disabled>Cancelled</button>
                                        <?php endif; ?>

                                        <form method="POST" class="d-inline" onsubmit="return confirm('PERMANENT DELETE: Are you sure you want to completely REMOVE this record? This cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Permanently Delete">
                                                <i class="bi bi-trash3"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('bookingSearch').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('#bookingsTable tbody tr');
    
    rows.forEach(row => {
        if(row.innerText.toLowerCase().indexOf(value) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
