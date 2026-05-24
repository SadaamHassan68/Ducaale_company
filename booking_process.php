<?php
require_once __DIR__ . '/includes/header.php';

$userAuth->requireRole('Passenger');

$flightManager = new Flight($pdo);
$bookingManager = new Booking($pdo);
$bookingMessage = '';

if (!isset($_GET['flight_id'])) {
    header("Location: index.php");
    exit;
}

$flight_id = (int)$_GET['flight_id'];

$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = :id");
$stmt->execute(['id' => $flight_id]);
$flight = $stmt->fetch();

if (!$flight) {
    die("Flight not found.");
}

$stmt = $pdo->prepare("SELECT * FROM seats WHERE flight_id = :id ORDER BY seat_number");
$stmt->execute(['id' => $flight_id]);
$seats = $stmt->fetchAll();

$rows = [];
foreach ($seats as $seat) {
    preg_match('/(\d+)/', $seat['seat_number'], $matches);
    $rowNum = $matches[1];
    $rows[$rowNum][] = $seat;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $seat_id = $_POST['seat_id'];
    $user_id = $_SESSION['user_id']; 
    $p_name = $_POST['passenger_name'] ?? ($_SESSION['name'] ?? 'Passenger');
    $p_phone = $_POST['passenger_phone'] ?? '';
    $p_email = $_POST['passenger_email'] ?? ($_SESSION['email'] ?? '');
    
    // DEBUG: Log POST data
    file_put_contents('debug_post.txt', print_r($_POST, true));
    
    // Find seat class for pricing multiplier
    $stmtClass = $pdo->prepare("SELECT seat_class FROM seats WHERE id = ?");
    $stmtClass->execute([$seat_id]);
    $seatClass = $stmtClass->fetchColumn();

    $multiplier = 1.0;
    if ($seatClass === 'First Class') $multiplier = 4.0;
    if ($seatClass === 'Business') $multiplier = 2.5;

    $base_price = $flight['base_price'] * $multiplier;
    
    $result = $bookingManager->createBooking($user_id, $flight_id, $seat_id, $base_price, $p_name, $p_phone, $p_email);
    
    if ($result['success']) {
        // Redirect to dashboard with a success message flag
        header("Location: dashboard.php?msg=booked");
        exit;
    } else {
        $bookingMessage = "Error: " . $result['message'];
    }
}

