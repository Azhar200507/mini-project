<?php
// driver/dashboard.php – Driver home dashboard
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$driverId  = $_SESSION['driver_id'];
$driverName= $_SESSION['driver_name'];
$ambId     = $_SESSION['driver_amb_id'];
$db        = getDB();

// Fetch driver + ambulance details
$stmt = $db->prepare("
    SELECT d.*, a.vehicle_no, a.area, a.status AS amb_status,
           a.latitude, a.longitude
    FROM drivers d
    JOIN ambulances a ON d.ambulance_id = a.id
    WHERE d.id = ?
");
$stmt->bind_param('i', $driverId);
$stmt->execute();
$driver = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Stats for this driver's ambulance
$totalAssigned = $db->query("SELECT COUNT(*) AS c FROM requests WHERE ambulance_id=$ambId")->fetch_assoc()['c'];
$pending       = $db->query("SELECT COUNT(*) AS c FROM requests WHERE ambulance_id=$ambId AND status='pending'")->fetch_assoc()['c'];
$accepted      = $db->query("SELECT COUNT(*) AS c FROM requests WHERE ambulance_id=$ambId AND status='accepted'")->fetch_assoc()['c'];
$completed     = $db->query("SELECT COUNT(*) AS c FROM requests WHERE ambulance_id=$ambId AND status='completed'")->fetch_assoc()['c'];

// Active request (pending or accepted)
$stmt = $db->prepare("
    SELECT r.id, r.location, r.status, r.created_at,
           u.name AS user_name, u.phone AS user_phone
    FROM requests r
    JOIN users u ON r.user_id = u.id
    WHERE r.ambulance_id = ? AND r.status IN ('pending','accepted')
    ORDER BY r.created_at DESC
    LIMIT 1
");
$stmt->bind_param('i', $ambId);
$stmt->execute();
$activeRequest = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Recent completed requests (last 5)
$stmt = $db->prepare("
    SELECT r.id, r.location, r.status, r.created_at,
           u.name AS user_name
    FROM requests r
    JOIN users u ON r.user_id = u.id
    WHERE r.ambulance_id = ? AND r.status IN ('completed','cancelled')
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $ambId);
$stmt->execute();
$recentDone = $stmt->get_result();
$stmt->close();
$db->close();

// Status color helpers
$statusColor = [
    'available' => '#10b981',
    'busy'      => '#f59e0b',
    'offline'   => '#94a3b8',
];
$ambStatusColor = $statusColor[$driver['amb_status']] ?? '#94a3b8';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* ---- Driver-specific overrides ---- */
        .driver-topbar {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,.3);
        }
        .driver-topbar-brand {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
        }
        .driver-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .active-request-card {
            background: linear-gradient(135deg, #0f172a, #1a0505);
            border-radius: 16px;
            padding: 1.75rem;
            color: #fff;
            border: 1px solid rgba(255,59,59,.25);
            position: relative;
            overflow: hidden;
        }
        .active-request-card::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            background: rgba(255,59,59,.08);
            border-radius: 50%;
        }
        .pulse-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #ff3b3b;
            display: inline-block;
            animation: pulse-badge 1.5s infinite;
        }
        .status-toggle-btn {
            border: none;
            padding: .55rem 1.25rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: .88rem;
            cursor: pointer;
            transition: all .3s;
        }
        .no-request-box {
            background: var(--light);
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            border: 2px dashed var(--border);
        }
    </style>
</head>
<body style="background:var(--light);">

