<?php
$pdo = new PDO("mysql:host=localhost;dbname=miniwebshop", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$itemId = $_POST['itemId'];
$quantity = $_POST['quantity'];

try {
    $pdo->beginTransaction();

    // Check if item exists
    $stmt = $pdo->prepare("SELECT stock FROM items WHERE itemId = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo "This item does not exist";
        $pdo->rollBack();
        exit;
    }

    // Check stock
    if ($item['stock'] < $quantity) {
        echo "Out of stock";
        $pdo->rollBack();
        exit;
    }

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (itemId, quantity) VALUES (?, ?)");
    $stmt->execute([$itemId, $quantity]);

    // Update stock
    $stmt = $pdo->prepare("UPDATE items SET stock = stock - ? WHERE itemId = ?");
    $stmt->execute([$quantity, $itemId]);

    $pdo->commit();
    echo "Ordered placed successfully";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error processing order" . $e;
}
