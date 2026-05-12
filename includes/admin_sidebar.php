<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar d-md-block collapse shadow-sm" id="sidebarMenu">
    <div class="position-sticky pt-0 h-100 d-flex flex-column">
        <div class="text-center mb-5 py-4 px-3 sidebar-logo-container">
            <div class="bg-primary bg-opacity-10 p-3 rounded-4 d-inline-block mb-3 shadow-sm border border-white border-opacity-10">
                <i class="bi bi-airplane-engines-fill text-primary fs-1"></i>
            </div>
            <h5 class="text-white fw-bold tracking-tight mb-1">DUCAALE</h5>
            <div class="small text-muted text-uppercase fw-bold letter-spacing-2" style="font-size: 0.6rem; opacity: 0.6;">Elite Command Center</div>
        </div>

        <div class="px-3 mb-4">
            <div class="d-flex align-items-center bg-white bg-opacity-5 p-2 rounded-3">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random" class="rounded-circle me-2" width="32" height="32">
                <div class="overflow-hidden">
                    <div class="text-white small fw-bold text-truncate"><?= htmlspecialchars($_SESSION['name']) ?></div>
                    <div class="text-muted" style="font-size: 0.65rem;"><?= htmlspecialchars($_SESSION['role']) ?></div>
                </div>
            </div>
        </div>

        <ul class="nav flex-column px-2 flex-grow-1">
            <li class="nav-item">
                <a class="nav-link nav-link-admin <?= $currentPage == 'dashboard.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/dashboard.php') ?>">
                    <i class="bi bi-grid-1x2-fill me-3"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link nav-link-admin <?= $currentPage == 'manage_flights.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/manage_flights.php') ?>">
                    <i class="bi bi-airplane-fill me-3"></i>
                    Flights
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link nav-link-admin <?= $currentPage == 'seat_pricing.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/seat_pricing.php') ?>">
                    <i class="bi bi-tags-fill me-3"></i>
                    Pricing
                </a>
            </li>

            <div class="text-muted text-uppercase fw-bold px-4 mt-4 mb-2 sidebar-heading" style="letter-spacing: 1px;">Reservations & Support</div>

            <li class="nav-item">
                <a class="nav-link nav-link-admin <?= $currentPage == 'manage_bookings.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/manage_bookings.php') ?>">
                    <i class="bi bi-journal-bookmark-fill me-3"></i>
                    Bookings
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link nav-link-admin <?= $currentPage == 'manifest.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/manifest.php') ?>">
                    <i class="bi bi-card-checklist me-3"></i>
                    Manifests
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link nav-link-admin <?= $currentPage == 'support.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/support.php') ?>">
                    <i class="bi bi-chat-dots-fill me-3"></i>
                    Support
                </a>
            </li>

            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <div class="text-muted text-uppercase fw-bold px-4 mt-4 mb-2 sidebar-heading" style="letter-spacing: 1px;">System Management</div>

                <li class="nav-item">
                    <a class="nav-link nav-link-admin <?= $currentPage == 'manage_staff.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/manage_staff.php') ?>">
                        <i class="bi bi-people-fill me-3"></i>
                        Staff
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-admin <?= $currentPage == 'reports.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/reports.php') ?>">
                        <i class="bi bi-bar-chart-fill me-3"></i>
                        Reports
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-admin <?= $currentPage == 'activity_logs.php' ? 'active' : '' ?> d-flex align-items-center" href="<?= base_url('admin/activity_logs.php') ?>">
                        <i class="bi bi-clock-history me-3"></i>
                        Logs
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="px-4 pb-4 mt-4">
            <a href="<?= base_url('logout.php') ?>" class="btn btn-danger w-100 rounded-3 py-2 fw-bold small shadow-sm">
                <i class="bi bi-box-arrow-right me-2"></i> Sign Out
            </a>
        </div>
    </div>
</div>
