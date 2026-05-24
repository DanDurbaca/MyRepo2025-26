<?php
include_once("comCode.php");
NavigationBar("home");
 
$temperature = $_POST['temperature'];
$humidity    = $_POST['humidity'];
$pressure    = $_POST['pressure'];
$light       = $_POST['light'];
$gas         = $_POST['gas'];
$station     = $_POST['station_serial'];
 
$sqlInsert = $conn->prepare("INSERT INTO measurement (timestamp, temperature, humidity, airPressure, lightIntensity, airQuality, station) VALUES (?,?,?,?,?,?,?)");
$sqlInsert->bind_param("siiiiii", $_POST['timestamp'], $temperature, $humidity, $pressure, $light, $gas, $station);
$sqlInsert->execute();
?>