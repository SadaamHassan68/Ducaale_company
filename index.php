<?php
require_once __DIR__ . '/includes/header.php';


$flightManager = new Flight($pdo);
$upcomingFlights = $flightManager->getUpcomingFlights(6);

$searchResults = [];
$isSearch = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search') {
    $origin = $_POST['origin'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $date = $_POST['date'] ?? '';
    $searchResults = $flightManager->searchFlights($origin, $destination, $date);
    $isSearch = true;
}
?>

<!-- Hero Section -->
<section class="hero-corporate" style="background: linear-gradient(rgba(15, 23, 42, 0.5), rgba(15, 23, 42, 0.5)), url('assets/img/hero_bg.png') no-repeat center center/cover;">
    <div class="container animate-fade-up">
        <span class="hero-badge">PREMIUM TRAVEL EXPERIENCE</span>
        <h1>Where will you go next?</h1>
        <p class="mx-auto">Discover and book premium flights to your dream destinations. Unparalleled comfort awaits.</p>
    </div>
</section>

<!-- Floating Search Bar -->
<div class="container reveal" id="book" data-animation="animate-scale-in">
    <div class="search-float-card">
        <form method="POST" action="index.php" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="search">
            
            <div class="col-lg-3 col-md-6">
                <div class="search-input-box">
                    <label>FROM</label>
                    <div class="input-wrap">
                        <i class="bi bi-geo-alt text-primary-blue"></i>
                        <input type="text" name="origin" placeholder="e.g. JFK" value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="search-input-box">
                    <label>TO</label>
                    <div class="input-wrap">
                        <i class="bi bi-pin-map text-primary-blue"></i>
                        <input type="text" name="destination" placeholder="e.g. LHR" value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="search-input-box">
                    <label>DEPARTURE</label>
                    <div class="input-wrap">
                        <i class="bi bi-calendar3 text-primary-blue"></i>
                        <input type="date" name="date" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <button type="submit" class="btn-search-blue">
                    <i class="bi bi-search"></i>
                    <span>Find Flights</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container py-5 mt-5">
    <?php if ($isSearch): ?>
        <!-- Search Results -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Search Results</h2>
                <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Clear Search</a>
            </div>
            <div class="row g-4">
                <?php if (empty($searchResults)): ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-search-heart display-1 text-muted opacity-25 mb-4"></i>
                        <h3 class="fw-bold">No Flights Found</h3>
                        <p class="text-muted">We couldn't find any flights for that route/date. Try another search.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($searchResults as $flight): 
                        $img = 'assets/img/flight_default.png';
                        $isPast = strtotime($flight['departure_time']) < time();
                        $status = $isPast ? 'Completed' : ($flight['status'] ?? 'Scheduled');
                        $statusClass = $isPast ? 'bg-secondary' : ($status == 'Scheduled' ? 'bg-success' : 'bg-warning');
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="flight-card-elite">
                                <div class="elite-img-box">
                                    <img src="<?= $img ?>" alt="<?= $flight['destination'] ?>">
                                    <div class="status-badge-elite <?= $statusClass ?>">
                                        <div class="status-dot"></div>
                                        <?= $status ?>
                                    </div>
                                    <div class="brand-badge-elite">Ducaale Air</div>
                                    <div class="flight-num-overlay">
                                        <i class="bi bi-airplane-engines"></i>
                                        <h3><?= htmlspecialchars($flight['flight_number']) ?></h3>
                                    </div>
                                    <div class="price-overlay-elite">
                                        <div class="price-label-elite">STARTING AT</div>
                                        <div class="price-value-elite">$<?= number_format($flight['base_price'], 0) ?></div>
                                    </div>
                                </div>
                                <div class="elite-card-body">
                                    <div class="elite-route-row">
                                        <div class="route-node"><span class="code"><?= strtoupper(substr($flight['origin'], 0, 3)) ?></span><span class="city"><?= $flight['origin'] ?></span></div>
                                        <div class="route-divider-icon"><i class="bi bi-airplane-fill"></i></div>
                                        <div class="route-node"><span class="code"><?= strtoupper(substr($flight['destination'], 0, 3)) ?></span><span class="city"><?= $flight['destination'] ?></span></div>
                                    </div>
                                    <div class="elite-details-grid">
                                        <div class="detail-sub-item"><span class="label">DEPARTURE</span><span class="value"><?= date('M d, Y', strtotime($flight['departure_time'])) ?></span></div>
                                        <div class="detail-sub-item text-end"><span class="label">AIRCRAFT</span><span class="value">Elite Jet</span></div>
                                    </div>
                                    <?php if ($isPast): ?>
                                        <button class="btn btn-secondary rounded-pill w-100 py-2 fw-bold" disabled>COMPLETED</button>
                                    <?php else: ?>
                                        <a href="booking_process.php?flight_id=<?= $flight['id'] ?>" class="btn btn-elite-book">BOOK NOW</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php 
    // Fetch distinct sets of flights
    $availableFlights = $flightManager->getUpcomingFlights(50, false); // Only upcoming
    $fullSchedule = $flightManager->getUpcomingFlights(50, true);    // All
    ?>

    <!-- Available Flights Section (Top) -->
    <div class="text-center mb-5 reveal" id="available">
        <span class="text-primary-blue fw-bold small text-uppercase letter-spacing-1">READY TO BOOK</span>
        <h2 class="fw-bold fs-1 mt-2">Available Flights <span class="badge bg-success fs-6 ms-2"><?= count($availableFlights) ?> Active</span></h2>
        <p class="text-muted mx-auto" style="max-width: 600px;">Find and reserve your seat on our currently open routes.</p>
    </div>

    <div class="row g-4 mb-5 pb-5 border-bottom reveal">
        <?php if (empty($availableFlights)): ?>
            <div class="col-12 text-center py-4">
                <p class="text-muted italic">No upcoming flights scheduled at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($availableFlights as $flight): 
                $img = 'assets/img/flight_default.png';
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="flight-card-elite">
                        <div class="elite-img-box">
                            <img src="<?= $img ?>" alt="<?= $flight['destination'] ?>">
                            <div class="status-badge-elite bg-success">
                                <div class="status-dot"></div>
                                Scheduled
                            </div>
                            <div class="brand-badge-elite">Ducaale Air</div>
                            <div class="flight-num-overlay">
                                <i class="bi bi-airplane-engines"></i>
                                <h3><?= htmlspecialchars($flight['flight_number']) ?></h3>
                            </div>
                            <div class="price-overlay-elite">
                                <div class="price-label-elite">STARTING AT</div>
                                <div class="price-value-elite">$<?= number_format($flight['base_price'], 0) ?></div>
                            </div>
                        </div>
                        <div class="elite-card-body">
                            <div class="elite-route-row">
                                <div class="route-node"><span class="code"><?= strtoupper(substr($flight['origin'], 0, 3)) ?></span><span class="city"><?= $flight['origin'] ?></span></div>
                                <div class="route-divider-icon"><i class="bi bi-airplane-fill"></i></div>
                                <div class="route-node"><span class="code"><?= strtoupper(substr($flight['destination'], 0, 3)) ?></span><span class="city"><?= $flight['destination'] ?></span></div>
                            </div>
                            <div class="elite-details-grid">
                                <div class="detail-sub-item"><span class="label">DEPARTURE</span><span class="value"><?= date('M d, Y', strtotime($flight['departure_time'])) ?></span></div>
                                <div class="detail-sub-item text-end"><span class="label">AIRCRAFT</span><span class="value">Elite Jet</span></div>
                            </div>
                            <a href="booking_process.php?flight_id=<?= $flight['id'] ?>" class="btn btn-elite-book">BOOK NOW</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Full Flight Schedule Section (Bottom) -->
    <div class="text-center mb-5 mt-5 reveal" id="destinations">
        <span class="text-primary-blue fw-bold small text-uppercase letter-spacing-1">FLIGHT LOG</span>
        <h2 class="fw-bold fs-1 mt-2">Our Full Flight Schedule <span class="badge bg-primary-blue fs-6 ms-2"><?= count($fullSchedule) ?> Total</span></h2>
        <p class="text-muted mx-auto" style="max-width: 600px;">A complete historical and future record of Ducaale Airline operations.</p>
    </div>

    <div class="row g-4 reveal">
        <?php foreach ($fullSchedule as $flight): 
            $img = 'assets/img/flight_default.png';
            $isPast = strtotime($flight['departure_time']) < time();
            $status = $isPast ? 'Completed' : ($flight['status'] ?? 'Scheduled');
            $statusClass = $isPast ? 'bg-secondary' : ($status == 'Scheduled' ? 'bg-success' : 'bg-warning');
        ?>
            <div class="col-lg-4 col-md-6">
                <div class="flight-card-elite <?= $isPast ? 'opacity-75' : '' ?>">
                    <div class="elite-img-box">
                        <img src="<?= $img ?>" alt="<?= $flight['destination'] ?>">
                        <div class="status-badge-elite <?= $statusClass ?>">
                            <div class="status-dot"></div>
                            <?= $status ?>
                        </div>
                        <div class="brand-badge-elite">Ducaale Air</div>
                        <div class="flight-num-overlay">
                            <i class="bi bi-airplane-engines"></i>
                            <h3><?= htmlspecialchars($flight['flight_number']) ?></h3>
                        </div>
                        <div class="price-overlay-elite">
                            <div class="price-label-elite">STARTING AT</div>
                            <div class="price-value-elite">$<?= number_format($flight['base_price'], 0) ?></div>
                        </div>
                    </div>
                    <div class="elite-card-body">
                        <div class="elite-route-row">
                            <div class="route-node"><span class="code"><?= strtoupper(substr($flight['origin'], 0, 3)) ?></span><span class="city"><?= $flight['origin'] ?></span></div>
                            <div class="route-divider-icon"><i class="bi bi-airplane-fill"></i></div>
                            <div class="route-node"><span class="code"><?= strtoupper(substr($flight['destination'], 0, 3)) ?></span><span class="city"><?= $flight['destination'] ?></span></div>
                        </div>
                        <div class="elite-details-grid">
                            <div class="detail-sub-item"><span class="label">DEPARTURE</span><span class="value"><?= date('M d, Y', strtotime($flight['departure_time'])) ?></span></div>
                            <div class="detail-sub-item text-end"><span class="label">AIRCRAFT</span><span class="value">Elite Jet</span></div>
                        </div>
                        <?php if ($isPast): ?>
                            <button class="btn btn-secondary rounded-pill w-100 py-2 fw-bold" disabled>COMPLETED</button>
                        <?php else: ?>
                            <a href="booking_process.php?flight_id=<?= $flight['id'] ?>" class="btn btn-elite-book">BOOK NOW</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- The Ducaale Standard Section -->
