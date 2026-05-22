<?php
// admin/stations_readonly.php
// This page displays all stations in a read-only format for Admin users

// Include database configuration and authentication checks
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Check if the current user is an Admin; if not, redirect to homepage
if (($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../index.php");
    exit; // Stop further execution
}

// Establish a connection to the database
$conn = getDbConnection();

// Fetch all stations along with their owner's username
// LEFT JOIN ensures stations without owners are still included
$stations = $conn->query(
    "SELECT s.pk_serialNumber, s.name, s.description, u.pk_username AS owner_username
     FROM station s
     LEFT JOIN user u ON s.fk_user_owns = u.pk_username
     ORDER BY s.pk_serialNumber" // Sort by serial number
)->fetchAll();

// Load the view file that will display the stations in a read-only table
require __DIR__ . '/stations_readonly_view.php';
?>