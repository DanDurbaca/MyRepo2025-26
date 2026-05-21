<?php
// controller/stations.php
// Handles displaying, registering, and updating user stations

require_once __DIR__ . '/../config/database.php';   // Database connection setup
require_once __DIR__ . '/../includes/auth_check.php'; // Ensure user is logged in

$conn = getDbConnection(); // Initialize PDO connection
$username = $_SESSION['username']; // Currently logged-in user

$success_message = ''; // Feedback for successful actions
$error_message   = ''; // Feedback for errors

// Load stations owned by the current user
$stmt = $conn->prepare(
    "SELECT pk_serialNumber, name, description
     FROM station
     WHERE fk_user_owns = :username
     ORDER BY name"
);
$stmt->execute(['username' => $username]);
$stations = $stmt->fetchAll(); // Array of user's stations

// Load stations that are unassigned and available for registration
$stmt = $conn->query(
    "SELECT pk_serialNumber
     FROM station
     WHERE fk_user_owns IS NULL
     ORDER BY pk_serialNumber"
);
$available_stations = $stmt->fetchAll(); // Array of available stations

// Handle POST requests (registering or updating stations)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // Determine which action to perform

    /* REGISTER STATION */
    if ($action === 'register_station') {
        $serial = $_POST['serial_number'] ?? ''; // Selected serial number
        $name   = trim($_POST['station_name'] ?? ''); // Entered station name
        $desc   = trim($_POST['description'] ?? ''); // Optional description

        if ($serial && $name) {
            // Assign the station to the current user
            $stmt = $conn->prepare(
                "UPDATE station
                 SET fk_user_owns = :username,
                     name = :name,
                     description = :desc
                 WHERE pk_serialNumber = :sn
                 AND fk_user_owns IS NULL" // Ensure station is not already taken
            );
            $stmt->execute([
                'username' => $username,
                'name' => $name,
                'desc' => $desc,
                'sn' => $serial
            ]);

            if ($stmt->rowCount()) {
                // If the update affected a row, registration was successful
                $success_message = "Station registered successfully.";
                header("Location: stations.php"); // Redirect to refresh page
                exit;
            } else {
                $error_message = "Station not available."; // Could be already assigned
            }
        } else {
            $error_message = "Serial number and name required."; // Required fields missing
        }
    }

    /* UPDATE STATION */
    if ($action === 'update_station') {
        $sn   = $_POST['serial_number'];      // Station serial number to update
        $name = trim($_POST['station_name']); // Updated name
        $desc = trim($_POST['description']);  // Updated description

        if ($name) {
            // Update station only if owned by the current user
            $stmt = $conn->prepare(
                "UPDATE station
                 SET name = :name, description = :desc
                 WHERE pk_serialNumber = :sn
                 AND fk_user_owns = :username"
            );
            $stmt->execute([
                'name' => $name,
                'desc' => $desc,
                'sn' => $sn,
                'username' => $username
            ]);

            $success_message = "Station updated.";
            header("Location: stations.php"); // Refresh page to show changes
            exit;
        }
    }
}

// Load the view and pass all data
require __DIR__ . '/../pages/stations_view.php';
?>