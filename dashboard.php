<?php
require_once __DIR__ . '/includes/header.php';

$userAuth->requireRole('Passenger');

$user_id = $_SESSION['user_id'];

// Fetch all bookings with flight details
$stmt = $pdo->prepare("
    SELECT b.id as booking_id, b.booking_reference, b.final_price, b.status, b.created_at,
           f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time,
           s.seat_number, s.seat_class
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN seats s ON b.seat_id = s.id
    WHERE b.user_id = :user_id
    ORDER BY f.departure_time ASC
");
$stmt->execute(['user_id' => $user_id]);
$allBookings = $stmt->fetchAll();

$upcoming = [];
$past = [];

foreach ($allBookings as $b) {
    if (strtotime($b['departure_time']) > time()) {
        $upcoming[] = $b;
    } else {
        $past[] = $b;
    }
}
?>

<div class="container-fluid p-0">
    <!-- Slim Premium Header -->
    <div class="py-5 text-white position-relative overflow-hidden" style="background: var(--navy-dark);">
        <div class="container position-relative z-index-1">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="text-primary-blue fw-bold small text-uppercase letter-spacing-2 mb-2 d-block">Elite Member Portal</span>
                    <h1 class="display-5 fw-bold mb-0">Welcome Back, <?= explode(' ', $_SESSION['name'])[0] ?></h1>
                    <p class="text-white-opacity-70 mt-2 mb-0">Your journey with Ducaale Airline continues. We are at your service.</p>
                </div>
                <div class="col-md-4 text-md-end mt-4 mt-md-0">
                    <div class="d-inline-flex align-items-center gap-3 p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 backdrop-blur">
                        <div class="text-start">
                            <div class="small text-white-opacity-70 fw-bold">TOTAL JOURNEYS</div>
                            <div class="fs-4 fw-bold"><?= count($allBookings) ?></div>
                        </div>
                        <div class="bg-primary-blue p-2 rounded-3">
                            <i class="bi bi-airplane-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <a href="contact.php" class="btn btn-sm btn-white bg-white bg-opacity-10 text-white border-white border-opacity-25 rounded-pill px-3">
                    <i class="bi bi-headset me-1"></i> 24/7 Support
                </a>
                <a href="faq.php" class="btn btn-sm btn-white bg-white bg-opacity-10 text-white border-white border-opacity-25 rounded-pill px-3">
                    <i class="bi bi-info-circle me-1"></i> Help Center
                </a>
            </div>
        </div>
        <!-- Decorative subtle wave -->
        <div class="position-absolute bottom-0 start-0 w-100 opacity-10">
            <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg"><path fill="#3b82f6" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5">
            <!-- Left Side: Journey Timeline -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h3 class="fw-bold mb-0">Your Upcoming Flights</h3>
                    <a href="index.php#available" class="btn btn-link text-primary-blue fw-bold text-decoration-none">Book New Flight <i class="bi bi-plus"></i></a>
                </div>

                <?php if (empty($upcoming)): ?>
                    <div class="text-center py-5 bg-light rounded-5 border border-dashed reveal">
                        <i class="bi bi-calendar2-x display-4 text-muted opacity-25 mb-3"></i>
                        <h5 class="fw-bold text-muted">No scheduled flights</h5>
                        <p class="text-muted small">Ready for your next adventure? Explore our destinations.</p>
                        <a href="index.php" class="btn btn-primary-blue rounded-pill px-4 mt-2 fw-bold">Explore Now</a>
                    </div>
                <?php else: ?>
                    <div class="journey-timeline">
                        <?php foreach ($upcoming as $index => $b): ?>
                            <div class="timeline-item reveal" data-animation="animate-fade-up">
                                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden hover-lift transition-all-custom">
                                    <div class="row g-0">
                                        <!-- Date Ribbon -->
                                        <div class="col-md-2 bg-light d-flex flex-column justify-content-center align-items-center p-3 border-end">
                                            <span class="small text-muted fw-bold text-uppercase"><?= date('M', strtotime($b['departure_time'])) ?></span>
                                            <span class="display-6 fw-bold text-dark"><?= date('d', strtotime($b['departure_time'])) ?></span>
                                            <span class="small text-muted"><?= date('H:i', strtotime($b['departure_time'])) ?></span>
                                        </div>
                                        <!-- Flight Details -->
                                        <div class="col-md-10 p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary-blue bg-opacity-10 text-primary-blue rounded-pill px-3 py-1 fw-bold small"><?= $b['flight_number'] ?></span>
                                                    <span class="text-muted small fw-bold font-monospace">REF: <?= $b['booking_reference'] ?></span>
                                                </div>
                                                <div class="text-end">
                                                    <?php 
                                                        $status = $b['status'] ?? 'Pending';
                                                        $statusClass = 'bg-success';
                                                        if ($status == 'Pending') $statusClass = 'bg-warning text-dark';
                                                        if ($status == 'Cancelled') $statusClass = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?= $statusClass ?> rounded-pill px-3 py-1 fw-bold small text-uppercase letter-spacing-1"><?= $status ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="row align-items-center g-4">
                                                <div class="col-md-7">
                                                    <div class="elite-route-row justify-content-start gap-4">
                                                        <div class="route-node text-start">
                                                            <span class="code fs-2 fw-bold text-dark"><?= strtoupper(substr($b['origin'], 0, 3)) ?></span>
                                                            <span class="city small text-muted d-block"><?= $b['origin'] ?></span>
                                                        </div>
                                                        <div class="route-divider-icon" style="width: 40px; height: 40px; background: #f8fafc;">
                                                            <i class="bi bi-airplane-fill text-primary-blue"></i>
                                                        </div>
                                                        <div class="route-node text-start">
                                                            <span class="code fs-2 fw-bold text-dark"><?= strtoupper(substr($b['destination'], 0, 3)) ?></span>
                                                            <span class="city small text-muted d-block"><?= $b['destination'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-5 text-md-end">
                                                    <?php if ($status === 'Confirmed'): ?>
                                                        <a href="ticket.php?ref=<?= $b['booking_reference'] ?>" class="btn btn-primary-blue rounded-pill px-4 py-3 fw-bold w-100 w-md-auto">
                                                            <i class="bi bi-qr-code me-2"></i> E-TICKET
                                                        </a>
                                                    <?php elseif ($status === 'Pending'): ?>
                                                        <button class="btn btn-light border rounded-pill px-4 py-3 fw-bold w-100 w-md-auto text-muted" disabled>
                                                            <i class="bi bi-hourglass-split me-2"></i> PENDING APPROVAL
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-danger rounded-pill px-4 py-3 fw-bold w-100 w-md-auto" disabled>
                                                            <i class="bi bi-x-circle me-2"></i> CANCELLED
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="mt-4 pt-3 border-top d-flex gap-4">
                                                <div class="small text-muted"><i class="bi bi-person-workspace me-1"></i> <?= $b['seat_class'] ?></div>
                                                <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i> Seat <?= $b['seat_number'] ?></div>
                                                <div class="small text-muted ms-auto"><i class="bi bi-clock me-1"></i> Boarding in <?= date('H:i', strtotime('-40 minutes', strtotime($b['departure_time']))) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Side: Sidebar Info -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <!-- Member Card -->
                    <div class="card border-0 shadow-sm rounded-5 overflow-hidden mb-4 reveal" data-animation="animate-scale-in">
                        <div class="bg-navy-dark p-4 text-center text-white">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=2563eb&color=fff&size=128" class="rounded-circle shadow border border-4 border-white border-opacity-10 mb-3" width="90">
                            <h4 class="fw-bold mb-1"><?= $_SESSION['name'] ?></h4>
                            <span class="badge bg-primary-blue rounded-pill px-3 py-1 small text-uppercase letter-spacing-1">Elite Voyager</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                <span class="text-muted">Member Since</span>
                                <span class="fw-bold"><?= date('M Y') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                <span class="text-muted">Loyalty Tier</span>
                                <span class="text-primary-blue fw-bold">Platinum Elite</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Miles Earned</span>
                                <span class="fw-bold"><?= number_format(count($allBookings) * 1250) ?> pts</span>
                            </div>
                        </div>
                    </div>

                    <!-- Travel History Quick Link -->
                    <div class="card border-0 shadow-sm rounded-5 bg-light p-4 reveal">
                        <h6 class="fw-bold text-dark mb-4">Travel History</h6>
                        <?php if (empty($past)): ?>
                            <p class="text-muted small mb-0">No past flights recorded.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($past, 0, 3) as $p): ?>
                                <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                                    <div class="bg-white rounded-circle p-2 shadow-sm"><i class="bi bi-airplane text-muted small"></i></div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-bold text-dark"><?= $p['origin'] ?> to <?= $p['destination'] ?></div>
                                        <div class="small text-muted" style="font-size: 0.7rem;"><?= date('M d, Y', strtotime($p['departure_time'])) ?></div>
                                    </div>
                                    <i class="bi bi-check2-circle text-success"></i>
                                </div>
                            <?php endforeach; ?>
                            <a href="bookings.php" class="btn btn-sm btn-outline-secondary rounded-pill w-100 fw-bold mt-2">View Full History</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .backdrop-blur { backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
    .transition-all-custom { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .hover-lift:hover { transform: translateY(-5px); }
    .letter-spacing-2 { letter-spacing: 2px; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
