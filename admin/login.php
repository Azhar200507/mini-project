<?php
// admin/login.php – Admin login page
session_start();
define('BASE_URL', '../');

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($id, $uname, $hash);
        $stmt->fetch();
        $stmt->close();
        $db->close();

        if ($id && password_verify($password, $hash)) {
            $_SESSION['admin_id']       = $id;
            $_SESSION['admin_username'] = $uname;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-page" style="background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);">
    <div class="auth-card">
        <div class="auth-logo" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
            <i class="fas fa-user-shield"></i>
        </div>

        <h4 class="text-center fw-800 mb-1">Admin Panel</h4>
        <p class="text-center text-muted small mb-4">Ambulance911 Administration</p>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-custom alert-auto-dismiss">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Demo hint -->
        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.82rem;border-radius:8px;">
            <strong>Demo:</strong> admin / admin123
        </div>

        <form method="POST" data-validate>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" name="username" class="form-control border-start-0"
                           placeholder="admin"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" id="adminPwd"
                           class="form-control border-start-0"
                           placeholder="Password" required>
                    <button type="button" class="input-group-text bg-light"
                            onclick="togglePwd()">
                        <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-700">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Admin Panel
            </button>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            <a href="../login.php" class="text-primary">← Back to User Login</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
function togglePwd() {
    const f = document.getElementById('adminPwd');
    const i = document.getElementById('eyeIcon');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye');
    i.classList.toggle('fa-eye-slash');
}
</script>
</body>
</html>
