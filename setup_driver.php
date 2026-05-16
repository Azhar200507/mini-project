<?php
/**
 * setup_driver.php
 * Run ONCE at: http://localhost/ambulance_locator/setup_driver.php
 * Creates the drivers table and seeds driver accounts with correct bcrypt hashes.
 * DELETE this file after running.
 */

require_once 'config/database.php';
$db = getDB();

$hash = password_hash('driver123', PASSWORD_BCRYPT);

$steps = [];

// 1. Create drivers table
$sql = "CREATE TABLE IF NOT EXISTS drivers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ambulance_id INT          NOT NULL UNIQUE,
    name         VARCHAR(100) NOT NULL,
    phone        VARCHAR(20)  NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    license_no   VARCHAR(50)  NOT NULL DEFAULT '',
    experience   TINYINT      NOT NULL DEFAULT 0,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ambulance_id) REFERENCES ambulances(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($db->query($sql)) {
    $steps[] = ['ok', 'drivers table created (or already exists)'];
} else {
    $steps[] = ['err', 'Table creation failed: ' . $db->error];
}

// 2. Seed drivers
$drivers = [
    [1,  'Rajan Mehta',   '9001122334', 'rajan@driver.com',   'DL-MH-2019-001', 5],
    [2,  'Suresh Yadav',  '9002233445', 'suresh@driver.com',  'DL-MH-2020-002', 4],
    [3,  'Deepak Nair',   '9003344556', 'deepak@driver.com',  'DL-MH-2018-003', 6],
    [4,  'Manoj Tiwari',  '9004455667', 'manoj@driver.com',   'DL-DL-2021-004', 3],
    [5,  'Arun Verma',    '9005566778', 'arun@driver.com',    'DL-DL-2017-005', 7],
    [6,  'Kiran Reddy',   '9006677889', 'kiran@driver.com',   'DL-KA-2022-006', 2],
    [7,  'Prasad Iyer',   '9007788990', 'prasad@driver.com',  'DL-KA-2016-007', 8],
    [8,  'Sanjay Gupta',  '9008899001', 'sanjay@driver.com',  'DL-TN-2019-008', 5],
    [9,  'Ramesh Pillai', '9009900112', 'ramesh@driver.com',  'DL-TN-2020-009', 4],
    [10, 'Naresh Joshi',  '9010011223', 'naresh@driver.com',  'DL-GJ-2018-010', 6],
];

$stmt = $db->prepare("INSERT IGNORE INTO drivers
    (ambulance_id, name, phone, email, password, license_no, experience)
    VALUES (?,?,?,?,?,?,?)");

foreach ($drivers as $d) {
    $stmt->bind_param('isssssi', $d[0], $d[1], $d[2], $d[3], $hash, $d[4], $d[5]);
    if ($stmt->execute()) {
        $steps[] = ['ok', "Driver seeded: {$d[1]} ({$d[3]})"];
    } else {
        $steps[] = ['warn', "Skipped (already exists): {$d[1]}"];
    }
}
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:680px;">
    <div class="text-center mb-4">
        <div style="width:64px;height:64px;background:#2563eb;border-radius:16px;
                    display:flex;align-items:center;justify-content:center;
                    font-size:1.8rem;color:#fff;margin:0 auto 1rem;">🚑</div>
        <h3 class="fw-bold">Driver Module Setup</h3>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <?php foreach ($steps as [$type, $msg]): ?>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="text-<?= $type === 'ok' ? 'success' : ($type === 'warn' ? 'warning' : 'danger') ?>">
                    <?= $type === 'ok' ? '✔' : ($type === 'warn' ? '⚠' : '✘') ?>
                </span>
                <span class="small"><?= htmlspecialchars($msg) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="alert alert-success">
        <strong>Driver Login Credentials</strong><br>
        Email: <code>rajan@driver.com</code> &nbsp;|&nbsp; Password: <code>driver123</code><br>
        <small class="text-muted">(All 10 drivers use password: driver123)</small>
    </div>

    <div class="alert alert-warning">
        ⚠️ Delete <code>setup_driver.php</code> after setup.
    </div>

    <div class="d-flex gap-2">
        <a href="driver/login.php" class="btn btn-primary">Driver Login</a>
        <a href="admin/dashboard.php" class="btn btn-outline-secondary">Admin Panel</a>
        <a href="index.php" class="btn btn-outline-secondary">Home</a>
    </div>
</div>
</body>
</html>
