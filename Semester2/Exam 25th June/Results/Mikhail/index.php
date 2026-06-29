<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<?php
session_start();
$connection = new mysqli("localhost", "root", "", "ppl");
if (isset($_GET["country"])) {
    $_SESSION["country"] = $_GET["country"];
}
if (!isset($_SESSION["country"])) {
    $_SESSION["country"] = "default";
}
if (isset($_GET["city"])) {
    $_SESSION["city"] = $_GET["city"];
}
if (!isset($_SESSION["city"])) {
    $_SESSION["city"] = "default";
}
if (isset($_GET["nameorderby"])) {
    $_SESSION["nameorderby"] = $_GET["nameorderby"];
}
if (!isset($_SESSION["nameorderby"])) {
    $_SESSION["nameorderby"] = "no";
}
if (isset($_GET["ageorderby"])) {
    $_SESSION["ageorderby"] = $_GET["ageorderby"];
}
if (!isset($_SESSION["ageorderby"])) {
    $_SESSION["ageorderby"] = "no";
}
?>

<body>
    <form>
        <select name="country" onchange="this.form.submit()">
            <option value="default">Please select a country</option>
            <?php
            $sqlQuery = $connection->prepare("select * from countries");
            $sqlQuery->execute();
            $result = $sqlQuery->get_result();
            while ($row = $result->fetch_assoc()) {
            ?>
                <option value="<?= $row["CountryId"] ?>" <?= ($_SESSION["country"] == $row["CountryId"]) ? "selected" : "" ?>><?= $row["CountryName"] ?></option>
            <?php } ?>
        </select>
    </form>
    <?php
    if ($_SESSION["country"] != "default") {
    ?>
        <form>
            <select name="city" onchange="this.form.submit()">
                <option value="default">Please select a city</option>
                <?php
                $sqlQuery = $connection->prepare("select * from cities where countryid=?");
                $sqlQuery->bind_param("i", $_SESSION["country"]);
                $sqlQuery->execute();
                $result = $sqlQuery->get_result();
                while ($row = $result->fetch_assoc()) {
                ?>
                    <option value="<?= $row["CityId"] ?>" <?= ($_SESSION["city"] == $row["CityId"]) ? "selected" : "" ?>><?= $row["CityName"] ?></option>
                <?php } ?>
            </select>
        </form>
        <?php
        if ($_SESSION["city"] != "default") {
        ?>
            <table>
                <tr>
                    <th>Name
                        <form>
                            <select name="nameorderby" onchange="this.form.submit()">
                                <option value="no" <?= ($_SESSION["nameorderby"] == "no") ? "selected" : "" ?>>No order</option>
                                <option value="ASC" <?= ($_SESSION["nameorderby"] == "ASC") ? "selected" : "" ?>>ASC</option>
                                <option value="DESC" <?= ($_SESSION["nameorderby"] == "DESC") ? "selected" : "" ?>>DESC</option>
                            </select>
                        </form>
                    </th>
                    <th>Age
                        <form>
                            <select name="ageorderby" onchange="this.form.submit()">
                                <option value="no" <?= ($_SESSION["ageorderby"] == "no") ? "selected" : "" ?>>No order</option>
                                <option value="ASC" <?= ($_SESSION["ageorderby"] == "ASC") ? "selected" : "" ?>>ASC</option>
                                <option value="DESC" <?= ($_SESSION["ageorderby"] == "DESC") ? "selected" : "" ?>>DESC</option>
                            </select>
                        </form>
                    </th>
                </tr>
                <?php
                if ($_SESSION["nameorderby"] == "no" && $_SESSION["ageorderby"] == "no") {
                    $sqlQuery = $connection->prepare("select * from ppl where cityid=?");
                    $sqlQuery->bind_param("i", $_SESSION["city"]);
                } else if ($_SESSION["ageorderby"] == "no") {
                    if ($_SESSION["nameorderby"] == "ASC") {
                        $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by personname ASC");
                    } else {
                        $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by personname DESC");
                    }
                    $sqlQuery->bind_param("i", $_SESSION["city"]);
                } else if ($_SESSION["nameorderby"] == "no") {
                    if ($_SESSION["ageorderby"] == "ASC") {
                        $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by age ASC");
                    } else {
                        $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by age DESC");
                    }
                    $sqlQuery->bind_param("i", $_SESSION["city"]);
                } else {
                    if ($_SESSION["nameorderby"] == "ASC"){
                        if($_SESSION["ageorderby"]=="ASC"){
                            $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by personname ASC, age ASC");
                        }
                        else{
                            $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by personname ASC, age DESC");
                        }
                    }
                    else{
                        if($_SESSION["ageorderby"]=="ASC"){
                            $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by personname DESC, age ASC");
                        }
                        else{
                            $sqlQuery = $connection->prepare("select * from ppl where cityid=? order by personname DESC, age DESC");
                        }
                    }
                    $sqlQuery->bind_param("i", $_SESSION["city"]);
                }
                $sqlQuery->execute();
                $result = $sqlQuery->get_result();
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
    }
    ?>
</body>

</html>