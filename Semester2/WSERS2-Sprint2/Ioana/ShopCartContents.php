<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
</head>
<body>
    <?php
    include_once("CommonCode.php");

    if (!isset($_SESSION['logged_in_user']) || (isset($_SESSION['userType']) && $_SESSION['userType'] == "administrator")) {
        header("Location: Home.php");
        exit();
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $purchaseCompleted = false;
    $createdOrderId = null;
    $purchaseError = "";
    if (isset($_POST['buy_all_now'])) {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

        if (count($cart) === 0) {
            $purchaseError = ($language === "GR") ? "Το καλάθι είναι άδειο." : "Your cart is empty.";
        } else {
            $sqlFindUser = $connection->prepare("SELECT userID FROM clients WHERE Username = ? LIMIT 1");

            if (!$sqlFindUser) {
                $purchaseError = ($language === "GR") ? "Σφάλμα κατά την επεξεργασία της παραγγελίας." : "Could not process your order.";
            } else {
                $sqlFindUser->bind_param("s", $_SESSION['logged_in_user']);
                $sqlFindUser->execute();
                $sqlUserResult = $sqlFindUser->get_result();
                $userRow = $sqlUserResult->fetch_assoc();

                if (!$userRow) {
                    $purchaseError = ($language === "GR") ? "Ο χρήστης δεν βρέθηκε." : "User not found.";
                } else {
                    $userId = (int)$userRow['userID'];
                    $productIds = array_map('intval', array_keys($cart));
                    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                    $types = str_repeat('i', count($productIds));

                    $sqlProducts = $connection->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");

                    if (!$sqlProducts) {
                        $purchaseError = ($language === "GR") ? "Σφάλμα ανάκτησης προϊόντων." : "Could not fetch products.";
                    } else {
                        $sqlProducts->bind_param($types, ...$productIds);
                        $sqlProducts->execute();
                        $sqlProductsResult = $sqlProducts->get_result();

                        $pricesById = [];
                        while ($productRow = $sqlProductsResult->fetch_assoc()) {
                            $pricesById[(int)$productRow['id']] = (float)$productRow['price'];
                        }

                        $totalAmount = 0;
                        foreach ($cart as $productId => $qty) {
                            $productId = (int)$productId;
                            $qty = (int)$qty;

                            if ($qty <= 0 || !isset($pricesById[$productId])) {
                                $purchaseError = ($language === "GR") ? "Μη έγκυρα προϊόντα στο καλάθι." : "Invalid products found in cart.";
                                break;
                            }

                            $totalAmount += $pricesById[$productId] * $qty;
                        }

                        if (!$purchaseError) {
                            $connection->begin_transaction();

                            try {
                                $sqlInsertOrder = $connection->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
                                if (!$sqlInsertOrder) {
                                    throw new Exception('Failed to prepare order insert.');
                                }

                                $sqlInsertOrder->bind_param("id", $userId, $totalAmount);
                                if (!$sqlInsertOrder->execute()) {
                                    throw new Exception('Failed to insert order.');
                                }

                                $createdOrderId = (int)$connection->insert_id;

                                $sqlInsertItem = $connection->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                                if (!$sqlInsertItem) {
                                    throw new Exception('Failed to prepare order item insert.');
                                }

                                foreach ($cart as $productId => $qty) {
                                    $productId = (int)$productId;
                                    $qty = (int)$qty;
                                    $unitPrice = $pricesById[$productId];

                                    $sqlInsertItem->bind_param("iiid", $createdOrderId, $productId, $qty, $unitPrice);
                                    if (!$sqlInsertItem->execute()) {
                                        throw new Exception('Failed to insert order item.');
                                    }
                                }

                                $connection->commit();
                                $_SESSION['cart'] = [];
                                $purchaseCompleted = true;
                            } catch (Exception $e) {
                                $connection->rollback();
                                $createdOrderId = null;
                                $purchaseError = ($language === "GR") ? "Η παραγγελία δεν ολοκληρώθηκε." : "Order could not be completed.";
                            }
                        }
                    }
                }
            }
        }
    }

    if (isset($_POST['remove_item'], $_POST['product_id'])) {
        $removeId = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$removeId]);
    }

    NavigationBar("ShopCart");

    $productIds = array_map('intval', array_keys($_SESSION['cart']));
    $itemsInCart = [];

    if (count($productIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $types = str_repeat('i', count($productIds));

        $sqlCartItems = $connection->prepare("SELECT id, product_name_en, product_name_gr, price FROM products WHERE id IN ($placeholders)");
        if ($sqlCartItems) {
            $sqlCartItems->bind_param($types, ...$productIds);
            $sqlCartItems->execute();
            $sqlResult = $sqlCartItems->get_result();
            while ($row = $sqlResult->fetch_assoc()) {
                $itemsInCart[] = $row;
            }
        }
    }

    $userOrders = [];
    $orderItemsByOrder = [];
    $ordersLoadError = "";

    $sqlFindUserForOrders = $connection->prepare("SELECT userID FROM clients WHERE Username = ? LIMIT 1");
    if (!$sqlFindUserForOrders) {
        $ordersLoadError = ($language === "GR") ? "Δεν ήταν δυνατή η φόρτωση παραγγελιών." : "Could not load your orders.";
    } else {
        $sqlFindUserForOrders->bind_param("s", $_SESSION['logged_in_user']);
        $sqlFindUserForOrders->execute();
        $sqlUserOrdersResult = $sqlFindUserForOrders->get_result();
        $userOrderRow = $sqlUserOrdersResult->fetch_assoc();

        if ($userOrderRow) {
            $currentUserId = (int)$userOrderRow['userID'];

            $sqlUserOrders = $connection->prepare(
                "SELECT order_id, order_date, status, total_amount
                 FROM orders
                 WHERE user_id = ?
                 ORDER BY order_id DESC"
            );

            if (!$sqlUserOrders) {
                $ordersLoadError = ($language === "GR") ? "Δεν ήταν δυνατή η φόρτωση παραγγελιών." : "Could not load your orders.";
            } else {
                $sqlUserOrders->bind_param("i", $currentUserId);
                $sqlUserOrders->execute();
                $sqlUserOrdersResult = $sqlUserOrders->get_result();

                while ($orderRow = $sqlUserOrdersResult->fetch_assoc()) {
                    $userOrders[] = $orderRow;
                }

                if (count($userOrders) > 0) {
                    $orderIds = array_map(
                        static function ($order) {
                            return (int)$order['order_id'];
                        },
                        $userOrders
                    );

                    $orderPlaceholders = implode(',', array_fill(0, count($orderIds), '?'));
                    $orderTypes = str_repeat('i', count($orderIds));

                    $sqlUserOrderItems = $connection->prepare(
                        "SELECT oi.order_id, oi.quantity, oi.unit_price,
                                p.product_name_en, p.product_name_gr
                         FROM order_items oi
                         INNER JOIN products p ON p.id = oi.product_id
                         WHERE oi.order_id IN ($orderPlaceholders)
                         ORDER BY oi.order_id DESC, oi.order_item_id ASC"
                    );

                    if (!$sqlUserOrderItems) {
                        $ordersLoadError = ($language === "GR") ? "Δεν ήταν δυνατή η φόρτωση ειδών παραγγελίας." : "Could not load your order items.";
                    } else {
                        $sqlUserOrderItems->bind_param($orderTypes, ...$orderIds);
                        $sqlUserOrderItems->execute();
                        $sqlUserOrderItemsResult = $sqlUserOrderItems->get_result();

                        while ($itemRow = $sqlUserOrderItemsResult->fetch_assoc()) {
                            $orderId = (int)$itemRow['order_id'];
                            if (!isset($orderItemsByOrder[$orderId])) {
                                $orderItemsByOrder[$orderId] = [];
                            }
                            $orderItemsByOrder[$orderId][] = $itemRow;
                        }
                    }
                }
            }
        }
    }
    ?>

    <h1><?= ($language === "GR") ? "Το Καλάθι μου" : "My Cart" ?></h1>

    <?php if ($purchaseCompleted) { ?>
        <p><?= ($language === "GR") ? "Ευχαριστούμε για την παραγγελία σας." : "Thank you for shopping with us." ?></p>
        <p><?= ($language === "GR") ? "Αριθμός Παραγγελίας:" : "Order Number:" ?> <?= (int)$createdOrderId ?></p>
    <?php } ?>

    <?php if ($purchaseError) { ?>
        <p><?= htmlspecialchars($purchaseError) ?></p>
    <?php } ?>

    <?php if (count($itemsInCart) === 0) { ?>
        <p><?= ($language === "GR") ? "Το καλάθι είναι άδειο." : "Your cart is empty." ?></p>
    <?php } else { ?>
        <ul>
            <?php
            $total = 0;
            foreach ($itemsInCart as $item) {
                $qty = $_SESSION['cart'][(int)$item['id']];
                $total = $total + ($item['price'] * $qty);
            ?>
                <li>
                    <?= htmlspecialchars(($language === "GR") ? $item['product_name_gr'] : $item['product_name_en']) ?> (Qty: <?= $qty ?>) - <?= t('currency_symbol') ?><?= number_format($item['price'] * $qty, 2) ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                        <button type="submit" name="remove_item" value="1">Remove</button>
                    </form>
                </li>
            <?php } ?>
        </ul>

        <p><strong>Total: <?= t('currency_symbol') ?><?= number_format($total, 2) ?></strong></p>

        <form method="POST">
            <button type="submit" name="buy_all_now" value="1">buy all now</button>
        </form>
    <?php } ?>

    <h2><?= ($language === "GR") ? "Οι Παραγγελίες μου" : "Your Orders" ?></h2>

    <?php if ($ordersLoadError) { ?>
        <p><?= htmlspecialchars($ordersLoadError) ?></p>
    <?php } elseif (count($userOrders) === 0) { ?>
        <p><?= ($language === "GR") ? "Δεν υπάρχουν παραγγελίες ακόμα." : "You do not have any orders yet." ?></p>
    <?php } else { ?>
        <?php foreach ($userOrders as $order) {
            $orderId = (int)$order['order_id'];
            $status = strtolower((string)$order['status']);
            ?>
            <div>
                <h3><?= ($language === "GR") ? "Παραγγελία" : "Order" ?> #<?= $orderId ?></h3>
                <p>
                    <?= ($language === "GR") ? "Ημερομηνία" : "Date" ?>: <?= htmlspecialchars($order['order_date']) ?> |
                    <?= ($language === "GR") ? "Κατάσταση" : "Status" ?>: <?= htmlspecialchars(ucfirst($status)) ?> |
                    <?= ($language === "GR") ? "Σύνολο" : "Total" ?>: <?= t('currency_symbol') ?><?= number_format((float)$order['total_amount'], 2) ?>
                </p>

                <?php if (isset($orderItemsByOrder[$orderId]) && count($orderItemsByOrder[$orderId]) > 0) { ?>
                    <ul>
                        <?php foreach ($orderItemsByOrder[$orderId] as $item) { ?>
                            <li>
                                <?= htmlspecialchars(($language === "GR") ? $item['product_name_gr'] : $item['product_name_en']) ?>
                                - <?= ($language === "GR") ? "Ποσ." : "Qty" ?>: <?= (int)$item['quantity'] ?>
                                - <?= ($language === "GR") ? "Τιμή" : "Unit" ?>: <?= t('currency_symbol') ?><?= number_format((float)$item['unit_price'], 2) ?>
                                - <?= ($language === "GR") ? "Γραμμή" : "Line" ?>: <?= t('currency_symbol') ?><?= number_format((float)$item['unit_price'] * (int)$item['quantity'], 2) ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p><?= ($language === "GR") ? "Δεν βρέθηκαν είδη παραγγελίας." : "No order items found." ?></p>
                <?php } ?>
            </div>
            <hr>
        <?php } ?>
    <?php } ?>
</body>
</html>