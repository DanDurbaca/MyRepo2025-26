<?php
$pdo = new PDO("mysql:host=localhost;dbname=miniwebshop", "root", "");

$stmt = $pdo->query("
    SELECT o.OrderId, i.itemName, o.quantity
    FROM orders o
    JOIN items i ON o.itemId = i.itemId
");

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($orders);
