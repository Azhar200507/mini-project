<?php
// driver/update_status.php – Driver changes their ambulance availability status
session_start();

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$ambId     = $_SESSION['driver_amb_id'];
$newStatus = $_POST['status'] ?? '';
$allowed   = ['available', 'busy', 'offline'];

if (in_array($newStatus, $allowed)) {
    $db   = getDB();
    $stmt = $db->prepare("UPDATE ambulances SET status=? WHERE id=?");
    $stmt->bind_param('si', $newStatus, $ambId);
    $stmt->execute();
    $stmt->close();
    $db->close();
}

header('Location: dashboard.php');
exit;
