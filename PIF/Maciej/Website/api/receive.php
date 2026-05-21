<?php

require_once '../config/database.php';

$conn = getDbConnection();

// Get POST values
$station_serial = trim($_POST['station_serial'] ?? '');
$timestamp      = trim($_POST['timestamp'] ?? '');
$temperature    = trim($_POST['temperature'] ?? '');
$humidity       = trim($_POST['humidity'] ?? '');
$pressure       = trim($_POST['pressure'] ?? '');
$light          = trim($_POST['light'] ?? '');
$gas            = trim($_POST['gas'] ?? '');

$stmt = $conn->prepare("SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?");
$stmt->execute([$station_serial]);

$station = $stmt->fetch();
if (!$station) {
    die("Error: Station '$station_serial' does not exist.");
}


// Display received data (for testing)
echo "<h2>Received Data</h2>";

echo "Station Serial: $station_serial <br>";
echo "Timestamp: $timestamp <br>";
echo "Temperature: $temperature <br>";
echo "Humidity: $humidity <br>";
echo "Pressure: $pressure <br>";
echo "Light: $light <br>";
echo "Gas: $gas <br>";


try {
    $stmt = $conn->prepare("
        INSERT INTO measurement
        (temperature, humidity, pressure, light, gas, timestamp, fk_station_records)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $temperature,
        $humidity,
        $pressure,
        $light,
        $gas,
        $timestamp,
        $station_serial
    ]);

    echo "<br><strong>Data inserted successfully!</strong>";

} catch(PDOException $e) {

    echo "<br><strong>Database Error:</strong> " . $e->getMessage();

}
?>