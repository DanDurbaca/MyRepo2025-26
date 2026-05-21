<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
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

    if (isset($_POST["remove_item"])) {
        $productName = $_POST["product_name"] ?? "";

        if (isset($_SESSION["cart"][$productName])) {
            unset($_SESSION["cart"][$productName]);
        }

        header("Location: cart.php?lang=" . $language);
        exit;
    }

    if (isset($_POST["clear_cart"])) {
        $_SESSION["cart"] = [];
        header("Location: cart.php?lang=" . $language);
        exit;
    }

    NavigationBar($arrayOfTranslations["CartBtn"] ?? "Cart");
    ?>

    <header>
        <h1><?= $arrayOfTranslations["CartTitle"] ?? "Your Cart" ?></h1>
        <h2><?= $arrayOfTranslations["CartSubTitle"] ?? "All selected products are listed below." ?></h2>
    </header>

    <main class="cart-page">
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
                            <td><?= htmlspecialchars($name) ?></td>
                            <td><?= number_format($item["price"], 2) ?> EUR</td>
                            <td><?= (int)$item["quantity"] ?> g</td>
                            <td><?= number_format($subtotal, 2) ?> EUR</td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($key) ?>">
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

                    <a href="#" class="checkout-btn">
                        <?= $arrayOfTranslations["CheckoutBtn"] ?? "Checkout" ?>
                    </a>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>