<?php
$connection = new mysqli("localhost", "root", "", "MiniWebShop");

if (isset($_GET["viewStocks"])) {
    $sqlSelectItems = $connection->prepare("SELECT * from Items");
    $sqlSelectItems->execute();
    $result = $sqlSelectItems->get_result();
?>
    <table>
        <tr>
            <th>Item</th>
            <th>Quantity</th>
        </tr>
        <?php
        while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?= $row["itemName"] ?></td>
                <td><?= $row["stock"] ?></td>
            </tr>

        <?php

        } ?>
    </table>
    <?php
}


if (isset($_GET["viewOrders"])) {
    $sqlSelectItems = $connection->prepare("SELECT * from Orders natural join items");
    $sqlSelectItems->execute();
    $result = $sqlSelectItems->get_result();
    if ($result->num_rows == 0) {
        print("No orders yet !");
    } else {


    ?>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity ordered</th>
            </tr>
            <?php
            while ($row = $result->fetch_assoc()) {
            ?>
                <tr>
                    <td><?= $row["itemName"] ?></td>
                    <td><?= $row["quantity"] ?></td>
                </tr>

            <?php

            } ?>
        </table>
    <?php
    }
}


if (isset($_GET["listItems"])) {
    $sqlSelectItems = $connection->prepare("SELECT * from Items");
    $sqlSelectItems->execute();
    $result = $sqlSelectItems->get_result();
    while ($row = $result->fetch_assoc()) {
    ?>
        <option value="<?= $row["itemId"] ?>"><?= $row["itemName"] ?></option>
<?php
    }
}

if (isset($_POST["orderItem"], $_POST["orderQty"])) {
    $sqlSelectItem = $connection->prepare("SELECT * from Items where itemId = ?");
    $sqlSelectItem->bind_param("i", $_POST["orderItem"]);
    $sqlSelectItem->execute();
    $result = $sqlSelectItem->get_result();
    if ($result->num_rows == 0) {
        print("This item does not exist");
    } else {
        $row = $result->fetch_assoc();

        if ($row["stock"] < $_POST["orderQty"])
            print("Out of stock");
        else {
            $sqlUpdate = $connection->prepare("Update Items Set stock = stock-? where itemId=?");
            $sqlUpdate->bind_param("ii", $_POST["orderQty"], $_POST["orderItem"]);
            $sqlUpdate->execute();
            $Insert = $connection->prepare("Insert into orders(itemId,quantity) values(?,?)");
            $Insert->bind_param("ii", $_POST["orderItem"], $_POST["orderQty"]);
            $Insert->execute();
            print("successfully ordered");
        }
    }
}
