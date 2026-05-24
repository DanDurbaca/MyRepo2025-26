<?php
// receiver.php
require_once 'config/db.php'; 

// Check if this is a POST request from the Pi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get the data from the Pi
    $serial = $conn->real_escape_string($_POST['station_serial']);
    $temp   = $conn->real_escape_string($_POST['temperature']);
    $hum    = $conn->real_escape_string($_POST['humidity']);
    $press  = $conn->real_escape_string($_POST['pressure']);
    $light  = $conn->real_escape_string($_POST['light']);
    $gas    = $conn->real_escape_string($_POST['gas']);
    $time   = $conn->real_escape_string($_POST['timestamp']);

    // Insert into the database
    $sql = "INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records) 
            VALUES ('$temp', '$hum', '$press', '$light', '$gas', '$time', '$serial')";

    if ($conn->query($sql)) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error: " . $conn->error;
    }
}
?>
