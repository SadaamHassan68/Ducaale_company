<?php
require_once __DIR__ . '/../includes/admin_header.php';

// Only Admins should see the full activity log
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied: Only Administrators can view activity logs.");
}

// Fetch logs
$stmt = $pdo->query("
    SELECT l.action, l.details, l.created_at, u.name, u.email, u.role
    FROM activity_logs l
    JOIN users u ON l.admin_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Security & Activity Logs</h2>
    <div class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">
        <i class="bi bi-shield-lock-fill me-1"></i> Admin Only
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>User</th>
                        <th>Action Performed</th>
                        <th class="pe-4">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">No activity logs recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="small text-muted fw-bold"><?= date('M d, Y', strtotime($log['created_at'])) ?></div>
                                    <div class="small text-dark"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($log['name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($log['role']) ?></div>
                                </td>
                                <td>
                                    <?php
                                        $actionColor = 'bg-secondary';
                                        if (strpos(strtolower($log['action']), 'add') !== false || strpos(strtolower($log['action']), 'create') !== false) $actionColor = 'bg-success';
                                        elseif (strpos(strtolower($log['action']), 'delete') !== false || strpos(strtolower($log['action']), 'cancel') !== false) $actionColor = 'bg-danger';
                                        elseif (strpos(strtolower($log['action']), 'edit') !== false || strpos(strtolower($log['action']), 'change') !== false) $actionColor = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $actionColor ?> rounded-pill"><?= htmlspecialchars($log['action']) ?></span>
                                </td>
                                <td class="pe-4">
                                    <span class="text-muted small"><?= htmlspecialchars($log['details']) ?></span>
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
