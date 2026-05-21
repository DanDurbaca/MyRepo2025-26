<?php
// index.php
// Application entry point and redirector

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Check login status
if (isset($_SESSION['username'])) {

    // User is logged in → dashboard
    header('Location: controller/dashboard.php');

} else {

    // User not logged in → login page
    header('Location: login.php');
}

exit;
