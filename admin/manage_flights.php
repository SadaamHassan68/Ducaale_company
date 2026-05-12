<?php
require_once __DIR__ . '/../includes/admin_header.php';

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Log function
    function logAction($pdo, $admin_id, $action_str, $details) {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$admin_id, $action_str, $details]);
    }

    if ($action === 'add' || $action === 'edit') {
        $flight_number = $_POST['flight_number'];
        $origin = $_POST['origin'];
        $destination = $_POST['destination'];
        $departure_time = $_POST['departure_time'];
        $arrival_time = $_POST['arrival_time'];
        $base_price = $_POST['base_price'];
        $total_seats = $_POST['total_seats'];
        $aircraft_type = $_POST['aircraft_type'];
        
        if ($action === 'add') {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("INSERT INTO flights (flight_number, origin, destination, departure_time, arrival_time, base_price, total_seats, aircraft_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$flight_number, $origin, $destination, $departure_time, $arrival_time, $base_price, $total_seats, $aircraft_type]);
                $flight_id = $pdo->lastInsertId();
                
                // Generate basic seats based on total seats (assumes simple generation for new flights)
                $seatColumns = ['A', 'B', 'C', 'D', 'E', 'F'];
                $rows = ceil($total_seats / 6);
                
                $seatStmt = $pdo->prepare("INSERT INTO seats (flight_id, seat_number, seat_class, seat_type, status) VALUES (?, ?, ?, ?, 'Available')");
                for ($row = 1; $row <= $rows; $row++) {
                    $seat_class = 'Economy';
                    if ($row <= 2) $seat_class = 'First Class';
                    elseif ($row <= 4) $seat_class = 'Business';

                    foreach ($seatColumns as $col) {
                        $seatStmt->execute([$flight_id, $row . $col, $seat_class, 'Middle']);
                    }
                }
                
                logAction($pdo, $_SESSION['user_id'], 'Add Flight', "Added flight $flight_number from $origin to $destination");
                $pdo->commit();
                $success = "Flight added successfully.";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Error adding flight: " . $e->getMessage();
            }
        } elseif ($action === 'edit') {
            $id = $_POST['flight_id'];
            $stmt = $pdo->prepare("UPDATE flights SET origin=?, destination=?, departure_time=?, arrival_time=?, base_price=?, aircraft_type=? WHERE id=?");
            if ($stmt->execute([$origin, $destination, $departure_time, $arrival_time, $base_price, $aircraft_type, $id])) {
                logAction($pdo, $_SESSION['user_id'], 'Edit Flight', "Edited details for flight $flight_number");
                $success = "Flight updated successfully.";
            } else {
                $error = "Failed to update flight.";
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['flight_id'];
        $flight_number = $_POST['flight_number_delete'];
        $stmt = $pdo->prepare("DELETE FROM flights WHERE id=?");
        if ($stmt->execute([$id])) {
            logAction($pdo, $_SESSION['user_id'], 'Delete Flight', "Deleted flight $flight_number");
            $success = "Flight deleted.";
        }
    } elseif ($action === 'status') {
        $id = $_POST['flight_id'];
        $status = $_POST['status'];
        $flight_number = $_POST['flight_number_status'];
        $stmt = $pdo->prepare("UPDATE flights SET status=? WHERE id=?");
        if ($stmt->execute([$status, $id])) {
            logAction($pdo, $_SESSION['user_id'], 'Change Status', "Changed status of $flight_number to $status");
            $success = "Flight status updated.";
        }
    }
}

// Fetch all flights
$flights = $pdo->query("SELECT * FROM flights ORDER BY departure_time DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Flight Management</h2>
    <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addFlightModal">
        <i class="bi bi-plus-lg me-1"></i> Add New Flight
    </button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card admin-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Flight</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Aircraft</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($flights)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No flights found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($flights as $flight): 
                            $badgeClass = 'bg-secondary';
                            if ($flight['status'] == 'Scheduled') $badgeClass = 'bg-primary';
                            if ($flight['status'] == 'Boarding') $badgeClass = 'bg-success';
                            if ($flight['status'] == 'Delayed') $badgeClass = 'bg-warning text-dark';
                            if ($flight['status'] == 'Cancelled') $badgeClass = 'bg-danger';
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($flight['flight_number']) ?></div>
                                    <div class="small text-muted">$<?= number_format($flight['base_price'], 2) ?> base</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-medium"><?= htmlspecialchars($flight['origin']) ?></span>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                        <span class="fw-medium"><?= htmlspecialchars($flight['destination']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div><?= date('M d, Y', strtotime($flight['departure_time'])) ?></div>
                                    <div class="small text-muted"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-airplane me-1"></i><?= htmlspecialchars($flight['aircraft_type']) ?></span>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="action" value="status">
                                        <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                                        <input type="hidden" name="flight_number_status" value="<?= htmlspecialchars($flight['flight_number']) ?>">
                                        <select name="status" class="form-select form-select-sm border-0 <?= $badgeClass ?> text-white fw-medium shadow-sm" style="width: 120px;" onchange="this.form.submit()">
                                            <option value="Scheduled" class="bg-white text-dark" <?= $flight['status'] == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                            <option value="Delayed" class="bg-white text-dark" <?= $flight['status'] == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                                            <option value="Boarding" class="bg-white text-dark" <?= $flight['status'] == 'Boarding' ? 'selected' : '' ?>>Boarding</option>
                                            <option value="Departed" class="bg-white text-dark" <?= $flight['status'] == 'Departed' ? 'selected' : '' ?>>Departed</option>
                                            <option value="Completed" class="bg-white text-dark" <?= $flight['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" class="bg-white text-dark" <?= $flight['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light me-1" title="Edit" data-bs-toggle="modal" data-bs-target="#editFlightModal<?= $flight['id'] ?>">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                    
                                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this flight? This will also delete all associated bookings and seats.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                                        <input type="hidden" name="flight_number_delete" value="<?= htmlspecialchars($flight['flight_number']) ?>">
                                        <button type="submit" class="btn btn-sm btn-light" title="Delete">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Edit Modal for this flight -->
                            <div class="modal fade" id="editFlightModal<?= $flight['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content border-0 rounded-4 shadow">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold">Edit Flight <?= htmlspecialchars($flight['flight_number']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                                                
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted text-uppercase fw-bold">Origin</label>
                                                        <input type="text" class="form-control" name="origin" value="<?= htmlspecialchars($flight['origin']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted text-uppercase fw-bold">Destination</label>
                                                        <input type="text" class="form-control" name="destination" value="<?= htmlspecialchars($flight['destination']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted text-uppercase fw-bold">Departure Time</label>
                                                        <input type="datetime-local" class="form-control" name="departure_time" value="<?= date('Y-m-d\TH:i', strtotime($flight['departure_time'])) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted text-uppercase fw-bold">Arrival Time</label>
                                                        <input type="datetime-local" class="form-control" name="arrival_time" value="<?= date('Y-m-d\TH:i', strtotime($flight['arrival_time'])) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted text-uppercase fw-bold">Base Price ($)</label>
                                                        <input type="number" step="0.01" class="form-control" name="base_price" value="<?= $flight['base_price'] ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted text-uppercase fw-bold">Aircraft Type</label>
                                                        <input type="text" class="form-control" name="aircraft_type" value="<?= htmlspecialchars($flight['aircraft_type']) ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Flight Modal -->
<div class="modal fade" id="addFlightModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Flight</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small text-muted text-uppercase fw-bold">Flight Number</label>
                            <input type="text" class="form-control" name="flight_number" placeholder="e.g. WEH-505" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Origin</label>
                            <input type="text" class="form-control" name="origin" placeholder="e.g. JFK" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Destination</label>
                            <input type="text" class="form-control" name="destination" placeholder="e.g. LHR" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Departure Time</label>
                            <input type="datetime-local" class="form-control" name="departure_time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Arrival Time</label>
                            <input type="datetime-local" class="form-control" name="arrival_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Base Price ($)</label>
                            <input type="number" step="0.01" class="form-control" name="base_price" placeholder="450.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Total Seats</label>
                            <input type="number" class="form-control" name="total_seats" value="60" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Aircraft Type</label>
                            <input type="text" class="form-control" name="aircraft_type" value="Boeing 737" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Flight</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
