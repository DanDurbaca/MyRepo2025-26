<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CS:GO Case Shop</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <header>
        <img src="pictures/Logo.png" alt="Logo">
        <h1>CS:GO Case Shop</h1>
    </header>
    <?php
    /** @var array $arrayOfTranslations */
    include_once("commoncode.php");
    Melnav("Cart");

    if (isset($_POST["PlaceOrder"])){
        if (count($_SESSION["Cart"])>0){

            $initialStatus = 'pending';
            $sqlInsertOrder = $connection->prepare("insert into Orders(username, status) VALUES(?, ?)");
            $sqlInsertOrder->bind_param("ss", $_SESSION["username"], $initialStatus);
            $sqlInsertOrder-> execute();

            $order_id = $connection->insert_id;

            foreach ($_SESSION["Cart"] as $itemId => $itemQuantity){
                $sqlInsertBoughtItems = $connection->prepare("insert into BoughtItems(orderId, productId, quantity) VALUES(?,?,?)");
                $sqlInsertBoughtItems->bind_param("iii", $order_id, $itemId, $itemQuantity);
                $sqlInsertBoughtItems-> execute();  
            }

            $_SESSION["Cart"] = [];
        }
    }
    ?>

    <div class="container">
        <h1><?= $arrayOfTranslations["cart1"] ?></h1>
        <?php if (count($_SESSION["Cart"]) > 0) { ?>
            <table>
                <tr>
                    <th><?= $arrayOfTranslations["cart2"] ?></th>
                    <th><?= $arrayOfTranslations["cart3"] ?></th>
                    <th><?= $arrayOfTranslations["cart4"] ?></th>
                </tr>
                <?php
                $total = 0;
                foreach ($_SESSION["Cart"] as $itemId => $itemQuantity) {
                    $sqlFindItem = $connection->prepare("Select * from Products where id=?");
                    $sqlFindItem->bind_param("s", $itemId);
                    $sqlFindItem->execute();
                    $result = $sqlFindItem->get_result();
                    $product = $result->fetch_assoc();
                    $total += $itemQuantity * $product["Price"];
                ?>
                    <tr>
                        <td> <?= htmlspecialchars($product["ProductNameEN"]) ?> </td> 
                        <td> <?= $itemQuantity ?> </td>
                        <td><?= $itemQuantity * $product["Price"] ?>.00$</td>
                        <td>
                            <form method="POST">
                                <input type="hidden" value="<?= $itemId ?>" name="itemToDelete">
                                <input type="submit" value="<?= $arrayOfTranslations["cart12"] ?>">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?= $arrayOfTranslations["cart5"] ?></td>
                    <td></td>
                    <td><b><?= $total ?>.00$</b></td>
                </tr>
            </table>
            <form method="POST">
                <input type="submit" value="<?= $arrayOfTranslations["cart11"] ?>" name="PlaceOrder">
            </form>
        <?php } else { ?>
            <p><?= $arrayOfTranslations["cart6"] ?></p>
        <?php } ?>
    </div>

    <div class="container" style="margin-top: 40px;">
        <hr style="border: 0; height: 1px; background: #ccc; margin-bottom: 30px;">
        <h2><?= $arrayOfTranslations["cart7"] ?></h2>
        
        <?php
        $sqlOrderHistory = $connection->prepare("SELECT * FROM Orders WHERE username = ? ORDER BY id DESC");
        $sqlOrderHistory->bind_param("s", $_SESSION["username"]);
        $sqlOrderHistory->execute();
        $historyResult = $sqlOrderHistory->get_result();

        if ($historyResult->num_rows > 0) {
            ?>
            <table border="1" style="width:100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: #4b5563; color: white;">
                        <th style="padding: 10px;"><?= $arrayOfTranslations["cart8"] ?></th>
                        <th style="padding: 10px;"><?= $arrayOfTranslations["cart9"] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $historyResult->fetch_assoc()) { 
                        $statusColor = ($row["status"] == "pending") ? "#d97706" : "#16a34a";
                        ?>
                        <tr>
                            <td style="padding: 10px;">#<?= $row["id"] ?></td>
                            <td style="padding: 10px; font-weight: bold; color: <?= $statusColor ?>;">
                                <?= ucfirst(htmlspecialchars($row["status"])) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<p style='color: gray;'>" .$arrayOfTranslations["cart10"] . "</p>";
        }
        ?>
    </div>

</body>
</html>