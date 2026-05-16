<?php
/**
 * API: api/register.php
 * Method: POST
 * Body params: name, phone, email, password
 * Returns: JSON { success, message, user_id? }
 *
 * Used by Flutter app for user registration.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Read JSON body (Flutter sends JSON) or form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST; // fallback for form-encoded
}

$name     = trim($input['name']     ?? '');
$phone    = trim($input['phone']    ?? '');
$email    = trim($input['email']    ?? '');
$password = trim($input['password'] ?? '');

// Validation
if (empty($name) || empty($phone) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

$db = getDB();

// Check duplicate email
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $db->close();
    echo json_encode(['success' => false, 'message' => 'Email already registered.']);
    exit;
}
$stmt->close();

// Insert user
$hashed = password_hash($password, PASSWORD_BCRYPT);
$ins    = $db->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
$ins->bind_param('ssss', $name, $phone, $email, $hashed);

if ($ins->execute()) {
    $newId = $db->insert_id;
    $ins->close();
    $db->close();
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful.',
        'user_id' => $newId,
    ]);
} else {
    $ins->close();
    $db->close();
    echo json_encode(['success' => false, 'message' => 'Registration failed. Try again.']);
}
