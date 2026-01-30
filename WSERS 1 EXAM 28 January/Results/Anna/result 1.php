<?php
$data = [];
//This part reads the CSV file and puts each row into the $data array
if (($handle = fopen("test.csv", "r")) !== false) {
    while (($row = fgetcsv($handle)) !== false) {
        $data[] = $row;
    }
    fclose($handle);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
</head>
<body>
    <?php
     $f = fopen("test.csv", "r");
    $a = 0;
    $b = 0;
    $c = 0;
    $d = 0;
    $e = 0;
    $t = 0;
    $z = 0;
    $y = 0;
    $w = 0;
    $x = 0;
    $num1 = 0;
    $num2 = 0;
    $num0 = 0;
    while (!feof($f)) {
        $l = fgets($f);
        $elems = explode(",", $l);
        for ($i = 0; $i < count($elems); $i++) {
            if ($elems[$i] == "r") $rt++;
            else if ($elems[$i] == "a" || $elems[$i] == " a") $a++;
            else if ($elems[$i] == "b" || $elems[$i] == " b") $b++;
            else if ($elems[$i] == "c" || $elems[$i] == " c") $c++;
            else if ($elems[$i] == "e" || $elems[$i] == " e") $e++;
            else if ($elems[$i] == "t" || $elems[$i] == " t") $t++;
            else if ($elems[$i] == "z" || $elems[$i] == " z") $z++;
            else if ($elems[$i] == "y" || $elems[$i] == " y") $y++;
            else if ($elems[$i] == "w" || $elems[$i] == " w") $w++;
            else if ($elems[$i] == "x" || $elems[$i] == " x") $x++;
            else if ($elems[$i] == "d" || $elems[$i] == " d") $d++;
            else if ($elems[$i] == "1" || $elems[$i] == " 1") $num1++;
            else if ($elems[$i] == "2" || $elems[$i] == " 2") $num2++;
            else if ($elems[$i] == "0" || $elems[$i] == " 0") $num0++;
        }
    }
    fclose($f);
    ?>
      <h2>Task 1</h2>
     <form>
            <select name="numLines">
                <option value="0"><?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "0") {
                                    print "selected "; }
                                    ?>Show line 0</option>
                <option value="1"><?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "1") {
                                    print "selected "; }
                                    ?>Show line 1</option>
                <option value="2"><?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "2") {
                                    print "selected "; }
                                    ?>Show line 2</option>
                <option value="3"><?php if (isset($_GET["numLines"]) && $_GET["numLines"] == "3") {
                                    print "selected "; }
                                    ?>Show line 3</option>
            </select>
            <input type="submit" value="Display" />
        </form>
        <h2>Task 2</h2>
         <form>
            <input type="submit" name="showAll" value="Show statistics for all lines" />
        </form>
        <h2>Count for csv</h2>
        <table>
        <tr>
            <th>a</th>
            <th>b</th>
            <th>c</th>
            <th>d</th>
            <th>e</th>
            <th>t</th>
            <th>z</th>
            <th>y</th>
            <th>w</th>
            <th>x</th>
            <th>0</th>
            <th>1</th>
            <th>2</th>
        </tr>
        <tr>
            <td><?= $a ?></td>
            <td><?= $b ?></td>
            <td><?= $c ?></td>
            <td><?= $d ?></td>
            <td><?= $e ?></td>
            <td><?= $t ?></td>
            <td><?= $z ?></td>
            <td><?= $y ?></td>
            <td><?= $w ?></td>
            <td><?= $x ?></td>
            <td><?= $num0 ?></td>
            <td><?= $num1 ?></td>
            <td><?= $num2 ?></td>
        </tr>
    </table>
</body>
</html>