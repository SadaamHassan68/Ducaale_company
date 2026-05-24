<?php
ob_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$userAuth = new User($pdo);
$isLoggedIn = $userAuth->isLoggedIn();
$userName = $isLoggedIn ? $_SESSION['name'] : '';
$userRole = $isLoggedIn ? $_SESSION['role'] : '';

$dashboardLink = base_url('login.php');
if ($isLoggedIn) {
    if ($userRole === 'Admin') $dashboardLink = base_url('admin/dashboard.php');
    elseif ($userRole === 'Staff') $dashboardLink = base_url('admin/manage_flights.php');
    else $dashboardLink = base_url('dashboard.php');
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ducaale Airline - Sky-High Clarity</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <!-- Google Search Console Verification -->
    <meta name="google-site-verification" content="oGOaRA2-q-h2SRr7M_a5_rx-tSgwQ6s9Ae6q6nVPUQU" />
</head>
<body>

    <!-- Corporate Navbar -->
    <nav class="navbar navbar-expand-lg navbar-ducaale sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('index.php') ?>">
                <i class="bi bi-airplane-fill text-primary-blue"></i>
                <span>DUCAALE</span>
            </a>
            
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-1"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (!isset($hideMainHeader) || !$hideMainHeader): ?>
                    <ul class="navbar-nav mx-auto gap-2">
                        <li class="nav-item"><a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">HOME</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php#available') ?>">DESTINATIONS</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php#features') ?>">FEATURES</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('contact.php') ?>">CONTACT</a></li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav mx-auto"></ul>
                <?php endif; ?>

                <div class="d-flex align-items-center">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light rounded-pill px-4 py-2 small fw-bold dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5"></i> <?= htmlspecialchars($userName) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-3 p-2">
                                <li><a class="dropdown-item rounded-3" href="<?= $dashboardLink ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item rounded-3" href="<?= base_url('profile.php') ?>"><i class="bi bi-person-gear me-2"></i> Profile</a></li>
                                <li><hr class="dropdown-divider opacity-10"></li>
                                <li><a class="dropdown-item rounded-3 text-danger" href="<?= base_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= base_url('login.php') ?>" class="btn btn-primary-blue rounded-pill px-4 py-2 small fw-bold text-white text-decoration-none">SIGN IN</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <?php if ($currentPage != 'index.php' && $currentPage != 'login.php' && $currentPage != 'signup.php'): ?>
        <!-- Spacing for subpages -->
        <div style="height: 40px;"></div>
    <?php endif; ?>
