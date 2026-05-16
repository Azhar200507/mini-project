<?php
// register.php – User registration page
session_start();
define('BASE_URL', '');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit;
}

require_once 'config/database.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    // Basic validation
    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();

        // Check duplicate email
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $ins = $db->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
            $ins->bind_param('ssss', $name, $phone, $email, $hashed);

            if ($ins->execute()) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $ins->close();
        }
        $stmt->close();
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <i class="fas fa-ambulance"></i>
        </div>

        <h4 class="text-center fw-800 mb-1">Create Account</h4>
        <p class="text-center text-muted small mb-4">Join Ambulance911 – It's free</p>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom alert-auto-dismiss">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-custom">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <a href="login.php" class="fw-bold ms-1">Login now</a>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="" data-validate id="registerForm">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" name="name" class="form-control border-start-0"
                           placeholder="Rahul Sharma"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-phone text-muted"></i>
                    </span>
                    <input type="tel" name="phone" class="form-control border-start-0"
                           placeholder="9876543210"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0"
                           placeholder="you@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" id="password"
                           class="form-control border-start-0"
                           placeholder="Min. 6 characters" required minlength="6">
                    <button type="button" class="input-group-text bg-light"
                            onclick="togglePwd('password','eyeIcon1')">
                        <i class="fas fa-eye text-muted" id="eyeIcon1"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="confirm" id="confirm"
                           class="form-control border-start-0"
                           placeholder="Repeat password" required>
                    <button type="button" class="input-group-text bg-light"
                            onclick="togglePwd('confirm','eyeIcon2')">
                        <i class="fas fa-eye text-muted" id="eyeIcon2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-emergency w-100 text-center">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            Already have an account?
            <a href="login.php" class="text-danger fw-600">Login here</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
function togglePwd(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    if (f.type === 'password') {
        f.type = 'text';
        i.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        f.type = 'password';
        i.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>
