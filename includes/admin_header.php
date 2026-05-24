<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$userAuth = new User($pdo);

// Ensure the user is logged in and is either Admin or Staff
if (!$userAuth->isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$role = $_SESSION['role'];
if ($role !== 'Admin' && $role !== 'Staff') {
    die("Access Denied: You do not have permission to view this backend.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backend Admin - Ducaale Airline</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin-premium.css?v=' . time()) ?>">
</head>
<body class="admin-mode">

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <?php require_once __DIR__ . '/admin_sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="admin-main-content px-0">
            <!-- Topbar -->
            <header class="admin-topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light d-lg-none me-2 me-md-3" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <h4 class="mb-0 fw-bold text-dark fs-6 fs-sm-5 fs-md-4">Backend Portal</h4>
                    <a href="<?= base_url('index.php') ?>" class="btn btn-sm btn-outline-light ms-2 ms-md-3 rounded-pill px-2 px-md-3 py-1 fw-bold border-opacity-25" target="_blank" title="View Website">
                        <i class="bi bi-globe2 me-md-1"></i><span class="d-none d-md-inline">View Website</span>
                    </a>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="input-group d-none d-lg-flex me-4 shadow-sm" style="width: 300px;">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-0" placeholder="Search PNR or Flight...">
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle fw-medium border-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random" class="rounded-circle" width="32" height="32" alt="Avatar">
                            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3">
                            <li><a class="dropdown-item py-2" href="<?= base_url('profile.php') ?>"><i class="bi bi-person me-2 text-muted"></i> Profile</a></li>
                            <li><a class="dropdown-item py-2" href="<?= base_url('profile.php') ?>"><i class="bi bi-gear me-2 text-muted"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-medium" href="<?= base_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Page Content Wraps Here -->
            <main class="px-4 py-4">
