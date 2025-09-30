<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .redColor {
            background-color: red;
        }
    </style>
</head>

<body>
    <?php
    /*
Ex 5: 
Write a program that will display all integer numbers between 200 and 300 and will highlight 
in red all non-divisible by 8 numbers. 

*/

    for ($i = 200; $i <= 300; $i++) {
        if ($i % 8 != 0) {
            /* 
            <?php print 
            is equivalent to the 
            <?= 

            */
    ?>
            <div class='redColor'> <?= $i ?> </div>
    <?php

        } else {
            print($i . "<br>");
        }
    }
    ?>

    ?>
</body>

</html>