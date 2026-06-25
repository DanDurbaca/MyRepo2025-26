<?php
session_start();

if (!isset($_SESSION["Country"])) $_SESSION["Country"] = -1;

if (!isset($_SESSION["City"])) $_SESSION["City"] = -1;

if (!isset($_SESSION["OrderByName"])) $_SESSION["OrderByName"] = 0;
if (!isset($_SESSION["OrderByAge"])) $_SESSION["OrderByAge"] = 0;


if (isset($_POST["OrderByName"])) $_SESSION["OrderByName"] = $_POST["OrderByName"];

if (isset($_POST["OrderByAge"])) $_SESSION["OrderByAge"] = $_POST["OrderByAge"];


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

            <tr>
                <th>
                    <form method="POST">
                        <select name="OrderByName" onchange="this.form.submit()">
                            <option value=0>No order</option>
                            <option value=1 <?= $_SESSION["OrderByName"] == 1 ? "selected" : "" ?>>ASC</option>
                            <option value=2 <?= $_SESSION["OrderByName"] == 2 ? "selected" : "" ?>>DESC</option>
                        </select>
                    </form>
                </th>
                <th>

                    <form method="POST">
                        <select name="OrderByAge" onchange="this.form.submit()">
                            <option value=0>No order</option>
                            <option value=1 <?= $_SESSION["OrderByAge"] == 1 ? "selected" : "" ?>>ASC</option>
                            <option value=2 <?= $_SESSION["OrderByAge"] == 2 ? "selected" : "" ?>>DESC</option>
                        </select>
                    </form>

                </th>
            </tr>

            <?php
            $sql = "Select * from Ppl where CityId = ?";

            if ($_SESSION["OrderByName"] == 1) $sql = $sql . " ORDER BY PersonName ASC";
            if ($_SESSION["OrderByName"] == 2) $sql = $sql . " ORDER BY PersonName DESC";

            $sqlC = " ORDER BY ";
            if ($_SESSION["OrderByName"] != 0) $sqlC = " , ";

            if ($_SESSION["OrderByAge"] == 1) $sql = $sql . $sqlC . "  Age ASC";
            if ($_SESSION["OrderByAge"] == 2) $sql = $sql . $sqlC . "  Age DESC";


            $SqlSelectCities = $connection->prepare($sql);
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