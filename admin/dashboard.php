<?php
// admin/dashboard.php – Admin dashboard with stats and charts
session_start();
define('BASE_URL', '../');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$db = getDB();
$activePage = 'dashboard';

// Stats
$totalUsers      = $db->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalAmbulances = $db->query("SELECT COUNT(*) AS c FROM ambulances")->fetch_assoc()['c'];
$totalRequests   = $db->query("SELECT COUNT(*) AS c FROM requests")->fetch_assoc()['c'];
$pendingRequests = $db->query("SELECT COUNT(*) AS c FROM requests WHERE status='pending'")->fetch_assoc()['c'];
$availableAmb    = $db->query("SELECT COUNT(*) AS c FROM ambulances WHERE status='available'")->fetch_assoc()['c'];
$busyAmb         = $db->query("SELECT COUNT(*) AS c FROM ambulances WHERE status='busy'")->fetch_assoc()['c'];

// Request status breakdown for pie chart
$statusData = [];
$res = $db->query("SELECT status, COUNT(*) AS cnt FROM requests GROUP BY status");
while ($row = $res->fetch_assoc()) {
    $statusData[$row['status']] = (int)$row['cnt'];
}

// Monthly requests for bar chart (last 6 months)
$monthlyData = [];
$res = $db->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') AS month,
           COUNT(*) AS cnt
    FROM requests
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY created_at ASC
");
while ($row = $res->fetch_assoc()) {
    $monthlyData[] = $row;
}

// Recent requests
$recentReqs = $db->query("
    SELECT r.id, r.location, r.status, r.created_at,
           u.name AS user_name, a.driver_name, a.vehicle_no
    FROM requests r
    JOIN users u ON r.user_id = u.id
    JOIN ambulances a ON r.ambulance_id = a.id
    ORDER BY r.created_at DESC
    LIMIT 8
");

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="admin-wrapper">

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main -->
    <div class="admin-main">

        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h6 class="mb-0 fw-700">Dashboard</h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">
                    <i class="fas fa-user-shield me-1"></i>
                    <?= htmlspecialchars($_SESSION['admin_username']) ?>
                </span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <div class="admin-content">

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <?php
                $cards = [
                    ['label'=>'Total Users',      'value'=>$totalUsers,      'icon'=>'fa-users',     'color'=>'blue',   'link'=>'users.php'],
                    ['label'=>'Total Ambulances', 'value'=>$totalAmbulances, 'icon'=>'fa-ambulance', 'color'=>'red',    'link'=>'ambulances.php'],
                    ['label'=>'Total Requests',   'value'=>$totalRequests,   'icon'=>'fa-bell',      'color'=>'orange', 'link'=>'requests.php'],
                    ['label'=>'Pending Requests', 'value'=>$pendingRequests, 'icon'=>'fa-clock',     'color'=>'green',  'link'=>'requests.php?status=pending'],
                ];
                foreach ($cards as $c): ?>
                <div class="col-6 col-md-3">
                    <a href="<?= $c['link'] ?>" class="text-decoration-none">
                        <div class="stat-widget">
                            <div class="stat-widget-icon <?= $c['color'] ?>">
                                <i class="fas <?= $c['icon'] ?>"></i>
                            </div>
                            <div>
                                <div class="stat-widget-num"><?= $c['value'] ?></div>
                                <div class="stat-widget-label"><?= $c['label'] ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Bar Chart: Monthly Requests -->
                <div class="col-lg-7">
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h6 class="fw-700 mb-0">
                                <i class="fas fa-chart-bar text-danger me-2"></i>Monthly Requests
                            </h6>
                        </div>
                        <div class="card-custom-body">
                            <canvas id="monthlyChart" height="120"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart: Request Status -->
                <div class="col-lg-5">
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h6 class="fw-700 mb-0">
                                <i class="fas fa-chart-pie text-danger me-2"></i>Request Status
                            </h6>
                        </div>
                        <div class="card-custom-body">
                            <canvas id="statusChart" height="160"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ambulance Status Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card-custom text-center p-4">
                        <div class="mb-2" style="font-size:2.5rem;color:#10b981;">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div class="fw-800 fs-3"><?= $availableAmb ?></div>
                        <div class="text-muted small">Available Ambulances</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom text-center p-4">
                        <div class="mb-2" style="font-size:2.5rem;color:#f59e0b;">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div class="fw-800 fs-3"><?= $busyAmb ?></div>
                        <div class="text-muted small">Busy Ambulances</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom text-center p-4">
                        <div class="mb-2" style="font-size:2.5rem;color:#94a3b8;">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div class="fw-800 fs-3"><?= $totalAmbulances - $availableAmb - $busyAmb ?></div>
                        <div class="text-muted small">Offline Ambulances</div>
                    </div>
                </div>
            </div>

            <!-- Recent Requests Table -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h6 class="fw-700 mb-0">
                        <i class="fas fa-list text-danger me-2"></i>Recent Requests
                    </h6>
                    <a href="requests.php" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:var(--light);">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>User</th>
                                    <th>Driver</th>
                                    <th>Vehicle</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recentReqs->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $row['id'] ?></td>
                                    <td class="fw-600"><?= htmlspecialchars($row['user_name']) ?></td>
                                    <td><?= htmlspecialchars($row['driver_name']) ?></td>
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
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /admin-content -->
    </div><!-- /admin-main -->
</div><!-- /admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
// Monthly Bar Chart
const monthlyLabels = <?= json_encode(array_column($monthlyData, 'month')) ?>;
const monthlyCounts = <?= json_encode(array_map('intval', array_column($monthlyData, 'cnt'))) ?>;

new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthlyLabels.length ? monthlyLabels : ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: [{
            label: 'Requests',
            data: monthlyCounts.length ? monthlyCounts : [2,4,3,5,6,3],
            backgroundColor: 'rgba(255,59,59,.7)',
            borderColor: '#ff3b3b',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// Status Pie Chart
const statusLabels = <?= json_encode(array_keys($statusData)) ?>;
const statusCounts = <?= json_encode(array_values($statusData)) ?>;

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels.length ? statusLabels : ['Pending','Accepted','Completed','Cancelled'],
        datasets: [{
            data: statusCounts.length ? statusCounts : [3,2,5,1],
            backgroundColor: ['#f59e0b','#2563eb','#10b981','#94a3b8'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } }
        },
        cutout: '65%'
    }
});
</script>
</body>
</html>
