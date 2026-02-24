<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    $carAvailable = ["Volvo", "Saab", "Mercedes", "Audi", "Dacia"];
    if (isset($_GET["cars"], $_GET["fname"], $_GET["lname"])) {
        if (!isset($carAvailable[$_GET["cars"]])) {
            print("HA HA GOTCHA HACKER!");
            die();
        }
        print("User" . $_GET["fname"] . "loves" . $_GET["cars"]);
    }
    ?>
    <form method="POST">
        <label for="fname">First name:</label><br>
        <input type="text" id="fname" name="fname"><br>
        <label for="lname">Last name:</label><br>
        <input type="text" id="lname" name="lname"><br>
        <select name="cars" id="cars">
            Pick your car:
            <?php
            foreach ($carAvailable as $i => $car) {
            ?>
                <option value="<?= $i ?>"> <?= $car ?></option>
            <?php
            }
            ?>
        </select><br>
        <input type="submit" value="Submit">
    </form>
</body>

</html>