<?php
$mysqli = new mysqli("localhost","root","","MiniWebShop");

if ($mysqli->connect_errno) {
    echo "Failed to connect to database";
    exit();
}

// If no POST, return fruits as JSON
if (!isset($_POST['items'])) {
    $result = $mysqli->query("SELECT itemId, itemName, stock FROM Items");
    $item = [];
    while($row = $result->fetch_assoc()) $item[] = $row;
    echo json_encode($item);
    $mysqli->close();
    exit();
}

// Get POST values
$itemId = intval($_POST['items']);
$quantity = intval($_POST['quantity']);


$row = $result->fetch_assoc();
$available = intval($row['stock']);


$mysqli->close();
?>
