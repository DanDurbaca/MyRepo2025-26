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

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_POST['cart_action'], $_POST['product_id']) && isset($_SESSION['logged_in_user'])) {
        $productId = (int)$_POST['product_id'];

        if ($productId > 0) {
            if ($_POST['cart_action'] === 'add') {
                if (!isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] = 0;
                }
                $_SESSION['cart'][$productId]++;
            } elseif ($_POST['cart_action'] === 'remove_one') {
                if (isset($_SESSION['cart'][$productId]) && $_SESSION['cart'][$productId] > 0) {
                    $_SESSION['cart'][$productId]--;
                    if ($_SESSION['cart'][$productId] === 0) {
                        unset($_SESSION['cart'][$productId]);
                    }
                }
            }
        }
    }

    NavigationBar("Products");

    ?>
    <div class="AllProducts">
        <?php

        $sqlSelectProducts = $connection -> prepare("SELECT * from products");
        $sqlSelectProducts -> execute ();
        $sqlResultProduct = $sqlSelectProducts -> get_result();

        while ($oneProduct = $sqlResultProduct -> fetch_assoc()) {
                $quantity = isset($_SESSION['cart'][(int)$oneProduct['id']]) ? $_SESSION['cart'][(int)$oneProduct['id']] : 0;

                ?>
                <div class="Product">
                    <div><?= $oneProduct[($language == "EN") ? "product_name_en" : "product_name_gr"] ?></div>
                        <img src="<?= $oneProduct["image_link"] ?>" alt="<?= $oneProduct["product_name_en"] ?>">
                        <div><?= $oneProduct[($language == "EN") ? "description_en" : "description_gr"] ?></div>
                        <div><?= t('currency_symbol') ?><?= number_format($oneProduct["price"], 2) ?></div>
                        <div class="product-action-form">
                            <?php if (isset($_SESSION['logged_in_user']) && (!isset($_SESSION['userType']) || $_SESSION['userType'] != "administrator")) { ?>
                                <?php if ($quantity > 0) { ?>
                                    <span class="quantity-display"><?= $quantity ?></span>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="product_id" value="<?= (int)$oneProduct['id'] ?>">
                                        <input type="hidden" name="cart_action" value="remove_one">
                                        <button type="submit" class="product-action-btn remove-one"><?= ($language === "GR") ? "Αφαίρεση ένα" : "Remove one" ?></button>
                                    </form>
                                <?php } ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="product_id" value="<?= (int)$oneProduct['id'] ?>">
                                    <input type="hidden" name="cart_action" value="add">
                                    <button type="submit" class="product-action-btn add"><?= ($language === "GR") ? "Προσθήκη στο καλάθι" : "Add to cart" ?></button>
                                </form>
                            <?php } ?>
                        </div>
            </div>
            <?php
        }
        
        ?>


    </div>
    
</body>



</html>
