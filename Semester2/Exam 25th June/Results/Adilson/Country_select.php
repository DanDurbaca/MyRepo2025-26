<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    session_start();
    $connection = new mysqli("localhost", "root", "", "Ppl");

    if (!isset($_SESSION["selectedCountry"])) {
        $_SESSION["selectedCountry"] = "notselected";
    }
    if (isset($_POST["country"])) {
        $_SESSION["selectedCountry"] = $_POST["country"];
    }

    if (!isset($_SESSION["selectedcity"])) {
        $_SESSION["selectedcity"] = "notselected";
    }
    if (isset($_POST["cities"])) {
        $_SESSION["selectedcity"] = $_POST["cities"];
    }

    if (!isset($_SESSION["nameorder"])) {
        $_SESSION["nameorder"] = "noorder";
    }
    if (isset($_POST["nameorder"])) {
        $_SESSION["nameorder"] = $_POST["nameorder"];
    }

    if (!isset($_SESSION["ageorder"])) {
        $_SESSION["ageorder"] = "noorder";
    }
    if (isset($_POST["ageorder"])) {
        $_SESSION["ageorder"] = $_POST["ageorder"];
    }


    ?>
    <form method="POST">
        <select name=country onchange="this.form.submit()">
            <option value="notselected" <?php if ($_SESSION["selectedCountry"] == "notselected") print "selected"; ?>>Please select a country</option>
            <?php
            $sqlCountry = $connection->prepare("SELECT * FROM Countries");
            $sqlCountry->execute();
            $result = $sqlCountry->get_result();

            while ($row = $result->fetch_assoc()) {
            ?>
                <option value="<?= $row["CountryId"] ?>" <?php if ($_SESSION["selectedCountry"] == $row["CountryId"]) print "selected"; ?>> <?= $row["CountryName"] ?> </option>

            <?php
            }
            ?>
        </select>

        <?php
        if ($_SESSION["selectedCountry"] != "notselected") {

            $sqlCity = $connection->prepare("SELECT * FROM Cities where CountryId = ?");
            $sqlCity->bind_param("i", $_SESSION["selectedCountry"]);
            $sqlCity->execute();
            $result2 = $sqlCity->get_result();
        ?>
            <select name="cities" onchange="this.form.submit()">
                <option value="notselected" <?php if ($_SESSION["selectedcity"] == "notselected") print "selected"; ?>>Please select a city</option>
                <?php
                while ($row2 = $result2->fetch_assoc()) {
                ?>
                    <option value="<?= $row2["CityId"] ?>" <?php if ($_SESSION["selectedcity"] == $row2["CityId"]) print "selected"; ?>> <?= $row2["CityName"] ?> </option>
                <?php
                }
                ?>
            </select>
            <?php

            if ($_SESSION["selectedcity"] != "notselected") {
            ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Age</th>
                        </tr>
                        <tr>
                            <th><select name="nameorder" onchange="this.form.submit()">
                                <option value="noorder" <?php if ($_SESSION["nameorder"] == "noorder") print "selected"; ?>>No Order</option>
                                <option value="ASC" <?php if ($_SESSION["nameorder"] == "ASC") print "selected"; ?>>Ascending</option>
                                <option value="DESC" <?php if ($_SESSION["nameorder"] == "DESC") print "selected"; ?>>Descending</option>
                            </select></th>

                            <th><select name="ageorder" onchange="this.form.submit()">
                                 <option value="noorder" <?php if ($_SESSION["ageorder"] == "noorder") print "selected"; ?>>No Order</option>
                                 <option value="ASC" <?php if ($_SESSION["ageorder"] == "ASC") print "selected"; ?>>Ascending</option>
                                <option value="DESC" <?php if ($_SESSION["ageorder"] == "DESC") print "selected"; ?>>Descending</option>
                            </select></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sqlpeople = $connection->prepare("SELECT * FROM Ppl where CityId = ?");
                        $sqlpeople->bind_param("i", $_SESSION["selectedcity"]);
                        $sqlpeople->execute();
                        $result3 = $sqlpeople->get_result();
                        while ($row3 = $result3->fetch_assoc()) {
                        ?>
                            <tr>
                                <td><?= $row3["PersonName"] ?></td>
                                <td><?= $row3["Age"] ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>



        <?php
            }
        }
        ?>

    </form>


</body>

</html>