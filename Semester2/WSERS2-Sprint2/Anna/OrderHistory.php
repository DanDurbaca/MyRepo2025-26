<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order History</title>
</head>
<?php
include_once("Database.php");
include_once("CommonCode.php");

if ($_SESSION["UserLogged"] === false) {
    header("Location: Login.php?lang=" . $language);
    exit();
}

$username = $_SESSION["Username"];

// Fetch order history for the logged-in regular user
$userOrders = [];
$historyQuery = $connection->prepare("
    SELECT o.OrderID, o.OrderStatus, b.Quantity, p.ProductNameEN, p.Price 
    FROM Orders o
    JOIN BoughtItems b ON o.OrderID = b.OrderID
    JOIN Products p ON b.id = p.id
    WHERE o.Username = ?
    ORDER BY o.OrderID DESC;
");
$historyQuery->bind_param("s", $username);
$historyQuery->execute();
$result = $historyQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $userOrders[$row['OrderID']]['status'] = $row['OrderStatus'];
    $userOrders[$row['OrderID']]['items'][] = [
        'Name' => $row['ProductNameEN'],
        'Qty' => $row['Quantity'],
        'Price' => $row['Price']
    ];
}

NavigationBar("OrderHistory.php");
?>
<body>
    <div class="welcome divCentered" style="max-width:800px; margin:30px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
        <h1>Your Order History</h1>
        
        <?php if (empty($userOrders)): ?>
            <p>You have not made any orders yet.</p>
        <?php else: ?>
            <?php foreach ($userOrders as $id => $order): ?>
                <div class="historyCard" style="border: 1px solid #eee; padding: 15px; margin-bottom: 15px; border-radius: 6px; text-align: left;">
                    <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 10px;">
                        <span>Order #<?= $id ?></span>
                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 14px; 
                            <?= $order['status'] === 'pending' ? 'background:#fff3cd; color:#856404;' : 'background:#d4edda; color:#155724;' ?>">
                            <?= strtoupper($order['status']) ?>
                        </span>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <?php foreach ($order['items'] as $item): ?>
                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                <td style="padding: 6px 0;"><?= htmlspecialchars($item['Name']) ?></td>
                                <td style="padding: 6px 0; text-align: center;">x<?= $item['Qty'] ?></td>
                                <td style="padding: 6px 0; text-align: right;"><?= htmlspecialchars($item['Price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>