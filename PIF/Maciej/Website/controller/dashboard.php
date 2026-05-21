<?php
// controller/dashboard.php
// Controller for the main dashboard page, fetches user-specific data

require_once __DIR__ . '/../config/database.php';   // Database connection
require_once __DIR__ . '/../includes/auth_check.php'; // Ensure user is logged in
require_once __DIR__ . '/../includes/functions.php';  // Helper functions for DB queries

$conn = getDbConnection(); // Initialize PDO connection

// Retrieve session data for the logged-in user
$username = $_SESSION['username'];                // Username of logged-in user
$role = $_SESSION['role'] ?? 'User';             // User role, default to 'User'
$firstName = $_SESSION['firstName'] ?? $username; // First name for greetings or fallback to username

$is_admin = ($role === 'Admin'); // Boolean flag to check if user is admin

$stations = []; // Array to hold user's stations
$dashboardData = []; // Array to hold latest measurements for each station
$error = null;  // Error message container

try {
    // Fetch stations owned by current user, sorted by name
    $stmt = $conn->prepare("
        SELECT pk_serialNumber, name, description
        FROM station
        WHERE fk_user_owns = :username
        ORDER BY name
    ");

    $stmt->execute(['username' => $username]); // Bind current username to query
    $stations = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch results as associative array

    $outdoorTemp = null;    // Variable to hold outdoor temperature from weather API
    $weatherError = null;   // Variable to hold any errors related to fetching weather data
    try {
        $weatherJson = @file_get_contents(
            "https://api.open-meteo.com/v1/forecast?latitude=49.6116&longitude=6.1319&current_weather=true"
        );
        if ($weatherJson !== false) {
            $luxWeather = json_decode($weatherJson, true);
            $outdoorTemp = $luxWeather['current_weather']['temperature'] ?? null;
        } 
        else {
            $weatherError = "Could not fetch outdoor weather.";
        }
    } catch (Exception $e) {
        $weatherError = "Weather service unavailable.";
    }

    // Load dashboard data per station
    foreach ($stations as $station) {

        $station_sn = $station['pk_serialNumber'];

        // Latest measurement
        $stmt = $conn->prepare("
            SELECT *
            FROM measurement
            WHERE fk_station_records = :station_sn
            ORDER BY timestamp DESC
            LIMIT 1
        ");

        $stmt->execute([
            'station_sn' => $station_sn
        ]);

        $latest = $stmt->fetch(PDO::FETCH_ASSOC);


        // Last 24 measurements for graph
        $stmt = $conn->prepare("
            SELECT
                timestamp,
                temperature
            FROM measurement
            WHERE fk_station_records = :station_sn
            ORDER BY timestamp DESC
            LIMIT 24
        ");

        $stmt->execute([
            'station_sn' => $station_sn
        ]);

        $graph = array_reverse(
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );


        // Statistics
        $stmt = $conn->prepare("
            SELECT
                AVG(temperature) as avg_temp,
                MIN(temperature) as min_temp,
                MAX(temperature) as max_temp,
                COUNT(*) as total_records
            FROM measurement
            WHERE fk_station_records = :station_sn
        ");

        $stmt->execute([
            'station_sn' => $station_sn
        ]);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);


        // Status message
        $status = "✅ Conditions normal";
        if ($latest) {
            if ($latest['gas'] > 800) {
                $status = "⚠️ Poor air quality";
            } elseif ($latest['humidity'] > 70) {
                $status = "💧 High humidity";
            } elseif ($latest['temperature'] > 26) {
                $status = "🔥 Warm room";
            } else {
                $status = "✅ Conditions normal";
            }
        } 
        
        else {
            $status = "No data";
        }


        $dashboardData[$station_sn] = [
            'latest' => $latest,
            'graph'  => $graph,
            'stats'  => $stats,
            'status' => $status,
            'outdoorTemp' => $outdoorTemp
        ];
    }

} catch (PDOException $e) {

    $error = 'Failed to load dashboard.';
}

// Pass the retrieved data to the dashboard view for rendering
require __DIR__ . '/../pages/dashboard_view.php';
?>