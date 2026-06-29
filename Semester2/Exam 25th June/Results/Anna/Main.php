<?php
session_start();
$connection = new mysqli("localhost", "root", "", "Ppl");
$sqlQuery = $connection->prepare("SELECT * FROM Countries;");
$sqlQuery->execute();
$result = $sqlQuery->get_result();
while (($row = $result->fetch_assoc())) {
    $CountryId = $row["CountryId"];
    $CountryName = $row["CountryName"];
    $_SESSION["CountryId"] = $CountryId;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="POST">
        <select name="Country" onchange="this.form.submit()">
            <option>Please select a country</option>
            <?php
            $sqlQuery = $connection = new mysqli("localhost", "root", "", "Ppl");
            $sqlQuery = $connection->prepare("SELECT * FROM Countries;");
            $sqlQuery->execute();
            $result = $sqlQuery->get_result();
            while ($row = $result->fetch_assoc()) {
                $_SESSION["CountryId"] = $CountryId;
            ?>
                <option><?= $row["CountryName"] ?></option>
            <?php
            }
            ?>
        </select>
    </form>
    
    <form>
        <select name="Cities" onchange="this.form.submit()">
            <option>Please select a city</option>
            <?php
             $sqlQuery = $connection = new mysqli("localhost", "root", "", "Ppl");
            $sqlQuery = $connection->prepare("SELECT * FROM Cities;");
            $sqlQuery->execute();
            $result = $sqlQuery->get_result();
            while ($row = $result->fetch_assoc()) {
            ?>
                <option><?= $row["CityName"] ?></option>
            <?php
            }
            ?>
        </select>
    </form>
        <form>
            <table>
                <tbody>
                    <tr>
            <?php
             $sqlQuery = $connection = new mysqli("localhost", "root", "", "Ppl");
            $sqlQuery = $connection->prepare("SELECT * FROM Ppl;");
            $sqlQuery->execute();
            $result = $sqlQuery->get_result();
            while ($row = $result->fetch_assoc()) {
            ?>
                    
                    <td><?= $row["PersonName"] ?></td> 
                    <td><?= $row["Age"] ?></td>
                
            <?php
            }
            ?>
                    <tr>
        </tbody>
            </table>
        </form>
</body>

</html>