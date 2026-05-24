<?php
include_once("db.php");

$conn = db_connect();

if (!$conn) {
    die("Error: Database connection failed");
}

$temperature = floatval($_POST['temperature']);
$humidity    = floatval($_POST['humidity']);
$pressure    = floatval($_POST['pressure']);
$light       = floatval($_POST['light']);
$gas         = floatval($_POST['gas']);
$station     = $conn->real_escape_string($_POST['station_serial']);
$timestamp   = $conn->real_escape_string($_POST['timestamp']);

if (empty($station) || empty($timestamp)) {
    die("Error: station_serial and timestamp are required");
}

// Verify station exists
$verify = $conn->query("SELECT st_serial FROM env_station WHERE st_serial='$station' LIMIT 1");
if (!$verify || $verify->num_rows === 0) {
    die("Error: Station '$station' does not exist");
}

$sqlInsert = $conn->prepare("INSERT INTO env_record (rec_temperature, rec_humidity, rec_pressure, rec_light, rec_gas, rec_station, rec_timestamp) VALUES (?,?,?,?,?,?,?)");
if (!$sqlInsert) {
    die("Prepare failed: " . $conn->error);
}
$sqlInsert->bind_param("dddddss", $temperature, $humidity, $pressure, $light, $gas, $station, $timestamp);
if (!$sqlInsert->execute()) {
    die("Execute failed: " . $sqlInsert->error);
}
echo "Record inserted successfully";
?>