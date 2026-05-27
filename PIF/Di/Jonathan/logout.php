<?php
// logout.php - Logout script
// Purpose: End the user's session and redirect to the home page.
// This ensures the user is logged out securely and cannot access protected pages.

require_once 'config.php'; // Include config to access session
session_destroy(); // Destroy the session, removing all session data
header('Location: index.php'); // Redirect to home page
exit; // Stop script execution after redirect
?>