function renderSeat($seat) {
    $classes = ['seat', 'd-flex', 'align-items-center', 'justify-content-center', 'rounded', 'fw-bold'];
    
    if ($seat['status'] === 'Booked' || $seat['status'] === 'Reserved') {
        $classes[] = 'booked';
    } else {
        $classes[] = 'available';
    }

    if ($seat['seat_type'] === 'Emergency Exit') {
        $classes[] = 'exit-row';
    }
    
    $classStr = implode(' ', $classes);
    
    return sprintf(
        '<div class="%s" data-seat-id="%s" data-seat-num="%s" title="Seat %s (%s)">%s</div>',
        $classStr,
        $seat['id'],
        $seat['seat_number'],
        $seat['seat_number'],
        $seat['seat_type'],
        $seat['seat_number']
    );
}
?>

    <div class="container mt-5 mb-5">
        
        <?php if ($bookingMessage): ?>
            <div class="alert <?= strpos($bookingMessage, 'Success') !== false ? 'alert-success' : 'alert-danger' ?> text-center shadow-sm" role="alert">
                <h4 class="alert-heading"><?= strpos($bookingMessage, 'Success') !== false ? 'Booking Confirmed!' : 'Booking Failed' ?></h4>
                <p class="mb-0"><?= htmlspecialchars($bookingMessage) ?></p>
                <?php if (strpos($bookingMessage, 'Success') !== false): ?>
                    <hr>
                    <a href="index.php" class="btn btn-success mt-2">Return to Search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Flight Summary Sidebar -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Flight Summary</h4>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Flight</span>
                            <span class="fw-bold"><?= htmlspecialchars($flight['flight_number']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Route</span>
                            <span class="fw-bold"><?= htmlspecialchars($flight['origin']) ?> to <?= htmlspecialchars($flight['destination']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Departure</span>
                            <span class="fw-bold"><?= date('M d, Y H:i', strtotime($flight['departure_time'])) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="text-muted fs-5">Base Price</span>
                            <span class="price-tag fs-3">$<?= number_format($flight['base_price'], 2) ?></span>
                        </div>

                        <div class="seat-legend bg-light p-3 rounded-3 mt-4">
                            <h6 class="fw-bold mb-3">Legend</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="legend-box available me-2"></div> Available
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="legend-box booked me-2"></div> Booked
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="legend-box exit me-2"></div> Emergency Exit
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seat Map Area -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-5">
                        <h3 class="fw-bold text-center mb-2">Select Your Seat</h3>
                        <p class="text-center text-muted mb-5">Click on an available seat to proceed with booking.</p>
                        
                        <div class="plane-fuselage mx-auto">
                            <div class="plane-cockpit"></div>
                            <div class="plane-wing left"></div>
                            <div class="plane-wing right"></div>
                            
                                <div class="seat-grid">

                                    <?php 
                                    $currentClass = '';
                                    foreach ($rows as $rowNum => $rowSeats): 
                                        $rowClass = $rowSeats[0]['seat_class'];
                                        if ($rowClass !== $currentClass): 
                                            $currentClass = $rowClass;
                                    ?>
                                        <div class="cabin-divider">
                                            <span><?= $currentClass ?> Cabin</span>
                                        </div>
                                    <?php endif; ?>

                                        <div class="seat-row d-flex justify-content-center mb-3">
                                            <div class="seat-row-number d-flex align-items-center justify-content-center me-3"><?= $rowNum ?></div>
                                            
                                            <?php 
                                            $leftSeats = array_slice($rowSeats, 0, 3);
                                            $rightSeats = array_slice($rowSeats, 3, 3);
                                            ?>
                                            
                                            <div class="seat-group left-side d-flex gap-2 me-4">
                                                <?php foreach ($leftSeats as $seat): ?>
                                                    <?= renderSeat($seat) ?>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <div class="aisle" style="width: 40px;"></div>
                                            
                                            <div class="seat-group right-side d-flex gap-2">
                                                <?php foreach ($rightSeats as $seat): ?>
                                                    <?= renderSeat($seat) ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                </div>

                            
                            <div class="text-center mt-5">
                                <div class="small text-muted text-uppercase fw-bold opacity-50 tracking-widest">Tail Section</div>
                                <div class="mt-2" style="width: 100px; height: 10px; background: #e2e8f0; margin: 0 auto; border-radius: 0 0 10px 10px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header border-bottom-0">
            <h5 class="modal-title fw-bold">Confirm Passenger Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body py-4">
            <div class="text-center mb-4">
                <h4 class="mb-1">Seat <span id="modalSeatNum" class="text-primary"></span></h4>
                <p class="text-muted small mb-2">Please provide the passenger details for this journey.</p>
                <div class="alert alert-warning border-0 rounded-3 p-2 small mt-0 mx-auto" style="max-width: 400px;">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Simulation:</strong> This is a mock booking. Do not enter real contact or payment information.
                </div>
            </div>
            
            <form method="POST" action="booking_process.php?flight_id=<?= $flight_id ?>" id="bookingForm">
                <input type="hidden" name="action" value="book">
                <input type="hidden" id="selected_seat_id" name="seat_id" value="">

                
                <div class="mb-3">
                    <label class="form-label small text-muted text-uppercase fw-bold">Full Name</label>
                    <input type="text" class="form-control rounded-3" name="passenger_name" value="<?= htmlspecialchars($_SESSION['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted text-uppercase fw-bold">Phone Number</label>
                    <input type="text" class="form-control rounded-3" name="passenger_phone" placeholder="+252..." required>
                </div>
                <div class="mb-0">
                    <label class="form-label small text-muted text-uppercase fw-bold">Email Address</label>
                    <input type="email" class="form-control rounded-3" name="passenger_email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required>
                </div>
            </form>
          </div>
          <div class="modal-footer border-top-0 justify-content-center">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="bookingForm" class="btn btn-primary px-4 fw-bold shadow-sm">Reserve Now <i class="bi bi-airplane-fill ms-2"></i></button>

          </div>
        </div>
      </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seats = document.querySelectorAll('.seat.available');
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            const seatInput = document.getElementById('selected_seat_id');
            const modalSeatNum = document.getElementById('modalSeatNum');
            const form = document.getElementById('bookingForm');

            seats.forEach(seat => {
                seat.addEventListener('click', function() {
                    const seatId = this.getAttribute('data-seat-id');
                    const seatNum = this.getAttribute('data-seat-num');
                    
                    seatInput.value = seatId;
                    modalSeatNum.textContent = seatNum;
                    
                    confirmModal.show();
                });
            });
        });
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
