<?php
// admin/ambulances.php – Manage ambulances (CRUD)
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$db         = getDB();
$activePage = 'ambulances';
$msg        = '';
$msgType    = '';

// ---- DELETE ----
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM ambulances WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ambulances.php?msg=deleted');
    exit;
}

// ---- ADD ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $driver  = trim($_POST['driver_name'] ?? '');
    $phone   = trim($_POST['phone']       ?? '');
    $vehicle = trim($_POST['vehicle_no']  ?? '');
    $area    = trim($_POST['area']        ?? '');
    $lat     = (float)($_POST['latitude']  ?? 0);
    $lng     = (float)($_POST['longitude'] ?? 0);
    $status  = $_POST['status'] ?? 'available';

    if ($driver && $phone && $vehicle && $area) {
        $stmt = $db->prepare("INSERT INTO ambulances (driver_name,phone,vehicle_no,area,latitude,longitude,status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssddss', $driver, $phone, $vehicle, $area, $lat, $lng, $status);
        if ($stmt->execute()) {
            $msg     = 'Ambulance added successfully!';
            $msgType = 'success';
        } else {
            $msg     = 'Failed to add ambulance.';
            $msgType = 'danger';
        }
        $stmt->close();
    } else {
        $msg     = 'All required fields must be filled.';
        $msgType = 'danger';
    }
}

// ---- EDIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    $id      = (int)$_POST['id'];
    $driver  = trim($_POST['driver_name'] ?? '');
    $phone   = trim($_POST['phone']       ?? '');
    $vehicle = trim($_POST['vehicle_no']  ?? '');
    $area    = trim($_POST['area']        ?? '');
    $lat     = (float)($_POST['latitude']  ?? 0);
    $lng     = (float)($_POST['longitude'] ?? 0);
    $status  = $_POST['status'] ?? 'available';

    $stmt = $db->prepare("UPDATE ambulances SET driver_name=?,phone=?,vehicle_no=?,area=?,latitude=?,longitude=?,status=? WHERE id=?");
    $stmt->bind_param('ssssddsi', $driver, $phone, $vehicle, $area, $lat, $lng, $status, $id);
    if ($stmt->execute()) {
        $msg     = 'Ambulance updated successfully!';
        $msgType = 'success';
    } else {
        $msg     = 'Update failed.';
        $msgType = 'danger';
    }
    $stmt->close();
}

if (isset($_GET['msg'])) {
    $msg     = $_GET['msg'] === 'deleted' ? 'Ambulance deleted.' : '';
    $msgType = 'warning';
}

// Fetch ambulances with optional search
$search = trim($_GET['search'] ?? '');
$sql    = "SELECT * FROM ambulances";
if ($search) {
    $like = "%$search%";
    $stmt = $db->prepare("SELECT * FROM ambulances WHERE driver_name LIKE ? OR vehicle_no LIKE ? OR area LIKE ? ORDER BY id DESC");
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $ambulances = $stmt->get_result();
    $stmt->close();
} else {
    $ambulances = $db->query("SELECT * FROM ambulances ORDER BY id DESC");
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ambulances – Admin</title>
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
                <h6 class="mb-0 fw-700">Manage Ambulances</h6>
            </div>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-1"></i>Add Ambulance
            </button>
        </div>

        <div class="admin-content">

            <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-custom alert-auto-dismiss mb-3">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Search -->
            <div class="card-custom mb-4">
                <div class="card-custom-body">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by driver, vehicle, area..."
                               value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($search): ?>
                        <a href="ambulances.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-ambulance text-danger me-2"></i>
                        All Ambulances (<?= $ambulances->num_rows ?>)
                    </h6>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:var(--navy);color:rgba(255,255,255,.8);">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Driver</th>
                                    <th>Phone</th>
                                    <th>Vehicle No.</th>
                                    <th>Area</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($amb = $ambulances->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $amb['id'] ?></td>
                                    <td class="fw-600"><?= htmlspecialchars($amb['driver_name']) ?></td>
                                    <td><?= htmlspecialchars($amb['phone']) ?></td>
                                    <td><code><?= htmlspecialchars($amb['vehicle_no']) ?></code></td>
                                    <td><?= htmlspecialchars($amb['area']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $amb['status'] ?>">
                                            <span class="status-dot <?= $amb['status'] ?>"></span>
                                            <?= ucfirst($amb['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d M Y', strtotime($amb['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit button -->
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                                onclick="openEdit(<?= htmlspecialchars(json_encode($amb)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <!-- Delete button -->
                                        <a href="ambulances.php?delete=<?= $amb['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Delete this ambulance?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-700">
                    <i class="fas fa-plus-circle text-danger me-2"></i>Add New Ambulance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <?php include '_ambulance_form.php'; ?>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i>Add Ambulance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-700">
                    <i class="fas fa-edit text-primary me-2"></i>Edit Ambulance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body" id="editFormBody">
                    <?php include '_ambulance_form.php'; ?>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Ambulance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
function openEdit(data) {
    document.getElementById('editId').value = data.id;
    // Populate edit modal fields
    const modal = document.getElementById('editModal');
    modal.querySelector('[name="driver_name"]').value = data.driver_name;
    modal.querySelector('[name="phone"]').value       = data.phone;
    modal.querySelector('[name="vehicle_no"]').value  = data.vehicle_no;
    modal.querySelector('[name="area"]').value        = data.area;
    modal.querySelector('[name="latitude"]').value    = data.latitude;
    modal.querySelector('[name="longitude"]').value   = data.longitude;
    modal.querySelector('[name="status"]').value      = data.status;
    new bootstrap.Modal(modal).show();
}
</script>
</body>
</html>
