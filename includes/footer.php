<?php // includes/footer.php ?>
<footer class="site-footer">
    <div class="container">
        <div class="row gy-4">
            <!-- Brand column -->
            <div class="col-lg-4">
                <h5 class="footer-brand">
                    <i class="fas fa-ambulance text-danger me-2"></i>Ambulance<span class="text-danger">911</span>
                </h5>
                <p class="footer-desc">
                    Fast, reliable emergency ambulance services at your fingertips.
                    Available 24/7 to respond to your medical emergencies.
                </p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <!-- Quick links -->
            <div class="col-lg-2 col-md-4">
                <h6 class="footer-heading">Quick Links</h6>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>register.php">Register</a></li>
                    <li><a href="<?= BASE_URL ?>index.php#contact">Contact</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="col-lg-3 col-md-4">
                <h6 class="footer-heading">Services</h6>
                <ul class="footer-links">
                    <li><a href="#">Emergency Response</a></li>
                    <li><a href="#">Patient Transport</a></li>
                    <li><a href="#">ICU Ambulance</a></li>
                    <li><a href="#">Air Ambulance</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-3 col-md-4">
                <h6 class="footer-heading">Emergency Contact</h6>
                <ul class="footer-links">
                    <li><i class="fas fa-phone text-danger me-2"></i>108 (National)</li>
                    <li><i class="fas fa-phone text-danger me-2"></i>102 (Ambulance)</li>
                    <li><i class="fas fa-envelope text-danger me-2"></i>help@ambulance911.com</li>
                    <li><i class="fas fa-map-marker-alt text-danger me-2"></i>India</li>
                </ul>
            </div>
        </div>

        <hr class="footer-divider">
        <div class="footer-bottom text-center">
            <p class="mb-0">
                &copy; <?= date('Y') ?> Ambulance911. Built for College Mini Project.
                <span class="text-danger">❤</span> Made with PHP &amp; Bootstrap 5.
            </p>
        </div>
    </div>
</footer>
