<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php
    include_once("nav.php");
    NavigationBar(("Products"))
    ?>
    <header>
        <h1><?= $arrayOfTranslations["ProductsTitle"] ?></h1>
        <h2><?= $arrayOfTranslations["ProductsSubTitle"] ?></h2>
    </header>

    <div class="Products">
        <?php
        $fileProducts = fopen("Products.csv", "r");
        fgets($fileProducts);
        while (!feof($fileProducts)) {
            $oneProduct = fgets($fileProducts);
            $individualItemComponents = explode(";", $oneProduct);
            if (count($individualItemComponents) == 6) {
        ?>
                <div class="OneProduct">
                    <div class="TitleProduct"><?= $individualItemComponents[($language == "EN") ? 0 : 5] ?></div>
                    <img class="ProductsImage" src="./WebsiteImages/<?= $individualItemComponents[1] ?>">
                    <div><?= $individualItemComponents[2] ?> EUR/g</div>
                    <div><?= $individualItemComponents[($language == "EN") ? 3 : 4] ?></div>
                    <div class="weed-select">
                        <select>
                            <option value="0"><?= $arrayOfTranslations["ProductsSelect"] ?>:</option>
                            <option value="1">1g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= $individualItemComponents[2] ?></option>
                            <option value="2">2g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 2 ?>$</option>
                            <option value="3">3g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 3 ?>$</option>
                            <option value="4">4g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 4 ?>$</option>
                            <option value="5">5g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 5 ?>$</option>
                            <option value="6">6g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 6 ?>$</option>
                            <option value="7">7g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 7 ?>$</option>
                            <option value="8">8g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 8 ?>$</option>
                            <option value="9">9g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 9 ?>$</option>
                            <option value="10">10g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (int)$individualItemComponents[2] * 9.5 ?>$</option>
                        </select>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </div>
</body>

</html>