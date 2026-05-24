    <footer class="footer-corporate mt-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="footer-logo">
                        <i class="bi bi-airplane-fill text-primary-blue"></i>
                        <span>DUCAALE</span>
                    </div>
                    <p class="opacity-75 small mb-4">Experience the world with unparalleled comfort and elite service. Your journey begins with the Ducaale Standard.</p>
                </div>

                <div class="col-md-2 col-6">
                    <h6 class="fw-bold text-uppercase mb-4">EXPLORE</h6>
                    <ul class="list-unstyled footer-links opacity-75 small">
                        <li class="mb-2"><a href="<?= base_url('index.php') ?>" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?= base_url('index.php#destinations') ?>" class="text-white text-decoration-none">Destinations</a></li>
                        <li class="mb-2"><a href="<?= base_url('index.php#features') ?>" class="text-white text-decoration-none">Features</a></li>
                    </ul>
                </div>

                <div class="col-md-3 col-6">
                    <h6 class="fw-bold text-uppercase mb-4">SUPPORT</h6>
                    <ul class="list-unstyled footer-links opacity-75 small">
                        <li class="mb-2"><a href="<?= base_url('faq.php') ?>" class="text-white text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="<?= base_url('contact.php') ?>" class="text-white text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="<?= base_url('flight_status.php') ?>" class="text-white text-decoration-none">Flight Status</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h6 class="fw-bold text-uppercase mb-4">CONTACT</h6>
                    <ul class="list-unstyled footer-links opacity-75 small">
                        <li class="mb-2 d-flex align-items-center gap-2"><i class="bi bi-geo-alt text-primary-blue"></i> Mogadishu, Somalia</li>
                        <li class="mb-2 d-flex align-items-center gap-2"><i class="bi bi-telephone text-primary-blue"></i> +252 614612010</li>
                        <li class="mb-2 d-flex align-items-center gap-2"><i class="bi bi-envelope text-primary-blue"></i> info@ducaale.com</li>
                    </ul>
                </div>
            </div>

            <hr class="my-5 opacity-10">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small opacity-50">
                <div>&copy; <?= date('Y') ?> Ducaale Airline. All rights reserved. <span class="ms-2 text-warning d-block d-md-inline-block fw-bold">⚠️ Educational / Portfolio Project (Not a Real Commercial Airline)</span></div>
                <div class="d-flex gap-4">
                    <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth reveal animation for elements -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const animation = entry.target.dataset.animation || 'animate-fade-up';
                        entry.target.classList.add(animation);
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
