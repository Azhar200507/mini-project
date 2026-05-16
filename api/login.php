<?php
/**
 * API: api/login.php
 * Method: POST
 * Body params: email, password
 * Returns: JSON { success, message, user? }
 *
 * Used by Flutter app for user authentication.
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

$email    = trim($input['email']    ?? '');
$password = trim($input['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, name, phone, email, password, created_at FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$db->close();

if ($user && password_verify($password, $user['password'])) {
    // Remove password from response
    unset($user['password']);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful.',
        'user'    => $user,
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
}
