<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar($arrayOfTranslations["ProductBtn"]);
    ?>
    <div class="welcome divCentered">
        <h1 style="font-family: 'Segoe UI', Arial, sans-serif; font-size: 2.8rem; color: #3b3b5c; margin-bottom: 18px; letter-spacing: 1px;">
            <?= $arrayOfTranslations["ProductsT"] ?>
        </h1>
    </div>

    <div class="allDivs">
        <?php
        $fileProducts = fopen("Products.csv", "r");
        $headerOfTable = fgets($fileProducts);
        while (!feof($fileProducts)) {
            $oneProduct = fgets($fileProducts);
            $individualItemComponents = explode(";", $oneProduct);
            if (count($individualItemComponents) == 6) {
        ?>
                <div class="divStyle">
                    <div class="productNameDivStyle"><?= $individualItemComponents[($language == "english") ? 0 : 5] ?></div>
                    <img src="Pictures/<?= $individualItemComponents[1] ?>" alt="Brake Pads" style="width:180px; height:180px; object-fit:cover; border-radius:8px;">
                    <div class="colorWite"><?= $individualItemComponents[2] ?></div>
                    <div><?= $individualItemComponents[($language == "english") ? 3 : 4] ?></div>
                </div>
        <?php
            }
        }
        ?>

    </div>


</body>

</html>