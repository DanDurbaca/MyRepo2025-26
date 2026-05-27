<?php
//admin/users_readonly.php
// Include database connection and authentication check
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Only allow Admin users; redirect others
if (($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

// Connect to the database
$conn = getDbConnection();

// Fetch all users from the database, ordered by username
$users = $conn->query(
    "SELECT pk_username, firstName, lastName, email, role
     FROM user ORDER BY pk_username"
)->fetchAll();

// Load the view that displays users in a read-only table
require __DIR__ . '/users_readonly_view.php';
?>