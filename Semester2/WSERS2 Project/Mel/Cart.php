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
    include_once("commoncode.php");
    Melnav("Cart");
    ?>
    <div class="container">
        <h1>Shop cart contents</h1>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
            <?php
            $total=0;
            foreach ($_SESSION["Cart"] as $itemId => $itemQuantity) {
                $sqlFindItem = $connection->prepare("Select * from Products where id=?");
                $sqlFindItem->bind_param("s", $itemId);
                $sqlFindItem->execute();
                $result = $sqlFindItem->get_result();
                $product=$result->fetch_assoc();
                $total+=$itemQuantity*$product["Price"];
            ?>
                <tr>
                    <td> <?= $product["ProductNameEN"] ?> </td> 
                    <td> <?= $itemQuantity ?> </td>
                    <td><?= $itemQuantity*$product["Price"] ?>.00$</td>
                    <td>
                        <form method="POST"><input type="hidden" value="<?= $itemId ?>" name="itemToDelete"><input type="submit" value="Delete"></form>
                    </td>
                </tr>
            <?php
            }
            ?>
            <tr>
                <td>Total: </td>
                <td></td>
                <td><b><?= $total ?>.00$</b></td>
            </tr>
        </table>
    </div>
</body>

</html>