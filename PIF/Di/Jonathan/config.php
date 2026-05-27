<?php
// config.php - Database configuration and connection
// This file contains the database connection settings for the Indoor Climate Data Website.
// It uses PDO for secure database interactions.
// Purpose: Establish a connection to the MySQL database using the provided credentials.
// This ensures all database operations in the website are secure and efficient.

// Start session for user authentication (always, and only once, before any output)
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params(0, '/', '', $secure, true);
        if (function_exists('ini_set')) {
            @ini_set('session.cookie_httponly', '1');
            @ini_set('session.cookie_secure', $secure ? '1' : '0');
            @ini_set('session.cookie_samesite', 'Lax');
        }
    }
    session_start();
}

$host = 'localhost:3306'; // Database host with port (e.g., localhost:3306 for local MySQL server)
$dbname = 'portableindoorfeedback'; // Database name as per the project schema
$username = 'root'; // Database username for authentication
$password = ''; // Database password for authentication

// Create PDO connection
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

// If Composer autoloader is present, require it so libraries are available
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

// Optional API shared secret used to harden API endpoints.
// Set via environment variable PIF_API_SECRET. If empty, API endpoints remain accessible without this token.
$api_shared_secret = getenv('PIF_API_SECRET') ?: '';

// Centralized error/exception handling: log details and show friendly message.
// For API requests (paths containing '/api/'), return JSON error responses.
$isApi = (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false);

set_error_handler(function ($severity, $message, $file, $line) {
    // Respect current error_reporting level and suppressed (@) errors.
    if (!(error_reporting() & $severity)) {
        return false;
    }
    // TEMP: log PHP warnings/notices so we can see what triggers 500s.
    @file_put_contents(
        '/tmp/pif_php_warning.log',
        '[' . date('c') . '] ' . $message . ' in ' . $file . ':' . $line . ' (severity ' . $severity . ")\n",
        FILE_APPEND | LOCK_EX
    );
    // convert warnings/notices to ErrorException so they can be caught by exception handler
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($e) use ($isApi) {
    $msg = sprintf("Uncaught exception: %s in %s on line %d", $e->getMessage(), $e->getFile(), $e->getLine());
    error_log($msg . "\n" . $e->getTraceAsString());
    // TEMP: also write uncaught exceptions to a local debug file for diagnosis.
    @file_put_contents('/tmp/pif_exception.log', '[' . date('c') . '] ' . $msg . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND | LOCK_EX);
    // Only attempt to set HTTP response code and headers if headers have not been sent yet.
    if (!headers_sent()) {
        if ($isApi) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Internal server error.']);
        } else {
            http_response_code(500);
            echo "<h1>Internal server error</h1>";
        }
    }
    exit;
});

// NOTE: Application-level HTTPS enforcement removed — project runs over HTTP for local development per owner request.