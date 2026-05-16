<?php
// admin/requests.php – Manage emergency requests
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$db         = getDB();
$activePage = 'requests';
$msg        = '';
$msgType    = '';

// Update request status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id     = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';
    $allowed = ['pending','accepted','completed','cancelled'];

    if (in_array($status, $allowed)) {
        $stmt = $db->prepare("UPDATE requests SET status=? WHERE id=?");
        $stmt->bind_param('si', $status, $id);
        if ($stmt->execute()) {
            // If completed/cancelled, free the ambulance
            if (in_array($status, ['completed','cancelled'])) {
                $res = $db->prepare("SELECT ambulance_id FROM requests WHERE id=?");
                $res->bind_param('i', $id);
                $res->execute();
                $res->bind_result($ambId);
                $res->fetch();
                $res->close();

                $upd = $db->prepare("UPDATE ambulances SET status='available' WHERE id=?");
                $upd->bind_param('i', $ambId);
                $upd->execute();
                $upd->close();
            }
            $msg     = 'Request status updated.';
            $msgType = 'success';
        } else {
            $msg     = 'Update failed.';
            $msgType = 'danger';
        }
        $stmt->close();
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');

$sql    = "SELECT r.id, r.location, r.status, r.created_at,
                  u.name AS user_name, u.phone AS user_phone,
                  a.driver_name, a.vehicle_no, a.phone AS amb_phone
           FROM requests r
           JOIN users u ON r.user_id = u.id
           JOIN ambulances a ON r.ambulance_id = a.id
           WHERE 1=1";
$params = [];
$types  = '';

if ($statusFilter && in_array($statusFilter, ['pending','accepted','completed','cancelled'])) {
    $sql    .= " AND r.status = ?";
    $params[] = $statusFilter;
    $types   .= 's';
}
if ($search) {
    $like    = "%$search%";
    $sql    .= " AND (u.name LIKE ? OR r.location LIKE ? OR a.driver_name LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'sss';
}
$sql .= " ORDER BY r.created_at DESC";

$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
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
    <title>Manage Requests – Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="admin-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h6 class="mb-0 fw-700">Emergency Requests</h6>
            </div>
        </div>

        <div class="admin-content">

            <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-custom alert-auto-dismiss mb-3">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card-custom mb-4">
                <div class="card-custom-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search user, driver, location..."
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <?php foreach (['pending','accepted','completed','cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="requests.php" class="btn btn-outline-secondary w-100">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-bell text-danger me-2"></i>
                        Requests (<?= $requests->num_rows ?>)
                    </h6>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:var(--navy);color:rgba(255,255,255,.8);">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>User</th>
                                    <th>Driver</th>
                                    <th>Vehicle</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-center">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $requests->fetch_assoc()): ?>
                                <tr data-status="<?= $row['status'] ?>">
                                    <td class="ps-3 text-muted"><?= $row['id'] ?></td>
                                    <td>
                                        <div class="fw-600"><?= htmlspecialchars($row['user_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['user_phone']) ?></small>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($row['driver_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['amb_phone']) ?></small>
                                    </td>
                                    <td><code><?= htmlspecialchars($row['vehicle_no']) ?></code></td>
                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                    <td>
                                        <?php
                                        $b = ['pending'=>'warning','accepted'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                                        ?>
                                        <span class="badge bg-<?= $b[$row['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" class="d-flex gap-1 justify-content-center">
                                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                            <select name="status" class="form-select form-select-sm"
                                                    style="width:120px;">
                                                <?php foreach (['pending','accepted','completed','cancelled'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $row['status']===$s?'selected':'' ?>>
                                                    <?= ucfirst($s) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status"
                                                    class="btn btn-sm btn-primary">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
