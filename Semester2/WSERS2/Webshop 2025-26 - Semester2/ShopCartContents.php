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


</body>

</html>