<?php
include "queries.php";
session_start();
if(isset($_SESSION["username"])){
    $username = $_SESSION["username"]; //login check
}
else{
    header("Location:login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "navbar.php"; ?>
</head>
<body>
    <link rel="stylesheet" href="mystyle.css">
    <?php
        print("<h1>Here are the measurements, $username</h1>");
    ?>
    <form method="POST">
        <select name = "station">
            <?php $sql = $conn -> prepare("SELECT * FROM station WHERE fk_user_owns = ?"); //check stations that current user owns
            $sql -> bind_param("s",$username);
            $sql -> execute();
            $result = $sql -> get_result();
            if(mysqli_num_rows($result) > 0){
                while($rows = $result -> fetch_assoc()){
                    print("<option value=".$rows["pk_serialNumber"].">".$rows["name"]."</option>");
                }
            }?>
        </select>
        <input type = "Submit" name = "Showmeasure" value = "Show Measurements">
    </form>
    <?php
    if($_SERVER["REQUEST_METHOD"]=="POST" || isset($_SESSION['stationId'])){
        if(isset($_SESSION['stationId'])){
            $id = $_SESSION['stationId'];
        }
        else{
            $id = $_POST["station"];
            $_SESSION['stationId'] = $id;
        }    
        $query = "SELECT pk_measurement, timestamp,temperature,humidity,light,gas
                FROM measurement 
                WHERE fk_station_records = ?"; //get measurements for selected station
        if(isset($_GET["filter"])){ //check for sorting
            $query .= " ORDER BY timestamp DESC";
        }
        $sql = $conn -> prepare($query);
        $sql -> bind_param("s",$id);
        $sql -> execute();
        $result = $sql->get_result();
                if(mysqli_num_rows($result)>0){
                    print("<table>");
                    print("
                        <tr><th>Measurement</th>
                        <th><a href='measurements.php?filter=1'>Timestamp</a></th>
                        <th>Temperature</th><th>Humidity</th>
                        <th>Light Intensity</th>
                        <th>Air Quality</tr>");
                    while($row = mysqli_fetch_assoc($result)){ //output measurements
                        print("<tr>");
                        print("<td>".$row["pk_measurement"]."</td>"."<td>".$row["timestamp"]."</td>"."<td>".$row["temperature"]."</td>"."<td>".$row["humidity"]."</td>"."<td>".$row["light"]."</td>"."<td>".$row["gas"]."</td>");
                        print("</tr>");
                    }
                    print("</table>");
                    print("<br></br>");
                }
        }
    ?>
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>