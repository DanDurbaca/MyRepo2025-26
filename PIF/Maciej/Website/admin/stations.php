<?php
// admin/stations.php
// Controller for the Admin Stations page
// Handles loading stations/users and processing create, update, delete actions

// Include database connection and authentication check
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Only allow Admin users; redirect others to homepage
if (($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

// Establish database connection
$conn = getDbConnection();

// Initialize message variables
$success = '';
$error = '';


//LOAD STATIONS FROM DATABASE
// Fetch all stations, including owner username (if assigned)
// LEFT JOIN ensures stations without owners are still included
$stations = $conn->query(
    "SELECT s.pk_serialNumber, s.name, s.description, s.fk_user_owns,
            u.pk_username
     FROM station s
     LEFT JOIN user u ON s.fk_user_owns = u.pk_username
     ORDER BY s.pk_serialNumber"
)->fetchAll();


//LOAD USERS FROM DATABASE
// Used to populate the "Owner" dropdown in the form
$users = $conn->query(
    "SELECT pk_username FROM user ORDER BY pk_username"
)->fetchAll();


//HANDLE FORM SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    
    //CREATE NEW STATION
    if ($action === 'create') {
        $sn = trim($_POST['serial_number']); // Station Serial Number
        $name = trim($_POST['name']);        // Station Name
        $desc = trim($_POST['description']); // Optional description

        if ($sn && $name) {
            // Insert new station into database
            $stmt = $conn->prepare(
                "INSERT INTO station (pk_serialNumber, name, description)
                 VALUES (:sn, :name, :desc)"
            );
            $stmt->execute([
                'sn'   => $sn,
                'name' => $name,
                'desc' => $desc
            ]);
            $success = "Station created.";
        } else {
            $error = "Serial number and name required.";
        }
    }


    //UPDATE EXISTING STATION
    if ($action === 'update') {
        // Update station info including owner assignment
        $stmt = $conn->prepare(
            "UPDATE station
             SET name = :name,
                 description = :desc,
                 fk_user_owns = :owner
             WHERE pk_serialNumber = :sn"
        );
        $stmt->execute([
            'name'  => $_POST['name'],
            'desc'  => $_POST['description'],
            'owner' => $_POST['owner'] ?: null, // Set null if unassigned
            'sn'    => $_POST['serial_number']
        ]);
        $success = "Station updated.";
    }


    //DELETE STATION
    if ($action === 'delete') {
        // Delete station by serial number
        $stmt = $conn->prepare(
            "DELETE FROM station WHERE pk_serialNumber = :sn"
        );
        $stmt->execute(['sn' => $_POST['serial_number']]);
        $success = "Station deleted.";
    }

    // After form processing, redirect to avoid resubmission
    header("Location: stations.php");
    exit;
}

// Load the view that displays the stations page
require __DIR__ . '/stations_view.php';
?>