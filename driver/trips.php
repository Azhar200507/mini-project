<?php
// driver/trips.php – Full trip history for the driver
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$driverName = $_SESSION['driver_name'];
$ambId      = $_SESSION['driver_amb_id'];
$db         = getDB();

// Filter
$statusFilter = $_GET['status'] ?? '';
$allowed      = ['pending','accepted','completed','cancelled'];

$sql    = "SELECT r.id, r.location, r.status, r.created_at,
                  u.name AS user_name, u.phone AS user_phone
           FROM requests r
           JOIN users u ON r.user_id = u.id
           WHERE r.ambulance_id = ?";
$params = [$ambId];
$types  = 'i';

if ($statusFilter && in_array($statusFilter, $allowed)) {
    $sql    .= " AND r.status = ?";
    $params[] = $statusFilter;
    $types   .= 's';
}
$sql .= " ORDER BY r.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$trips = $stmt->get_result();
$stmt->close();

// Summary counts
$counts = [];
foreach ($allowed as $s) {
    $counts[$s] = $db->query("SELECT COUNT(*) AS c FROM requests WHERE ambulance_id=$ambId AND status='$s'")->fetch_assoc()['c'];
}
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip History – Driver Panel</title>
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
        .filter-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .4rem 1rem; border-radius: 50px;
            font-size: .82rem; font-weight: 600;
            border: 1.5px solid var(--border);
            color: var(--text-muted);
            text-decoration: none;
            transition: all .2s;
        }
        .filter-pill:hover, .filter-pill.active {
            background: var(--red); color: #fff; border-color: var(--red);
        }
        .filter-pill.active-pill  { background:#2563eb;  color:#fff; border-color:#2563eb; }
        .filter-pill.completed-pill{ background:#10b981; color:#fff; border-color:#10b981; }
        .filter-pill.cancelled-pill{ background:#94a3b8; color:#fff; border-color:#94a3b8; }
    </style>
</head>
<body style="background:var(--light);">

<div class="driver-topbar">
    <div class="driver-topbar-brand">
        <a href="dashboard.php" class="text-white text-decoration-none me-2">
            <i class="fas fa-arrow-left opacity-75"></i>
        </a>
        <i class="fas fa-history text-danger me-2"></i>Trip History
    </div>
    <a href="logout.php" class="btn btn-sm btn-outline-light opacity-75">
        <i class="fas fa-sign-out-alt"></i>
    </a>
</div>

<div class="container py-4">

    <!-- Summary pills -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="trips.php"
           class="filter-pill <?= !$statusFilter ? 'active' : '' ?>">
            All <span class="badge bg-secondary ms-1"><?= array_sum($counts) ?></span>
        </a>
        <?php
        $pillMeta = [
            'pending'   => ['warning', 'fa-clock',         'pending-pill'],
            'accepted'  => ['primary', 'fa-ambulance',     'active-pill'],
            'completed' => ['success', 'fa-check-circle',  'completed-pill'],
            'cancelled' => ['secondary','fa-times-circle', 'cancelled-pill'],
        ];
        foreach ($pillMeta as $s => [$color, $icon, $cls]): ?>
        <a href="trips.php?status=<?= $s ?>"
           class="filter-pill <?= $statusFilter===$s ? $cls : '' ?>">
            <i class="fas <?= $icon ?>"></i>
            <?= ucfirst($s) ?>
            <span class="badge bg-<?= $color ?> ms-1"><?= $counts[$s] ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="card-custom">
        <div class="card-custom-header">
            <h6 class="fw-700 mb-0">
                <i class="fas fa-list text-danger me-2"></i>
                <?= $statusFilter ? ucfirst($statusFilter) . ' Trips' : 'All Trips' ?>
                (<?= $trips->num_rows ?>)
            </h6>
        </div>
        <div class="card-custom-body p-0">
            <?php if ($trips->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:var(--navy);color:rgba(255,255,255,.8);">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Patient</th>
                            <th>Phone</th>
                            <th>Pickup Location</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($trip = $trips->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-3 text-muted small"><?= $trip['id'] ?></td>
                            <td class="fw-600"><?= htmlspecialchars($trip['user_name']) ?></td>
                            <td>
                                <a href="tel:<?= $trip['user_phone'] ?>" class="text-muted small">
                                    <i class="fas fa-phone me-1 text-danger"></i>
                                    <?= htmlspecialchars($trip['user_phone']) ?>
                                </a>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                <?= htmlspecialchars($trip['location']) ?>
                            </td>
                            <td>
                                <?php
                                $b = ['pending'=>'warning','accepted'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                                ?>
                                <span class="badge bg-<?= $b[$trip['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($trip['status']) ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= date('d M Y', strtotime($trip['created_at'])) ?><br>
                                <span class="opacity-75"><?= date('h:i A', strtotime($trip['created_at'])) ?></span>
                            </td>
                            <td>
                                <?php if ($trip['status'] === 'pending'): ?>
                                <form method="POST" action="update_request.php" class="d-inline">
                                    <input type="hidden" name="request_id" value="<?= $trip['id'] ?>">
                                    <input type="hidden" name="status" value="accepted">
                                    <button class="btn btn-sm btn-primary">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php elseif ($trip['status'] === 'accepted'): ?>
                                <form method="POST" action="update_request.php" class="d-inline">
                                    <input type="hidden" name="request_id" value="<?= $trip['id'] ?>">
                                    <input type="hidden" name="status" value="completed">
                                    <button class="btn btn-sm btn-success"
                                            onclick="return confirm('Mark as completed?')">
                                        <i class="fas fa-flag-checkered"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                <p>No <?= $statusFilter ?: '' ?> trips found.</p>
                <a href="trips.php" class="btn btn-outline-danger btn-sm">View All</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
