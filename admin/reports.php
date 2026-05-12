<?php
require_once __DIR__ . '/../includes/admin_header.php';

// Only Admins can view financial reports
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied: Only Administrators can view financial reports.");
}

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// 1. Total Revenue in Date Range
$stmt = $pdo->prepare("SELECT COALESCE(SUM(final_price), 0) FROM bookings WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'Confirmed'");
$stmt->execute([$start_date, $end_date]);
$total_revenue = $stmt->fetchColumn();

// 2. Route Profitability / Popularity
$stmt = $pdo->prepare("
    SELECT f.origin, f.destination, COUNT(b.id) as total_bookings, COALESCE(SUM(b.final_price), 0) as route_revenue
    FROM flights f
    LEFT JOIN bookings b ON f.id = b.flight_id AND b.status = 'Confirmed' AND DATE(b.created_at) BETWEEN ? AND ?
    GROUP BY f.origin, f.destination
    ORDER BY route_revenue DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$route_stats = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Financial Reports</h2>
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Print Report
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold">End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card bg-success text-white border-0 shadow-sm rounded-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">Total Confirmed Revenue</h5>
                    <div class="small text-white-50">From <?= date('M d, Y', strtotime($start_date)) ?> to <?= date('M d, Y', strtotime($end_date)) ?></div>
                </div>
                <h1 class="display-5 fw-bold mb-0">$<?= number_format($total_revenue, 2) ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="fw-bold mb-0">Route Profitability & Popularity</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Route</th>
                        <th class="text-center">Total Bookings</th>
                        <th class="text-end pe-4">Generated Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($route_stats)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">No data available for this date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($route_stats as $stat): 
                            // Calculate percentage of total revenue for the progress bar
                            $percentage = ($total_revenue > 0) ? ($stat['route_revenue'] / $total_revenue) * 100 : 0;
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">
                                        <?= htmlspecialchars($stat['origin']) ?> <i class="bi bi-arrow-right mx-1 text-muted"></i> <?= htmlspecialchars($stat['destination']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border fs-6"><?= $stat['total_bookings'] ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="fw-bold text-success">$<?= number_format($stat['route_revenue'], 2) ?></div>
                                    <div class="progress mt-1 ms-auto" style="height: 4px; width: 100px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage ?>%;"></div>
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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
