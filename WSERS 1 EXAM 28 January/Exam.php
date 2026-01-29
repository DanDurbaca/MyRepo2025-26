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
    $nLines = 0;
    while (!feof($f)) {
        $nLines++;
        fgets($f);
    }
    fclose($f);
    ?>
    <form>
        <select name="numLines">
            <?php
            for ($i = 0; $i < $nLines; $i++) {
            ?>
                <option value=<?= $i ?> <?php if (isset($_GET["numLines"])) if ($_GET["numLines"] == $i) print "selected"  ?>>Show line <?= $i ?> </option>
            <?php
            }
            ?>
        </select>
        <input type="submit" value="Display">
    </form>
    <?php
    if (isset($_GET["numLines"])) {
        $f = fopen("test.csv", "r");
        $curLine = 0;
        $distinctValues = [];
        while (!feof($f)) {
            $line = fgets($f);
            if ($_GET["numLines"] == $curLine) {
                $arrItems = explode(",", $line);
                for ($i = 0; $i < count($arrItems); $i++) {
                    if (!isset($distinctValues[$arrItems[$i]])) {
                        $distinctValues[$arrItems[$i]] = 1;
                    } else {
                        $distinctValues[$arrItems[$i]]++;
                    }
                }
                break;
            }
            $curLine++;
        }
        fclose($f);

        print("<br>The number of unique values on line " . $i . " is " . count($distinctValues));
        $minFrValue = 100000000;
        $minKey = "";

        $maxFrValue = 0;
        $maxKey = "";

        foreach ($distinctValues as $key => $value) {
            if ($value < $minFrValue) {
                $minFrValue = $value;
                $minFrKey = $key;
            }

            if ($value > $maxFrValue) {
                $maxFrValue = $value;
                $maxFrKey = $key;
            }
        }
        print("<br>The least frequent value on this line is " . $minFrKey);
        print("<br>The most frequent value on this line is " . $maxFrKey);
    }
    ?>

    <table>
        <?php
        foreach ($distinctValues as $key => $value) {
        ?>
            <tr>
                <td <?php if ($key == $minFrKey) print("class = 'blue'");
                    if ($key == $maxFrKey) print("class = 'red'"); ?>> <?= $key . "..." . $value  ?></td>
            </tr>
        <?php } ?>
    </table>

</body>

</html>