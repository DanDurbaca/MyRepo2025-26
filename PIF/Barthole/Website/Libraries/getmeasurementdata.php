<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Measurement</title>
</head>

<body>
    <?php
    include("conndb.php");
    $temperature = $_POST['temperature'];
    $humidity    = $_POST['humidity'];
    $pressure    = $_POST['pressure'];
    $light       = $_POST['light'];
    $gas         = $_POST['gas'];
    $station     = $_POST['station_serial'];
    $timestamp   = $_POST['timestamp'];
    $sqlInsert = $conn->prepare("INSERT INTO measurements (temperature, humidity, pressure, light, gas, timestamp, fk_station_records) VALUES (?,?,?,?,?,?,?)");
    $sqlInsert->bind_param("dddddss", $temperature, $humidity, $pressure, $light, $gas, $timestamp, $station);
    $sqlInsert->execute();
    ?>
</body>

</html>