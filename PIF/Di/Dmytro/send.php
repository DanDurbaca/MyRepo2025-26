<!DOCTYPE html>
<html>
<head>
</head>
<body>
<form action = "receive.php" method="POST">
    <h2>Input Serial Number:<input type = "text" name = "station_serial" value = ""></h2>
    <h2>Input timestamp:<input type = "datetime" name = "timestamp" value = ""></h2>
    <h2>Input pressure:<input type = "number" name = "pressure" value = ""></h2>
    <h2>Input temperature:<input type = "number" name = "temperature" value = ""></h2>
    <h2>Input humidity:<input type = "number" name = "humidity" value = 0></h2>
    <h2>Input gas:<input type = "number" name = "gas" value = ""></h2>
    <h2>Input light:<input type = "text" name = "light" value = ""></h2>
    <input type = "submit" name = "Send" Value = "Send">
</form>
</body>
</html>