<?php
// index.php – Public landing page
session_start();
define('BASE_URL', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambulance911 – Emergency Ambulance Support Near You</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Page Loader -->
<div class="page-loader" id="pageLoader">
    <div class="text-center">
        <div class="loader-ring mb-3"></div>
        <div style="color:rgba(255,255,255,.5);font-size:.85rem;">Loading...</div>
    </div>
</div>

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center gy-5">
            <!-- Left: Text -->
            <div class="col-lg-6">
                <div class="hero-badge">
                    <span class="status-dot available"></span>
                    24/7 Emergency Service Active
                </div>

                <h1 class="hero-title">
                    Emergency Ambulance<br>
                    <span class="highlight">Support Near You</span>
                </h1>

                <p class="hero-subtitle">
                    Get the nearest ambulance to your location in minutes.
                    Fast, reliable, and available around the clock for every medical emergency.
                </p>

                <div class="d-flex flex-wrap gap-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user/ambulances.php" class="btn-emergency">
                            <i class="fas fa-ambulance me-2"></i>Find Ambulance
                        </a>
                        <a href="user/dashboard.php" class="btn-outline-light-custom">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn-emergency">
                            <i class="fas fa-ambulance me-2"></i>Get Started Free
                        </a>
                        <a href="login.php" class="btn-outline-light-custom">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Stats -->
                <div class="hero-stats mt-4">
                    <div class="hero-stat-item">
                        <span class="hero-stat-num" data-target="500" data-suffix="+">0</span>
                        <span class="hero-stat-label">Ambulances</span>
                    </div>
                    <div class="hero-stat-item">
                        <span class="hero-stat-num" data-target="10000" data-suffix="+">0</span>
                        <span class="hero-stat-label">Lives Saved</span>
                    </div>
                    <div class="hero-stat-item">
                        <span class="hero-stat-num" data-target="50" data-suffix="+">0</span>
                        <span class="hero-stat-label">Cities</span>
                    </div>
                </div>
            </div>

            <!-- Right: Illustration -->
            <div class="col-lg-6 text-center">
                <div class="hero-illustration position-relative d-inline-block">
                    <div class="hero-ambulance-icon">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <!-- Ping dots -->
                    <div class="hero-ping" style="top:10%;left:10%;"></div>
                    <div class="hero-ping" style="top:60%;right:8%;animation-delay:.5s;"></div>
                    <div class="hero-ping" style="bottom:10%;left:30%;animation-delay:1s;"></div>

                    <!-- Info chips -->
                    <div class="position-absolute top-0 end-0 translate-middle-y me-n3"
                         style="background:#fff;border-radius:10px;padding:.5rem .9rem;box-shadow:0 4px 16px rgba(0,0,0,.15);font-size:.8rem;font-weight:700;color:#0f172a;">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>Live Tracking
                    </div>
                    <div class="position-absolute bottom-0 start-0 translate-middle-y ms-n3"
                         style="background:#fff;border-radius:10px;padding:.5rem .9rem;box-shadow:0 4px 16px rgba(0,0,0,.15);font-size:.8rem;font-weight:700;color:#0f172a;">
                        <i class="fas fa-clock text-danger me-1"></i>~4 min ETA
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURES SECTION
     ============================================================ -->
<section class="py-5 bg-white" id="features">
    <div class="container py-4">
        <div class="text-center mb-5 fade-up">
            <span class="section-badge">Why Choose Us</span>
            <h2 class="section-title">Everything You Need in an Emergency</h2>
            <p class="section-subtitle">
                Our platform connects you with the nearest available ambulance instantly,
                with real-time tracking and 24/7 support.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3 fade-up">
                <div class="feature-card">
                    <div class="feature-icon red"><i class="fas fa-bolt"></i></div>
                    <h5 class="fw-700 mb-2">Instant Response</h5>
                    <p class="text-muted small mb-0">
                        Connect with the nearest ambulance in under 60 seconds.
                        No delays, no waiting.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-up">
                <div class="feature-card">
                    <div class="feature-icon blue"><i class="fas fa-map-marked-alt"></i></div>
                    <h5 class="fw-700 mb-2">Live Location</h5>
                    <p class="text-muted small mb-0">
                        Track your ambulance in real-time on the map.
                        Know exactly when help arrives.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-up">
                <div class="feature-card">
                    <div class="feature-icon green"><i class="fas fa-user-md"></i></div>
                    <h5 class="fw-700 mb-2">Trained Drivers</h5>
                    <p class="text-muted small mb-0">
                        All our drivers are certified EMTs with first-aid
                        training and emergency protocols.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-up">
                <div class="feature-card">
                    <div class="feature-icon orange"><i class="fas fa-shield-alt"></i></div>
                    <h5 class="fw-700 mb-2">Fully Equipped</h5>
                    <p class="text-muted small mb-0">
                        Every ambulance carries advanced life-support
                        equipment and medications.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SERVICES SECTION
     ============================================================ -->
<section class="py-5" id="services" style="background:var(--light);">
    <div class="container py-4">
        <div class="text-center mb-5 fade-up">
            <span class="section-badge">Our Services</span>
            <h2 class="section-title">Comprehensive Emergency Care</h2>
            <p class="section-subtitle">
                From basic life support to advanced ICU ambulances,
                we cover every medical emergency scenario.
            </p>
        </div>

        <div class="row g-4 align-items-center">
            <div class="col-lg-6 fade-up">
                <div class="row g-3">
                    <?php
                    $services = [
                        ['icon'=>'fa-heartbeat',    'color'=>'red',    'title'=>'Basic Life Support',   'desc'=>'Oxygen, CPR, first aid, and patient stabilization.'],
                        ['icon'=>'fa-procedures',   'color'=>'blue',   'title'=>'Advanced Life Support', 'desc'=>'ICU-equipped ambulances with cardiac monitors.'],
                        ['icon'=>'fa-baby',          'color'=>'green',  'title'=>'Neonatal Transport',   'desc'=>'Specialized care for newborns and premature infants.'],
                        ['icon'=>'fa-helicopter',   'color'=>'orange', 'title'=>'Air Ambulance',         'desc'=>'Helicopter services for critical long-distance cases.'],
                    ];
                    foreach ($services as $s): ?>
                    <div class="col-6">
                        <div class="feature-card text-center p-3">
                            <div class="feature-icon <?= $s['color'] ?> mx-auto mb-2">
                                <i class="fas <?= $s['icon'] ?>"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size:.9rem;"><?= $s['title'] ?></h6>
                            <p class="text-muted mb-0" style="font-size:.78rem;"><?= $s['desc'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-6 fade-up">
                <div style="background:linear-gradient(135deg,var(--navy),#1a0505);border-radius:20px;padding:2.5rem;color:#fff;">
                    <h3 class="fw-800 mb-3">How It Works</h3>
                    <?php
                    $steps = [
                        ['num'=>'01','title'=>'Register / Login','desc'=>'Create your free account in 30 seconds.'],
                        ['num'=>'02','title'=>'Find Ambulance',  'desc'=>'Browse nearby available ambulances on the map.'],
                        ['num'=>'03','title'=>'Send Request',    'desc'=>'Tap "Request" and share your location.'],
                        ['num'=>'04','title'=>'Help Arrives',    'desc'=>'Ambulance dispatched and on its way to you.'],
                    ];
                    foreach ($steps as $step): ?>
                    <div class="d-flex gap-3 mb-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,59,59,.2);
                                    display:flex;align-items:center;justify-content:center;
                                    color:var(--red);font-weight:800;font-size:.85rem;flex-shrink:0;">
                            <?= $step['num'] ?>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:.95rem;"><?= $step['title'] ?></div>
                            <div style="font-size:.83rem;color:rgba(255,255,255,.55);"><?= $step['desc'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="register.php" class="btn-emergency d-inline-block mt-2">
                        <i class="fas fa-arrow-right me-2"></i>Get Started Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     STATS SECTION
     ============================================================ -->
<section class="stats-section" id="stats">
    <div class="container">
        <div class="row g-4 text-center">
            <?php
            $stats = [
                ['num'=>500,   'suffix'=>'+', 'label'=>'Ambulances Fleet',    'icon'=>'fa-ambulance'],
                ['num'=>10000, 'suffix'=>'+', 'label'=>'Lives Saved',          'icon'=>'fa-heart'],
                ['num'=>50,    'suffix'=>'+', 'label'=>'Cities Covered',       'icon'=>'fa-city'],
                ['num'=>4,     'suffix'=>' min','label'=>'Avg Response Time',  'icon'=>'fa-clock'],
            ];
            foreach ($stats as $s): ?>
            <div class="col-6 col-md-3 fade-up">
                <div class="stat-card">
                    <i class="fas <?= $s['icon'] ?> text-danger mb-3" style="font-size:2rem;"></i>
                    <span class="stat-number" data-target="<?= $s['num'] ?>" data-suffix="<?= $s['suffix'] ?>">0</span>
                    <div class="stat-label"><?= $s['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     TESTIMONIALS
     ============================================================ -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5 fade-up">
            <span class="section-badge">Testimonials</span>
            <h2 class="section-title">What People Say</h2>
        </div>

        <div class="row g-4">
            <?php
            $testimonials = [
                ['name'=>'Rahul Sharma',  'city'=>'Mumbai',    'initial'=>'R',
                 'text'=>'The ambulance arrived in just 6 minutes. The app was so easy to use during a stressful situation. Truly a lifesaver!'],
                ['name'=>'Priya Singh',   'city'=>'Delhi',     'initial'=>'P',
                 'text'=>'My father had a cardiac episode and within minutes we had an ambulance at our door. The driver was professional and calm.'],
                ['name'=>'Amit Kumar',    'city'=>'Bangalore', 'initial'=>'A',
                 'text'=>'Excellent service. The real-time tracking feature gave us peace of mind while waiting. Highly recommended to everyone.'],
            ];
            foreach ($testimonials as $t): ?>
            <div class="col-md-4 fade-up">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="testimonial-avatar"><?= $t['initial'] ?></div>
                        <div>
                            <div class="fw-700"><?= $t['name'] ?></div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= $t['city'] ?></small>
                        </div>
                    </div>
                    <div class="text-warning mb-2">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-muted small mb-0">"<?= $t['text'] ?>"</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     GOOGLE MAP SECTION
     ============================================================ -->
<section class="py-5" style="background:var(--light);">
    <div class="container py-4">
        <div class="text-center mb-4 fade-up">
            <span class="section-badge">Coverage Area</span>
            <h2 class="section-title">Find Ambulances Near You</h2>
            <p class="section-subtitle">We operate across major cities in India with growing coverage.</p>
        </div>
        <div class="fade-up" style="border-radius:16px;overflow:hidden;box-shadow:var(--shadow-lg);">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d3769.0!2d72.8777!3d19.0760!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin"
                width="100%" height="420" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<!-- ============================================================
     CONTACT SECTION
     ============================================================ -->
<section class="contact-section" id="contact">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center mb-5 fade-up">
                <span class="section-badge">Contact Us</span>
                <h2 class="section-title text-white">Get In Touch</h2>
                <p class="text-white-50">Have questions? We're here to help 24/7.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7 fade-up">
                <div class="contact-form-card">
                    <form id="contactForm" data-validate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Your Name</label>
                                <input type="text" class="form-control" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Email Address</label>
                                <input type="email" class="form-control" placeholder="john@example.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50 small">Subject</label>
                                <input type="text" class="form-control" placeholder="How can we help?" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50 small">Message</label>
                                <textarea class="form-control" rows="4" placeholder="Your message..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-emergency w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/script.js"></script>

<script>
// Contact form demo submit
document.getElementById('contactForm')?.addEventListener('submit', function(e){
    e.preventDefault();
    showToast('Message sent! We will get back to you soon.', 'success');
    this.reset();
    this.classList.remove('was-validated');
});
</script>
</body>
</html>
