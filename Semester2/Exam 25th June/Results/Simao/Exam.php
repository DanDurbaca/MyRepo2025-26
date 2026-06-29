<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WSERS Exam</title>
</head>

<body>

    <?php $connection = new mysqli("localhost", "root", "", "Ppl"); ?>

    <form method="GET">
        <select name="country" onchange="this.form.submit()">
            <option>Please select a country</option>
            <?php
            $sql = $connection->prepare("SELECT CountryName, CountryId FROM Countries");
            $sql->execute();
            $result = $sql->get_result();
            $row = $result->fetch_assoc();
            if (!isset($_GET["country"])) {
                while ($row = $result->fetch_assoc()) {

            ?>
                    <option value="<?= $row["CountryId"] ?>">
                        <?= $row["CountryName"] ?>
                    </option>
            <?php

                }
                $_SESSION["currentCountry"] = $row["CountryId"] /*or $_GET["country"]*/ ;
            }

            ?>
        </select>
    </form>

    <form method="GET">
        <select name="city" onchange="this.form.submit()">
            <option>Please select a city</option>
            <?php
            $sql = $connection->prepare("SELECT CityName, CityId FROM Cities JOIN Countries using (CountryId)");
            $sql->execute();
            $result = $sql->get_result();
            $row = $result->fetch_assoc();

            if (/*isset($_SESSION["currentCountry"]) && */!isset($_GET["city"])) {
                while ($row = $result->fetch_assoc()) {

            ?>
                    <option value="<?= $row["CityId"] ?>">
                        <?= $row["CityName"] ?>
                    </option>

            <?php

                }
                $_SESSION["currentCity"] = $row["CityId"] /*or $_GET["country"]*/ ;
            }
            ?>
        </select>
    </form>

    <form method="GET">
        <table>
            <?php
            $sql = $connection->prepare("SELECT PersonName, PersonId FROM Ppl JOIN Cities using (CityId)");
            $sql->execute();
            $result = $sql->get_result();
            $row = $result->fetch_assoc();
            ?>

            <select>
                <option>

                </option>
            </select>
        </table>
    </form>
</body>

</html>