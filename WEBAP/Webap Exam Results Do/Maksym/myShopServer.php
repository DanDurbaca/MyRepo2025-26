<?php
$conn = new mysqli("localhost", "root", "", "MiniWebShop");
$action = $_GET["action"] ?? "";

if ($action == "getItemsOptions") {
    $res = $conn->query("SELECT itemId, itemName FROM items");
    while ($row = $res->fetch_assoc()) {
        echo "<option value='" . $row["itemId"] . "'>" . $row["itemName"] . "</option>";
    }
    exit;
}

if ($action == "getItemsTable") {
    $res = $conn->query("SELECT itemName, stock FROM items");
    echo "<table border='1'>";
    echo "<tr><th>Item</th><th>Quantity</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr><td>" . $row["itemName"] . "</td><td>" . $row["stock"] . "</td></tr>";
    }
    echo "</table>";
    exit;
}

if ($action == "getOrdersTable") {
    $res = $conn->query("SELECT o.orderId, i.itemName, o.quantity FROM orders o JOIN items i ON i.itemId = o.itemId ORDER BY o.orderId DESC");
    if ($res->num_rows == 0) {
        echo "No orders yet";
        exit;
    }
    echo "<table border='1'>";
    echo "<tr><th>OrderId</th><th>Item</th><th>Quantity</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr><td>" . $row["orderId"] . "</td><td>" . $row["itemName"] . "</td><td>" . $row["quantity"] . "</td></tr>";
    }
    echo "</table>";
    exit;
}

if ($action == "placeOrder") {
    $itemId = $_GET["itemId"];
    $qty = $_GET["qty"];
    $res = $conn->query("SELECT stock FROM items WHERE itemId = $itemId");
    if ($res->num_rows == 0) {
        echo "This item does not exist";
        exit;
    }
    $row = $res->fetch_assoc();
    $stock = $row["stock"];
    if ($stock < $qty) {
        echo "Out of stock";
        exit;
    }
    $conn->query("INSERT INTO orders(itemId, quantity) VALUES($itemId, $qty)");
    $newStock = $stock - $qty;
    $conn->query("UPDATE items SET stock = $newStock WHERE itemId = $itemId");
    echo "Ordered placed successfully";
    exit;
}

?>
