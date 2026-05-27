<?php
// includes/auth_check.php
// This file ensures that only logged-in users can access certain pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // PHP sessions store user login state across pages
}

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) { // $_SESSION['username'] is set at login
    header('Location: /PIF/Website/index.php'); // Redirects to login page
    exit; // Stop execution of the current script
}
?>