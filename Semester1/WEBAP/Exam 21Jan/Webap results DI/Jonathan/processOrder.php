<?php
$host = 'localhost';
$dbname = 'MiniWebShop';
$username = 'root';
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'loadItems') {
        $stmt = $pdo->query("SELECT itemId, itemName FROM Items");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $options = '<option value="">Select an item</option>';
        foreach ($items as $item) {
            $options .= '<option value="' . $item['itemId'] . '">' . $item['itemName'] . '</option>';
        }
        echo $options;
    } elseif ($_POST['action'] == 'placeOrder') {
        $itemId = $_POST['itemId'];
        $quantity = $_POST['quantity'];

        $stmt = $pdo->prepare("SELECT stock FROM Items WHERE itemId = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            echo "This item does not exist";
            exit;
        }

        if ($item['stock'] < $quantity) {
            echo "Out of stock";
            exit;
        }

        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO Orders (itemId, quantity) VALUES (?, ?)");
            $stmt->execute([$itemId, $quantity]);
            
            $stmt = $pdo->prepare("UPDATE Items SET stock = stock - ? WHERE itemId = ?");
            $stmt->execute([$quantity, $itemId]);
            
            $pdo->commit();
            echo "Order placed successfully";
        } catch (Exception $e) {
            $pdo->rollback();
            echo "Error placing order";
        }
    } elseif ($_POST['action'] == 'refresh') {
        $stmt = $pdo->query("SELECT itemName, stock FROM Items");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $itemsHtml = '<table border="1"><tr><th>Item Name</th><th>Stock</th></tr>';
        foreach ($items as $item) {
            $itemsHtml .= '<tr><td>' . htmlspecialchars($item['itemName']) . '</td><td>' . $item['stock'] . '</td></tr>';
        }
        $itemsHtml .= '</table>';
        
        $stmt = $pdo->query("SELECT o.OrderId, i.itemName, o.quantity FROM Orders o JOIN Items i ON o.itemId = i.itemId");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $ordersHtml = '<table border="1"><tr><th>Order ID</th><th>Item Name</th><th>Quantity</th></tr>';
        foreach ($orders as $order) {
            $ordersHtml .= '<tr><td>' . $order['OrderId'] . '</td><td>' . htmlspecialchars($order['itemName']) . '</td><td>' . $order['quantity'] . '</td></tr>';
        }
        $ordersHtml .= '</table>';
        
        echo json_encode([
            'items' => $itemsHtml,
            'orders' => $ordersHtml
        ]);
    }
}
?>
