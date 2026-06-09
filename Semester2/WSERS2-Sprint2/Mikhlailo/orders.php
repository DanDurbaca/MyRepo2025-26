<?php
include_once("function.php");

if (empty($_SESSION['logged_in_user'])) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$username = $_SESSION['logged_in_user'];

// Handle admin action to change order status (for admin users only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_delivered']) && !empty($_SESSION['is_admin'])) {
    $orderId = intval($_POST['set_delivered']);
    $stmt = $db->prepare('UPDATE Orders SET status = ? WHERE id = ?');
    if ($stmt) {
        $s = 'delivered';
        $stmt->bind_param('si', $s, $orderId);
        $stmt->execute();
        $stmt->close();
    }
}

// If admin view - show all orders
if (!empty($_SESSION['is_admin'])) {
    $stmt = $db->prepare('SELECT id, username, clientId, orderDate, status FROM Orders ORDER BY orderDate DESC');
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // regular user - only their orders
    $stmt = $db->prepare('SELECT id, username, clientId, orderDate, status FROM Orders WHERE username = ? ORDER BY orderDate DESC');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

function getOrderItems($db, $orderId)
{
    $stmt = $db->prepare('SELECT b.quantity, p.productName, p.productPicture, p.price FROM BoughtProducts b JOIN Products p ON b.productId = p.productId WHERE b.orderId = ?');
    $res = [];
    if ($stmt) {
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    return $res;
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
<meta charset="UTF-8">
<title>Orders - OrangeShop</title>
<link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>

<?php NavigationBar($page="Orders"); ?>

<section class="orders">
    <h2>Your Orders</h2>

    <?php if (empty($orders)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <strong>Order #<?= intval($order['id']) ?></strong>
                        <span><?= htmlspecialchars($order['username']) ?></span>
                        <span><?= htmlspecialchars($order['orderDate']) ?></span>
                        <span class="status <?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span>
                    </div>

                    <div class="order-items">
                        <table>
                            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                <?php
                                $items = getOrderItems($db, intval($order['id']));
                                $total = 0;
                                foreach ($items as $it) {
                                    $sub = floatval($it['price']) * intval($it['quantity']);
                                    $total += $sub;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($it['productName']) ?></td>
                                        <td><?= intval($it['quantity']) ?></td>
                                        <td>$<?= number_format($it['price'],2) ?></td>
                                        <td>$<?= number_format($sub,2) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot><tr><td colspan="3">Total</td><td>$<?= number_format($total,2) ?></td></tr></tfoot>
                        </table>
                    </div>

                    <div class="order-actions">
                        <?php if (!empty($_SESSION['is_admin']) && $order['status'] === 'pending'): ?>
                            <form method="POST" style="display:inline-block;">
                                <button type="submit" name="set_delivered" value="<?= intval($order['id']) ?>">Mark as delivered</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

</body>
</html>
