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
    <h2>Task 1:</h2>
    <form method="POST">
        <select name="numLines">
            <?php
            $lines = 0;
            $arr = [];

            $ftest = fopen("test.csv", "r");
            while (!feof($ftest)) {
                $data = fgets($ftest);
                $elements = explode(",", $data);
                $lines++;
                foreach ($element as $i) {
                    if (isset($arr[$i])) {
                        $arr[$i]++;
                    } else {
                        $arr[$i] = 1;
                    }
                }
            ?>
                <option value="0">Show line <?= $lines ?></option>

            <?php
            }
            ?>
        </select>
        <input type="submit" value="Display" />
    </form>
        <?php
        var_dump($arr);
        ?>
</body>

</html>