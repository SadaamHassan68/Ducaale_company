<?php
require_once __DIR__ . '/includes/header.php';

$flightNumber = $_GET['flight'] ?? '';
$flightInfo = null;
$error = '';

if ($flightNumber) {
    $stmt = $pdo->prepare("SELECT * FROM flights WHERE flight_number = ? ORDER BY departure_time DESC LIMIT 1");
    $stmt->execute([$flightNumber]);
    $flightInfo = $stmt->fetch();
    
    if (!$flightInfo) {
        $error = "No flight found with number " . htmlspecialchars($flightNumber);
    }
}
?>

<!-- Header -->
<div class="py-5 text-white text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--navy-dark) 0%, var(--primary-blue) 100%);">
    <!-- Subtle Background Decor -->
    <i class="bi bi-radar position-absolute top-50 start-0 translate-middle-y opacity-10" style="font-size: 20rem; margin-left: -5rem;"></i>
    
    <div class="container py-4 position-relative z-index-1">
        <div class="elite-page-header">
            <div class="elite-header-icon shadow-lg bg-white text-primary-blue"><i class="bi bi-radar"></i></div>
            <h1 class="elite-header-title text-white">Flight Tracker</h1>
            <p class="elite-header-sub text-white-opacity-70">Real-time status updates for Ducaale Airline routes.</p>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Search Card -->
            <div class="search-float-card mb-5 reveal" data-animation="animate-scale-in">
                <form method="GET" action="flight_status.php" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <div class="search-input-box">
                            <label>ENTER FLIGHT NUMBER</label>
                            <div class="input-wrap">
                                <i class="bi bi-airplane text-primary-blue"></i>
                                <input type="text" name="flight" placeholder="e.g. WEH-505" value="<?= htmlspecialchars($flightNumber) ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn-search-blue h-100 py-3">
                            <i class="bi bi-radar"></i> Track
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 rounded-4 p-4 shadow-sm animate-fade-in text-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-2 mb-2 d-block"></i>
                    <h5 class="fw-bold"><?= $error ?></h5>
                    <p class="small mb-0 opacity-75">Double-check the flight number and try again.</p>
                </div>
            <?php endif; ?>

            <?php if ($flightInfo): 
                $isPast = strtotime($flightInfo['departure_time']) < time();
                $status = $isPast ? 'Landed' : ($flightInfo['status'] ?? 'Scheduled');
                $statusClass = $isPast ? 'bg-secondary' : ($status == 'Scheduled' ? 'bg-success' : 'bg-warning');
                $statusIcon = $isPast ? 'bi-check-circle-fill' : ($status == 'Scheduled' ? 'bi-clock-fill' : 'bi-exclamation-circle-fill');
            ?>
                <div class="flight-card-elite reveal">
                    <!-- Status Header Overlay -->
                    <div class="elite-img-box" style="height: 180px;">
                        <img src="assets/img/flight_default.png" alt="Flight Status">
                        <div class="status-badge-elite <?= $statusClass ?>">
                            <div class="status-dot"></div>
                            <?= $status ?>
                        </div>
                        <div class="brand-badge-elite">Live Updates</div>
                        
                        <div class="flight-num-overlay">
                            <i class="bi bi-airplane-engines"></i>
                            <h3><?= htmlspecialchars($flightInfo['flight_number']) ?></h3>
                        </div>
                    </div>

                    <div class="elite-card-body">
                        <!-- High Impact Route -->
                        <div class="elite-route-row my-4">
                            <div class="route-node">
                                <span class="code"><?= strtoupper(substr($flightInfo['origin'], 0, 3)) ?></span>
                                <span class="city"><?= $flightInfo['origin'] ?></span>
                            </div>
                            <div class="route-divider-icon" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="bi bi-airplane-fill"></i>
                            </div>
                            <div class="route-node">
                                <span class="code"><?= strtoupper(substr($flightInfo['destination'], 0, 3)) ?></span>
                                <span class="city"><?= $flightInfo['destination'] ?></span>
                            </div>
                        </div>

                        <!-- Status Timeline -->
                        <div class="p-4 rounded-4 bg-light mb-4">
                            <div class="row g-4 text-center">
                                <div class="col-6">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Departure</div>
                                    <div class="fw-bold fs-4 text-primary-blue"><?= date('H:i', strtotime($flightInfo['departure_time'])) ?></div>
                                    <div class="small text-muted"><?= date('M d, Y', strtotime($flightInfo['departure_time'])) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Estimated Arrival</div>
                                    <div class="fw-bold fs-4 text-primary-blue"><?= date('H:i', strtotime($flightInfo['arrival_time'] ?? $flightInfo['departure_time'] . ' + 3 hours')) ?></div>
                                    <div class="small text-muted"><?= date('M d, Y', strtotime($flightInfo['departure_time'])) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="elite-details-grid">
                            <div class="detail-sub-item">
                                <span class="label">GATE</span>
                                <span class="value"><?= $isPast ? 'Arrived' : 'B-12' ?></span>
                            </div>
                            <div class="detail-sub-item text-center">
                                <span class="label">AIRCRAFT</span>
                                <span class="value">Ducaale Elite</span>
                            </div>
                            <div class="detail-sub-item text-end">
                                <span class="label">BAGGAGE</span>
                                <span class="value"><?= $isPast ? 'Belt 4' : 'N/A' ?></span>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small mb-0">
                                <i class="<?= $statusIcon ?> text-<?= str_replace('bg-', '', $statusClass) ?> me-1"></i>
                                Current Status: <strong class="text-<?= str_replace('bg-', '', $statusClass) ?>"><?= strtoupper($status) ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
            <?php elseif (!$flightNumber && !$error): ?>
                <!-- Helper Cards -->
                <div class="row g-4 reveal">
                    <div class="col-md-6">
                        <div class="feature-card-elite">
                            <div class="feature-icon-box"><i class="bi bi-broadcast"></i></div>
                            <h4>Live Telemetry</h4>
                            <p>Direct data link to our flight operations center for up-to-the-minute accuracy.</p>
                        </div>
                    </div>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
