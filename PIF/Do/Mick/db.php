<?php
// db.php - simplified mysqli connection and auth helpers
// Adapted to a simpler procedural style for clarity and compatibility.
session_start();

// Adjust these credentials to your local setup if needed
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'webuser');
define('DB_PASS', '123');
define('DB_NAME', 'webapp');

// Create a persistent connection in $conn
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    // For local/dev use give a clear error. In production, hide DB details.
    die('Database connection error. Check credentials and that MySQL is running.');
}
mysqli_set_charset($conn, 'utf8mb4');

function db_connect() {
    global $conn;
    return $conn;
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function is_admin() {
    return !empty($_SESSION['is_admin']);
}

// Backwards compatibility: if user_id is set but username/session variants not, populate them.
if (!empty($_SESSION['user_id']) && empty($_SESSION['username'])) {
    // In this app we store the username in user_id (string). Copy to legacy keys used elsewhere.
    $_SESSION['username'] = $_SESSION['user_id'];
    $_SESSION['User'] = $_SESSION['user_id'];
    $_SESSION['UserLoggedIn'] = true;
}

?>
