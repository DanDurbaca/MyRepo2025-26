<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
</head>
<body>
    <?php
    include_once("CommonCode.php");

    if (!isset($_SESSION['logged_in_user']) || (isset($_SESSION['userType']) && $_SESSION['userType'] == "administrator")) {
        header("Location: Home.php");
        exit();
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $purchaseCompleted = false;
    if (isset($_POST['buy_all_now'])) {
        $_SESSION['cart'] = [];
        $purchaseCompleted = true;
    }

    if (isset($_POST['remove_item'], $_POST['product_id'])) {
        $removeId = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$removeId]);
    }

    NavigationBar("ShopCart");

    $productIds = array_map('intval', array_keys($_SESSION['cart']));
    $itemsInCart = [];

    if (count($productIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $types = str_repeat('i', count($productIds));

        $sqlCartItems = $connection->prepare("SELECT id, product_name_en, product_name_gr, price FROM products WHERE id IN ($placeholders)");
        if ($sqlCartItems) {
            $sqlCartItems->bind_param($types, ...$productIds);
            $sqlCartItems->execute();
            $sqlResult = $sqlCartItems->get_result();
            while ($row = $sqlResult->fetch_assoc()) {
                $itemsInCart[] = $row;
            }
        }
    }
    ?>

    <h1><?= ($language === "GR") ? "Το Καλάθι μου" : "My Cart" ?></h1>

    <?php if ($purchaseCompleted) { ?>
        <p>thank you for shpping with us</p>
    <?php } ?>

    <?php if (count($itemsInCart) === 0) { ?>
        <p><?= ($language === "GR") ? "Το καλάθι είναι άδειο." : "Your cart is empty." ?></p>
    <?php } else { ?>
        <ul>
            <?php
            $total = 0;
            foreach ($itemsInCart as $item) {
                $qty = $_SESSION['cart'][(int)$item['id']];
                $total = $total + ($item['price'] * $qty);
            ?>
                <li>
                    <?= htmlspecialchars(($language === "GR") ? $item['product_name_gr'] : $item['product_name_en']) ?> (Qty: <?= $qty ?>) - <?= t('currency_symbol') ?><?= number_format($item['price'] * $qty, 2) ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                        <button type="submit" name="remove_item" value="1">Remove</button>
                    </form>
                </li>
            <?php } ?>
        </ul>

        <p><strong>Total: <?= t('currency_symbol') ?><?= number_format($total, 2) ?></strong></p>

        <form method="POST">
            <button type="submit" name="buy_all_now" value="1">buy all now</button>
        </form>
    <?php } ?>
</body>
</html>