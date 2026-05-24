<?php
// send.php
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Measurement</title>
</head>
<body>

<h2>Test Measurement Sender</h2>

<form method="POST" action="receive.php">

    <label>Station Serial:</label><br>
    <input type="text" name="station_serial"><br><br>

    <label>Timestamp:</label><br>
    <input type="datetime-local" name="timestamp"><br><br>

    <label>Temperature (°C):</label><br>
    <input type="number" step="0.01" name="temperature"><br><br>

    <label>Humidity (%):</label><br>
    <input type="number" step="0.01" name="humidity"><br><br>

    <label>Pressure (hPa):</label><br>
    <input type="number" step="0.01" name="pressure"><br><br>

    <label>Light (Lux):</label><br>
    <input type="number" step="0.01" name="light"><br><br>

    <label>Gas (ppm):</label><br>
    <input type="number" step="0.01" name="gas"><br><br>

    <button type="submit">Submit</button>

</form>

</body>
</html>