<?php
include_once("commonphp.php");

// require POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

// minimal required fields
$required = ['timestamp','temperature','humidity','pressure','light','gas','station_serial'];
foreach ($required as $f) {
    if (!isset($_POST[$f])) {
        http_response_code(400);
        echo "Missing field: $f";
        exit;
    }
}


$timestamp   = $_POST['timestamp']; 
$temperature = (int)$_POST['temperature'];
$humidity    = (int)$_POST['humidity'];
$pressure    = (int)$_POST['pressure'];
$light       = (int)$_POST['light'];
$gas         = (int)$_POST['gas'];
$station     = (int)$_POST['station_serial'];

// prepare statement 
$stmt = $conn->prepare(
    "INSERT INTO `Measurement` (timestamp_Measurement, temperature, humidity, airpressure, lightintensity, airquality, station) VALUES (?,?,?,?,?,?,?)"
);
if (!$stmt) {
    http_response_code(500);
    echo "Prepare failed: " . $conn->error;
    exit;
}

// bind and execute
if (!$stmt->bind_param("siiiiii", $timestamp, $temperature, $humidity, $pressure, $light, $gas, $station)) {
    http_response_code(500);
    echo "Bind failed: " . $stmt->error;
    $stmt->close();
    exit;
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo "Execute failed: " . $stmt->error;
    $stmt->close();
    exit;
}

$stmt->close();
http_response_code(201);
echo "OK";
?>