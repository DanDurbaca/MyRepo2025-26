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

    <h1>Our Products</h1>

    <div class="AllProducts">

        <?php
        $fileProducts = fopen("Products.csv", "r");
        fgets($fileProducts);
        while (!feof($fileProducts)) {
            $oneProduct = fgets($fileProducts);
            $individualItemComponents = explode(";", $oneProduct);
            if (count($individualItemComponents) == 6) {
        ?>
                <div class="OneProduct">
                    <div><?= $individualItemComponents[($language == "EN") ? 0 : 5] ?></div>
                    <img src="./images/<?= $individualItemComponents[1] ?>">
                    <div><?= $individualItemComponents[2] ?> EUR/kg</div>
                    <div><?= $individualItemComponents[($language == "EN") ? 3 : 4] ?></div>
                </div>

        <?php
            }
        }
        ?>





    </div>


</body>

</html>