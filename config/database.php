<?php
// ============================================================
// config/database.php
// Central database connection using MySQLi
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Change if your MySQL has a password
define('DB_NAME', 'ambulance_locator');

/**
 * Returns a MySQLi connection object.
 * Terminates with an error message if connection fails.
 */
function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        // In production you would log this; for dev we show it
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
