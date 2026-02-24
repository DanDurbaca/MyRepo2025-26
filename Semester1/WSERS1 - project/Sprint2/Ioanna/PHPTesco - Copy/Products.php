<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Products");

    ?>
    <div class="AllProducts">
        <?php
        $fileProducts = fopen("Products.csv", "r");
        fgets($fileProducts);
        while(!feof($fileProducts)) {
            $productLine = fgets($fileProducts);
            $individualProduct = explode(";", $productLine);
            if (count($individualProduct)==6) {
                ?>
                <div class="Product">
                    <div><?= $individualProduct[($language == "EN") ? 0 : 4] ?></div>
                    <img src="Images/<?= $individualProduct[1] ?>" alt="<?= $individualProduct[0] ?>">
                    <div><?= $individualProduct[2] ?> <?= t('currency_symbol') ?></div>
                    <div><?= $individualProduct[($language == "EN") ? 3 : 5] ?></div>
            </div>
            <?php
        }
        
        ?>
        <?php
        }
        fclose($fileProducts);
        ?>

    </div>
    
</body>



</html>
