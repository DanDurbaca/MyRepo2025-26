<?php
include_once("CommonCode.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'])) {
    if (!isset($_SESSION["Cart"])) {
        $_SESSION["Cart"] = [];
    }

    $id = $_POST['product_id'];
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($qty > 0) {
        if (isset($_SESSION["Cart"][$id])) {
            $_SESSION["Cart"][$id] += $qty;
        } else {
            $_SESSION["Cart"][$id] = $qty;
        }
    }

    header("Location: ShopCartContents.php?lang=" . $language);
    exit();
}

if (!isset($_SESSION["UserLogged"]) || $_SESSION["UserLogged"] !== true || $_SESSION["UserType"] === "Admin") {
    header("Location: Home.php?lang=" . $language);
    exit();
}

if (isset($_GET['remove'])) {
    $idToRemove = $_GET['remove'];
    if (isset($_SESSION["Cart"][$idToRemove])) {
        unset($_SESSION["Cart"][$idToRemove]);
    }
    header("Location: ShopCartContents.php?lang=" . $language);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="Products.css?v=<?php echo time(); ?>">
    <title><?= $arrayOfTranslations["ShopCartTitle"] ?? "Your Alchemy Satchel" ?></title>
</head>

<body>

    <?php
    includeCSS("ShopCart.css");
    NavigationBar($arrayOfTranslations["ShopCartTitle"] ?? "Your Alchemy Satchel");
    ?>

    <div style="text-align: center; margin: 40px 0;">
        <div class="header-banner">
            <h2 class="shop-title"><?= ($language === "EN") ? "Your Alchemy Satchel" : "SEU SACO DE ALCIMIA" ?></h2>
            <div class="title-underline"></div>
        </div>
    </div>

    <div class="cart-wrapper">
        <?php if (empty($_SESSION["Cart"])): ?>
            <div class="empty-cart-msg" style="text-align:center; padding:50px;">
                <p><?= $arrayOfTranslations["CartEmptyMsg"] ?? "Your satchel is empty, traveler..." ?></p>
                <a href="Products.php?lang=<?= $language ?>" class="back-link"><?= $arrayOfTranslations["CartReturnBtn"] ?? "Return to Shop" ?></a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>POTION</th>
                        <th style="text-align: center;">QTY</th>
                        <th style="text-align: right;">PRICE</th>
                        <th style="text-align: center;">REMOVE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grandTotal = 0;
                    foreach ($_SESSION["Cart"] as $itemId => $itemQty) {
                        $stmt = $connection->prepare("SELECT * FROM products WHERE ProductID = ?");
                        $stmt->bind_param("i", $itemId);
                        $stmt->execute();
                        $res = $stmt->get_result();

                        if ($row = $res->fetch_assoc()) {
                            $pName = ($language == "EN") ? $row["ProductNameEN"] : $row["ProductNamePT"];
                            $subtotal = $row['Price'] * $itemQty;
                            $grandTotal += $subtotal;
                    ?>
                            <tr>
                                <td class="potion-name"><?= htmlspecialchars($pName) ?></td>
                                <td style="text-align: center;"><?= $itemQty ?></td>
                                <td style="text-align: right;" class="gold-text"><?= number_format($subtotal, 2) ?> EUR</td>
                                <td style="text-align: center;">
                                    <a href="ShopCartContents.php?lang=<?= $language ?>&remove=<?= $itemId ?>" class="remove-btn">&times;</a>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="cart-total-row">
                        <td colspan="2">GRAND TOTAL</td>
                        <td style="text-align: right;"><?= number_format($grandTotal, 2) ?> EUR</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="cart-actions" style="margin-top:20px; display:flex; justify-content:space-between;">
                <a href="Products.php?lang=<?= $language ?>" class="continue-btn">Continue Shopping</a>
                <button class="checkout-btn">Finalize Order</button>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>