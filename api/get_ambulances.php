<?php
/**
 * API: api/get_ambulances.php
 * Method: GET
 * Query params (optional):
 *   status  – filter by status (available|busy|offline)
 *   area    – filter by area keyword
 *   limit   – max results (default 50)
 *
 * Returns: JSON { success, count, ambulances[] }
 *
 * Used by Flutter app to list nearby ambulances.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

$db = getDB();

$status = $_GET['status'] ?? '';
$area   = trim($_GET['area'] ?? '');
$limit  = min((int)($_GET['limit'] ?? 50), 100); // cap at 100

$sql    = "SELECT id, driver_name, phone, vehicle_no, area, latitude, longitude, status, created_at FROM ambulances WHERE 1=1";
$params = [];
$types  = '';

if ($status && in_array($status, ['available','busy','offline'])) {
    $sql    .= " AND status = ?";
    $params[] = $status;
    $types   .= 's';
}

if ($area) {
    $like    = "%$area%";
    $sql    .= " AND area LIKE ?";
    $params[] = $like;
    $types   .= 's';
}

$sql .= " ORDER BY status='available' DESC, id ASC LIMIT ?";
$params[] = $limit;
$types   .= 'i';

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$ambulances = [];
while ($row = $result->fetch_assoc()) {
    // Cast numeric fields
    $row['id']        = (int)$row['id'];
    $row['latitude']  = (float)$row['latitude'];
    $row['longitude'] = (float)$row['longitude'];
    $ambulances[]     = $row;
}

$stmt->close();
$db->close();

echo json_encode([
    'success'    => true,
    'count'      => count($ambulances),
    'ambulances' => $ambulances,
]);
