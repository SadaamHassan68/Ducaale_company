<?php
require_once __DIR__ . '/../includes/admin_header.php';

// Only Admins or Staff should manage support tickets
$role = $_SESSION['role'];
$success = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $ticket_id])) {
        $success = "Ticket #$ticket_id updated to $new_status.";
        
        // Log the action
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details) VALUES (?, ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], 'Update Ticket Status', "Ticket #$ticket_id status changed to $new_status"]);
    } else {
        $error = "Failed to update ticket status.";
    }
}

// Fetch all tickets
$stmt = $pdo->query("
    SELECT t.*, u.name as user_name, u.email as user_email
    FROM support_tickets t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$tickets = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Support Tickets</h2>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card admin-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Ticket ID</th>
                        <th>User / Contact</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No support tickets found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $t['id'] ?></td>
                                <td>
                                    <?php if ($t['user_id']): ?>
                                        <div class="fw-bold"><?= htmlspecialchars($t['user_name']) ?></div>
                                        <div class="small text-muted"><i class="bi bi-person-check-fill text-primary me-1"></i> <?= htmlspecialchars($t['user_email']) ?></div>
                                    <?php else: ?>
                                        <div class="fw-bold"><?= htmlspecialchars($t['guest_name']) ?></div>
                                        <div class="small text-muted"><i class="bi bi-person-fill text-secondary me-1"></i> <?= htmlspecialchars($t['guest_email']) ?> (Guest)</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= htmlspecialchars($t['subject']) ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 250px;"><?= htmlspecialchars($t['message']) ?></div>
                                </td>
                                <td>
                                    <?php 
                                        $badgeClass = 'bg-primary';
                                        if ($t['status'] === 'In Progress') $badgeClass = 'bg-warning text-dark';
                                        if ($t['status'] === 'Resolved') $badgeClass = 'bg-success';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3"><?= $t['status'] ?></span>
                                </td>
                                <td class="small text-muted"><?= date('M d, Y H:i', strtotime($t['created_at'])) ?></td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                            <li><button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#viewModal<?= $t['id'] ?>"><i class="bi bi-eye me-2"></i> View Full Message</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                    <input type="hidden" name="status" value="In Progress">
                                                    <button type="submit" class="dropdown-item py-2 text-warning"><i class="bi bi-hourglass-split me-2"></i> Mark In Progress</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                    <input type="hidden" name="status" value="Resolved">
                                                    <button type="submit" class="dropdown-item py-2 text-success"><i class="bi bi-check-circle me-2"></i> Mark Resolved</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- View Message Modal -->
                                    <div class="modal fade" id="viewModal<?= $t['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="fw-bold modal-title">Ticket #<?= $t['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start p-4">
                                                    <div class="mb-3">
                                                        <label class="small text-muted text-uppercase fw-bold d-block mb-1">Subject</label>
                                                        <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($t['subject']) ?></div>
                                                    </div>
                                                    <hr class="my-3">
                                                    <div>
                                                        <label class="small text-muted text-uppercase fw-bold d-block mb-1">Message</label>
                                                        <div class="text-dark bg-light p-3 rounded-3" style="white-space: pre-wrap;"><?= htmlspecialchars($t['message']) ?></div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
