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

$orderMessage = "";

// SPRINT 2: Process the Checkout/Make Order request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["checkout"])) {
    if (!empty($_SESSION["Cart"])) {
        $username = $_SESSION["Username"];

        // Use a transaction to ensure both tables insert flawlessly together
        $connection->begin_transaction();
        try {
            // 1. Insert parent record into Orders table (OrderStatus defaults to 'pending')
            $orderStmt = $connection->prepare("INSERT INTO Orders (Username) VALUES (?);");
            $orderStmt->bind_param("s", $username);
            $orderStmt->execute();
            $newOrderID = $connection->insert_id;

            // 2. Insert child records into BoughtItems table
            $itemStmt = $connection->prepare("INSERT INTO BoughtItems (OrderID, id, Quantity) VALUES (?, ?, ?);");
            foreach ($_SESSION["Cart"] as $itemid => $itemQuantity) {
                $itemStmt->bind_param("iii", $newOrderID, $itemid, $itemQuantity);
                $itemStmt->execute();
            }

            $connection->commit();
            $_SESSION["Cart"] = []; // Empty out the shopping cart session
            $orderMessage = "<p class='successMsg' style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; text-align: center;'>Order placed successfully! You can track it in your history.</p>";
        } catch (Exception $e) {
            $connection->rollback();
            $orderMessage = "<p class='errorMsg' style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; text-align: center;'>Error placing order: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        $orderMessage = "<p class='errorMsg' style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; text-align: center;'>Your cart is currently empty!</p>";
    }
}

NavigationBar("Cart.php");
?>

<body>
    <div class="welcome divCentered">
        <h1 class="cartTitle"><?= $arrayOfTranslations["CartTitle"] ?></h1>
        <p class="cartSubtitle"><?= $arrayOfTranslations["CartSubtitle"] ?></p>

        <?= $orderMessage ?>

        <table>
            <tr>
                <th><?= $arrayOfTranslations["ItemName"] ?></th>
                <th><?= $arrayOfTranslations["Quantity"] ?></th>
                <th>Action</th>
            </tr>
            <?php
            $total = 0;
            foreach ($_SESSION["Cart"] as $itemid => $itemQuantity) {
                $sqlQuery = $connection->prepare("SELECT * FROM products WHERE id = ?;");
                $sqlQuery->bind_param("i", $itemid);
                $sqlQuery->execute();
                $result = $sqlQuery->get_result();
                $product = $result->fetch_assoc();
                
                // Strips the currency symbol out if your price field contains characters like '£' or '€'
                $cleanPrice = str_replace(['£', '€'], '', $product["Price"]);
                $price = floatval($cleanPrice);
                $total += $price * $itemQuantity;
            ?>
                <tr>
                    <td><?= htmlspecialchars($product["ProductNameEN"]) ?></td>
                    <td><?= $itemQuantity ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
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
            
            <form method="POST" action="">
                <button type="submit" name="checkout" class="checkoutBtn"><?= $arrayOfTranslations["Checkout"] ?></button>
            </form>
        </div>
    </div>
</body>

</html>