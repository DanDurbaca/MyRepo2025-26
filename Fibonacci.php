<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php

    /*
Ex 6:  
Write a program that will display the list of the first 20 Fibonacci numbers. 
Fibonacci numbers are explained here: https://www.mathsisfun.com/numbers/fibonacci-sequence.html  
*/

    $a = 0;
    $b = 1;


    for ($i = 1;; $i++) {
        $c = $a + $b;

        print($i . "   " . $a . "<br>");

        $a = $b;
        $b = $c;
        if ($a > 1000000) {
            break;
        }
    }



    ?>
</body>

</html>