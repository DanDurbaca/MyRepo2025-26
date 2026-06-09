<?php
include_once("nav.php");

if (
    empty($_SESSION["UserLogged"]) ||
    $_SESSION["UserLogged"] !== true ||
    (!empty($_SESSION["IsAdmin"]) && $_SESSION["IsAdmin"] === true)
) {
    header("Location: profile.php?lang=" . $language);
    exit;
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$orderMessage = "";

// Remove one item
if (isset($_POST["remove_item"])) {
    $productName = $_POST["product_name"] ?? "";

    if (isset($_SESSION["cart"][$productName])) {
        unset($_SESSION["cart"][$productName]);
    }

    header("Location: cart.php?lang=" . $language);
    exit;
}

// Clear cart
if (isset($_POST["clear_cart"])) {
    $_SESSION["cart"] = [];
    header("Location: cart.php?lang=" . $language);
    exit;
}

// Create order
if (isset($_POST["checkout"])) {
    if (!empty($_SESSION["cart"]) && !empty($_SESSION["Username"])) {
        $username = $_SESSION["Username"];
        $total = 0;

        foreach ($_SESSION["cart"] as $item) {
            $total += $item["price"] * $item["quantity"];
        }

        $status = "pending";

        $sqlOrder = $connection->prepare(
            "INSERT INTO Orders (Username, TotalPrice, OrderStatus) VALUES (?, ?, ?)"
        );
        $sqlOrder->bind_param("sds", $username, $total, $status);

        if ($sqlOrder->execute()) {
            $orderId = $connection->insert_id;

            $sqlItem = $connection->prepare(
                "INSERT INTO OrderItems 
                (OrderID, ProductNameEN, ProductNameDE, Price, Quantity, Subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($_SESSION["cart"] as $item) {
                $nameEN = $item["name_en"];
                $nameDE = $item["name_de"];
                $price = $item["price"];
                $quantity = $item["quantity"];
                $subtotal = $price * $quantity;

                $sqlItem->bind_param(
                    "issdid",
                    $orderId,
                    $nameEN,
                    $nameDE,
                    $price,
                    $quantity,
                    $subtotal
                );

                $sqlItem->execute();
            }

            $_SESSION["cart"] = [];
            $orderMessage = $arrayOfTranslations["OrderCreated"] ?? "Your order was placed successfully. Status: pending.";
        } else {
            $orderMessage = $arrayOfTranslations["OrderCreateError"] ?? "Error creating order.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations["CartBtn"] ?? "Cart" ?></title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php NavigationBar($arrayOfTranslations["CartBtn"] ?? "Cart"); ?>

    <header>
        <h1><?= $arrayOfTranslations["CartTitle"] ?? "Your Cart" ?></h1>
        <h2><?= $arrayOfTranslations["CartSubTitle"] ?? "All selected products are listed below." ?></h2>
    </header>

    <main class="cart-page">

        <?php if ($orderMessage !== ""): ?>
            <section class="cart-box">
                <h3><?= htmlspecialchars($orderMessage, ENT_QUOTES, 'UTF-8') ?></h3>
            </section>
            <br>
        <?php endif; ?>

        <?php if (empty($_SESSION["cart"])): ?>
            <section class="cart-box">
                <h3><?= $arrayOfTranslations["CartEmpty"] ?? "Your cart is empty." ?></h3>
            </section>
        <?php else: ?>
            <section class="cart-box">
                <table>
                    <tr>
                        <th><?= $arrayOfTranslations["CartProduct"] ?? "Product" ?></th>
                        <th><?= $arrayOfTranslations["CartPrice"] ?? "Price" ?></th>
                        <th><?= $arrayOfTranslations["CartQuantity"] ?? "Quantity" ?></th>
                        <th><?= $arrayOfTranslations["CartSubtotal"] ?? "Subtotal" ?></th>
                        <th><?= $arrayOfTranslations["CartAction"] ?? "Action" ?></th>
                    </tr>

                    <?php
                    $total = 0;

                    foreach ($_SESSION["cart"] as $key => $item) {
                        $name = ($language == "EN") ? $item["name_en"] : $item["name_de"];
                        $subtotal = $item["price"] * $item["quantity"];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format($item["price"], 2) ?> EUR</td>
                            <td><?= (int)$item["quantity"] ?> g</td>
                            <td><?= number_format($subtotal, 2) ?> EUR</td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" name="remove_item" class="logout-nav">
                                        <?= $arrayOfTranslations["CartRemove"] ?? "Remove" ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <th colspan="3"><?= $arrayOfTranslations["CartTotal"] ?? "Total" ?></th>
                        <th colspan="2"><?= number_format($total, 2) ?> EUR</th>
                    </tr>
                </table>

                <br>

                <div class="cart-actions">
                    <form method="POST" class="cart-clear-form">
                        <button type="submit" name="clear_cart" class="logout-nav">
                            <?= $arrayOfTranslations["CartClear"] ?? "Clear cart" ?>
                        </button>
                    </form>

                    <form method="POST">
                        <button type="submit" name="checkout" class="checkout-btn">
                            <?= $arrayOfTranslations["CheckoutBtn"] ?? "Checkout" ?>
                        </button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <br>

        <section class="cart-box">
            <h3><?= htmlspecialchars($arrayOfTranslations["OrderHistory"] ?? "Order History", ENT_QUOTES, 'UTF-8') ?></h3>

            <?php
            $username = $_SESSION["Username"];

            $sqlOrders = $connection->prepare(
                "SELECT * FROM Orders WHERE Username = ? ORDER BY OrderDate DESC"
            );
            $sqlOrders->bind_param("s", $username);
            $sqlOrders->execute();
            $ordersResult = $sqlOrders->get_result();

            if ($ordersResult->num_rows === 0) {
                echo "<p>" . htmlspecialchars($arrayOfTranslations["OrderNoHistory"] ?? "You have no previous orders.", ENT_QUOTES, 'UTF-8') . "</p>";
            } else {
                while ($order = $ordersResult->fetch_assoc()) {
            ?>
                    <div class="order-box">
                        <h3>
                            <?= htmlspecialchars($arrayOfTranslations["OrderNumber"] ?? "Order", ENT_QUOTES, 'UTF-8') ?>
                            #<?= (int)$order["OrderID"] ?>
                            -
                            <?php
                            if ($order["OrderStatus"] === "pending") {
                                echo htmlspecialchars($arrayOfTranslations["StatusPending"] ?? "Pending", ENT_QUOTES, 'UTF-8');
                            } elseif ($order["OrderStatus"] === "allowed") {
                                echo htmlspecialchars($arrayOfTranslations["StatusAllowed"] ?? "Allowed", ENT_QUOTES, 'UTF-8');
                            } elseif ($order["OrderStatus"] === "rejected") {
                                echo htmlspecialchars($arrayOfTranslations["StatusRejected"] ?? "Rejected", ENT_QUOTES, 'UTF-8');
                            } elseif ($order["OrderStatus"] === "completed") {
                                echo htmlspecialchars($arrayOfTranslations["StatusCompleted"] ?? "Completed", ENT_QUOTES, 'UTF-8');
                            } else {
                                echo htmlspecialchars($order["OrderStatus"], ENT_QUOTES, 'UTF-8');
                            }
                            ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($arrayOfTranslations["OrderDate"] ?? "Date", ENT_QUOTES, 'UTF-8') ?>:
                            <?= htmlspecialchars($order["OrderDate"], ENT_QUOTES, 'UTF-8') ?><br>

                            <?= htmlspecialchars($arrayOfTranslations["OrderTotal"] ?? "Total", ENT_QUOTES, 'UTF-8') ?>:
                            <?= number_format($order["TotalPrice"], 2) ?> EUR
                        </p>

                        <table>
                            <tr>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartProduct"] ?? "Product", ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartPrice"] ?? "Price", ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartQuantity"] ?? "Quantity", ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartSubtotal"] ?? "Subtotal", ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>

                            <?php
                            $orderId = $order["OrderID"];

                            $sqlItems = $connection->prepare(
                                "SELECT * FROM OrderItems WHERE OrderID = ?"
                            );
                            $sqlItems->bind_param("i", $orderId);
                            $sqlItems->execute();
                            $itemsResult = $sqlItems->get_result();

                            while ($item = $itemsResult->fetch_assoc()) {
                                $productName = ($language == "EN")
                                    ? $item["ProductNameEN"]
                                    : $item["ProductNameDE"];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= number_format($item["Price"], 2) ?> EUR</td>
                                    <td><?= (int)$item["Quantity"] ?> g</td>
                                    <td><?= number_format($item["Subtotal"], 2) ?> EUR</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <br>
            <?php
                }
            }
            ?>
        </section>
    </main>
</body>

</html>