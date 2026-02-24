<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
    <style>
    </style>
</head>

<body>

    <?php
    include_once("CommonCode.php");
    NavigationBar($arrayOfTranslations["ProductsBtn"]);
    ?>


    <h2>Our Products</h2>

    <style>
        input[type="number"] {
            background-color: #8e6fff69;
            color: black;
        }
    </style>

        <div class="ProductItems">
        <?php
        $fileProducts = fopen("Products.csv", "r");
        fgets($fileProducts);
        while (!feof($fileProducts)) {
            $oneProduct = fgets($fileProducts);
            $individualItemComponents = explode(";", $oneProduct);
            if (count($individualItemComponents) == 8) {
        ?>
                <div class="OneProduct">
                    <div><?= $individualItemComponents[$language == "EN" ? 0 : 7] ?></div>
                    <img src=" Pictures/<?= $individualItemComponents[1] ?>">
                    <div><?= $individualItemComponents[$language == "EN" ? 3 : 5] ?></div>
                    <div><?= $individualItemComponents[$language == "EN" ? 4 : 6] ?></div>
                    <div><?= $individualItemComponents[2] ?>EUR</div>
                </div>
        <?php
            }
        }
        ?>
        </div>

</body>

</html>