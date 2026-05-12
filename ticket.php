<?php
require_once __DIR__ . '/includes/header.php';

$userAuth->requireRole('Passenger');

$ref = $_GET['ref'] ?? '';
if (!$ref) {
    header("Location: index.php");
    exit;
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

// Destination Intel (Simulated)
$destWeather = [
    'temp' => rand(28, 34),
    'condition' => 'Clear Skies',
    'icon' => 'sun-fill'
];
?>

<div class="container-fluid p-0">
    <!-- Action Header -->
    <div class="bg-navy-dark py-4 text-white no-print">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="dashboard.php" class="btn btn-outline-light rounded-pill border-opacity-25 px-3"><i class="bi bi-arrow-left"></i></a>
                    <h5 class="mb-0 fw-bold">Official E-Ticket</h5>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary-blue rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-printer-fill me-2"></i> Print Pass
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                
                <!-- The Ultimate Boarding Pass -->
                <div class="ticket-wrapper reveal shadow-2xl" data-animation="animate-scale-in">
                    <!-- Top Notch -->
                    <div class="ticket-header-modern bg-navy-dark text-white p-4 p-md-5 position-relative overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center position-relative z-index-2">
                            <div class="d-flex align-items-center gap-4">
                                <div class="bg-primary-blue p-3 rounded-4 shadow-lg border border-white border-opacity-20">
                                    <i class="bi bi-airplane-engines-fill fs-1"></i>
                                </div>
                                <div>
                                    <h1 class="fw-black mb-0 letter-spacing-2 tracking-tighter" style="font-size: 2rem;">DUCAALE AIR</h1>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-white text-navy-dark rounded-pill px-3 py-1 fw-black small text-uppercase">Elite Voyager</span>
                                        <span class="text-white-opacity-70 small fw-bold text-uppercase letter-spacing-1">Confirmed Pass</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-white-opacity-70 small fw-bold text-uppercase mb-1 tracking-widest">PNR RECORD</div>
                                <h2 class="fw-black text-primary-blue mb-0 font-monospace" style="font-size: 2.5rem;"><?= htmlspecialchars($ticket['booking_reference']) ?></h2>
                            </div>
                        </div>
                        
                        <!-- Abstract Background Decoration -->
                        <div class="position-absolute top-0 end-0 h-100 w-100 opacity-10 pointer-events-none">
                            <svg viewBox="0 0 100 100" class="h-100 w-100"><circle cx="100" cy="0" r="60" fill="white" /></svg>
                        </div>
                    </div>

                    <!-- Main Body -->
                    <div class="ticket-body bg-white border-start border-end position-relative overflow-hidden">
                        <!-- Watermark - Centered Green Stamp -->
                        <div class="position-absolute top-50 start-50 translate-middle pointer-events-none" style="transform: translate(-50%, -50%) rotate(-30deg) !important; font-size: 10rem; font-weight: 900; z-index: 0; color: rgba(16, 185, 129, 0.15); white-space: nowrap;">
                            VALID
                        </div>
                        
                        <!-- Perforation Line Left/Right -->
                        <div class="perforation-hole hole-left"></div>
                        <div class="perforation-hole hole-right"></div>

                        <div class="row g-0">
                            <!-- Flight Core Info -->
                            <div class="col-md-8 p-4 p-md-5 border-end border-dashed-premium">
                                <div class="row g-4 mb-5">
                                    <div class="col-12">
                                        <div class="text-muted small fw-black text-uppercase tracking-widest mb-1">Passenger Name</div>
                                        <h2 class="fw-black text-dark mb-0 text-uppercase" style="font-size: 2.2rem;"><?= htmlspecialchars($ticket['passenger_name']) ?></h2>
                                    </div>
                                </div>

                                <div class="flight-visual-row mb-5 py-4 px-3 bg-light rounded-5 border border-white">
                                    <div class="row align-items-center g-0">
                                        <div class="col-5 text-center">
                                            <h1 class="display-1 fw-black text-navy-dark mb-0"><?= strtoupper(substr($ticket['origin'], 0, 3)) ?></h1>
                                            <div class="city-label fw-black text-primary-blue text-uppercase letter-spacing-1 small"><?= $ticket['origin'] ?></div>
                                            <div class="time-label fw-bold fs-5 text-dark mt-2"><?= date('H:i', strtotime($ticket['departure_time'])) ?></div>
                                        </div>
                                        <div class="col-2 text-center">
                                            <div class="airplane-path">
                                                <div class="airplane-icon-move">
                                                    <i class="bi bi-airplane-fill fs-3 text-primary-blue"></i>
                                                    <div class="small fw-black text-primary-blue mt-1" style="font-size: 0.6rem; letter-spacing: 2px;">VALID</div>
                                                </div>
                                                <div class="dashed-line"></div>
                                            </div>
                                        </div>
                                        <div class="col-5 text-center">
                                            <h1 class="display-1 fw-black text-navy-dark mb-0"><?= strtoupper(substr($ticket['destination'], 0, 3)) ?></h1>
                                            <div class="city-label fw-black text-primary-blue text-uppercase letter-spacing-1 small"><?= $ticket['destination'] ?></div>
                                            <div class="time-label fw-bold fs-5 text-dark mt-2"><?= date('H:i', strtotime($ticket['arrival_time'])) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4 text-center">
                                    <div class="col-3">
                                        <div class="text-muted small fw-black text-uppercase tracking-widest">Flight</div>
                                        <div class="fs-4 fw-black text-dark"><?= htmlspecialchars($ticket['flight_number']) ?></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted small fw-black text-uppercase tracking-widest">Date</div>
                                        <div class="fs-4 fw-black text-dark"><?= date('d M', strtotime($ticket['departure_time'])) ?></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted small fw-black text-uppercase tracking-widest">Gate</div>
                                        <div class="fs-4 fw-black text-dark">TBA</div>
                                    </div>
                                    <div class="col-3 text-danger">
                                        <div class="small fw-black text-uppercase tracking-widest">Boarding</div>
                                        <div class="fs-4 fw-black"><?= date('H:i', strtotime('-40 minutes', strtotime($ticket['departure_time']))) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stub Section -->
                            <div class="col-md-4 p-4 p-md-5 bg-light d-flex flex-column justify-content-between text-center border-top-mobile">
                                <div class="row g-4">
                                    <div class="col-6 col-md-12">
                                        <div class="text-muted small fw-black text-uppercase tracking-widest">Seat</div>
                                        <h1 class="display-2 fw-black text-primary-blue mb-0"><?= htmlspecialchars($ticket['seat_number']) ?></h1>
                                    </div>
                                    <div class="col-6 col-md-12">
                                        <div class="text-muted small fw-black text-uppercase tracking-widest">Class</div>
                                        <div class="fs-4 fw-black text-dark"><?= htmlspecialchars($ticket['seat_class']) ?></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted small fw-black text-uppercase tracking-widest">Status</div>
                                        <div class="badge bg-navy-dark rounded-pill px-4 py-2 mt-2 fw-black small">PRIORITY</div>
                                    </div>
                                </div>
                                
                                <div class="qr-zone mt-5 mt-md-auto text-center opacity-25">
                                    <i class="bi bi-shield-check display-1"></i>
                                    <div class="small text-muted mt-3 fw-bold font-monospace tracking-widest">OFFICIAL DOCUMENT</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Intelligence -->
                    <div class="ticket-footer bg-navy-dark text-white p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="small fw-bold opacity-75">
                            <i class="bi bi-info-circle-fill me-2"></i> Boarding closes 15 minutes before departure.
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="small fw-black text-uppercase tracking-widest opacity-50">Weather at Destination</span>
                            <div class="d-flex align-items-center gap-2 px-3 py-1 bg-white bg-opacity-10 rounded-pill">
                                <i class="bi bi-<?= $destWeather['icon'] ?> text-warning"></i>
                                <span class="fw-bold"><?= $destWeather['temp'] ?>°C</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5 no-print reveal">
                    <p class="text-muted small mb-0">Official Digital Travel Document issued by Ducaale Airline.</p>
                    <p class="text-muted small">Please ensure you have valid identification for check-in.</p>
                </div>
                
            </div>
        </div>
    </div>
</div>

<style>
    .fw-black { font-weight: 900; }
    .tracking-tighter { letter-spacing: -0.05em; }
    .ticket-wrapper { border-radius: 50px; overflow: hidden; }
    .border-dashed-premium { border-right: 4px dashed #f1f5f9 !important; }
    
    .perforation-hole {
        position: absolute; width: 40px; height: 40px; background: #fff; border-radius: 50%;
        top: 0; z-index: 3; box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }
    .hole-left { left: 0; transform: translate(-50%, -50%); top: 0; }
    .hole-right { right: 0; transform: translate(50%, -50%); top: 0; }
    
    .airplane-path { position: relative; height: 40px; display: flex; align-items: center; justify-content: center; }
    .dashed-line { width: 100%; height: 2px; border-top: 2px dashed #cbd5e1; position: absolute; z-index: 1; }
    .airplane-icon-move { position: relative; z-index: 2; background: #f8fafc; padding: 0 10px; }
    
    @media (max-width: 767px) {
        .border-dashed-premium { border-right: none !important; border-bottom: 4px dashed #f1f5f9 !important; }
        .border-top-mobile { border-top: 4px dashed #f1f5f9 !important; }
        .hole-left, .hole-right { top: 66.66% !important; }
        .display-1 { font-size: 3rem !important; }
    }
    
    /* Ultimate Print Styles - Aggressive One-Page Fix */
    @page { 
        margin: 0 !important; 
        size: auto;
    }
    @media print {
        html, body { 
            background: #fff !important; 
            margin: 0 !important; 
            padding: 0 !important; 
            height: auto !important;
            overflow: visible !important;
        }
        nav, header, footer, .no-print, .btn, .bg-navy-dark.py-4, .ticket-footer, .qr-zone, script, style { 
            display: none !important; 
        }
        .container-fluid { padding: 0 !important; margin: 0 !important; }
        .container { padding: 1cm !important; margin: 0 auto !important; width: 100% !important; max-width: 100% !important; }
        .row { margin: 0 !important; }
        .col-lg-10 { padding: 0 !important; width: 100% !important; }
        
        .ticket-wrapper { 
            border-radius: 0 !important; 
            box-shadow: none !important; 
            border: 1px solid #e2e8f0 !important;
            margin: 0 !important;
            width: 100% !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            transform: scale(0.92);
            transform-origin: top center;
        }
        .ticket-body { padding: 15px !important; }
        .p-4, .p-md-5 { padding: 1rem !important; }
        .mb-5 { margin-bottom: 1rem !important; }
        .bg-navy-dark { background-color: #0f172a !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        .bg-primary-blue { background-color: #0ea5e9 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        .bg-light { background-color: #f8fafc !important; color: #000 !important; -webkit-print-color-adjust: exact; }
        .text-primary-blue { color: #0ea5e9 !important; -webkit-print-color-adjust: exact; }
        .fw-black { font-weight: 900 !important; }
        .perforation-hole { display: none !important; }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
