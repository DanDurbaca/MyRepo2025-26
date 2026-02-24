<?php
session_start();
?>
<!doctype html>
<html lang="en" xmlns:mso="urn:schemas-microsoft-com:office:office" xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
</head>
<?php
$bShowForm = true;
if (isset($_POST["0"], $_POST["1"], $_POST["2"], $_POST["3"])) {
    $bShowForm = false;
}
?>
<?php
if ($bShowForm) {
?>

    <body>
        <h2>Task 1:</h2>
        <form>
            <select name="numLines">
                <option value="0">Show line 0</option>
                <option value="1">Show line 1</option>
                <option value="2" selected>Show line 2</option>
                <option value="3">Show line 3</option>
            </select>
            <input type="submit" value="Display" />
        </form>
    <?php
}
    ?>
    <?php
    $fHandler = fopen("test.csv", "r");
    while (!feof($fHandler)) {
        $oneUser = fgets($fHandler);
        $individuallettre = explode(";", $x);
    }
    ?>
    <?php

    $x = [];

    if (isset($x["x"])) {
        $x["x"]++;
    } else {
        $x["x"] = 1;
    };

    $y = [];
    if (isset($y["y"])) {
        $y["y"]++;
    } else {
        $y["y"] = 1;
    };

    $z = [];

    if (isset($z["z"])) {
        $z["z"]++;
    } else {
        $z["z"] = 1;
    };
    $t = [];

    if (isset($t["t"])) {
        $t["t"]++;
    } else {
        $t["t"] = 1;
    };
    $w = [];

    if (isset($w["w"])) {
        $w["w"]++;
    } else {
        $w["w"] = 1;
    };
    ?>
    <h2>Task 2:</h2>
    <form>
        <input type="submit" name="showAll" value="Show statistics for all lines" />
    </form>
    </body>

</html>