<?php
// user/dashboard.php – User dashboard
session_start();
define('BASE_URL', '../');

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$db       = getDB();

// Fetch user stats
$totalReq = $db->query("SELECT COUNT(*) AS c FROM requests WHERE user_id = $userId")->fetch_assoc()['c'];
$pending  = $db->query("SELECT COUNT(*) AS c FROM requests WHERE user_id = $userId AND status='pending'")->fetch_assoc()['c'];
$completed= $db->query("SELECT COUNT(*) AS c FROM requests WHERE user_id = $userId AND status='completed'")->fetch_assoc()['c'];
$available= $db->query("SELECT COUNT(*) AS c FROM ambulances WHERE status='available'")->fetch_assoc()['c'];

// Recent requests
$stmt = $db->prepare("
    SELECT r.id, r.location, r.status, r.created_at,
           a.driver_name, a.vehicle_no, a.phone AS amb_phone
    FROM requests r
    JOIN ambulances a ON r.ambulance_id = a.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$recentRequests = $stmt->get_result();
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="user-layout">

<!-- Top Bar -->
<div class="user-topbar">
    <div class="d-flex align-items-center gap-3">
        <div class="user-topbar-brand">
            <i class="fas fa-ambulance text-danger me-2"></i>Ambulance<span class="text-danger">911</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="ambulances.php" class="btn btn-danger btn-sm">
            <i class="fas fa-ambulance me-1"></i>Find Ambulance
        </a>
        <div class="dropdown">
            <div class="user-avatar" data-bs-toggle="dropdown">
                <?= strtoupper(substr($userName, 0, 1)) ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="request.php"><i class="fas fa-history me-2"></i>My Requests</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4">

    <!-- Welcome Banner -->
    <div class="mb-4 p-4 rounded-xl"
         style="background:linear-gradient(135deg,var(--navy),#1a0505);color:#fff;">
        <div class="row align-items-center">
            <div class="col">
                <h4 class="fw-800 mb-1">Welcome back, <?= htmlspecialchars($userName) ?>! 👋</h4>
                <p class="mb-0 text-white-50">
                    <?= $available ?> ambulances are available near you right now.
                </p>
            </div>
            <div class="col-auto">
                <a href="ambulances.php" class="btn-emergency">
                    <i class="fas fa-ambulance me-2"></i>Request Now
                </a>
            </div>
        </div>
    </div>

    <!-- Stat Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-widget">
                <div class="stat-widget-icon red"><i class="fas fa-list"></i></div>
                <div>
                    <div class="stat-widget-num"><?= $totalReq ?></div>
                    <div class="stat-widget-label">Total Requests</div>
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
                <div class="stat-widget-icon green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-widget-num"><?= $completed ?></div>
                    <div class="stat-widget-label">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-widget">
                <div class="stat-widget-icon blue"><i class="fas fa-ambulance"></i></div>
                <div>
                    <div class="stat-widget-num"><?= $available ?></div>
                    <div class="stat-widget-label">Available Now</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Requests Table -->
    <div class="card-custom mb-4">
        <div class="card-custom-header">
            <h6 class="fw-700 mb-0"><i class="fas fa-history text-danger me-2"></i>Recent Requests</h6>
            <a href="request.php" class="btn btn-sm btn-outline-danger">View All</a>
        </div>
        <div class="card-custom-body p-0">
            <?php if ($recentRequests->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:var(--light);">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recentRequests->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $row['id'] ?></td>
                            <td class="fw-600"><?= htmlspecialchars($row['driver_name']) ?></td>
                            <td><code><?= htmlspecialchars($row['vehicle_no']) ?></code></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td>
                                <?php
                                $sc = ['pending'=>'warning','accepted'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                                $sc2 = $sc[$row['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $sc2 ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                No requests yet. <a href="ambulances.php" class="text-danger">Find an ambulance</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="ambulances.php" class="card-custom d-block text-center p-4 text-decoration-none"
               style="transition:var(--transition);" onmouseover="this.style.transform='translateY(-4px)'"
               onmouseout="this.style.transform=''">
                <i class="fas fa-ambulance fa-2x text-danger mb-2 d-block"></i>
                <div class="fw-700">Find Ambulance</div>
                <small class="text-muted">Browse nearby ambulances</small>
            </a>
        </div>
        <div class="col-md-4">
            <a href="request.php" class="card-custom d-block text-center p-4 text-decoration-none"
               style="transition:var(--transition);" onmouseover="this.style.transform='translateY(-4px)'"
               onmouseout="this.style.transform=''">
                <i class="fas fa-history fa-2x text-primary mb-2 d-block"></i>
                <div class="fw-700">My Requests</div>
                <small class="text-muted">View request history</small>
            </a>
        </div>
        <div class="col-md-4">
            <a href="profile.php" class="card-custom d-block text-center p-4 text-decoration-none"
               style="transition:var(--transition);" onmouseover="this.style.transform='translateY(-4px)'"
               onmouseout="this.style.transform=''">
                <i class="fas fa-user-edit fa-2x text-success mb-2 d-block"></i>
                <div class="fw-700">My Profile</div>
                <small class="text-muted">Update your details</small>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
