<?php
require_once __DIR__ . '/includes/header.php';

$faqs = [
    'Booking & Flights' => [
        ['q' => 'How do I book a flight?', 'a' => 'Simply enter your origin, destination, and preferred date on the homepage. Browse the available flights, select your preferred seat from our interactive map, and confirm your booking.'],
        ['q' => 'Can I change my seat after booking?', 'a' => 'Yes, you can change your seat by contacting our support team with your booking reference. Direct online seat modification is coming soon.'],
        ['q' => 'What is the "Elite Command Center"?', 'a' => 'It is our backend management system that ensures all bookings are processed securely and flights are tracked in real-time.']
    ],
    'Payments & Offline Verification' => [
        ['q' => 'How does the offline payment work?', 'a' => 'After booking, your status remains "Pending". You must make the payment via our authorized physical centers or bank transfer, and our admins will verify it to confirm your ticket.'],
        ['q' => 'What happens if my payment is not verified?', 'a' => 'If payment is not verified within 24 hours of the flight departure, the booking may be automatically cancelled to free up the seat.']
    ],
    'Support & Tickets' => [
        ['q' => 'How do I track my support ticket?', 'a' => 'You will receive a ticket ID upon submission. Our admins update the status (Open, In Progress, Resolved) which you can check by contacting us again or viewing your dashboard.'],
        ['q' => 'What is the average response time?', 'a' => 'Our support team typically responds to all inquiries within 15 minutes during business hours.']
    ]
];
?>

<div class="container py-5 animate-fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <span class="badge bg-primary px-3 py-2 mb-3 rounded-pill text-uppercase letter-spacing-2">Help Center</span>
                <h1 class="fw-bold text-dark display-5">Frequently Asked Questions</h1>
                <p class="text-muted lead">Everything you need to know about flying with Wehliye Airlines.</p>
            </div>

            <!-- Search FAQ -->
            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-body p-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 pe-0"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" id="faqSearch" class="form-control border-0 shadow-none ps-3" placeholder="Search for answers (e.g. payment, seat, cancellation)...">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="nav flex-column nav-pills shadow-sm rounded-4 bg-white p-3 border" id="faq-tabs" role="tablist">
                        <?php $i = 0; foreach ($faqs as $category => $items): ?>
                            <button class="nav-link text-start py-3 px-4 fw-bold mb-2 <?= $i === 0 ? 'active' : '' ?>" 
                                    id="tab-<?= $i ?>" data-bs-toggle="pill" data-bs-target="#content-<?= $i ?>" type="button">
                                <i class="bi bi-folder2-open me-2"></i> <?= $category ?>
                            </button>
                        <?php $i++; endforeach; ?>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white mt-4 overflow-hidden">
                        <div class="card-body p-4 position-relative">
                            <i class="bi bi-headset position-absolute top-0 end-0 opacity-10" style="font-size: 5rem; transform: rotate(15deg);"></i>
                            <h5 class="fw-bold mb-3">Still have questions?</h5>
                            <p class="small opacity-75 mb-4">If you cannot find the answer you are looking for, our team is ready to help.</p>
                            <a href="contact.php" class="btn btn-white text-primary fw-bold w-100 rounded-pill py-2">Open a Ticket</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="tab-content" id="faq-tabContent">
                        <?php $i = 0; foreach ($faqs as $category => $items): ?>
                            <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="content-<?= $i ?>" role="tabpanel">
                                <h4 class="fw-bold text-dark mb-4 px-2"><?= $category ?></h4>
                                <div class="accordion accordion-flush" id="accordion-<?= $i ?>">
                                    <?php foreach ($items as $idx => $faq): ?>
                                        <div class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden faq-item" data-search="<?= strtolower($faq['q'] . ' ' . $faq['a']) ?>">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed fw-bold py-4 px-4 bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq-<?= $i ?>-<?= $idx ?>">
                                                    <?= $faq['q'] ?>
                                                </button>
                                            </h2>
                                            <div id="faq-<?= $i ?>-<?= $idx ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-<?= $i ?>">
                                                <div class="accordion-body p-4 bg-light border-top border-white border-opacity-10">
                                                    <p class="text-muted mb-0 leading-relaxed"><?= $faq['a'] ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php $i++; endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('faqSearch').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(item => {
        if (item.dataset.search.includes(val)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<style>
    .nav-pills .nav-link { color: #64748b; border-radius: 12px; }
    .nav-pills .nav-link.active { background: var(--primary-color); color: white; }
    .accordion-button:not(.collapsed) { color: var(--primary-color); background: #fff; }
    .accordion-button::after { background-size: 1rem; }
    .btn-white { background: white; border: none; }
    .btn-white:hover { background: #f8fafc; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