<!-- Top Bar -->
<div class="driver-topbar">
    <div class="driver-topbar-brand">
        <i class="fas fa-ambulance text-danger me-2"></i>
        Driver<span style="color:#93c5fd;">Panel</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <!-- Status quick-toggle -->
        <form method="POST" action="update_status.php" class="d-flex align-items-center gap-2">
            <select name="status" class="form-select form-select-sm"
                    style="width:130px;background:rgba(255,255,255,.1);
                           color:#fff;border-color:rgba(255,255,255,.2);"
                    onchange="this.form.submit()">
                <option value="available" <?= $driver['amb_status']==='available'?'selected':'' ?>>🟢 Available</option>
                <option value="busy"      <?= $driver['amb_status']==='busy'     ?'selected':'' ?>>🟡 Busy</option>
                <option value="offline"   <?= $driver['amb_status']==='offline'  ?'selected':'' ?>>⚫ Offline</option>
            </select>
        </form>

        <div class="dropdown">
            <div class="driver-avatar" data-bs-toggle="dropdown">
                <?= strtoupper(substr($driverName, 0, 1)) ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="profile.php">
                    <i class="fas fa-user me-2"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="trips.php">
                    <i class="fas fa-history me-2"></i>Trip History</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="container py-4">

    <!-- Welcome + vehicle info -->
    <div class="row g-3 mb-4 align-items-center">
        <div class="col">
            <h5 class="fw-800 mb-0">
                Welcome, <?= htmlspecialchars($driverName) ?> 👋
            </h5>
            <div class="text-muted small mt-1">
                <i class="fas fa-car me-1 text-danger"></i>
                <?= htmlspecialchars($driver['vehicle_no']) ?>
                &nbsp;·&nbsp;
                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                <?= htmlspecialchars($driver['area']) ?>
                &nbsp;·&nbsp;
                <span style="color:<?= $ambStatusColor ?>;font-weight:600;">
                    ● <?= ucfirst($driver['amb_status']) ?>
                </span>
            </div>
        </div>
        <div class="col-auto">
            <a href="trips.php" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-history me-1"></i>Trip History
            </a>
        </div>
    </div>

    <!-- Stat Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-widget">
                <div class="stat-widget-icon blue"><i class="fas fa-list-alt"></i></div>
                <div>
                    <div class="stat-widget-num"><?= $totalAssigned ?></div>
                    <div class="stat-widget-label">Total Trips</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-widget">
                <div class="stat-widget-icon orange"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-widget-num"><?= $pending ?></div>
                    <div class="stat-widget-label">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-widget">
                <div class="stat-widget-icon red"><i class="fas fa-ambulance"></i></div>
                <div>
                    <div class="stat-widget-num"><?= $accepted ?></div>
                    <div class="stat-widget-label">On Duty</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-widget">
                <div class="stat-widget-icon green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-widget-num"><?= $completed ?></div>
                    <div class="stat-widget-label">Completed</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Active Request -->
        <div class="col-lg-7">
            <h6 class="fw-700 mb-3">
                <span class="pulse-dot me-2"></span>Active Request
            </h6>

            <?php if ($activeRequest): ?>
            <div class="active-request-card mb-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-<?= $activeRequest['status']==='pending'?'warning':'primary' ?> mb-2">
                            <?= ucfirst($activeRequest['status']) ?>
                        </span>
                        <h5 class="fw-800 mb-1">
                            <?= htmlspecialchars($activeRequest['user_name']) ?>
                        </h5>
                        <div style="color:rgba(255,255,255,.6);font-size:.9rem;">
                            <i class="fas fa-phone me-1 text-danger"></i>
                            <a href="tel:<?= $activeRequest['user_phone'] ?>"
                               style="color:rgba(255,255,255,.8);">
                                <?= htmlspecialchars($activeRequest['user_phone']) ?>
                            </a>
                        </div>
                    </div>
                    <div style="font-size:.8rem;color:rgba(255,255,255,.4);">
                        #<?= $activeRequest['id'] ?>
                    </div>
                </div>

                <div class="mb-3 p-3 rounded-3" style="background:rgba(255,255,255,.06);">
                    <div style="font-size:.8rem;color:rgba(255,255,255,.5);margin-bottom:.3rem;">
                        PICKUP LOCATION
                    </div>
                    <div class="fw-600">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <?= htmlspecialchars($activeRequest['location']) ?>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($activeRequest['status'] === 'pending'): ?>
                    <!-- Accept -->
                    <form method="POST" action="update_request.php">
                        <input type="hidden" name="request_id" value="<?= $activeRequest['id'] ?>">
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="status-toggle-btn text-white"
                                style="background:#2563eb;">
                            <i class="fas fa-check me-1"></i>Accept
                        </button>
                    </form>
                    <!-- Decline -->
                    <form method="POST" action="update_request.php">
                        <input type="hidden" name="request_id" value="<?= $activeRequest['id'] ?>">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="status-toggle-btn"
                                style="background:rgba(255,255,255,.1);color:#fff;"
                                onclick="return confirm('Decline this request?')">
                            <i class="fas fa-times me-1"></i>Decline
                        </button>
                    </form>
                    <?php else: ?>
                    <!-- Complete -->
                    <form method="POST" action="update_request.php">
                        <input type="hidden" name="request_id" value="<?= $activeRequest['id'] ?>">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="status-toggle-btn text-white"
                                style="background:#10b981;"
                                onclick="return confirm('Mark this trip as completed?')">
                            <i class="fas fa-flag-checkered me-1"></i>Mark Complete
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Call patient -->
                    <a href="tel:<?= $activeRequest['user_phone'] ?>"
                       class="status-toggle-btn text-white"
                       style="background:#ff3b3b;display:inline-flex;align-items:center;gap:.4rem;">
                        <i class="fas fa-phone"></i> Call Patient
                    </a>
                </div>

                <div style="font-size:.78rem;color:rgba(255,255,255,.35);margin-top:1rem;">
                    <i class="fas fa-clock me-1"></i>
                    Received: <?= date('d M Y, h:i A', strtotime($activeRequest['created_at'])) ?>
                </div>
            </div>

            <?php else: ?>
            <div class="no-request-box">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-25"></i>
                <h6 class="text-muted fw-600">No active requests</h6>
                <p class="text-muted small mb-0">
                    You'll see incoming emergency requests here.<br>
                    Make sure your status is set to
                    <strong class="text-success">Available</strong>.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Quick Info + Recent -->
        <div class="col-lg-5">

            <!-- Ambulance Info Card -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-ambulance text-danger me-2"></i>My Ambulance
                    </h6>
                    <a href="profile.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
                <div class="card-custom-body">
                    <div class="d-flex flex-column gap-2">
                        <?php
                        $info = [
                            ['fa-id-card',    'Vehicle No.',   $driver['vehicle_no']],
                            ['fa-map-marker-alt','Area',       $driver['area']],
                            ['fa-phone',      'Phone',         $driver['phone']],
                            ['fa-id-badge',   'License',       $driver['license_no']],
                            ['fa-star',       'Experience',    $driver['experience'] . ' years'],
                        ];
                        foreach ($info as [$icon, $label, $value]): ?>
                        <div class="d-flex justify-content-between align-items-center py-1"
                             style="border-bottom:1px solid var(--border);">
                            <span class="text-muted small">
                                <i class="fas <?= $icon ?> me-2 text-danger"></i><?= $label ?>
                            </span>
                            <span class="fw-600 small"><?= htmlspecialchars($value) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Completed -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-history text-danger me-2"></i>Recent Trips
                    </h6>
                    <a href="trips.php" class="btn btn-sm btn-outline-danger">All</a>
                </div>
                <div class="card-custom-body p-0">
                    <?php if ($recentDone->num_rows > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php while ($r = $recentDone->fetch_assoc()): ?>
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-600 small"><?= htmlspecialchars($r['user_name']) ?></div>
                                    <div class="text-muted" style="font-size:.78rem;">
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                        <?= htmlspecialchars($r['location']) ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php $b = ['completed'=>'success','cancelled'=>'secondary']; ?>
                                    <span class="badge bg-<?= $b[$r['status']] ?? 'secondary' ?> mb-1 d-block">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                    <span class="text-muted" style="font-size:.72rem;">
                                        <?= date('d M', strtotime($r['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted small">No completed trips yet.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
// Auto-refresh page every 30 seconds to catch new requests
setTimeout(() => location.reload(), 30000);

// Countdown timer
let countdown = 30;
const timerEl = document.createElement('div');
timerEl.style.cssText = 'position:fixed;bottom:16px;left:16px;background:rgba(15,23,42,.8);color:rgba(255,255,255,.5);padding:.4rem .8rem;border-radius:8px;font-size:.75rem;z-index:999;';
document.body.appendChild(timerEl);
setInterval(() => {
    timerEl.textContent = `Auto-refresh in ${countdown--}s`;
    if (countdown < 0) countdown = 30;
}, 1000);
</script>
</body>
</html>
