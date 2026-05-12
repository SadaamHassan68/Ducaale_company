<?php
require_once __DIR__ . '/../includes/admin_header.php';

// In a real application, these multipliers could be stored in a `settings` table.
// For this thesis demonstration, we define them here to show the logic.
$multipliers = [
    'Economy' => 1.0,
    'Business' => 2.5,
    'First Class' => 4.0
];

// Fetch all flights to show their base price and calculated tiered prices
$flights = $pdo->query("SELECT id, flight_number, destination, base_price FROM flights ORDER BY id DESC LIMIT 10")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Seat Pricing Configuration</h2>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Pricing Multipliers</h5>
                <p class="text-muted small mb-4">Seat prices are dynamically calculated based on the flight's Base Price multiplied by the class tier.</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Economy Class</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">x</span>
                            <input type="number" step="0.1" class="form-control" value="1.0" readonly>
                        </div>
                        <div class="form-text">Standard Base Price</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase text-primary">Business Class</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">x</span>
                            <input type="number" step="0.1" class="form-control" value="2.5" readonly>
                        </div>
                        <div class="form-text">250% of Base Price</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase text-warning">First Class</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">x</span>
                            <input type="number" step="0.1" class="form-control" value="4.0" readonly>
                        </div>
                        <div class="form-text">400% of Base Price</div>
                    </div>
                    <button type="button" class="btn btn-primary w-100 fw-bold" disabled>Update Multipliers (Demo)</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-0">
                <div class="p-4 border-bottom">
                    <h5 class="fw-bold mb-0">Calculated Prices per Flight</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Flight</th>
                                <th>Base Price (Economy)</th>
                                <th class="text-primary">Business (x2.5)</th>
                                <th class="text-warning">First Class (x4.0)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($flights)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No flights found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($flights as $f): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <?= htmlspecialchars($f['flight_number']) ?>
                                            <div class="small text-muted fw-normal">To <?= htmlspecialchars($f['destination']) ?></div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">$<?= number_format($f['base_price'], 2) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">$<?= number_format($f['base_price'] * 2.5, 2) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-warning">$<?= number_format($f['base_price'] * 4.0, 2) ?></span>
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
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
