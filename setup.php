<?php
/**
 * setup.php – One-click database setup helper
 * Run this ONCE at: http://localhost/ambulance_locator/setup.php
 * It creates the database, tables, and inserts sample data with correct password hashes.
 * DELETE this file after setup for security.
 */

$host = 'localhost';
$user = 'root';
$pass = '';       // Change if your MySQL has a password

// Connect without selecting a database first
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die('<h2 style="color:red">Connection failed: ' . $conn->connect_error . '</h2>
         <p>Make sure XAMPP MySQL is running and credentials in setup.php are correct.</p>');
}

$conn->set_charset('utf8mb4');

// Generate hashes
$adminHash = password_hash('admin123', PASSWORD_BCRYPT);
$userHash  = password_hash('user123',  PASSWORD_BCRYPT);

$queries = [
    "CREATE DATABASE IF NOT EXISTS ambulance_locator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
    "USE ambulance_locator",

    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS ambulances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        vehicle_no VARCHAR(30) NOT NULL,
        area VARCHAR(150) NOT NULL,
        latitude DECIMAL(10,8) NOT NULL DEFAULT 0.00000000,
        longitude DECIMAL(11,8) NOT NULL DEFAULT 0.00000000,
        status ENUM('available','busy','offline') NOT NULL DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        ambulance_id INT NOT NULL,
        location VARCHAR(255) NOT NULL,
        status ENUM('pending','accepted','completed','cancelled') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (ambulance_id) REFERENCES ambulances(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Seed admins (skip if exists)
    "INSERT IGNORE INTO admins (username, password) VALUES ('admin', '$adminHash')",

    // Seed users
    "INSERT IGNORE INTO users (name, phone, email, password) VALUES
        ('Rahul Sharma',  '9876543210', 'rahul@example.com',  '$userHash'),
        ('Priya Singh',   '9123456780', 'priya@example.com',  '$userHash'),
        ('Amit Kumar',    '9988776655', 'amit@example.com',   '$userHash'),
        ('Sneha Patel',   '9871234560', 'sneha@example.com',  '$userHash'),
        ('Vikram Rao',    '9765432100', 'vikram@example.com', '$userHash')",

    // Seed ambulances
    "INSERT IGNORE INTO ambulances (id, driver_name, phone, vehicle_no, area, latitude, longitude, status) VALUES
        (1,  'Rajan Mehta',   '9001122334', 'MH-01-AB-1234', 'Andheri West, Mumbai',   19.13600000,  72.82600000, 'available'),
        (2,  'Suresh Yadav',  '9002233445', 'MH-02-CD-5678', 'Bandra East, Mumbai',    19.05400000,  72.84200000, 'available'),
        (3,  'Deepak Nair',   '9003344556', 'MH-03-EF-9012', 'Dadar, Mumbai',          19.01800000,  72.84300000, 'busy'),
        (4,  'Manoj Tiwari',  '9004455667', 'DL-04-GH-3456', 'Connaught Place, Delhi', 28.63290000,  77.21970000, 'available'),
        (5,  'Arun Verma',    '9005566778', 'DL-05-IJ-7890', 'Lajpat Nagar, Delhi',    28.56500000,  77.24300000, 'available'),
        (6,  'Kiran Reddy',   '9006677889', 'KA-06-KL-2345', 'Koramangala, Bangalore', 12.93500000,  77.62400000, 'offline'),
        (7,  'Prasad Iyer',   '9007788990', 'KA-07-MN-6789', 'Indiranagar, Bangalore', 12.97800000,  77.64100000, 'available'),
        (8,  'Sanjay Gupta',  '9008899001', 'TN-08-OP-0123', 'T. Nagar, Chennai',      13.04000000,  80.23400000, 'available'),
        (9,  'Ramesh Pillai', '9009900112', 'TN-09-QR-4567', 'Anna Nagar, Chennai',    13.08500000,  80.21000000, 'busy'),
        (10, 'Naresh Joshi',  '9010011223', 'GJ-10-ST-8901', 'Navrangpura, Ahmedabad', 23.03700000,  72.56000000, 'available')",

    // Seed requests
    "INSERT IGNORE INTO requests (id, user_id, ambulance_id, location, status) VALUES
        (1, 1, 1, 'Andheri Station, Mumbai',      'completed'),
        (2, 2, 2, 'Bandra Kurla Complex, Mumbai', 'accepted'),
        (3, 3, 4, 'India Gate, Delhi',            'pending'),
        (4, 4, 7, 'MG Road, Bangalore',           'completed'),
        (5, 5, 8, 'Marina Beach, Chennai',        'cancelled'),
        (6, 1, 5, 'Karol Bagh, Delhi',            'pending'),
        (7, 2, 3, 'Dadar Station, Mumbai',        'accepted'),
        (8, 3, 6, 'Whitefield, Bangalore',        'completed')",

    // Drivers table
    "CREATE TABLE IF NOT EXISTS drivers (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];
$errors  = [];
$success = [];

foreach ($queries as $sql) {
    if ($conn->query($sql) === true) {
        $success[] = substr($sql, 0, 60) . '...';
    } else {
        $errors[] = $conn->error . ' | Query: ' . substr($sql, 0, 80);
    }
}

// Seed drivers with correct bcrypt hash
$driverHash = password_hash('driver123', PASSWORD_BCRYPT);
$driverData = [
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
$dstmt = $conn->prepare("INSERT IGNORE INTO drivers (ambulance_id,name,phone,email,password,license_no,experience) VALUES (?,?,?,?,?,?,?)");
foreach ($driverData as $d) {
    $dstmt->bind_param('isssssi', $d[0], $d[1], $d[2], $d[3], $driverHash, $d[4], $d[5]);
    if ($dstmt->execute()) {
        $success[] = "Driver seeded: {$d[1]}";
    } else {
        $errors[] = "Driver skip/fail: {$d[1]}";
    }
}
$dstmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup – Ambulance911</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:700px;">
    <div class="text-center mb-4">
        <div style="width:64px;height:64px;background:#ff3b3b;border-radius:16px;
                    display:flex;align-items:center;justify-content:center;
                    font-size:1.8rem;color:#fff;margin:0 auto 1rem;">
            🚑
        </div>
        <h3 class="fw-800">Ambulance911 – Database Setup</h3>
    </div>

    <?php if (empty($errors)): ?>
    <div class="alert alert-success">
        <h5><i class="fas fa-check-circle me-2"></i>Setup Completed Successfully!</h5>
        <p class="mb-0">Database, tables, and sample data have been created.</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h6 class="fw-bold">Default Login Credentials</h6>
            <table class="table table-sm mb-0">
                <tr><th>Admin URL</th><td><a href="admin/login.php">admin/login.php</a></td></tr>
                <tr><th>Admin Login</th><td>admin / admin123</td></tr>
                <tr><th>User Login</th><td>rahul@example.com / user123</td></tr>
                <tr><th>Driver URL</th><td><a href="driver/login.php">driver/login.php</a></td></tr>
                <tr><th>Driver Login</th><td>rajan@driver.com / driver123</td></tr>
                <tr><th>Site URL</th><td><a href="index.php">index.php</a></td></tr>
            </table>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>⚠️ Security:</strong> Delete <code>setup.php</code> after setup is complete.
    </div>

    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-danger">Go to Site</a>
        <a href="admin/login.php" class="btn btn-primary">Admin Panel</a>
    </div>

    <?php else: ?>
    <div class="alert alert-danger">
        <h5>Setup encountered errors:</h5>
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <p>Check your MySQL credentials in <code>setup.php</code> and try again.</p>
    <?php endif; ?>

    <hr>
    <details>
        <summary class="text-muted small">Executed queries (<?= count($success) ?>)</summary>
        <ul class="small text-muted mt-2">
            <?php foreach ($success as $s): ?>
            <li><?= htmlspecialchars($s) ?></li>
            <?php endforeach; ?>
        </ul>
    </details>
</div>
</body>
</html>
