<?php
$mysqli = new mysqli("localhost","root","","MiniWebShop");

if ($mysqli->connect_errno) {
    echo "Failed to connect to database";
    exit();
}

if (!isset($_POST['itemId'])) {
    $result = $mysqli->query("SELECT itemId, fruitName, stock FROM items");
    $items = [];
    while($row = $result->fetch_assoc()) $items[] = $row;
    echo json_encode($items);
    $mysqli->close();
    exit();
}

$itemId = intval($_POST['itemId']);

$row = $result->fetch_assoc();
$stock = intval($row['stock']);

if ($stock < 0) {
    $mysqli->query("UPDATE items SET stock=0 WHERE itemId=$itemId");
    echo "Out of stock";
} else {
    $newStock = $stock ;
    $mysqli->query("UPDATE items SET stock=$newStock WHERE itemId=$itemId");
    echo "Order placed successfully";
}
$mysqli->close();
?>
