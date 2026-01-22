<?php
$connection = new mysqli("localhost", "root", "", "MiniWebShop");

$stmt = $connection->prepare(
    "SELECT itemId, itemName, stock FROM items"
);

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<table><tr>{$row["itemName"]}</tr><td>{$row["stock"]}</td></br>";
}