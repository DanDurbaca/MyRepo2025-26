<?php

$f = fopen("test.csv", "r");
$l0 = 0;
$l1 = 0;
$l2 = 0;
$l3 = 0;

$arr = [];

while (!feof($f)) {
    $l = fgets($f);
    $elems = explode(",", $l);
    for ($i = 0; $i < count($elems); $i++) {
        if (isset($arr["x"]))
            $arr["x"]++;
        else $arr["x"] = 1;
    }
}
fclose($f);


$f = fopen("test.csv", "r");
$x0 = 0;
$x1 = 0;
$x2 = 0;
$x3 = 0;




?>


<!doctype html>
<html lang="en" xmlns:mso="urn:schemas-microsoft-com:office:office" xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">

<head>

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

    <h2>Task 1:</h2>

    <form>
        <select name="numLines">
            <option value="0" <?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "0") {
                                    print "selected";
                                }  ?>>Show line 0</option>

            <option value="1" <?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "1") {
                                    print "selected";
                                }  ?>>Show line 1</option>

            <option value="2" <?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "2") {
                                    print "selected";
                                }  ?>>Show line 2</option>

            <option value="3" <?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "3") {
                                    print "selected";
                                }  ?>>Show line 3</option>
        </select>
        <input type="submit" value="Display" />
    </form>


    <table>
      
    
    </table>

    <h2>Task 2:</h2>

    <form>
        <input type="submit" name="showAll" value="Show statistics for all lines" />
    </form>

</body>

</html>