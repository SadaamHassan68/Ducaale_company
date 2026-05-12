<?php
require_once __DIR__ . '/../includes/admin_header.php';

// Only Admins can manage staff
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied: Only Administrators can manage staff accounts.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $password_hash, $role])) {
                // Log action
                $stmtLog = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details) VALUES (?, ?, ?)");
                $stmtLog->execute([$_SESSION['user_id'], 'Create Account', "Created $role account for $email"]);
                
                $success = "$role account created successfully.";
            } else {
                $error = "Failed to create account.";
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['user_id'];
        $email = $_POST['user_email'];
        
        if ($id == $_SESSION['user_id']) {
            $error = "You cannot delete your own account.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                $stmtLog = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details) VALUES (?, ?, ?)");
                $stmtLog->execute([$_SESSION['user_id'], 'Delete Account', "Deleted account $email"]);
                
                $success = "Account deleted.";
            }
        }
    }
}

// Fetch Staff and Admins
$users = $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE role IN ('Admin', 'Staff') ORDER BY role ASC, created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Staff Accounts</h2>
    <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="bi bi-person-plus-fill me-1"></i> Create Account
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

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created On</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=random" class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($user['name']) ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted"><?= htmlspecialchars($user['email']) ?></span>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'Admin'): ?>
                                    <span class="badge bg-danger rounded-pill px-3">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-info rounded-pill px-3">Staff</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="small text-muted"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="user_email" value="<?= htmlspecialchars($user['email']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Create System Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Assign Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Staff">Staff (Can manage flights & bookings)</option>
                            <option value="Admin">Admin (Full access, including financials & users)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Temporary Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <div class="form-text">User can change this later.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
