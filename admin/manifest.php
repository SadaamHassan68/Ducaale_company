<?php
require_once __DIR__ . '/../includes/admin_header.php';

$flight_num = $_GET['id'] ?? '';
if (!$flight_num) {
    die("Flight number required.");
}

// Fetch flight details
$stmt = $pdo->prepare("SELECT * FROM flights WHERE flight_number = ?");
$stmt->execute([$flight_num]);
$flight = $stmt->fetch();

if (!$flight) {
    die("Flight not found.");
}

// Fetch passenger list
$stmt = $pdo->prepare("
    SELECT u.name, u.email, b.booking_reference, b.status, s.seat_number, s.seat_class
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN seats s ON b.seat_id = s.id
    WHERE b.flight_id = ? AND b.status IN ('Confirmed', 'Checked-In')
    ORDER BY s.seat_number
");
$stmt->execute([$flight['id']]);
$passengers = $stmt->fetchAll();

$confirmedCount = count($passengers);
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Passenger Manifest</li>
            </ol>
        </nav>
        <h2 class="fw-bold text-dark">Flight Manifest: <?= htmlspecialchars($flight_num) ?></h2>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-printer me-1"></i> Print Manifest
        </a>
        <a href="manage_flights.php" class="btn btn-light border rounded-pill px-4">Back to Hub</a>
    </div>
</div>

<div class="card admin-card manifest-print-area">
    <div class="card-header bg-dark text-white p-4 border-0 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0">OFFICIAL PASSENGER MANIFEST</h4>
            <div class="small opacity-75 fw-bold text-uppercase tracking-widest">Ducaale Airlines Operations</div>
        </div>
        <div class="text-end">
            <div class="small opacity-75">Date: <?= date('d M Y') ?></div>
            <div class="fw-bold">Security Level: High</div>
        </div>
    </div>
    <div class="card-body p-5">
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="small text-muted text-uppercase fw-bold mb-1">Flight Number</div>
                <div class="fw-bold fs-5"><?= htmlspecialchars($flight['flight_number']) ?></div>
            </div>
            <div class="col-md-3 text-center">
                <div class="small text-muted text-uppercase fw-bold mb-1">Route</div>
                <div class="fw-bold fs-5"><?= htmlspecialchars($flight['origin']) ?> <i class="bi bi-arrow-right mx-1"></i> <?= htmlspecialchars($flight['destination']) ?></div>
            </div>
            <div class="col-md-3 text-center">
                <div class="small text-muted text-uppercase fw-bold mb-1">Departure</div>
                <div class="fw-bold fs-5"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
            </div>
            <div class="col-md-3 text-end">
                <div class="small text-muted text-uppercase fw-bold mb-1">Confirmed Pax</div>
                <div class="fw-bold fs-5 text-primary"><?= $confirmedCount ?> / <?= $flight['total_seats'] ?></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle border-top">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4">Seat</th>
                        <th class="py-3">Passenger Name</th>
                        <th class="py-3">Email Address</th>
                        <th class="py-3">Class</th>
                        <th class="py-3">Reference</th>
                        <th class="py-3 text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($passengers)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No confirmed passengers for this flight.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($passengers as $p): ?>
                            <tr>
                                <td class="py-3 px-4 fw-bold text-primary"><?= htmlspecialchars($p['seat_number']) ?></td>
                                <td class="py-3 fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="py-3 small text-muted"><?= htmlspecialchars($p['email']) ?></td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1"><?= htmlspecialchars($p['seat_class']) ?></span>
                                </td>
                                <td class="py-3 font-monospace small"><?= htmlspecialchars($p['booking_reference']) ?></td>
                                <td class="py-3 text-end">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1"><?= $p['status'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-5 pt-5 border-top border-dashed text-center d-none d-print-block">
            <div class="row">
                <div class="col-4">
                    <div class="border-top pt-2 mt-4 mx-4 small fw-bold text-muted">Captain Signature</div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mt-4 mx-4 small fw-bold text-muted">Ground Ops Supervisor</div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mt-4 mx-4 small fw-bold text-muted">Security Clearance</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .admin-main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .admin-sidebar, .admin-topbar { display: none !important; }
    .manifest-print-area { box-shadow: none !important; border: 1px solid #ddd !important; }
    body { background: #fff !important; }
}
.border-dashed { border-top: 2px dashed #e2e8f0 !important; }
</style>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
