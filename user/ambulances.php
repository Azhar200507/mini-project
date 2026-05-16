<?php
// user/ambulances.php – Browse and request ambulances
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$db       = getDB();

// Handle ambulance request via POST
$requestMsg  = '';
$requestType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ambulance_id'])) {
    $ambId    = (int)$_POST['ambulance_id'];
    $location = trim($_POST['location'] ?? '');

    if (empty($location)) {
        $requestMsg  = 'Please enter your location.';
        $requestType = 'danger';
    } else {
        // Check ambulance is still available
        $chk = $db->prepare("SELECT status FROM ambulances WHERE id = ?");
        $chk->bind_param('i', $ambId);
        $chk->execute();
        $chk->bind_result($ambStatus);
        $chk->fetch();
        $chk->close();

        if ($ambStatus !== 'available') {
            $requestMsg  = 'Sorry, this ambulance is no longer available.';
            $requestType = 'warning';
        } else {
            $ins = $db->prepare("INSERT INTO requests (user_id, ambulance_id, location) VALUES (?, ?, ?)");
            $ins->bind_param('iis', $userId, $ambId, $location);
            if ($ins->execute()) {
                // Mark ambulance as busy
                $upd = $db->prepare("UPDATE ambulances SET status='busy' WHERE id=?");
                $upd->bind_param('i', $ambId);
                $upd->execute();
                $upd->close();

                $requestMsg  = 'Ambulance requested successfully! Help is on the way.';
                $requestType = 'success';
            } else {
                $requestMsg  = 'Request failed. Please try again.';
                $requestType = 'danger';
            }
            $ins->close();
        }
    }
}

// Fetch ambulances
$statusFilter = $_GET['status'] ?? '';
$areaFilter   = trim($_GET['area'] ?? '');

$sql = "SELECT * FROM ambulances WHERE 1=1";
$params = [];
$types  = '';

if ($statusFilter && in_array($statusFilter, ['available','busy','offline'])) {
    $sql    .= " AND status = ?";
    $params[] = $statusFilter;
    $types   .= 's';
}
if ($areaFilter) {
    $sql    .= " AND area LIKE ?";
    $like    = "%$areaFilter%";
    $params[] = $like;
    $types   .= 's';
}
$sql .= " ORDER BY status='available' DESC, id ASC";

$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$ambulances = $stmt->get_result();
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Ambulance – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="user-layout">

<!-- Top Bar -->
<div class="user-topbar">
    <div class="user-topbar-brand">
        <a href="dashboard.php" class="text-decoration-none text-dark">
            <i class="fas fa-arrow-left me-2 text-muted"></i>
        </a>
        <i class="fas fa-ambulance text-danger me-2"></i>Find Ambulance
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small d-none d-md-inline">
            <i class="fas fa-user me-1"></i><?= htmlspecialchars($userName) ?>
        </span>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>

