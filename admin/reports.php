<?php
require_once __DIR__ . '/../includes/admin_header.php';

// Only Admins can view financial reports
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied: Only Administrators can view financial reports.");
}

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// 1. Total Revenue & Total Bookings in Date Range
$stmt = $pdo->prepare("SELECT COALESCE(SUM(final_price), 0) as total_revenue, COUNT(id) as total_bookings FROM bookings WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'Confirmed'");
$stmt->execute([$start_date . " 00:00:00", $end_date . " 23:59:59"]);
$totals = $stmt->fetch();
$total_revenue = $totals['total_revenue'];
$total_bookings = $totals['total_bookings'];
$average_booking = $total_bookings > 0 ? $total_revenue / $total_bookings : 0;

// 2. Route Profitability / Popularity
$stmt = $pdo->prepare("
    SELECT f.origin, f.destination, COUNT(b.id) as total_bookings, COALESCE(SUM(b.final_price), 0) as route_revenue
    FROM flights f
    LEFT JOIN bookings b ON f.id = b.flight_id AND b.status = 'Confirmed' AND b.created_at BETWEEN ? AND ?
    GROUP BY f.origin, f.destination
    ORDER BY route_revenue DESC
    LIMIT 10
");
$stmt->execute([$start_date . " 00:00:00", $end_date . " 23:59:59"]);
$route_stats = $stmt->fetchAll();

// 3. Daily Revenue Trend (For Line Chart)
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as booking_date, COALESCE(SUM(final_price), 0) as daily_revenue
    FROM bookings 
    WHERE created_at BETWEEN ? AND ? AND status = 'Confirmed'
    GROUP BY DATE(created_at)
    ORDER BY booking_date ASC
");
$stmt->execute([$start_date . " 00:00:00", $end_date . " 23:59:59"]);
$daily_trends = $stmt->fetchAll();

// 4. Booking Status Breakdown (For Pie Chart)
$stmt = $pdo->prepare("
    SELECT status, COUNT(id) as status_count 
    FROM bookings 
    WHERE created_at BETWEEN ? AND ?
    GROUP BY status
");
$stmt->execute([$start_date . " 00:00:00", $end_date . " 23:59:59"]);
$status_stats = $stmt->fetchAll();

// Prepare data for Chart.js
$chartLabels = [];
$chartRevenueData = [];
$chartBookingsData = [];

$trendLabels = [];
$trendData = [];

$statusLabels = [];
$statusData = [];
$statusColors = [];

foreach ($route_stats as $stat) {
    $chartLabels[] = $stat['origin'] . ' ✈ ' . $stat['destination'];
    $chartRevenueData[] = $stat['route_revenue'];
    $chartBookingsData[] = $stat['total_bookings'];
}

foreach ($daily_trends as $trend) {
    $trendLabels[] = date('M d', strtotime($trend['booking_date']));
    $trendData[] = $trend['daily_revenue'];
}

foreach ($status_stats as $stat) {
    $statusLabels[] = $stat['status'];
    $statusData[] = $stat['status_count'];
    if ($stat['status'] === 'Confirmed') $statusColors[] = '#1cc88a';
    elseif ($stat['status'] === 'Pending') $statusColors[] = '#f6c23e';
    elseif ($stat['status'] === 'Cancelled') $statusColors[] = '#e74a3b';
    else $statusColors[] = '#858796';
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    ob_clean();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Report_' . $start_date . '_to_' . $end_date . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Origin', 'Destination', 'Total Bookings', 'Revenue Generated']);
    foreach ($route_stats as $row) {
        fputcsv($output, [$row['origin'], $row['destination'], $row['total_bookings'], $row['route_revenue']]);
    }
    fclose($output);
    exit;
}
?>

