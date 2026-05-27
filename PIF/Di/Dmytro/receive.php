<?php
include "queries.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <?php
        $serial = $_POST["station_serial"];
        $timestamp = $_POST["timestamp"];
        $temperature = $_POST["temperature"];
        $humidity = $_POST["humidity"];
        $light = $_POST["light"];
        $pressure = $_POST["pressure"];
        $gas = $_POST['gas'];
        $sql = "INSERT INTO measurement (temperature,humidity,pressure,light,gas,timestamp,fk_station_records) VALUES('$temperature','$humidity','$pressure','$light','$gas','$timestamp','$serial')";
        if(mysqli_query($conn,$sql)){
            print("success");
        }
        else{
            mysqli_error($sql);
        }
    ?>
</body>
</html>
