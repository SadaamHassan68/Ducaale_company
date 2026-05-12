<?php
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    $user_id = $isLoggedIn ? $_SESSION['user_id'] : null;
    $guest_name = !$isLoggedIn ? trim($_POST['name']) : null;
    $guest_email = !$isLoggedIn ? trim($_POST['email']) : null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO support_messages (user_id, name, email, subject, message) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $name = $isLoggedIn ? $_SESSION['name'] : $guest_name;
        $email = $isLoggedIn ? ($_SESSION['email'] ?? 'passenger@ducaale.com') : $guest_email;

        if ($stmt->execute([$user_id, $name, $email, $subject, $message])) {
            $success = "Your message has been sent successfully. Our team will get back to you soon.";
        } else {
            $error = "Failed to send message. Please try again later.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="row g-0">
                    <!-- Contact Info Sidebar -->
                    <div class="col-md-4 bg-navy-dark text-white p-5 d-flex flex-column justify-content-center">
                        <h3 class="fw-bold mb-4">Ducaale Concierge</h3>
                        <p class="mb-5 text-white-opacity-70">Fill out the form and our team will get back to you within 24 hours.</p>
                        
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-white bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-telephone-fill fs-5 text-primary-blue"></i>
                            </div>
                            <div>
                                <div class="small text-white-opacity-70 fw-bold text-uppercase">Phone</div>
                                <div class="fw-medium">+252 614612010</div>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-white bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-envelope-fill fs-5 text-primary-blue"></i>
                            </div>
                            <div>
                                <div class="small text-white-opacity-70 fw-bold text-uppercase">Email</div>
                                <div class="fw-medium">support@ducaale.com</div>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="small text-white-opacity-70 fw-bold text-uppercase">Office</div>
                                <div class="fw-medium">Mogadishu, Somalia</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="col-md-8 p-5 bg-white">
                        <h2 class="fw-bold mb-4 text-dark">Get in Touch</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row g-3">
                                <?php if (!$isLoggedIn): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted text-uppercase fw-bold">Your Name</label>
                                        <input type="text" class="form-control" name="name" placeholder="John Doe" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted text-uppercase fw-bold">Email Address</label>
                                        <input type="email" class="form-control" name="email" placeholder="john@example.com" required>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Subject</label>
                                    <input type="text" class="form-control" name="subject" placeholder="Flight Inquiry" required>
                                </div>
                                <div class="col-12 mb-4">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Message</label>
                                    <textarea class="form-control" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold rounded-pill shadow-sm w-100">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
