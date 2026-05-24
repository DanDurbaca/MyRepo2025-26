<?php
// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();

// Database connection (using your existing database name)
define('DB_HOST', '127.0.0.1:3306');
define('DB_NAME', 'portableindoorfeedback');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change if you have a password

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['username']) && !empty($_SESSION['username']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>