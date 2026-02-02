<?php
$pdo = new PDO("mysql:host=localhost;dbname=miniwebshop", "root", "");

// fetch all items
$stmt = $pdo->query("SELECT itemId, itemName, stock FROM items");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// return JSON
echo json_encode($items);
