<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyModificationGarage Products</title>
    <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Products");

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    // Handle Add to Cart POST request
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addToCart"])) {
        $productName = $_POST["productName"];
        $productPrice = floatval(str_replace("$", "", $_POST["productPrice"]));
        $productImage = $_POST["productImage"];
        $quantity = intval($_POST["quantity"]);

        // Check if product already exists in cart
        $productExists = false;
        foreach ($_SESSION["cart"] as &$item) {
            if ($item["name"] === $productName) {
                $item["quantity"] += $quantity;
                $productExists = true;
                break;
            }
        }

        // If product doesn't exist, add it
        if (!$productExists) {
            $_SESSION["cart"][] = [
                "name" => $productName,
                "price" => $productPrice,
                "image" => $productImage,
                "quantity" => $quantity
            ];
        }
    }
    ?>
    <h2>Our Products</h2>
    <p>Browse our selection and find the perfect product for you!</p>

    <div class="allDivs">
    <?php 
    $fileProducts = fopen("products.csv", "r");
    $headerOfTable = fgets($fileProducts);
    while (!feof($fileProducts)) {
        $oneProduct = fgets($fileProducts);
        $individualItemComponents = explode(";", $oneProduct);
        if (count($individualItemComponents) == 6) {
            $productName = $individualItemComponents[($language=="EN") ? 0 : 5];
            $productImage = $individualItemComponents[1];
            $productPrice = $individualItemComponents[2];
            $productDesc = $individualItemComponents[($language=="EN") ? 3 : 4];
            ?>
            <div class="divStyle">
                <div class="productNameDivStyle"><?= $productName ?></div>
                <img src="img/<?= $productImage ?>" alt="<?= $productName ?>" style="width:180px; height:180px; object-fit:cover; border-radius:8px;">
                <div class="colorWite"><?= $productPrice ?></div>
                <div><?= $productDesc ?></div>
                <form method="POST" style="display: flex; flex-direction: column; gap: 10px; margin-top: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                        <label for="qty_<?= md5($productName) ?>" style="color: #e0e0e0; font-size: 14px;">Qty:</label>
                        <input type="number" id="qty_<?= md5($productName) ?>" name="quantity" value="1" min="1" max="99" style="width: 50px; padding: 5px; border-radius: 4px; border: none;">
                    </div>
                    <input type="hidden" name="productName" value="<?= $productName ?>">
                    <input type="hidden" name="productPrice" value="<?= $productPrice ?>">
                    <input type="hidden" name="productImage" value="<?= $productImage ?>">
                    <button type="submit" name="addToCart" class="addToCartBtn"><?= ($language=="EN") ? "Add to Cart" : "Adicionar ao Carrinho" ?></button>
                </form>
            </div>
            <?php
        }
    }
    fclose($fileProducts);
    ?>
        
    </div>
    
</body>

</html>