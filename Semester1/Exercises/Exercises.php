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
Ex 3: 
Write a program that will display in HTML all the ODD numbers between 200 and 250. 
*/

    for ($i = 200; $i <= 250; $i++) {
        if ($i % 2 == 1)
            print($i . "<br>");
    }
    print("Next exercise<br>");
    /*
Ex 4: 
Write a program that will display all integer numbers between 200 and 300 and will only highlight (in red color)
the ones that are divisible by 5. 
    */
    for ($i = 200; $i <= 300; $i++) {
        if ($i % 5 == 0) {
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



</body>

</html>