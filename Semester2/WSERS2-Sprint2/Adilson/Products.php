<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyModificationGarage Products</title>
    <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
    <style>
        .Pheader {
            text-align: center;
        }

        .Pwarning {
            text-align: center;
            color: crimson;
        }
    </style>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Products");
    $connection = new mysqli("localhost", "root", "", "Webshopdb");

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION["cart"]) && $_SESSION["UserLogged"] = true) {
        $_SESSION["cart"] = [];
    }


    ?>
    <div class="Pheader">
        <h1>Our Products</h1>
        <p>Browse our selection and find the perfect product for you!</p>
    </div>
    <?php if (!$_SESSION["UserLogged"]) { ?>
        <div>
            <h2 class="Pwarning">Log in to buy anything</h2>
        </div>
    <?php } ?>
    <div class="allDivs">
        <?php
        $sqlQuery = $connection->prepare("SELECT * FROM products");
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();
        while ($row = $result->fetch_assoc()) {
            if (count($row) == 7) {
                $productName = $row[($language == "EN") ? "productNameEN" : "productNamePT"];
                $productImage = $row["ImageLink"];
                $productPrice = $row["productPrice"];
                $productDesc = $row[($language == "EN") ? "descriptionEN" : "descriptionPT"];
        ?>
                <div class="divStyle">
                    <div class="productNameDivStyle"><?= $productName ?></div>
                    <img src="img/<?= $productImage ?>" alt="<?= $productName ?>" style="width:180px; height:180px; object-fit:cover; border-radius:8px;">
                    <div class="colorWite"><?= $productPrice ?></div>
                    <div><?= $productDesc ?></div>
                    <?php
                    if ($_SESSION["UserLogged"] && $_SESSION["ADMIN"] == 0) {

                    ?>
                        <form method="POST" style="display: flex; flex-direction: column; gap: 10px; margin-top: 12px;">
                            <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                                <label for="qty_<?= md5($productName) ?>" style="color: #e0e0e0; font-size: 14px;">Qty:</label>
                                <input type="number" id="qty_<?= md5($productName) ?>" name="quantity" value="1" min="1" max="99" style="width: 50px; padding: 5px; border-radius: 4px; border: none;">
                            </div>
                            <input type="hidden" name="productid" value="<?= $row["productID"] ?>">
                            <button type="submit" name="addToCart" class="addToCartBtn"><?= ($language == "EN") ? "Add to Cart" : "Adicionar ao Carrinho" ?></button>
                        </form><?php
                            }
                                ?>
                </div>
        <?php
            }
        }
        ?>

    </div>

</body>

</html>