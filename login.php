<?php
// login.php – User login page
session_start();
define('BASE_URL', '');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit;
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($id, $name, $hash);
        $stmt->fetch();
        $stmt->close();
        $db->close();

        if ($id && password_verify($password, $hash)) {
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $name;
            header('Location: user/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-ambulance"></i>
        </div>

        <h4 class="text-center fw-800 mb-1">Welcome Back</h4>
        <p class="text-center text-muted small mb-4">Login to your Ambulance911 account</p>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom alert-auto-dismiss">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Demo credentials hint -->
        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.82rem;border-radius:8px;">
            <strong>Demo:</strong> rahul@example.com / user123
        </div>

        <form method="POST" action="" data-validate>
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

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" id="password"
                           class="form-control border-start-0"
                           placeholder="Your password" required>
                    <button type="button" class="input-group-text bg-light"
                            onclick="togglePwd()">
                        <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-emergency w-100 text-center">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>

        <hr class="my-3">

        <div class="d-flex gap-2">
            <a href="driver/login.php" class="btn btn-outline-primary btn-sm flex-grow-1">
                <i class="fas fa-id-badge me-1"></i>Driver Login
            </a>
            <a href="admin/login.php" class="btn btn-outline-secondary btn-sm flex-grow-1">
                <i class="fas fa-user-shield me-1"></i>Admin Login
            </a>
        </div>

        <p class="text-center text-muted small mt-3 mb-0">
            Don't have an account?
            <a href="register.php" class="text-danger fw-600">Register free</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
function togglePwd() {
    const f = document.getElementById('password');
    const i = document.getElementById('eyeIcon');
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
