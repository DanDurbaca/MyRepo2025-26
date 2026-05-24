<?php
// save_data.php - This file has NO session check so the Pi can talk to it
require_once 'config/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serial = $conn->real_escape_string($_POST['station_serial']);
    $temp   = $conn->real_escape_string($_POST['temperature']);
    $hum    = $conn->real_escape_string($_POST['humidity']);
    $press  = $conn->real_escape_string($_POST['pressure']);
    $light  = $conn->real_escape_string($_POST['light']);
    $gas    = $conn->real_escape_string($_POST['gas']);
    $time   = $conn->real_escape_string($_POST['timestamp']);

    $sql = "INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records) 
            VALUES ('$temp', '$hum', '$press', '$light', '$gas', '$time', '$serial')";

    if ($conn->query($sql)) {
        echo "Data Recorded";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "DB Error: " . $conn->error;
    }
}
?>
