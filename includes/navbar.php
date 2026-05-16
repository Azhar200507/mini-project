<?php
// includes/navbar.php  – Public-facing sticky navbar
// Session must be started before including this file.
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNavbar">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>index.php">
            <span class="brand-icon"><i class="fas fa-ambulance"></i></span>
            <span class="brand-text">Ambulance<span class="text-danger">911</span></span>
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php#features">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php#services">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php#contact">Contact</a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>user/dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm px-3 ms-2"
                           href="<?= BASE_URL ?>logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>driver/login.php">Driver</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger btn-sm px-3 ms-2"
                           href="<?= BASE_URL ?>register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
