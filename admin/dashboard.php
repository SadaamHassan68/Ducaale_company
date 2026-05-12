<?php
require_once __DIR__ . '/../includes/admin_header.php';

// Calculate KPI Metrics
$metrics = [];
$metrics['total_revenue'] = $pdo->query("SELECT COALESCE(SUM(final_price), 0) FROM bookings WHERE status = 'Confirmed'")->fetchColumn();
$metrics['flights_today'] = $pdo->query("SELECT COUNT(*) FROM flights WHERE DATE(departure_time) = CURDATE()")->fetchColumn();
$metrics['total_passengers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Passenger'")->fetchColumn();
$metrics['active_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('Pending', 'Confirmed')")->fetchColumn();

// Get Live Flight Status (Next 5 Departures)
$stmt = $pdo->query("
    SELECT f.flight_number, f.destination, f.departure_time, f.status, f.total_seats,
           (SELECT COUNT(*) FROM seats WHERE flight_id = f.id AND status = 'Booked') as booked_seats
    FROM flights f
    WHERE f.departure_time >= NOW()
    ORDER BY f.departure_time ASC
    LIMIT 5
");
$liveFlights = $stmt->fetchAll();

// Get Revenue Data for Chart (Last 7 Days)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(final_price), 0) FROM bookings WHERE DATE(created_at) = ? AND status = 'Confirmed'");
    $stmt->execute([$date]);
    $chartData['labels'][] = date('M d', strtotime($date));
    $chartData['revenue'][] = $stmt->fetchColumn();
}

// Get Revenue by Seat Class (Doughnut Chart)
$stmt = $pdo->query("
    SELECT s.seat_class, SUM(b.final_price) as total_revenue 
    FROM bookings b 
    JOIN seats s ON b.seat_id = s.id 
    WHERE b.status = 'Confirmed' 
    GROUP BY s.seat_class
");
$revenueByClass = $stmt->fetchAll();

// Get Top Destinations by Revenue (Horizontal Bar Chart)
$stmt = $pdo->query("
    SELECT f.destination, SUM(b.final_price) as total_revenue 
    FROM bookings b 
    JOIN flights f ON b.flight_id = f.id 
    WHERE b.status = 'Confirmed' 
    GROUP BY f.destination 
    ORDER BY total_revenue DESC 
    LIMIT 5
");
$topDestinations = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-5 mt-2">
    <div>
        <h2 class="fw-bold text-dark mb-1 d-flex align-items-center">
            <span class="bg-primary-blue bg-opacity-10 p-2 rounded-3 me-3 d-inline-flex">
                <i class="bi bi-shield-lock-fill text-primary-blue fs-4"></i>
            </span>
            Command Center
        </h2>
        <p class="text-muted small mb-0 ms-5 ps-2">Ducaale Airline Intelligence & Global Operations Hub</p>
    </div>
    <div class="bg-white border border-secondary border-opacity-10 rounded-pill px-4 py-3 text-muted small shadow-sm d-flex align-items-center">
        <div class="spinner-grow spinner-grow-sm text-success me-3" role="status" style="width: 10px; height: 10px;"></div>
        <span class="fw-bold text-dark me-2">SYSTEM ACTIVE:</span> <?= date('l, F j, Y | H:i') ?>
    </div>
</div>

<!-- KPI Metrics Row -->
<div class="row g-4 mb-5">
    <div class="col-md-6 col-lg-3">
        <div class="card admin-card h-100 border-0 shadow-sm hover-up overflow-hidden">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-graph-up-arrow fs-1 text-primary"></i>
                </div>
                <div class="d-flex align-items-center">
                    <div class="kpi-card-icon gradient-blue shadow-blue me-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">Revenue</h6>
                        <h3 class="fw-bold mb-0 text-dark">$<?= number_format($metrics['total_revenue'], 0) ?></h3>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                        <i class="bi bi-arrow-up me-1"></i> Live
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card admin-card h-100 border-0 shadow-sm hover-up overflow-hidden">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-airplane fs-1 text-indigo"></i>
                </div>
                <div class="d-flex align-items-center">
                    <div class="kpi-card-icon gradient-indigo shadow-indigo me-3">
                        <i class="bi bi-airplane-fill"></i>
                    </div>
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">Flights</h6>
                        <h3 class="fw-bold mb-0 text-dark"><?= $metrics['flights_today'] ?></h3>
                    </div>
                </div>
                <div class="mt-3 text-muted small">
                    <i class="bi bi-calendar-check me-1"></i> Scheduled for today
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card admin-card h-100 border-0 shadow-sm hover-up overflow-hidden">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-people fs-1 text-green"></i>
                </div>
                <div class="d-flex align-items-center">
                    <div class="kpi-card-icon gradient-green shadow-green me-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">Passengers</h6>
                        <h3 class="fw-bold mb-0 text-dark"><?= $metrics['total_passengers'] ?></h3>
                    </div>
                </div>
                <div class="mt-3 text-muted small">
                    <i class="bi bi-shield-check me-1 text-success"></i> Registered users
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card admin-card h-100 border-0 shadow-sm hover-up overflow-hidden">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-ticket-perforated fs-1 text-amber"></i>
                </div>
                <div class="d-flex align-items-center">
                    <div class="kpi-card-icon gradient-amber shadow-amber me-3">
                        <i class="bi bi-ticket-detailed-fill"></i>
                    </div>
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">Active Bookings</h6>
                        <h3 class="fw-bold mb-0 text-dark"><?= $metrics['active_bookings'] ?></h3>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-warning bg-opacity-10 text-dark rounded-pill px-2 py-1 small">
                        Awaiting Review
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
        <div class="card admin-card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Financial Analytics</h5>
                    <div class="badge bg-light text-muted px-3 py-2 border rounded-pill">7 Day Trend</div>
                </div>
                <div style="height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue by Class (Doughnut) -->
    <div class="col-lg-4">
        <div class="card admin-card h-100">
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold text-dark mb-4 text-start">Revenue by Class</h5>
                <div style="height: 250px;">
                    <canvas id="classChart"></canvas>
                </div>
                <div class="mt-4 d-flex justify-content-around small fw-bold">
                    <?php foreach ($revenueByClass as $item): ?>
                        <div><i class="bi bi-circle-fill me-1" style="color: <?= ($item['seat_class'] == 'First Class' ? '#f59e0b' : ($item['seat_class'] == 'Business' ? '#6366f1' : '#3b82f6')) ?>;"></i> <?= $item['seat_class'] ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Global Operations Hub (Heatmap + Top Destinations) -->
    <div class="col-lg-12">
        <div class="card admin-card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row">
                    <!-- Visual Heatmap -->
                    <div class="col-lg-7 border-end border-secondary border-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                <i class="bi bi-globe-americas text-primary-blue me-2"></i>
                                Global Operations Hub
                            </h5>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold small d-flex align-items-center">
                                <span class="pulse-dot-small me-2"></span> LIVE TRACKING
                            </span>
                        </div>
                        <div class="position-relative bg-dark rounded-4 overflow-hidden shadow-inner" style="height: 400px; background: url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=2072&auto=format&fit=crop') no-repeat center center/cover;">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at center, rgba(15, 23, 42, 0.4) 0%, rgba(15, 23, 42, 0.9) 100%); backdrop-filter: blur(1px);"></div>
                            
                            <!-- Dynamic Pulse Points (Simulated) -->
                            <div class="position-absolute" style="top: 30%; left: 15%;">
                                <div class="pulse-ring"></div><div class="pulse-dot"></div>
                                <span class="badge bg-white text-dark rounded-pill mt-2 d-block small shadow-sm fw-bold" style="font-size: 0.65rem;">JFK / NEW YORK</span>
                            </div>
                            <div class="position-absolute" style="top: 40%; left: 45%;">
                                <div class="pulse-ring"></div><div class="pulse-dot"></div>
                                <span class="badge bg-white text-dark rounded-pill mt-2 d-block small shadow-sm fw-bold" style="font-size: 0.65rem;">LHR / LONDON</span>
                            </div>
                            <div class="position-absolute" style="top: 55%; left: 65%;">
                                <div class="pulse-ring highlight"></div><div class="pulse-dot highlight"></div>
                                <span class="badge bg-primary-blue text-white rounded-pill mt-2 d-block small shadow-lg fw-bold" style="font-size: 0.65rem;">MGQ / MOGADISHU</span>
                            </div>
                            
                            <div class="position-absolute bottom-0 start-0 p-4 w-100 bg-gradient-to-t from-dark">
                                <div class="d-flex align-items-center text-white small fw-bold opacity-75">
                                    <i class="bi bi-info-circle-fill me-2 text-primary-blue"></i>
                                    <span>Real-time density analytics active across primary Ducaale routes.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Top Destinations Chart -->
                    <div class="col-lg-5 ps-lg-4 mt-4 mt-lg-0">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold text-dark mb-0">Market Performance</h5>
                            <i class="bi bi-three-dots-vertical text-muted"></i>
                        </div>
                        <div style="height: 350px;">
                            <canvas id="destinationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pulse-dot { width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; position: relative; z-index: 2; box-shadow: 0 0 10px #3b82f6; }
.pulse-dot.highlight { background: #0ea5e9; box-shadow: 0 0 15px #0ea5e9; width: 14px; height: 14px; }
.pulse-ring {
    position: absolute; width: 30px; height: 30px; border-radius: 50%; background: #3b82f6;
    animation: ripple 2.5s infinite; top: -9px; left: -9px; opacity: 0.5;
}
.pulse-ring.highlight { background: #0ea5e9; animation-duration: 1.5s; }
.pulse-dot-small { width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: blink 1.5s infinite; }
@keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
@keyframes ripple { 0% { transform: scale(1); opacity: 0.8; } 100% { transform: scale(3); opacity: 0; } }

.hover-up { transition: all 0.3s ease; }
.hover-up:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important; }
.shadow-blue { box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3); }
.shadow-indigo { box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); }
.shadow-green { box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3); }
.shadow-amber { box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3); }
</style>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card admin-card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Live Fleet Status</h5>
                        <p class="text-muted small mb-0">Real-time load factors and departure sequencing</p>
                    </div>
                    <a href="manage_flights.php" class="btn btn-primary-blue rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-grid-fill me-2"></i> Flight Hub
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase" style="letter-spacing: 1px;">
                                <th class="border-0 ps-0">Flight</th>
                                <th class="border-0">Route</th>
                                <th class="border-0 text-center">Departure</th>
                                <th class="border-0">Operations Status</th>
                                <th class="border-0">Load Factor</th>
                                <th class="border-0 text-end pe-0">System</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($liveFlights as $flight): 
                                $occupancy = ($flight['total_seats'] > 0) ? round(($flight['booked_seats'] / $flight['total_seats']) * 100) : 0;
                                $statusColor = 'primary-blue';
                                $statusBg = 'bg-primary-blue';
                                if ($flight['status'] == 'Boarding') { $statusColor = 'success'; $statusBg = 'bg-success'; }
                                if ($flight['status'] == 'Delayed') { $statusColor = 'warning'; $statusBg = 'bg-warning'; }
                                if ($flight['status'] == 'Cancelled') { $statusColor = 'danger'; $statusBg = 'bg-danger'; }
                            ?>
                                <tr class="border-bottom border-light">
                                    <td class="ps-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-3 p-2 me-3">
                                                <i class="bi bi-airplane-engines text-dark fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= $flight['flight_number'] ?></div>
                                                <div class="text-muted" style="font-size: 0.7rem;">BOEING 787-9</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-dark">MGQ</span>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <span class="badge bg-light text-dark border-0 rounded-pill px-3"><?= strtoupper(substr($flight['destination'], 0, 3)) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-dark fs-5"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
                                        <div class="text-muted small">UTC+3</div>
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-<?= $statusColor ?> bg-opacity-10">
                                            <div class="spinner-grow spinner-grow-sm text-<?= $statusColor ?>" role="status" style="width: 8px; height: 8px;"></div>
                                            <span class="small fw-bold text-<?= $statusColor ?> text-uppercase"><?= $flight['status'] ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3" style="min-width: 160px;">
                                            <div class="progress flex-grow-1 shadow-none" style="height: 8px; background: #f1f5f9; border-radius: 4px;">
                                                <div class="progress-bar <?= $statusBg ?> rounded-pill" style="width: <?= $occupancy ?>%"></div>
                                            </div>
                                            <span class="small fw-bold text-dark"><?= $occupancy ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-0">
                                        <a href="manifest.php?id=<?= $flight['flight_number'] ?>" class="btn btn-sm btn-white border-0 shadow-none hover-primary-blue text-muted">
                                            <i class="bi bi-list-check fs-5"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

    const labels = <?= json_encode($chartData['labels']) ?>;
    const data = <?= json_encode($chartData['revenue']) ?>;

    // Main Revenue Chart
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: data,
                borderColor: '#3b82f6',
                borderWidth: 4,
                tension: 0.4,
                fill: true,
                backgroundColor: gradient,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.03)' },
                    ticks: { callback: value => '$' + value }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // Class Revenue Chart (Doughnut)
    const classCtx = document.getElementById('classChart').getContext('2d');
    new Chart(classCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($revenueByClass, 'seat_class')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($revenueByClass, 'total_revenue')) ?>,
                backgroundColor: ['#f59e0b', '#6366f1', '#3b82f6'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Destination Chart (Horizontal Bar)
    const destCtx = document.getElementById('destinationChart').getContext('2d');
    new Chart(destCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($topDestinations, 'destination')) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode(array_column($topDestinations, 'total_revenue')) ?>,
                backgroundColor: '#10b981',
                borderRadius: 10,
                barThickness: 25
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { callback: value => '$' + value } },
                y: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
