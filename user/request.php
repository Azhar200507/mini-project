<?php
// user/request.php – View all user requests
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

// Handle cancel request
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $rid  = (int)$_GET['cancel'];
    $stmt = $db->prepare("UPDATE requests SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'");
    $stmt->bind_param('ii', $rid, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location: request.php?msg=cancelled');
    exit;
}

// Fetch all requests
$stmt = $db->prepare("
    SELECT r.id, r.location, r.status, r.created_at,
           a.driver_name, a.vehicle_no, a.phone AS amb_phone, a.area
    FROM requests r
    JOIN ambulances a ON r.ambulance_id = a.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$requests = $stmt->get_result();
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests – Ambulance911</title>
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
        <i class="fas fa-history text-danger me-2"></i>My Requests
    </div>
    <a href="ambulances.php" class="btn btn-danger btn-sm">
        <i class="fas fa-plus me-1"></i>New Request
    </a>
</div>

<div class="container py-4">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
    <div class="alert alert-warning alert-custom alert-auto-dismiss mb-4">
        <i class="fas fa-check-circle me-2"></i>Request cancelled successfully.
    </div>
    <?php endif; ?>

    <div class="card-custom">
        <div class="card-custom-header">
            <h6 class="fw-700 mb-0">
                <i class="fas fa-list text-danger me-2"></i>All Requests
            </h6>
            <span class="badge bg-danger"><?= $requests->num_rows ?> total</span>
        </div>
        <div class="card-custom-body p-0">
            <?php if ($requests->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:var(--light);">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Location</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $requests->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-3 text-muted small"><?= $row['id'] ?></td>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($row['driver_name']) ?></div>
                                <small class="text-muted">
                                    <a href="tel:<?= $row['amb_phone'] ?>" class="text-muted">
                                        <i class="fas fa-phone me-1"></i><?= $row['amb_phone'] ?>
                                    </a>
                                </small>
                            </td>
                            <td><code><?= htmlspecialchars($row['vehicle_no']) ?></code></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($row['area']) ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'pending'   => 'warning',
                                    'accepted'  => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'secondary',
                                ];
                                $b = $badges[$row['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $b ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td class="text-muted small">
                                <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                <a href="?cancel=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Cancel this request?')">
                                    <i class="fas fa-times"></i>
                                </a>
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
                <p>No requests yet.</p>
                <a href="ambulances.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-ambulance me-1"></i>Find Ambulance
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
