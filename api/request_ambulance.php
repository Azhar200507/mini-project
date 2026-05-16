<?php
/**
 * API: api/request_ambulance.php
 * Method: POST
 * Body params: user_id, ambulance_id, location
 * Returns: JSON { success, message, request_id? }
 *
 * Used by Flutter app to submit an emergency request.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Accept JSON or form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$userId      = (int)($input['user_id']      ?? 0);
$ambulanceId = (int)($input['ambulance_id'] ?? 0);
$location    = trim($input['location']      ?? '');

// Validation
if (!$userId || !$ambulanceId || empty($location)) {
    echo json_encode(['success' => false, 'message' => 'user_id, ambulance_id, and location are required.']);
    exit;
}

$db = getDB();

// Verify user exists
$stmt = $db->prepare("SELECT id FROM users WHERE id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close(); $db->close();
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}
$stmt->close();

// Verify ambulance is available
$stmt = $db->prepare("SELECT id, status FROM ambulances WHERE id=?");
$stmt->bind_param('i', $ambulanceId);
$stmt->execute();
$stmt->bind_result($ambId, $ambStatus);
$stmt->fetch();
$stmt->close();

if (!$ambId) {
    $db->close();
    echo json_encode(['success' => false, 'message' => 'Ambulance not found.']);
    exit;
}

if ($ambStatus !== 'available') {
    $db->close();
    echo json_encode(['success' => false, 'message' => 'Ambulance is not available right now.']);
    exit;
}

// Insert request
$ins = $db->prepare("INSERT INTO requests (user_id, ambulance_id, location) VALUES (?, ?, ?)");
$ins->bind_param('iis', $userId, $ambulanceId, $location);

if ($ins->execute()) {
    $requestId = $db->insert_id;
    $ins->close();

    // Mark ambulance as busy
    $upd = $db->prepare("UPDATE ambulances SET status='busy' WHERE id=?");
    $upd->bind_param('i', $ambulanceId);
    $upd->execute();
    $upd->close();

    $db->close();
    echo json_encode([
        'success'    => true,
        'message'    => 'Ambulance requested successfully. Help is on the way!',
        'request_id' => $requestId,
    ]);
} else {
    $ins->close();
    $db->close();
    echo json_encode(['success' => false, 'message' => 'Request failed. Please try again.']);
}
