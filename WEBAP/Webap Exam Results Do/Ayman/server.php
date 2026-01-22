<?php
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost", "root", "", "MiniWebShop");

$getItems = $_REQUEST["getItems"] ?? "";

if ($getItems === "getItems") {
    header("Content-Type: application/json; charset=UTF-8");
    $res = $conn->query("SELECT itemId, itemName FROM Items ORDER BY itemName");
    $out = [];
    while ($row = $res->fetch_assoc()) $out[] = $row;
    echo json_encode($out);
    exit;
}

if ($getItems === "getItemsTable") {
    $res = $conn->query("SELECT itemName, stock FROM Items ORDER BY itemName");   
    echo "<tr><th>Item</th><th>Stock</th></tr>";
    while ($row = $res->fetch_assoc()) {
        $name = htmlspecialchars($row["itemName"]);
        $stock = (int)$row["stock"];
        echo "<tr><td>{$name}</td><td>{$stock}</td></tr>";
    }
    exit;
}

if ($getItems === "getOrdersTable") {
    $res = $conn->query("
        SELECT o.OrderId, i.itemName, o.quantity
        FROM Orders o
        JOIN Items i ON i.itemId = o.itemId
        ORDER BY o.OrderId DESC
    ");
    echo "<tr><th>Item</th><th>Quantity</th></tr>";
    while ($row = $res->fetch_assoc()) {
        $name = htmlspecialchars($row["itemName"]);
        $qty = (int)$row["quantity"];
        echo "</td><td>{$name}</td><td>{$qty}</td></tr>";
    }
    exit;
}

if ($getItems === "placeOrder") {
    $itemId = (int)($_POST["itemId"] ?? 0);
    $qty = (int)($_POST["qty"] ?? 0);

    if ($qty < 0) $qty = 0;

    $stmt = $conn->prepare("SELECT stock FROM Items WHERE itemId = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo "This item does not exist";
        exit;
    }

    $row = $res->fetch_assoc();
    $stock = (int)$row["stock"];

    if ($stock < $qty) {
        echo "Out of stock";
        exit;
    }

    $stmtIns = $conn->prepare("INSERT INTO Orders(itemId, quantity) VALUES(?, ?)");
    $stmtIns->bind_param("ii", $itemId, $qty);
    $stmtIns->execute();

    $newStock = $stock - $qty;
    $stmtUpd = $conn->prepare("UPDATE Items SET stock = ? WHERE itemId = ?");
    $stmtUpd->bind_param("ii", $newStock, $itemId);
    $stmtUpd->execute();

    echo "Ordered placed successfully";
    exit;
}
