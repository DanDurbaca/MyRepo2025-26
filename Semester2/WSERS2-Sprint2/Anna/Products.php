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
    include_once("Database.php");
    include_once("CommonCode.php");
    NavigationBar("Products.php");
    $connection = new mysqli("localhost", "root", "", "webshop");
    ?>
    <div class="welcome divCentered">
        <h1 style="font-family: 'Segoe UI', Arial, sans-serif; font-size: 2.8rem; color: #3b3b5c; margin-bottom: 18px; letter-spacing: 1px;">
            <?= $arrayOfTranslations["ProductsT"] ?>
        </h1>
    </div>

    <div class="allDivs">
        <?php
        $sqlQuery = $connection->prepare("SELECT * FROM products;");
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();
        while ($row = $result->fetch_assoc()) {
            if (count($row) == 7) {
        ?>
                <div class="divStyle">
                    <div class="productNameDivStyle"><?= $row[($language == "english") ? "ProductNameEN" : "ProductNameRU"] ?></div>
                    <img src="Pictures/<?= $row["ImageLink"] ?>" alt="Brake Pads" style="width:180px; height:180px; object-fit:cover; border-radius:8px;">
                    <div class="colorWite"><?= $row["Price"] ?></div>
                    <div><?= $row[($language == "english") ? "DescriptionEN" : "DescriptionRU"] ?></div>
                    <?php if ($_SESSION["Admin"] !== "yes"): ?>
                        <form method="POST">
                            <input type="number" name="quantity" value="1" min="1" required>
                            <input type="hidden" value="<?= $row["id"] ?>" name="productId">
                            <input type="submit" value="<?= $arrayOfTranslations["BuyBtn"] ?>" class="buyBtn">
                        </form>
                    <?php else: ?>
                        <p style="color:red;">Admins cannot purchase items</p>
                    <?php endif; ?>
                </div>
        <?php
            }
        }
        ?>

    </div>


</body>

</html>