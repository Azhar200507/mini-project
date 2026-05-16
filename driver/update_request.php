<?php
// driver/update_request.php – Driver accepts/completes/declines a request
session_start();

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$driverId = $_SESSION['driver_id'];
$ambId    = $_SESSION['driver_amb_id'];
$db       = getDB();

$requestId = (int)($_POST['request_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$allowed   = ['accepted', 'completed', 'cancelled'];

if (!$requestId || !in_array($newStatus, $allowed)) {
    header('Location: dashboard.php');
    exit;
}

// Verify this request belongs to this driver's ambulance
$stmt = $db->prepare("SELECT id, status FROM requests WHERE id=? AND ambulance_id=?");
$stmt->bind_param('ii', $requestId, $ambId);
$stmt->execute();
$stmt->bind_result($rid, $currentStatus);
$stmt->fetch();
$stmt->close();

if (!$rid) {
    $db->close();
    header('Location: dashboard.php');
    exit;
}

// Update request status
$upd = $db->prepare("UPDATE requests SET status=? WHERE id=?");
$upd->bind_param('si', $newStatus, $requestId);
$upd->execute();
$upd->close();

// Update ambulance availability
if (in_array($newStatus, ['completed', 'cancelled'])) {
    // Free the ambulance
    $db->query("UPDATE ambulances SET status='available' WHERE id=$ambId");
    $_SESSION['driver_amb_status'] = 'available';
} elseif ($newStatus === 'accepted') {
    $db->query("UPDATE ambulances SET status='busy' WHERE id=$ambId");
    $_SESSION['driver_amb_status'] = 'busy';
}

$db->close();
header('Location: dashboard.php');
exit;
