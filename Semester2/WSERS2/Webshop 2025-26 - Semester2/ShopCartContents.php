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
    NavigationBar("Products");
    //print($_SESSION["Username"]);

    if (isset($_POST["PlaceOrder"])) {
        if (count($_SESSION["Cart"]) > 0) {

            $sqlInsertOrder = $connection->prepare("INSERT INTO Orders (username) VALUES (?)");
            $sqlInsertOrder->bind_param("s", $_SESSION["Username"]);
            $sqlInsertOrder->execute();

            $order_id = $connection->insert_id;


            foreach ($_SESSION["Cart"] as $itemId => $itemQuantity) {

                $sqlInsertBoughtItem = $connection->prepare("INSERT INTO BoughtItems (orderId, productId, quantity) VALUES (?,?,?)");
                $sqlInsertBoughtItem->bind_param("iii", $order_id, $itemId, $itemQuantity);
                $sqlInsertBoughtItem->execute();
            }



            $_SESSION["Cart"] = [];
        }
    }

    ?>

    <?php
    if (count($_SESSION["Cart"]) > 0) {
    ?>

        <h1>Shop cart contents</h1>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
            </tr>
            <?php
            foreach ($_SESSION["Cart"] as $itemId => $itemQuantity) {
            ?>
                <tr>
                    <td> <?= $itemId ?> </td>
                    <td> <?= $itemQuantity ?> </td>
                </tr>
            <?php
            }
            ?>
        </table>


        <form method="POST">
            <input type="submit" value="Place order" name="PlaceOrder">
        </form>
    <?php
    } else {
        print("There is nothing to do here. Please add things to the cart first");
    }
    ?>
</body>

</html>