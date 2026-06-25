<?php
session_start();

if (!isset($_SESSION["Country"])) $_SESSION["Country"] = -1;

if (!isset($_SESSION["City"])) $_SESSION["City"] = -1;

if (isset($_POST["Country"])) $_SESSION["Country"] = $_POST["Country"];

if (isset($_POST["City"])) $_SESSION["City"] = $_POST["City"];

if ($_SESSION["Country"] == -1) $_SESSION["City"] = -1;


$connection = mysqli_connect("localhost", "root", "", "Ppl");
if (!$connection) {
    die("Error creating connection");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>People and countries:</title>
</head>

<body>
    <form method="POST">
        <select name="Country" onchange="this.form.submit()">
            <option value=-1>Please select a country</option>
            <?php
            $SqlSelectCountries = $connection->prepare("Select * from Countries");
            $SqlSelectCountries->execute();
            $result = $SqlSelectCountries->get_result();
            while ($row = $result->fetch_assoc()) {
            ?>
                <option value=<?= $row["CountryId"] ?> <?= $_SESSION["Country"] == $row["CountryId"] ? "selected" : "" ?>><?= $row["CountryName"] ?></option>
            <?php
            }
            ?>
        </select>
    </form>
    <?php
    if ($_SESSION["Country"] != -1) {
    ?>
        <form method="POST">
            <select name="City" onchange="this.form.submit()">
                <option value=-1>Please select a city</option>
                <?php
                $SqlSelectCities = $connection->prepare("Select * from Cities where CountryId = ?");
                $SqlSelectCities->bind_param("i", $_SESSION["Country"]);
                $SqlSelectCities->execute();
                $result = $SqlSelectCities->get_result();
                while ($row = $result->fetch_assoc()) {
                ?>
                    <option value=<?= $row["CityId"] ?> <?= $_SESSION["City"] == $row["CityId"] ? "selected" : "" ?>><?= $row["CityName"] ?></option>
                <?php
                }
                ?>
            </select>
        </form>
    <?php
    }
    ?>

    <?php
    if ($_SESSION["City"] != -1) {
    ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Age</th>
            </tr>
            <?php
            $SqlSelectCities = $connection->prepare("Select * from Ppl where CityId = ?");
            $SqlSelectCities->bind_param("i", $_SESSION["City"]);
            $SqlSelectCities->execute();
            $result = $SqlSelectCities->get_result();
            while ($row = $result->fetch_assoc()) {
            ?>
                <tr>
                    <td><?= $row["PersonName"] ?></td>
                    <td><?= $row["Age"] ?></td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php
    }
    ?>



</body>

</html>