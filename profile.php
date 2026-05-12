<?php
require_once __DIR__ . '/includes/header.php';

if (!$userAuth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT name, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        $result = $userAuth->updateProfile($user_id, $name, $email);
        if ($result['success']) {
            $success = $result['message'];
            $user['name'] = $name;
            $user['email'] = $email;
            $_SESSION['name'] = $name; // Update session
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } else {
            $result = $userAuth->updatePassword($user_id, $currentPassword, $newPassword);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Redirect link based on role
$dashboardLink = ($user['role'] === 'admin') ? 'admin/dashboard.php' : 'dashboard.php';
?>

<!-- Header -->
<div class="py-5 text-white text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--navy-dark) 0%, var(--primary-blue) 100%);">
    <!-- Subtle Background Decor -->
    <i class="bi bi-person-circle position-absolute top-50 start-0 translate-middle-y opacity-10" style="font-size: 20rem; margin-left: -5rem;"></i>
    
    <div class="container py-4 position-relative z-index-1">
        <div class="elite-page-header">
            <div class="elite-header-icon shadow-lg bg-white text-primary-blue"><i class="bi bi-person-badge"></i></div>
            <h1 class="elite-header-title text-white">Member Profile</h1>
            <p class="elite-header-sub text-white-opacity-70">Manage your professional identity and security settings.</p>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden reveal" data-animation="animate-scale-in">
                <div class="p-5 text-center bg-navy-dark text-white">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=2563eb&color=fff&size=128" class="rounded-circle shadow border border-4 border-white" width="120">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-3 border-navy-dark rounded-circle p-2" title="Active Account"></span>
                    </div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                    <span class="badge bg-primary-blue rounded-pill px-3 py-2 mt-2 text-uppercase small letter-spacing-1"><?= strtoupper($user['role']) ?></span>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action py-3 px-4 border-0 d-flex align-items-center">
                        <i class="bi bi-person-gear me-3 fs-5 text-primary-blue"></i> Profile Information
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action py-3 px-4 border-0 d-flex align-items-center">
                        <i class="bi bi-shield-lock me-3 fs-5 text-primary-blue"></i> Security & Privacy
                    </a>
                    <a href="<?= $dashboardLink ?>" class="list-group-item list-group-item-action py-3 px-4 border-0 d-flex align-items-center">
                        <i class="bi bi-grid me-3 fs-5 text-primary-blue"></i> Go to Dashboard
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action py-3 px-4 border-0 d-flex align-items-center text-danger">
                        <i class="bi bi-power me-3 fs-5"></i> Sign Out
                    </a>
                </div>
            </div>
            
            <div class="feature-card-elite mt-4 p-4 reveal">
                <div class="d-flex align-items-center gap-3">
                    <div class="feature-icon-box m-0" style="width: 50px; height: 50px; font-size: 1.2rem;">
                        <i class="bi bi-award"></i>
                    </div>
                    <div class="text-start">
                        <h6 class="fw-bold mb-0">Elite Status</h6>
                        <p class="small text-muted mb-0">Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-lg-8">
            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4 animate-fade-in d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                    <div class="fw-bold"><?= htmlspecialchars($success) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 animate-fade-in d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div class="fw-bold"><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Profile Card -->
                <div class="col-12 reveal" id="profile">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="bg-light p-4 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">Account Identity</h5>
                            <i class="bi bi-pencil-square text-primary-blue"></i>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="search-input-box bg-light border-0">
                                            <label>FULL NAME</label>
                                            <div class="input-wrap">
                                                <i class="bi bi-person text-primary-blue"></i>
                                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="search-input-box bg-light border-0">
                                            <label>EMAIL ADDRESS</label>
                                            <div class="input-wrap">
                                                <i class="bi bi-envelope text-primary-blue"></i>
                                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary-blue rounded-pill px-5 py-2 fw-bold">Update Profile</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Card -->
                <div class="col-12 reveal" id="security">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="bg-light p-4 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">Security & Credentials</h5>
                            <i class="bi bi-shield-lock text-danger"></i>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_password">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <div class="search-input-box bg-light border-0">
                                            <label>CURRENT PASSWORD</label>
                                            <div class="input-wrap">
                                                <i class="bi bi-lock text-primary-blue"></i>
                                                <input type="password" name="current_password" placeholder="••••••••" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="search-input-box bg-light border-0">
                                            <label>NEW PASSWORD</label>
                                            <div class="input-wrap">
                                                <i class="bi bi-key text-primary-blue"></i>
                                                <input type="password" name="new_password" placeholder="Min 6 characters" required minlength="6">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="search-input-box bg-light border-0">
                                            <label>CONFIRM NEW PASSWORD</label>
                                            <div class="input-wrap">
                                                <i class="bi bi-key-fill text-primary-blue"></i>
                                                <input type="password" name="confirm_password" placeholder="Repeat new password" required minlength="6">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-outline-danger rounded-pill px-5 py-2 fw-bold">Update Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
