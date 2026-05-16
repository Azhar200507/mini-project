<?php
// driver/profile.php – View and update driver profile & password
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$driverId   = $_SESSION['driver_id'];
$driverName = $_SESSION['driver_name'];
$ambId      = $_SESSION['driver_amb_id'];
$db         = getDB();

$msg     = '';
$msgType = '';

// ---- Update profile ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name  = trim($_POST['name']       ?? '');
    $phone = trim($_POST['phone']      ?? '');
    $lic   = trim($_POST['license_no'] ?? '');
    $exp   = (int)($_POST['experience'] ?? 0);

    if (empty($name) || empty($phone)) {
        $msg = 'Name and phone are required.'; $msgType = 'danger';
    } else {
        $stmt = $db->prepare("UPDATE drivers SET name=?,phone=?,license_no=?,experience=? WHERE id=?");
        $stmt->bind_param('sssii', $name, $phone, $lic, $exp, $driverId);
        if ($stmt->execute()) {
            $_SESSION['driver_name'] = $name;
            $msg = 'Profile updated successfully!'; $msgType = 'success';
        } else {
            $msg = 'Update failed.'; $msgType = 'danger';
        }
        $stmt->close();
    }
}

// ---- Change password ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = trim($_POST['current_password'] ?? '');
    $new     = trim($_POST['new_password']     ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    $stmt = $db->prepare("SELECT password FROM drivers WHERE id=?");
    $stmt->bind_param('i', $driverId);
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hash)) {
        $msg = 'Current password is incorrect.'; $msgType = 'danger';
    } elseif (strlen($new) < 6) {
        $msg = 'New password must be at least 6 characters.'; $msgType = 'danger';
    } elseif ($new !== $confirm) {
        $msg = 'New passwords do not match.'; $msgType = 'danger';
    } else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $upd = $db->prepare("UPDATE drivers SET password=? WHERE id=?");
        $upd->bind_param('si', $newHash, $driverId);
        $msg     = $upd->execute() ? 'Password changed successfully!' : 'Password change failed.';
        $msgType = $upd->execute() ? 'success' : 'danger';
        $upd->close();
    }
}

// Fetch fresh driver data
$stmt = $db->prepare("
    SELECT d.*, a.vehicle_no, a.area, a.status AS amb_status
    FROM drivers d JOIN ambulances a ON d.ambulance_id=a.id
    WHERE d.id=?
");
$stmt->bind_param('i', $driverId);
$stmt->execute();
$driver = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – Driver Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .driver-topbar {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            padding: 1rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,.3);
        }
        .driver-topbar-brand { font-size:1.15rem; font-weight:700; color:#fff; }
    </style>
</head>
<body style="background:var(--light);">

<div class="driver-topbar">
    <div class="driver-topbar-brand">
        <a href="dashboard.php" class="text-white text-decoration-none me-2">
            <i class="fas fa-arrow-left opacity-75"></i>
        </a>
        <i class="fas fa-user text-danger me-2"></i>My Profile
    </div>
    <a href="logout.php" class="btn btn-sm btn-outline-light opacity-75">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
    </a>
</div>

<div class="container py-4">
    <div class="row g-4 justify-content-center">

        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="card-custom text-center p-4">
                <!-- Avatar -->
                <div class="mx-auto mb-3"
                     style="width:80px;height:80px;border-radius:50%;
                            background:linear-gradient(135deg,#2563eb,#1d4ed8);
                            display:flex;align-items:center;justify-content:center;
                            font-size:2rem;color:#fff;font-weight:800;">
                    <?= strtoupper(substr($driver['name'], 0, 1)) ?>
                </div>
                <h5 class="fw-800"><?= htmlspecialchars($driver['name']) ?></h5>
                <p class="text-muted small mb-1">
                    <i class="fas fa-envelope me-1 text-danger"></i>
                    <?= htmlspecialchars($driver['email']) ?>
                </p>
                <p class="text-muted small mb-1">
                    <i class="fas fa-phone me-1 text-danger"></i>
                    <?= htmlspecialchars($driver['phone']) ?>
                </p>
                <p class="text-muted small mb-2">
                    <i class="fas fa-id-badge me-1 text-danger"></i>
                    <?= htmlspecialchars($driver['license_no']) ?>
                </p>
                <span class="badge bg-<?= $driver['is_active'] ? 'success' : 'danger' ?> mb-2">
                    <?= $driver['is_active'] ? 'Active Driver' : 'Inactive' ?>
                </span>

                <hr>

                <!-- Ambulance summary -->
                <div class="text-start">
                    <div class="fw-700 small mb-2 text-muted text-uppercase" style="letter-spacing:.5px;">
                        Assigned Ambulance
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Vehicle</span>
                        <code><?= htmlspecialchars($driver['vehicle_no']) ?></code>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Area</span>
                        <span class="fw-600"><?= htmlspecialchars($driver['area']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Status</span>
                        <span class="status-badge <?= $driver['amb_status'] ?>">
                            <span class="status-dot <?= $driver['amb_status'] ?>"></span>
                            <?= ucfirst($driver['amb_status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Forms -->
        <div class="col-lg-8">

            <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-custom alert-auto-dismiss mb-3">
                <i class="fas fa-<?= $msgType==='success'?'check-circle':'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Update Profile -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-edit text-danger me-2"></i>Update Profile
                    </h6>
                </div>
                <div class="card-custom-body">
                    <form method="POST" data-validate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Full Name</label>
                                <input type="text" name="name" class="form-control"
                                       value="<?= htmlspecialchars($driver['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Phone Number</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($driver['phone']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">License Number</label>
                                <input type="text" name="license_no" class="form-control"
                                       value="<?= htmlspecialchars($driver['license_no']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Experience (years)</label>
                                <input type="number" name="experience" class="form-control"
                                       min="0" max="50"
                                       value="<?= (int)$driver['experience'] ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Email Address</label>
                                <input type="email" class="form-control"
                                       value="<?= htmlspecialchars($driver['email']) ?>" disabled>
                                <small class="text-muted">Email cannot be changed. Contact admin.</small>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_profile" class="btn btn-primary">
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
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-lock text-danger me-2"></i>Change Password
                    </h6>
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
