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
        $sqlSelectProducts = $connection->prepare("SELECT * from products");
        // no bind
        $sqlSelectProducts->execute();
        $sqlResultProducts = $sqlSelectProducts->get_result();

        while ($oneProduct = $sqlResultProducts->fetch_assoc()) {
        ?>
            <div class="OneProduct">
                <div><?= $oneProduct[($language == "EN") ? "product_name_en" : "product_name_ro"] ?></div>
                <img src="./images/<?= $oneProduct["image_link"] ?>">
                <div><?= $oneProduct["price"] ?> EUR/kg</div>
                <div><?= $oneProduct[($language == "EN") ? "description_en" : "description_ro"] ?></div>
                <form method="POST">
                    <input type="number" placeholder="quantity" name="quantityToBuy">
                    <input type="hidden" value="<?= $oneProduct["id"] ?>" name="itemToBuy">
                    <input type="submit" value="BUY">
                </form>
            </div>

        <?php

        }
        ?>





    </div>


</body>

</html>