<div class="container py-4">

    <!-- Alert -->
    <?php if ($requestMsg): ?>
    <div class="alert alert-<?= $requestType ?> alert-custom alert-auto-dismiss mb-4">
        <i class="fas fa-<?= $requestType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <?= htmlspecialchars($requestMsg) ?>
    </div>
    <?php endif; ?>

    <!-- Location Input -->
    <div class="card-custom mb-4">
        <div class="card-custom-body">
            <h6 class="fw-700 mb-3"><i class="fas fa-map-marker-alt text-danger me-2"></i>Your Location</h6>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-location-arrow text-danger"></i></span>
                <input type="text" id="userLocation" class="form-control"
                       placeholder="Enter your current address or landmark..."
                       value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                <button class="btn btn-danger" onclick="detectLocation()">
                    <i class="fas fa-crosshairs me-1"></i>Detect
                </button>
            </div>
            <small class="text-muted mt-1 d-block">
                <i class="fas fa-info-circle me-1"></i>
                Enter your location before requesting an ambulance.
            </small>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-custom mb-4">
        <div class="card-custom-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-600">Search by Area</label>
                    <input type="text" name="area" class="form-control"
                           placeholder="e.g. Mumbai, Delhi..."
                           value="<?= htmlspecialchars($areaFilter) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-600">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="available" <?= $statusFilter==='available'?'selected':'' ?>>Available</option>
                        <option value="busy"      <?= $statusFilter==='busy'?'selected':'' ?>>Busy</option>
                        <option value="offline"   <?= $statusFilter==='offline'?'selected':'' ?>>Offline</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ambulance Cards -->
    <div class="row g-3" id="ambulanceGrid">
        <?php if ($ambulances->num_rows > 0): ?>
            <?php while ($amb = $ambulances->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4 ambulance-card-wrap">
                <div class="ambulance-card">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="driver-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-700 mb-1"><?= htmlspecialchars($amb['driver_name']) ?></h6>
                            <div class="text-muted small mb-1">
                                <i class="fas fa-phone me-1 text-danger"></i>
                                <a href="tel:<?= $amb['phone'] ?>" class="text-muted">
                                    <?= htmlspecialchars($amb['phone']) ?>
                                </a>
                            </div>
                            <code class="small"><?= htmlspecialchars($amb['vehicle_no']) ?></code>
                        </div>
                        <span class="status-badge <?= $amb['status'] ?>">
                            <span class="status-dot <?= $amb['status'] ?>"></span>
                            <?= ucfirst($amb['status']) ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">
                            <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                            <?= htmlspecialchars($amb['area']) ?>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-route me-1 text-primary"></i>
                            <?= number_format(rand(1,15) + rand(0,9)/10, 1) ?> km away (estimated)
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if ($amb['status'] === 'available'): ?>
                        <!-- Request form -->
                        <form method="POST" class="flex-grow-1">
                            <input type="hidden" name="ambulance_id" value="<?= $amb['id'] ?>">
                            <input type="hidden" name="location" id="loc_<?= $amb['id'] ?>">
                            <button type="submit"
                                    class="btn btn-danger btn-sm w-100"
                                    onclick="syncLocation(<?= $amb['id'] ?>)"
                                    data-amb-id="<?= $amb['id'] ?>">
                                <i class="fas fa-ambulance me-1"></i>Request
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-sm flex-grow-1" disabled>
                            <i class="fas fa-ban me-1"></i>
                            <?= $amb['status'] === 'busy' ? 'On Duty' : 'Offline' ?>
                        </button>
                        <?php endif; ?>

                        <a href="tel:<?= $amb['phone'] ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block opacity-25"></i>
            <h5 class="text-muted">No ambulances found</h5>
            <p class="text-muted small">Try adjusting your search filters.</p>
            <a href="ambulances.php" class="btn btn-outline-danger btn-sm">Clear Filters</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
// Sync the location input into the hidden field before form submit
function syncLocation(ambId) {
    const loc = document.getElementById('userLocation').value.trim();
    document.getElementById('loc_' + ambId).value = loc;
    if (!loc) {
        event.preventDefault();
        showToast('Please enter your location first.', 'warning');
    }
}

// Geolocation detect
function detectLocation() {
    if (!navigator.geolocation) {
        showToast('Geolocation not supported by your browser.', 'warning');
        return;
    }
    const btn = event.target;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude.toFixed(5);
            const lng = pos.coords.longitude.toFixed(5);
            document.getElementById('userLocation').value = `Lat: ${lat}, Lng: ${lng}`;
            btn.innerHTML = '<i class="fas fa-crosshairs me-1"></i>Detect';
            btn.disabled = false;
            showToast('Location detected!', 'success');
        },
        () => {
            btn.innerHTML = '<i class="fas fa-crosshairs me-1"></i>Detect';
            btn.disabled = false;
            showToast('Could not detect location. Please enter manually.', 'warning');
        }
    );
}
</script>
</body>
</html>
