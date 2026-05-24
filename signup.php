<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/User.php';

$userAuth = new User($pdo);
if ($userAuth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $result = $userAuth->register($name, $email, $password);
        if ($result['success']) {
            header("Location: login.php?registered=true");
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Ducaale Airlines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --primary: #2563eb; --navy: #0f172a; }
        body { font-family: 'Outfit', sans-serif; background: #fff; overflow-x: hidden; }
        .auth-container { min-height: 100vh; display: flex; }
        .auth-sidebar {
            flex: 1;
            background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.9)), url('assets/img/london.png') no-repeat center center/cover;
            display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 4rem; color: #fff;
        }
        .auth-form-section { flex: 0 0 500px; display: flex; flex-direction: column; justify-content: center; padding: 4rem; background: #fff; position: relative; }
        @media (max-width: 992px) { .auth-sidebar { display: none; } .auth-form-section { flex: 1; padding: 2rem; } }
        .glass-card { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; padding: 2.5rem; max-width: 500px; }
        .btn-auth { background: var(--primary); color: #fff; border-radius: 12px; padding: 12px; font-weight: 700; border: none; transition: all 0.3s; }
        .btn-auth:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); }
        .form-control-elite { background: #f8fafc; border: 2px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; transition: all 0.3s; }
        .form-control-elite:focus { background: #fff; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .animate-up { animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .back-home { position: absolute; top: 2rem; right: 2rem; color: var(--navy); text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-sidebar text-center">
        <div class="glass-card animate-up">
            <h1 class="display-5 fw-bold mb-3">Join the Elite</h1>
            <p class="lead opacity-75 mb-0">Create an account to start your journey with the world's most professional booking platform.</p>
        </div>
    </div>
    <div class="auth-form-section">
        <a href="index.php" class="back-home"><i class="bi bi-arrow-left"></i> Home</a>
        <div class="w-100 animate-up">
            <div class="mb-5">
                <h2 class="fw-bold text-dark mb-2">Create Account</h2>
                <p class="text-muted">Fill in your details to get started.</p>
                <div class="alert alert-warning border-0 rounded-3 p-2 small mt-2">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Simulation:</strong> This is a mock booking system for educational purposes. Do not use real personal passwords.
                </div>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 rounded-3 p-3 mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-elite" placeholder="John Doe" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-elite" placeholder="name@example.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
                    <input type="password" name="password" class="form-control form-control-elite" placeholder="••••••••" required minlength="6">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control form-control-elite" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-auth w-100 mb-4">Create Account</button>
            </form>
            <div class="text-center">
                <p class="text-muted small">Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Sign In</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