<style>
    /* Professional Report Styles */
    .report-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        background: #fff;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
    }
    .icon-circle {
        height: 3rem;
        width: 3rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255,255,255,0.2);
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-modern tbody tr {
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .table-modern tbody tr:hover {
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .table-modern td, .table-modern th {
        border: none;
        padding: 16px 20px;
    }
    .table-modern td:first-child, .table-modern th:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    .table-modern td:last-child, .table-modern th:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    
    @media print {
        .admin-sidebar, .admin-topbar, .print-hide {
            display: none !important;
        }
        .admin-main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .report-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }
        body {
            background-color: white !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Executive Dashboard</h2>
        <p class="text-muted mb-0">Advanced analytics and financial reports.</p>
    </div>
    <div class="d-flex gap-2 print-hide">
        <a href="?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&export=csv" class="btn btn-outline-success px-4 py-2 fw-bold shadow-sm rounded-pill d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-excel"></i> Export CSV
        </a>
        <button class="btn btn-dark px-4 py-2 fw-bold shadow-sm rounded-pill d-flex align-items-center gap-2" onclick="window.print()">
            <i class="bi bi-printer"></i> Export PDF
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card report-card mb-5 print-hide border-0 bg-white">
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold"><i class="bi bi-calendar3 me-1"></i> Start Date</label>
                <input type="date" class="form-control form-control-lg bg-light border-0 shadow-none" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold"><i class="bi bi-calendar3 me-1"></i> End Date</label>
                <input type="date" class="form-control form-control-lg bg-light border-0 shadow-none" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Update Analytics</button>
            </div>
        </form>
    </div>
</div>

<!-- Date Range Indicator for Print -->
<div class="d-none d-print-block mb-4">
    <h4 class="fw-bold text-dark border-bottom pb-2">Analytics Report: <?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?></h4>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Revenue Card -->
    <div class="col-xl-4 col-md-6">
        <div class="card report-card bg-gradient-success text-white h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-uppercase fw-bold small opacity-75">Total Revenue</div>
                    <div class="icon-circle"><i class="bi bi-currency-dollar fs-4"></i></div>
                </div>
                <div>
                    <h2 class="display-5 fw-bold mb-0">$<?= number_format($total_revenue, 2) ?></h2>
                    <div class="small mt-2 opacity-75">Generated from confirmed bookings</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bookings Card -->
    <div class="col-xl-4 col-md-6">
        <div class="card report-card bg-gradient-primary text-white h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-uppercase fw-bold small opacity-75">Total Bookings</div>
                    <div class="icon-circle"><i class="bi bi-ticket-detailed fs-4"></i></div>
                </div>
                <div>
                    <h2 class="display-5 fw-bold mb-0"><?= number_format($total_bookings) ?></h2>
                    <div class="small mt-2 opacity-75">Successfully confirmed flights</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Card -->
    <div class="col-xl-4 col-md-12">
        <div class="card report-card bg-gradient-info text-white h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-uppercase fw-bold small opacity-75">Average Value</div>
                    <div class="icon-circle"><i class="bi bi-graph-up-arrow fs-4"></i></div>
                </div>
                <div>
                    <h2 class="display-5 fw-bold mb-0">$<?= number_format($average_booking, 2) ?></h2>
                    <div class="small mt-2 opacity-75">Average revenue per transaction</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section 1: Trend Line -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card report-card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Revenue Trend Over Time</h5>
                <i class="bi bi-activity text-primary fs-4"></i>
            </div>
            <div class="card-body p-4">
                <canvas id="trendChart" height="280"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section 2: Bar & Doughnut -->
<div class="row g-4 mb-5">
    <!-- Bar Chart -->
    <div class="col-lg-7">
        <div class="card report-card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Revenue by Route</h5>
            </div>
            <div class="card-body p-4">
                <canvas id="revenueChart" height="280"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Doughnut Chart & Insights -->
    <div class="col-lg-5">
        <div class="card report-card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Market Share (Bookings)</h5>
            </div>
            <div class="card-body px-4 d-flex flex-column justify-content-center align-items-center">
                <div style="height: 220px; width: 100%;">
                    <canvas id="marketShareChart"></canvas>
                </div>
                <?php if(!empty($route_stats)): ?>
                    <div class="mt-4 w-100 p-3 bg-light rounded-3 text-center">
                        <span class="small text-muted text-uppercase fw-bold d-block mb-1">Top Performing Route</span>
                        <span class="fw-bold text-primary fs-5"><?= htmlspecialchars($route_stats[0]['origin']) ?> ✈ <?= htmlspecialchars($route_stats[0]['destination']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table & Status Chart -->
<div class="row g-4 mb-5">
    <!-- Ledger Table -->
    <div class="col-lg-8">
        <div class="card report-card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Route Performance Ledger</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive print-table-wrapper px-4 pb-4">
                    <table class="table table-modern align-middle mt-3">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Flight Route</th>
                                <th class="text-center">Total Bookings</th>
                                <th class="text-end">Revenue Generated</th>
                                <th class="text-end pe-4" style="width: 25%;">Contribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($route_stats)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted bg-white rounded-3 border">No data available for this date range.</td></tr>
                            <?php else: ?>
                                <?php foreach ($route_stats as $stat): 
                                    $percentage = ($total_revenue > 0) ? ($stat['route_revenue'] / $total_revenue) * 100 : 0;
                                ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded p-2 me-3 text-primary">
                                                    <i class="bi bi-airplane-engines fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark fs-6">
                                                        <?= htmlspecialchars($stat['origin']) ?> <i class="bi bi-arrow-right mx-1 text-muted small"></i> <?= htmlspecialchars($stat['destination']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border fs-6 px-3 py-2 rounded-pill shadow-sm"><?= $stat['total_bookings'] ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="fw-bold text-dark fs-5">$<?= number_format($stat['route_revenue'], 2) ?></div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="me-3 fw-medium text-muted"><?= number_format($percentage, 1) ?>%</span>
                                                <div class="progress shadow-sm" style="height: 8px; width: 120px; border-radius: 10px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage ?>%; border-radius: 10px;"></div>
                                                </div>
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
    </div>
    
    <!-- Status Pie Chart -->
    <div class="col-lg-4">
        <div class="card report-card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Booking Status Breakdown</h5>
            </div>
            <div class="card-body px-4 d-flex flex-column justify-content-center align-items-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#858796';
    
    const tooltipConfig = {
        backgroundColor: '#fff',
        titleColor: '#333',
        bodyColor: '#666',
        borderColor: '#e3e6f0',
        borderWidth: 1,
        padding: 12,
        boxPadding: 6,
        usePointStyle: true,
        titleFont: { size: 14, weight: 'bold' }
    };

    // 1. Revenue Trend Line Chart
    const trendCtx = document.getElementById('trendChart');
    if(trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendLabels) ?>,
                datasets: [{
                    label: 'Daily Revenue',
                    data: <?= json_encode($trendData) ?>,
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Smooth curves
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tooltipConfig, callbacks: { label: function(c) { return '$' + c.raw.toLocaleString(); } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f8f9fa', borderDash: [5, 5] },
                        ticks: { callback: function(value) { return '$' + value; } }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Revenue By Route Bar Chart
    const revenueCtx = document.getElementById('revenueChart');
    if(revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?= json_encode($chartRevenueData) ?>,
                    backgroundColor: 'rgba(28, 200, 138, 0.85)',
                    hoverBackgroundColor: 'rgba(28, 200, 138, 1)',
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tooltipConfig, callbacks: { label: function(c) { return '$' + c.raw.toLocaleString(); } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f8f9fa', borderDash: [5, 5] },
                        ticks: { callback: function(value) { return '$' + value; } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });
    }

    // 3. Market Share Doughnut Chart
    const marketShareCtx = document.getElementById('marketShareChart');
    if(marketShareCtx) {
        new Chart(marketShareCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    data: <?= json_encode($chartBookingsData) ?>,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                        '#858796', '#5a5c69', '#2c9faf', '#f8f9fa', '#d1d3e2'
                    ],
                    hoverOffset: 4,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, usePointStyle: true, font: { size: 11 } }
                    },
                    tooltip: { ...tooltipConfig, callbacks: { label: function(c) { return c.raw + ' Bookings'; } } }
                }
            }
        });
    }

    // 4. Booking Status Pie Chart
    const statusCtx = document.getElementById('statusChart');
    if(statusCtx) {
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($statusLabels) ?>,
                datasets: [{
                    data: <?= json_encode($statusData) ?>,
                    backgroundColor: <?= json_encode($statusColors) ?>,
                    hoverOffset: 4,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, usePointStyle: true, font: { size: 12 } }
                    },
                    tooltip: { ...tooltipConfig, callbacks: { label: function(c) { return c.raw + ' Bookings'; } } }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

