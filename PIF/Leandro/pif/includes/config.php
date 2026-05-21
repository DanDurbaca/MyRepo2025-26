<?php
/*
 * includes/config.php
 * Purpose: Application configuration and database connection.
 * Sections:
 *  - Database credentials and PDO connection setup (`$pdo`)
 *  - Session initialization via `session_start()` so other pages can rely on `$_SESSION`
 * Notes:
 *  - Errors throw exceptions and a simple failure message is shown on connection error.
 */
$host = "localhost";
$db   = "portableindoorfeedback";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed");
}

// Configure session
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
session_start();
?>