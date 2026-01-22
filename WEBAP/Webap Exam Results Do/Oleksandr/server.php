<?php

$conn = new mysqli("localhost", "root", "", "MiniWebShop");
if ($conn->connect_error) {
    die("DB error");
}

$action = $_GET["action"] ?? "";


if ($action == "getItems") {
    
    $res = $conn->query("SELECT itemId, itemName, stock FROM items");

    while ($row = $res->fetch_assoc()) {
        echo "<option value='" . $row["itemId"] . "'>" . $row["itemName"] . "</option>";
    }
    exit;
}

if ($action == "getTable"){
    $res = $conn->query("SELECT * FROM items");
    $table = "<table><tr><th>Item</td><th>Quantity</td></tr>";
    while ($row = $res->fetch_assoc()) {
        $table .= "<tr><td>". $row["itemName"] . "</td>" ."<td>". $row["stock"] . "</td></tr>" ;
    }
    $table .= "</table>";
    echo $table;
    exit; 
}

if ($action == "getOrderTable"){
    $res = $conn->query("SELECT * FROM orders JOIN items on items.itemId = orders.itemId");
    $table = "<table><tr><th>Item</td><th>Quantity ordered</td></tr>";
    while ($row = $res->fetch_assoc()) {
        $table .= "<tr><td>". $row["itemName"] . "</td>" ."<td>". $row["quantity"] . "</td></tr>" ;
    }
    $table .= "</table>";
    echo $table;
    exit; 
}



if ($action == "order") {

    $itemId = $_GET["itemId"];
    $amount  = $_GET["amount"];


    $sqlOrder3 = $conn->prepare("SELECT stock FROM items WHERE itemID = ?");
    $sqlOrder3->bind_param("i", $itemId);
    $sqlOrder3->execute();
    $result = $sqlOrder3->get_result();;

    if ($result->num_rows == 0) {
        echo "This Item does not exist";
        exit;
    }

    $row = $result->fetch_assoc();
    $stock = $row["stock"];

    if ($amount > $stock) {
        echo "Out of stock";
        exit;
    }

    if ($amount <= $stock) {
        $newAmount = $stock - $amount;
        $sqlOrder = $conn->prepare("INSERT INTO orders(itemId, quantity) VALUES(?,?)");
        $sqlOrder->bind_param("ii", $itemId, $amount);
        $sqlOrder->execute();
        $sqlOrder2 = $conn->prepare("UPDATE items SET stock = ? WHERE itemId = ?");
        $sqlOrder2->bind_param("ii", $newAmount,$itemId);
        $sqlOrder2->execute();
        echo "Order placed successfully";
        exit;
    }
}