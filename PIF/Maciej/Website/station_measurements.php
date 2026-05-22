<?php
// station_measurements.php
// Controller for viewing, filtering, and managing station measurement data


// Required dependencies (database connection, auth check, helper functions)
require_once __DIR__ . '/config/database.php';      // Provides getDbConnection()
require_once __DIR__ . '/includes/auth_check.php';  // Ensures user is logged in
require_once __DIR__ . '/includes/functions.php';   // Helper functions (e.g. isAdmin())

// Database connection
$conn = getDbConnection(); // Open PDO connection to the database

// User session & role information
$username = $_SESSION['username']; // Currently logged-in username
$is_admin = isAdmin();              // Boolean: true if user is Admin

// GET filters (station & date range)
$station_sn     = $_GET['station_sn'] ?? '';        // Selected station serial number
$start_datetime = $_GET['start_datetime'] ?? '';    // Start date/time from form
$end_datetime   = $_GET['end_datetime'] ?? '';      // End date/time from form

// Convert date inputs to MySQL datetime format
$start_mysql = $start_datetime
    ? date('Y-m-d H:i:s', strtotime($start_datetime)) // Convert to MySQL DATETIME
    : '';

$end_mysql = $end_datetime
    ? date('Y-m-d H:i:s', strtotime($end_datetime))   // Convert to MySQL DATETIME
    : '';


// Fetch stations for dropdown (admin vs regular user)
if ($is_admin) {

    // Admin can see all stations
    $stmt = $conn->query(
        "SELECT pk_serialNumber, name
         FROM station
         ORDER BY name"
    );

} else {

    // Regular user can only see stations they own
    $stmt = $conn->prepare(
        "SELECT pk_serialNumber, name
         FROM station
         WHERE fk_user_owns = :username
         ORDER BY name"
    );
    $stmt->execute(['username' => $username]);
}

// Fetch all station rows into an array
$stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables used in the view
$measurements = [];                 // Holds measurement records
$selected_station_name = '';        // Human-readable station name
$error_message = '';                // Error message for access or data issues
$selected_measurements = [];        // Track checked measurement IDs


// Validate access & fetch measurements (GET request)
if ($station_sn && $start_mysql && $end_mysql) {

    // Access check for regular users
    if (!$is_admin) {

        // Verify that the station belongs to the logged-in user
        $stmt = $conn->prepare(
            "SELECT COUNT(*)
             FROM station
             WHERE pk_serialNumber = :sn
             AND fk_user_owns = :username"
        );
        $stmt->execute([
            'sn'       => $station_sn,
            'username' => $username
        ]);

        // If count is zero, user has no access
        if ($stmt->fetchColumn() == 0) {
            $error_message = "You do not have access to this station.";
        }
    }


    // Fetch station name & measurements (only if access is allowed)
    if (empty($error_message)) {

        // Fetch station display name
        $stmt = $conn->prepare(
            "SELECT name
             FROM station
             WHERE pk_serialNumber = :sn"
        );
        $stmt->execute(['sn' => $station_sn]);

        // Fallback to serial number if name is missing
        $selected_station_name = $stmt->fetchColumn() ?? $station_sn;

        // Fetch measurements for station within selected date range
        $stmt = $conn->prepare(
            "SELECT pk_measurement,
                    timestamp,
                    temperature,
                    humidity,
                    pressure,
                    light,
                    gas
             FROM measurement
             WHERE fk_station_records = :sn
             AND timestamp BETWEEN :start AND :end
             ORDER BY timestamp DESC"
        );

        $stmt->execute([
            'sn'    => $station_sn,
            'start' => $start_mysql,
            'end'   => $end_mysql
        ]);

        // Store measurement rows for the view
        $measurements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


// Handle POST actions (selection & deletion)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve selected measurement IDs from checkboxes
    $selected_measurements = $_POST['selected_measurements'] ?? [];

    // Select All / Deselect All logic
    $selectAction = $_POST['select_action'] ?? '';

    if ($selectAction === 'all') {
        // Select every measurement currently loaded
        $selected_measurements = array_column($measurements, 'pk_measurement');
    } elseif ($selectAction === 'none') {
        // Clear all selections
        $selected_measurements = [];
    }

    // Delete selected or all measurements
    $deleteType = $_POST['delete_type'] ?? '';

    if ($deleteType === 'selected' && !empty($selected_measurements)) {

        // Build placeholder list (?, ?, ?) for safe IN() query
        $placeholders = implode(',', array_fill(0, count($selected_measurements), '?'));

        // Delete only selected measurements
        $stmt = $conn->prepare(
            "DELETE FROM measurement
             WHERE pk_measurement IN ($placeholders)"
        );
        $stmt->execute($selected_measurements);

        $selected_measurements = [];

    } elseif ($deleteType === 'all' && $station_sn) {

        // Delete all measurements for the selected station
        $stmt = $conn->prepare(
            "DELETE FROM measurement
             WHERE fk_station_records = ?"
        );
        $stmt->execute([$station_sn]);

        $selected_measurements = [];
    }


    // Redirect to preserve GET filters after POST (prevents resubmission)
    header(
        "Location: station_measurements.php?station_sn=" . urlencode($station_sn) .
        "&start_datetime=" . urlencode($start_datetime) .
        "&end_datetime=" . urlencode($end_datetime)
    );
    exit;
}


// Render page (header, view, footer)
require_once __DIR__ . '/includes/header.php';              // Page header & navigation
require_once __DIR__ . '/pages/station_measurements_view.php'; // Main content view
require_once __DIR__ . '/includes/footer.php';              // Page footer
?>