<!DOCTYPE html>
<html lang="en">
<head>
    <title>Document</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
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

        <!-- I am sorry for this exam but you did nothing wrong as a teacher. I generally suck at coding and I prepared me more for other exams, instead of this.
         Please don't be mad at yourself, because with our website projects, you showed us how to work properly in PHP.
         Also, the website project is even more fun :) -->

    <div class="CSV">
        <h2>Task 1:</h2>
            <?php
                $csvData = fopen("test.csv", "r");
                fgets($csvData);
                while (!feof($csvData)){
                    $oneValue = fgets($csvData);
                    $oneComponent = explode(";", $oneValue);
            ?>
                         
        <form>
            <select name="numLines">
                <option value="0">Show line 0 <?= $oneComponent[2] ?></option>
                <option value="1">Show line 1 <?= $oneComponent[2] ?></option>
                <option value="2" selected>Show line 2 <?= $oneComponent[2] ?></option>
                <option value="3">Show line 3 <?= $oneComponent[2] ?></option>
            </select>
            <input type="submit" value="Display" />
        </form>
        <?
                }
            ?>
    </div>

    <h2>Task 2:</h2>
        <form>
            <input type="submit" name="showAll" value="Show statistics for all lines" />
        </form>
</body>

</html>