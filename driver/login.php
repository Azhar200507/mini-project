<?php
// driver/login.php – Driver login page
session_start();
define('BASE_URL', '../');

// Already logged in
if (isset($_SESSION['driver_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT d.id, d.name, d.email, d.password, d.ambulance_id,
                   d.is_active, a.vehicle_no, a.status AS amb_status
            FROM drivers d
            JOIN ambulances a ON d.ambulance_id = a.id
            WHERE d.email = ?
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $db->close();

        if ($row && password_verify($password, $row['password'])) {
            if (!$row['is_active']) {
                $error = 'Your account has been deactivated. Contact admin.';
            } else {
                $_SESSION['driver_id']       = $row['id'];
                $_SESSION['driver_name']     = $row['name'];
                $_SESSION['driver_email']    = $row['email'];
                $_SESSION['driver_amb_id']   = $row['ambulance_id'];
                $_SESSION['driver_vehicle']  = $row['vehicle_no'];
                header('Location: dashboard.php');
                exit;
            }
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
    <title>Driver Login – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .driver-auth-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .driver-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(37,99,235,.15);
            border: 1px solid rgba(37,99,235,.3);
            color: #93c5fd;
            padding: .35rem .9rem;
            border-radius: 50px;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
        }
    </style>
</head>
<body>
<div class="driver-auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo" style="background: linear-gradient(135deg,#2563eb,#1d4ed8);">
            <i class="fas fa-id-badge"></i>
        </div>

        <div class="text-center mb-1">
            <span class="driver-badge">
                <i class="fas fa-steering-wheel"></i> Driver Portal
            </span>
        </div>

        <h4 class="text-center fw-800 mb-1">Driver Login</h4>
        <p class="text-center text-muted small mb-4">Sign in to manage your assignments</p>

        <!-- Error -->
        <?php if ($error): ?>
        <div class="alert alert-danger alert-custom alert-auto-dismiss">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Demo hint -->
        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.82rem;border-radius:8px;">
            <strong>Demo:</strong> rajan@driver.com / driver123
        </div>

        <form method="POST" data-validate>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0"
                           placeholder="driver@example.com"
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
                    <input type="password" name="password" id="driverPwd"
                           class="form-control border-start-0"
                           placeholder="Your password" required>
                    <button type="button" class="input-group-text bg-light"
                            onclick="togglePwd()">
                        <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-100 py-2 fw-700 border-0 rounded-3 text-white"
                    style="background:linear-gradient(135deg,#2563eb,#1d4ed8);
                           font-size:1rem;cursor:pointer;transition:all .3s;"
                    onmouseover="this.style.opacity='.9'"
                    onmouseout="this.style.opacity='1'">
                <i class="fas fa-sign-in-alt me-2"></i>Login as Driver
            </button>
        </form>

        <hr class="my-3">
        <div class="text-center small text-muted">
            <a href="../login.php" class="text-muted me-3">
                <i class="fas fa-user me-1"></i>User Login
            </a>
            <a href="../admin/login.php" class="text-muted">
                <i class="fas fa-user-shield me-1"></i>Admin Login
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
function togglePwd() {
    const f = document.getElementById('driverPwd');
    const i = document.getElementById('eyeIcon');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye');
    i.classList.toggle('fa-eye-slash');
}
</script>
</body>
</html>
