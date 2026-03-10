<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

</body>

<form method="GET">
    <?php
    $file = fopen("raw.csv", "r");
    while (!feof($file)) {
        $line = fgets($file);
        $lineArray = explode(",", $line);

    ?>
        <input type="checkbox" id="<?= $lineArray[0]  ?>" name="<?= $lineArray[0]  ?>" value="yes">
        <label for="<?= $lineArray[0]  ?>"> <?= $lineArray[0]  ?> </label><br>
    <?php
    }
    ?>
    <button type="submit"> Calculate calories</button>
    <?php
    $sumcalories = 0;
    $file = fopen("raw.csv", "r");
    while (!feof($file)) {
        $line = fgets($file);
        $lineArray = explode(",", $line);
        $calories = trim($lineArray[1]);
        if (isset($_GET[$lineArray[0]])) {
            $sumcalories += $calories;
        }
    }
    /* foreach ($_POST as $key => $value) {
        $sumcalories += $value;
    }
        */
    print("Your dish has " . $sumcalories . " number of calories");

    ?>
</form>

</html>