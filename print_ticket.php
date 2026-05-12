<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$ref = $_GET['ref'] ?? '';
if (!$ref || !isset($_SESSION['user_id'])) {
    die("Access Denied or Invalid Ticket.");
}

// Fetch the confirmed booking details
$stmt = $pdo->prepare("
    SELECT b.id, b.booking_reference, b.final_price, b.status, b.created_at,
           f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time, f.aircraft_type,
           s.seat_number, s.seat_class,
           u.name as passenger_name
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN seats s ON b.seat_id = s.id
    JOIN users u ON b.user_id = u.id
    WHERE b.booking_reference = ? AND b.user_id = ?
");
$stmt->execute([$ref, $_SESSION['user_id']]);
$ticket = $stmt->fetch();

if (!$ticket || $ticket['status'] !== 'Confirmed') {
    die("Ticket not found or booking is not confirmed.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boarding Pass - <?= htmlspecialchars($ticket['booking_reference']) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #fff; padding: 20px; }
        .boarding-pass { border: 2px solid #000; border-radius: 15px; overflow: hidden; max-width: 800px; margin: 0 auto; position: relative; }
        .ticket-header { background: #000; color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .ticket-body { padding: 30px; display: flex; }
        .main-info { flex: 2; border-right: 2px dashed #ccc; padding-right: 30px; }
        .side-info { flex: 1; padding-left: 30px; background: #f8f9fa; }
        .airport-code { font-size: 3.5rem; font-weight: 800; line-height: 1; margin-bottom: 0; color: #0d6efd; }
        .label { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-uppercase: uppercase; margin-bottom: 2px; }
        .value { font-size: 1.1rem; font-weight: 700; color: #000; margin-bottom: 15px; }
        .qr-code { text-align: center; margin-top: 20px; }
        .qr-code i { font-size: 6rem; color: #000; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .boarding-pass { border: 2px solid #000 !important; box-shadow: none !important; }
            .ticket-header { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
            .side-info { background: #f8f9fa !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container text-center no-print mb-4">
        <button class="btn btn-primary fw-bold px-4" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print Boarding Pass</button>
        <p class="small text-muted mt-2">If the print dialog doesn't open automatically, click the button above.</p>
    </div>

    <div class="boarding-pass shadow-sm">
        <div class="ticket-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-airplane-engines-fill fs-3"></i>
                <h4 class="mb-0 fw-bold">WEHLIYE AIRLINE</h4>
            </div>
            <div class="text-end">
                <div class="label text-white-50">Booking Reference (PNR)</div>
                <h3 class="mb-0 fw-bold text-primary"><?= htmlspecialchars($ticket['booking_reference']) ?></h3>
            </div>
        </div>

        <div class="ticket-body">
            <div class="main-info">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="label">Passenger Name</div>
                        <div class="value fs-4 text-uppercase"><?= htmlspecialchars($ticket['passenger_name']) ?></div>
                    </div>
                </div>

                <div class="row align-items-center mb-4">
                    <div class="col-5">
                        <div class="airport-code text-uppercase"><?= htmlspecialchars($ticket['origin']) ?></div>
                        <div class="value"><?= date('H:i', strtotime($ticket['departure_time'])) ?></div>
                        <div class="small text-muted"><?= date('M d, Y', strtotime($ticket['departure_time'])) ?></div>
                    </div>
                    <div class="col-2 text-center">
                        <i class="bi bi-airplane fs-2 text-muted"></i>
                    </div>
                    <div class="col-5 text-end">
                        <div class="airport-code text-uppercase"><?= htmlspecialchars($ticket['destination']) ?></div>
                        <div class="value"><?= date('H:i', strtotime($ticket['arrival_time'])) ?></div>
                        <div class="small text-muted"><?= date('M d, Y', strtotime($ticket['arrival_time'])) ?></div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-4">
                        <div class="label">Flight</div>
                        <div class="value"><?= htmlspecialchars($ticket['flight_number']) ?></div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="label">Gate</div>
                        <div class="value">TBA</div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="label">Boarding</div>
                        <div class="value text-danger"><?= date('H:i', strtotime('-40 minutes', strtotime($ticket['departure_time']))) ?></div>
                    </div>
                </div>
            </div>

            <div class="side-info">
                <div class="mb-3">
                    <div class="label">Seat</div>
                    <div class="value fs-2"><?= htmlspecialchars($ticket['seat_number']) ?></div>
                </div>
                <div class="mb-3">
                    <div class="label">Class</div>
                    <div class="value"><?= htmlspecialchars($ticket['seat_class']) ?></div>
                </div>
                <div class="mb-3">
                    <div class="label">Aircraft</div>
                    <div class="value small"><?= htmlspecialchars($ticket['aircraft_type']) ?></div>
                </div>
                
                <div class="qr-code">
                    <i class="bi bi-qr-code"></i>
                    <div class="small text-muted font-monospace mt-1">SCAN AT GATE</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container text-center mt-4">
        <p class="text-muted small">Please present this boarding pass and a valid ID at the airport check-in counter.</p>
    </div>

</body>
</html>
