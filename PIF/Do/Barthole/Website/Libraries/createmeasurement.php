<?php
include("../Libraries/conndb.php");

$timestamp = date("Y-m-d H:i:s");
$createtemp = random_int(-70, 60);
$createhum = random_int(0, 100);
$createpress = random_int(300, 1100);
$createlight = random_int(0, 100000);
$creategas = random_int(0, 5000);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $insertmeasstmt = $conn->prepare(
        "INSERT INTO measurements 
        (fk_station_records, temperature, humidity, pressure, light, gas, timestamp)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $insertmeasstmt->bind_param(
        "sddddds",
        $_POST["stationslist"],
        $createtemp,
        $createhum,
        $createpress,
        $createlight,
        $creategas,
        $timestamp
    );

    $insertmeasstmt->execute();
    $insertmeasstmt->close();

    echo "<tr>
        <td>$timestamp</td>
        <td>$createtemp °C</td>
        <td>$createhum %</td>
        <td>$createpress hPa</td>
        <td>$createlight lx</td>
        <td>$creategas ppm</td>
    </tr>";
}
