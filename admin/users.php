<?php
// admin/users.php – View and manage registered users
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$db         = getDB();
$activePage = 'users';
$msg        = '';
$msgType    = '';

// Delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: users.php?msg=deleted');
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $msg     = 'User deleted successfully.';
    $msgType = 'warning';
}

// Search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $like = "%$search%";
    $stmt = $db->prepare("SELECT u.*, (SELECT COUNT(*) FROM requests r WHERE r.user_id=u.id) AS req_count
                          FROM users u
                          WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?
                          ORDER BY u.created_at DESC");
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $users = $stmt->get_result();
    $stmt->close();
} else {
    $users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM requests r WHERE r.user_id=u.id) AS req_count
                         FROM users u ORDER BY u.created_at DESC");
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users – Admin</title>
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
                <h6 class="mb-0 fw-700">Manage Users</h6>
            </div>
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
                               placeholder="Search by name, email, phone..."
                               value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($search): ?>
                        <a href="users.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-users text-danger me-2"></i>
                        Registered Users (<?= $users->num_rows ?>)
                    </h6>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:var(--navy);color:rgba(255,255,255,.8);">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Requests</th>
                                    <th>Joined</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $user['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:34px;height:34px;border-radius:50%;
                                                        background:linear-gradient(135deg,var(--red),#ff8080);
                                                        display:flex;align-items:center;justify-content:center;
                                                        color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0;">
                                                <?= strtoupper(substr($user['name'],0,1)) ?>
                                            </div>
                                            <span class="fw-600"><?= htmlspecialchars($user['name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $user['req_count'] ?></span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d M Y', strtotime($user['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="users.php?delete=<?= $user['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Delete user <?= htmlspecialchars(addslashes($user['name'])) ?>? This will also delete their requests.')">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
