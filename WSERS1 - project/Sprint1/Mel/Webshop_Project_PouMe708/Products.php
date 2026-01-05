<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - Mystery Box Shop</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
<header>
    <img src="pictures/Logo.png" alt="Mystery Box Shop Logo">
    <h1>CS:GO Case Shop</h1>
</header>
<?php
include_once("commoncode.php");
Melnav("Products");
?>
<div class="container">
    <h1><?= $arrayOfTranslations["WelcomeTextProducts"] ?></h1>

        <div class="AllProducts">

            <?php 
            
                $fileProducts = fopen("Products.csv", "r");
                $headerOfTable = fgets($fileProducts);
                while (!feof($fileProducts)) {
                    $product = fgets($fileProducts);
                    $individualItemComponents = explode(";", $product);
                if (count($individualItemComponents) == 6) {

            ?>

            <div class="product">
                <img src="pictures/<?= $individualItemComponents[2] ?>"<?= $individualItemComponents[($language == "EN") ? 0 :5] ?>>
                <h3><?= $individualItemComponents[($language == "EN") ? 0 : 1] ?></h3>
                <p><?= $individualItemComponents[3] ?></p>
                <p><?= $individualItemComponents[($language == "EN") ? 4 :5] ?></p>
                <button><?= $arrayOfTranslations["WelcomeTextProducts2"] ?></button>                                    
            </div>
    <?php
                }
            }
    ?>
        </div>
    </div>
</div>
</body>
</html>
