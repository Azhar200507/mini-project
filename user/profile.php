<?php
// user/profile.php – View and update user profile
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

$userId = $_SESSION['user_id'];
$db     = getDB();

$msg     = '';
$msgType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name  = trim($_POST['name']  ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($phone)) {
        $msg     = 'Name and phone are required.';
        $msgType = 'danger';
    } else {
        $stmt = $db->prepare("UPDATE users SET name=?, phone=? WHERE id=?");
        $stmt->bind_param('ssi', $name, $phone, $userId);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $msg     = 'Profile updated successfully!';
            $msgType = 'success';
        } else {
            $msg     = 'Update failed. Please try again.';
            $msgType = 'danger';
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = trim($_POST['current_password'] ?? '');
    $new     = trim($_POST['new_password']     ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    $stmt = $db->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hash)) {
        $msg     = 'Current password is incorrect.';
        $msgType = 'danger';
    } elseif (strlen($new) < 6) {
        $msg     = 'New password must be at least 6 characters.';
        $msgType = 'danger';
    } elseif ($new !== $confirm) {
        $msg     = 'New passwords do not match.';
        $msgType = 'danger';
    } else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $upd = $db->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param('si', $newHash, $userId);
        if ($upd->execute()) {
            $msg     = 'Password changed successfully!';
            $msgType = 'success';
        } else {
            $msg     = 'Password change failed.';
            $msgType = 'danger';
        }
        $upd->close();
    }
}

// Fetch user data
$stmt = $db->prepare("SELECT name, phone, email, created_at FROM users WHERE id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="user-layout">

<div class="user-topbar">
    <div class="user-topbar-brand">
        <a href="dashboard.php" class="text-decoration-none text-dark me-2">
            <i class="fas fa-arrow-left text-muted"></i>
        </a>
        <i class="fas fa-user text-danger me-2"></i>My Profile
    </div>
    <a href="../logout.php" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
    </a>
</div>

<div class="container py-4">
    <div class="row g-4 justify-content-center">

        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="card-custom text-center p-4">
                <div class="mx-auto mb-3"
                     style="width:80px;height:80px;border-radius:50%;
                            background:linear-gradient(135deg,var(--red),#ff8080);
                            display:flex;align-items:center;justify-content:center;
                            font-size:2rem;color:#fff;font-weight:800;">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h5 class="fw-800"><?= htmlspecialchars($user['name']) ?></h5>
                <p class="text-muted small mb-1">
                    <i class="fas fa-envelope me-1 text-danger"></i><?= htmlspecialchars($user['email']) ?>
                </p>
                <p class="text-muted small mb-1">
                    <i class="fas fa-phone me-1 text-danger"></i><?= htmlspecialchars($user['phone']) ?>
                </p>
                <p class="text-muted small">
                    <i class="fas fa-calendar me-1 text-danger"></i>
                    Joined <?= date('d M Y', strtotime($user['created_at'])) ?>
                </p>
                <span class="badge bg-success">Verified User</span>
            </div>
        </div>

        <!-- Edit Forms -->
        <div class="col-lg-8">

            <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-custom alert-auto-dismiss mb-3">
                <i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Update Profile -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0"><i class="fas fa-edit text-danger me-2"></i>Update Profile</h6>
                </div>
                <div class="card-custom-body">
                    <form method="POST" data-validate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Full Name</label>
                                <input type="text" name="name" class="form-control"
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Phone Number</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Email Address</label>
                                <input type="email" class="form-control"
                                       value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_profile" class="btn btn-danger">
                                    <i class="fas fa-save me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0"><i class="fas fa-lock text-danger me-2"></i>Change Password</h6>
                </div>
                <div class="card-custom-body">
                    <form method="POST" data-validate>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-600">Current Password</label>
                                <input type="password" name="current_password" class="form-control"
                                       placeholder="Enter current password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">New Password</label>
                                <input type="password" name="new_password" class="form-control"
                                       placeholder="Min. 6 characters" required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control"
                                       placeholder="Repeat new password" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="change_password" class="btn btn-outline-danger">
                                    <i class="fas fa-key me-1"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
