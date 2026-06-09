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

if (isset($_POST["PlaceOrder"])) {
    if (count($_SESSION["Cart"]) > 0) {
        
        $currentUserID = isset($_SESSION["userid"]) ? (int)$_SESSION["userid"] : 0;
        
        if ($currentUserID === 0 && isset($_SESSION["Username"])) {
            $stmtUser = $connection->prepare("SELECT UserID FROM Users WHERE Username = ?");
            $stmtUser->bind_param("s", $_SESSION["Username"]);
            $stmtUser->execute();
            $resUser = $stmtUser->get_result();
            if ($userRow = $resUser->fetch_assoc()) {
                $currentUserID = (int)$userRow['UserID'];
                $_SESSION["userid"] = $currentUserID; 
            }
            $stmtUser->close();
        }

        if ($currentUserID > 0) {
    
            $sqlInsertOrder = $connection->prepare("INSERT INTO Orders (UserID, OrderStatus) VALUES (?, 'Pending')");
            $sqlInsertOrder->bind_param("i", $currentUserID);
            $sqlInsertOrder->execute(); 
            
            $order_id = $connection->insert_id;
            $sqlInsertOrder->close();

            $sqlInsertBoughtItem = $connection->prepare("INSERT INTO BoughtItems (OrderID, ProductID) VALUES (?, ?)");
            
            foreach ($_SESSION["Cart"] as $itemId => $itemQuantity) {
                for ($i = 0; $i < $itemQuantity; $i++) {
                    $sqlInsertBoughtItem->bind_param("ii", $order_id, $itemId);
                    $sqlInsertBoughtItem->execute(); 
                }
            }
            $sqlInsertBoughtItem->close();

            $_SESSION["Cart"] = [];
            
            header("Location: ShopCartContents.php?lang=" . $language . "&success=1");
            exit();
            
        } else {
            header("Location: Home.php?lang=" . $language);
            exit();
        }
    }
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
            <h2 class="shop-title"><?= ($language === "EN") ? "Your Alchemy Satchel" : "SEU SACO DE ALQUIMIA" ?></h2>
            <div class="title-underline"></div>
        </div>
    </div>

    <div class="cart-wrapper">
        <?php if (isset($_GET['success'])): ?>
            <div class="empty-cart-msg" style="text-align:center; padding:50px;">
                <p style="color: #2ecc71; font-size: 1.5rem; text-shadow: 0 0 10px rgba(46,204,113,0.4);">✨ Order Finalized Successfully! Your potions are brewing! ✨</p>
                <a href="Products.php?lang=<?= $language ?>" class="back-link">Return to Shop</a>
            </div>
        <?php elseif (empty($_SESSION["Cart"])): ?>
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
                        $stmt->close();
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

            <form method="POST" action="ShopCartContents.php?lang=<?= $language ?>">
                <div class="cart-actions" style="margin-top:20px; display:flex; justify-content:space-between; align-items: center;">
                    <a href="Products.php?lang=<?= $language ?>" class="continue-btn" style="text-decoration: none;">Continue Shopping</a>
                    <button type="submit" name="PlaceOrder" class="checkout-btn">Finalize Order</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>