<?php

$conn = new mysqli("localhost", "root", "", "miniwebshop");
if ($conn->connect_error) die("DB error");
if ($_GET["action"] ?? "" == "getItems") {
    $res = $conn->query("SELECT * FROM Items");
    $rows = [];

    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }

    echo json_encode($rows);
    exit;
}
if ($_GET["action"] ?? "" == "getOrders") {
    $res = $conn->query("SELECT * FROM Orders");
    $rows = [];

    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }

    echo json_encode($rows);
    exit;
}

if ($_POST["action"] ?? "" == "order") {

    $itemId = intval($_POST["itemId"]);
    $qty = intval($_POST["quantity"]);

    $res = $conn->query("SELECT * FROM Items WHERE itemId=$itemId");
    if ($res->num_rows == 0) {
        echo "This item does not exist";
        exit;
    }

    $item = $res->fetch_assoc();

    if ($item["stock"] < $qty) {
        echo "Out of stock";
        exit;
    }

    $conn->query("INSERT INTO Orders(itemId, quantity) VALUES($itemId,$qty)");

    $conn->query("UPDATE Items SET stock = stock - $qty WHERE itemId=$itemId");

    echo "Ordered placed successfully";
    exit;
}
