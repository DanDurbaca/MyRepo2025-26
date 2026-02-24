<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .blue {
            background-color: lightblue;
        }

        .red {
            background-color: lightpink;
        }
    </style>
</head>

<body>
    <?php
    $f = fopen("test.csv", "r");
    $countLines = 0;
    while (!feof($f)) {
        $countLines++;
        fgets($f);
    }
    print($countLines);
    ?>
    <form>
        <select name="mySelect">
            <?php
            for ($i = 0; $i < $countLines; $i++) {
            ?>
                <option value="<?= $i ?>" <?php if (isset($_GET["mySelect"])) if ($_GET["mySelect"] == $i) print("selected"); ?>> Show line <?= $i ?></option>
            <?php
            }
            ?>
        </select>
        <input type="submit" value="Display">
    </form>

    <?php
    if (isset($_GET["mySelect"])) {
        $arr = [];

        $f = fopen("test.csv", "r");
        $lineNum = 0;
        while (!feof($f)) {
            $line = fgets($f);
            if ($lineNum == $_GET["mySelect"]) {
                // compute statistics for this line !!!!!
                $lineArr = explode(",", $line);
                for ($i = 0; $i < count($lineArr); $i++) {
                    $oneCharacter = trim($lineArr[$i]);

                    if (isset($arr[$oneCharacter]))
                        $arr[$oneCharacter]++;
                    else
                        $arr[$oneCharacter] = 1;
                }
            }
            $lineNum++;
        }
    }

    print("Total number of items on line " . $_GET["mySelect"] . " is " . count($arr));

    $minValue = 1000000;
    $maxValue = 0;

    foreach ($arr as $key => $value) {
        if ($value < $minValue) {
            $minValue = $value;
            $keyMinValue = $key;
        }

        if ($value > $maxValue) {
            $maxValue = $value;
            $keyMaxValue = $key;
        }
    }

    print("<br>The most frequent value in the csv is " . $keyMaxValue);
    print("<br>The least frequent value in the csv is " . $keyMinValue);
    ?>
    <table>
        <tr>
            <?php
            foreach ($arr as $key => $value) {
            ?>
                <td <?php if ($value == $minValue) print("class='blue'");
                    if ($value == $maxValue) print("class='red'"); ?>><?= $key . "..." . $value ?> </td>
            <?php
            }
            ?>
        </tr>
    </table>

</body>

</html>