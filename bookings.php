<?php
require_once __DIR__ . '/includes/header.php';

if (!$userAuth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'All';

// Build Query
$sql = "
    SELECT b.id as booking_id, b.booking_reference, b.final_price, b.status, b.created_at,
           f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time, f.aircraft_type,
           s.seat_number, s.seat_class
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN seats s ON b.seat_id = s.id
    WHERE b.user_id = :user_id
";

if ($status_filter !== 'All') {
    if ($status_filter === 'Past') {
        $sql .= " AND f.departure_time < NOW()";
    } else {
        $sql .= " AND b.status = :status AND f.departure_time >= NOW()";
    }
}

$sql .= " ORDER BY f.departure_time DESC";

$stmt = $pdo->prepare($sql);
$params = ['user_id' => $user_id];
if ($status_filter !== 'All' && $status_filter !== 'Past') {
    $params['status'] = $status_filter;
}
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<!-- Header -->
<div class="bg-navy-dark py-5 text-white text-center no-print">
    <div class="container py-4">
        <div class="elite-page-header">
            <div class="elite-header-icon"><i class="bi bi-clock-history"></i></div>
            <h1 class="elite-header-title">Travel History</h1>
            <p class="elite-header-sub">A complete historical record of your premium journeys.</p>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Filter Navigation -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4 reveal">
        <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
            <a href="?status=All" class="btn <?= $status_filter == 'All' ? 'btn-primary-blue' : 'btn-light border-0' ?> rounded-pill px-4 fw-bold">All Flights</a>
            <a href="?status=Confirmed" class="btn <?= $status_filter == 'Confirmed' ? 'btn-primary-blue' : 'btn-light border-0' ?> rounded-pill px-4 fw-bold">Upcoming</a>
            <a href="?status=Past" class="btn <?= $status_filter == 'Past' ? 'btn-primary-blue' : 'btn-light border-0' ?> rounded-pill px-4 fw-bold">Past</a>
        </div>
        <div class="search-input-box bg-white shadow-sm border rounded-pill py-2 px-3" style="max-width: 400px; width: 100%;">
            <div class="input-wrap">
                <i class="bi bi-search text-primary-blue"></i>
                <input type="text" id="bookingSearch" placeholder="Search reference or destination..." class="border-0">
            </div>
        </div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="text-center py-5 reveal" data-animation="animate-scale-in">
            <i class="bi bi-journal-x display-1 text-muted opacity-25 mb-4"></i>
            <h3 class="fw-bold">No Records Found</h3>
            <p class="text-muted">You haven't made any bookings in this category yet.</p>
            <a href="index.php" class="btn btn-primary-blue rounded-pill px-5 py-3 fw-bold mt-2">Book Your First Flight</a>
        </div>
    <?php else: ?>
        <div class="row g-4" id="bookingsContainer">
            <?php foreach ($bookings as $b): 
                $isPast = strtotime($b['departure_time']) < time();
                $statusColor = 'primary-blue';
                if ($b['status'] == 'Pending') $statusColor = 'warning';
                if ($b['status'] == 'Cancelled' || $isPast) $statusColor = 'secondary';
                if ($b['status'] == 'Confirmed' && !$isPast) $statusColor = 'success';
            ?>
                <div class="col-lg-6 booking-item reveal" data-search="<?= strtolower($b['booking_reference'] . ' ' . $b['destination'] . ' ' . $b['flight_number']) ?>">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 transition-all-custom">
                        <div class="card-header bg-light border-bottom p-4 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-<?= $statusColor === 'primary-blue' ? 'primary' : (str_replace('text-dark', '', $statusColor)) ?> rounded-pill px-3 py-2 text-uppercase fw-bold letter-spacing-1 small">
                                    <?= $isPast ? 'COMPLETED' : $b['status'] ?>
                                </span>
                            </div>
                            <span class="fw-bold text-primary-blue">REF: <?= $b['booking_reference'] ?></span>
                        </div>
                        <div class="card-body p-4">
                            <div class="elite-route-row mb-4 py-2">
                                <div class="route-node">
                                    <span class="code" style="font-size: 1.8rem;"><?= strtoupper(substr($b['origin'], 0, 3)) ?></span>
                                    <span class="city small fw-bold"><?= $b['origin'] ?></span>
                                </div>
                                <div class="route-divider-icon" style="width: 45px; height: 45px;">
                                    <i class="bi bi-airplane-fill" style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="route-node">
                                    <span class="code" style="font-size: 1.8rem;"><?= strtoupper(substr($b['destination'], 0, 3)) ?></span>
                                    <span class="city small fw-bold"><?= $b['destination'] ?></span>
                                </div>
                            </div>
                            
                            <div class="bg-light p-3 rounded-4 mb-4">
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <div class="small text-muted fw-bold text-uppercase">Flight</div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($b['flight_number']) ?></div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="small text-muted fw-bold text-uppercase">Seat</div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($b['seat_number']) ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted fw-bold text-uppercase">Class</div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($b['seat_class']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 text-center mb-4">
                                <div class="col-6 border-end">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Departure</div>
                                    <div class="fw-bold"><?= date('H:i', strtotime($b['departure_time'])) ?></div>
                                    <div class="small text-muted"><?= date('M d, Y', strtotime($b['departure_time'])) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Arrival</div>
                                    <div class="fw-bold"><?= date('H:i', strtotime($b['arrival_time'])) ?></div>
                                    <div class="small text-muted"><?= date('M d, Y', strtotime($b['arrival_time'])) ?></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fs-4 fw-bold text-dark">$<?= number_format($b['final_price'], 0) ?></div>
                                <div class="d-flex gap-2">
                                    <?php if ($b['status'] == 'Confirmed'): ?>
                                        <a href="ticket.php?ref=<?= $b['booking_reference'] ?>" class="btn btn-primary-blue rounded-pill px-4 fw-bold">
                                            <i class="bi bi-qr-code me-2"></i> E-Ticket
                                        </a>
                                    <?php endif; ?>
                                    <a href="contact.php?ref=<?= $b['booking_reference'] ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                                        Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('bookingSearch').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.booking-item').forEach(item => {
        if (item.dataset.search.includes(val)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
