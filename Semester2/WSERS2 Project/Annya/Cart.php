<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<?php
include_once("Database.php");
include_once("CommonCode.php");
if ($_SESSION["UserLogged"] === false) {
    header("Location: Login.php?lang=" . $language);
    exit();
}
if (isset($_POST["removeProduct"])) {
    $productRemove = $_POST["removeProduct"];
    unset($_SESSION["Cart"][$productRemove]);
}
NavigationBar("Cart.php");
?>

<body>
    <?php
    ?>
    <!-- Cart Container -->
    <div class="welcome divCentered">
        <h1 class="cartTitle"><?= $arrayOfTranslations["CartTitle"] ?></h1>
        <p class="cartSubtitle"><?= $arrayOfTranslations["CartSubtitle"] ?></p>

        <!-- Items will be inserted here later -->
        <table>
            <tr>
                <th><?= $arrayOfTranslations["ItemName"] ?></th>
                <th><?= $arrayOfTranslations["Quantity"] ?></th>
            </tr>
            <?php
            $total = 0;
            foreach ($_SESSION["Cart"] as $itemid => $itemQuantity) {
                $sqlQuery = $connection->prepare("SELECT * FROM products WHERE id = ?;");
                $sqlQuery->bind_param("i", $itemid);
                $sqlQuery->execute();
                $result = $sqlQuery->get_result();
                $product = $result->fetch_assoc();
                $price = floatval($product["Price"]);
                $total += $price * $itemQuantity;
            ?>
                <tr>
                    <td><?= $product["ProductNameEN"] ?></td>
                    <td><?= $itemQuantity ?></td>
                    <td><form method="POST" style="display: inline;">
                            <button type="submit" name="removeProduct" value="<?= $itemid ?>" class="removeBtn">
                                <?= $arrayOfTranslations["RemoveBtn"] ?>
                            </button>
                        </form>
</td>
                </tr>
            <?php
            }
            ?>
        </table>
        <div class="cartSummary">
            <h2><?= $arrayOfTranslations["Total"] ?>: €<?= number_format($total, 2) ?></h2>
            <button class="checkoutBtn"><?= $arrayOfTranslations["Checkout"] ?></button>
        </div>
    </div>
</body>

</html>