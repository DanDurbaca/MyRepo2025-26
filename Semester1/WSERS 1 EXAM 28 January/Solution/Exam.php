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
    function displayStats($lineNumber)
    {

        $f = fopen("test.csv", "r");
        $curLine = 0;
        $distinctValues = [];
        while (!feof($f)) {
            $line = fgets($f);
            if ($lineNumber == $curLine) {
                $arrItems = explode(",", $line);
                for ($i = 0; $i < count($arrItems); $i++) {
                    $curValue = trim($arrItems[$i]);
                    if (!isset($distinctValues[$curValue])) {
                        $distinctValues[$curValue] = 1;
                    } else {
                        $distinctValues[$curValue]++;
                    }
                }
                break;
            }
            $curLine++;
        }
        fclose($f);

        print("<br>The number of unique values on line " . $lineNumber . " is " . count($distinctValues));
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

    ?>

        <br><br>
        <table>
            <tr>
                <?php
                foreach ($distinctValues as $key => $value) {
                ?>

                    <td <?php if ($value == $minFrValue) print("class = 'blue'");
                        if ($value == $maxFrValue) print("class = 'red'"); ?>> <?= $key . "..." . $value  ?></td>
                <?php }
                ?>
            </tr>
        </table>
    <?php
    }
    ?>
    <h2>Task 1:</h2>
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
        displayStats($_GET["numLines"]);
    } ?>

    <h2>Task 2:</h2>
    <form>
        <input type="submit" name="showAll" value="Show statistics for all lines">
    </form>
    <?php
    if (isset($_GET["showAll"])) {
        for ($i = 0; $i < $nLines; $i++) {
            displayStats($i);
        }
    }
    ?>

</body>

</html>