<section class="py-5 bg-light reveal" id="features">
    <div class="container py-5 text-center">
        <span class="text-primary-blue fw-bold small text-uppercase letter-spacing-1">WHY DUCAALE AIR</span>
        <h2 class="fw-bold fs-1 mt-2 mb-5">The Ducaale Standard</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card-elite">
                    <div class="feature-icon-box">
                        <i class="bi bi-airplane-engines"></i>
                    </div>
                    <h4>Premium Fleet</h4>
                    <p>Fly in our state-of-the-art aircraft equipped with modern amenities, ultra-comfort seating, and high-speed Wi-Fi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card-elite">
                    <div class="feature-icon-box">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h4>Instant E-Tickets</h4>
                    <p>Experience a paperless journey. Receive your digital boarding pass and flight updates immediately via email or SMS.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card-elite">
                    <div class="feature-icon-box">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Secure Booking</h4>
                    <p>Your safety and privacy are our top priorities. We use bank-grade encryption to protect your data and payments.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Support Hub Section -->
<div class="container py-5 reveal" data-animation="animate-scale-in">
    <div class="support-hub-card">
        <div class="row g-5 align-items-center">
            <div class="col-lg-7">
                <span class="support-tag">SUPPORT HUB</span>
                <h2 class="fw-bold display-5 mb-4">Questions? Our experts are here to help.</h2>
                <p class="opacity-75 mb-5">Whether you need to change your seat, inquire about a refund, or plan a complex route, our dedicated support team is available 24/7.</p>
                <a href="contact.php" class="btn btn-primary-blue rounded-pill px-5 py-3 fw-bold fs-5">Contact Support</a>
            </div>
            <div class="col-lg-5">
                <div class="stats-glass-card">
                    <div class="stat-item">
                        <div class="bg-primary-blue bg-opacity-10 p-3 rounded-circle"><i class="bi bi-clock text-primary-blue fs-4"></i></div>
                        <div>
                            <div class="small opacity-75">Avg. Response Time: <span class="text-primary-blue fw-bold">Under 15 mins</span></div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle"><i class="bi bi-check2-circle text-success fs-4"></i></div>
                        <div>
                            <div class="small opacity-75">Resolution Rate: <span class="text-success fw-bold">99.8%</span></div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle"><i class="bi bi-chat-text text-info fs-4"></i></div>
                        <div>
                            <div class="small opacity-75">Tickets Resolved: <span class="text-info fw-bold">24,500+</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<div class="container py-5">
    <div class="newsletter-section">
        <div class="mail-icon-circle">
            <i class="bi bi-envelope"></i>
        </div>
        <h2 class="fw-bold mb-3">Never Miss a Flight Deal</h2>
        <p class="text-muted mb-5">Subscribe to our newsletter and receive exclusive offers, travel guides, and early access to new routes.</p>
        <form class="d-flex justify-content-center gap-2 mx-auto newsletter-form" style="max-width: 600px;">
            <input type="email" class="form-control rounded-start p-3 border-end-0" placeholder="Your Email Address" style="border-radius: 12px 0 0 12px !important;">
            <button class="btn btn-primary-blue px-5 fw-bold" type="submit" style="border-radius: 0 12px 12px 0 !important;">Subscribe</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
