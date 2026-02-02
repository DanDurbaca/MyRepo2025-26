<?php
$connection = new mysqli("localhost", "root", "", "miniwebshop");

if(isset($_GET["FetchItems"])){
    $sql = $connection->prepare("SELECT * FROM items ORDER BY itemId");
    $sql->execute();
    $result = $sql->get_result();
    while($row = $result->fetch_assoc()){
        print("<option value =". $row["itemId"]. ">". $row["itemName"]. "</option>");
    }
}

if(isset($_GET["TableItems"])){
    $sql = $connection->prepare("SELECT * FROM items ORDER BY itemId");
    $sql->execute();
    $result = $sql->get_result();
    while($row = $result->fetch_assoc()){
        print("<tr><td>". $row["itemName"]. "</td><td>". $row["stock"]. "</td></tr>");
    }
}

if(isset($_GET["SendItem"]) && isset($_GET["SendQuantity"])){
    $sql = $connection->prepare("SELECT * FROM items WHERE itemId = ". $_GET["SendItem"]);
    $sql->execute();
    $result = $sql->get_result();
    $row = $result->fetch_assoc();
    if($_GET["SendItem"] != $row["itemId"]) {
        print("<p>This item does not exist</p>");
    } elseif($_GET["SendQuantity"] > $row["stock"]) {
        print("<p>Out of stock</p>");
    } elseif($_GET["SendQuantity"] <= $row["stock"] && $_GET["SendQuantity"] > 0) {
        $total = $row["stock"] - $_GET["SendQuantity"];
        $sql = $connection->prepare("UPDATE items SET stock = ". $total. " WHERE itemId = ". $_GET["SendItem"]);
        $sql->execute();
        $sql = $connection->prepare("INSERT orders SET itemId = ". $_GET["SendItem"]. " ,quantity = ". $_GET["SendQuantity"]);
        $sql->execute();
        print("<p>Order placed successfully</p>");
    }
}

if(isset($_GET["FetchOrders"])){
    $sql = $connection->prepare("SELECT * FROM orders ORDER BY OrderId");
    $sql->execute();
    $result = $sql->get_result();
    while($row = $result->fetch_assoc()){
        print("<tr><td>". $row["OrderId"]. "</td><td>". $row["itemId"]. "</td><td>". $row["quantity"]. "</td></tr>");
    }
}
?>