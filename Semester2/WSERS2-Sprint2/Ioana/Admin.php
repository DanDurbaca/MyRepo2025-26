<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Admin");

    if (!isset($_SESSION['userType']) || $_SESSION['userType'] != "administrator") {
        header("Location: Home.php");
        exit();
    }
    ?>

    <h1>Admin Page - Add Product</h1>

    <?php
    $bShowForm = true;

    if (isset($_POST["productNameEn"], $_POST["productNameGr"], $_POST["price"])) {
        $bShowForm = false;
        print("Adding product...<br>");

        $imageLink = "";

        if (isset($_FILES["productImage"]) && $_FILES["productImage"]["error"] == 0) {
            $extension = strtolower(pathinfo($_FILES["productImage"]["name"], PATHINFO_EXTENSION));
            $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];

            if (!in_array($extension, $allowedExtensions)) {
                $bShowForm = true;
                print("Error: only image files (jpg, jpeg, png, gif, webp) are allowed.");
            } else {
                $newName = substr($_POST["productNameEn"], 0, 10) . "." . $extension;
                $uploadPath = "Images/" . $newName;

                move_uploaded_file($_FILES["productImage"]["tmp_name"], $uploadPath);

                $imageLink = "Images/" . $newName;
            }
        }

        $sqlInsertProduct = $connection->prepare("INSERT INTO products (product_name_en, product_name_gr, description_en, description_gr, price, image_link) VALUES (?,?,?,?,?,?)");
        $sqlInsertProduct->bind_param("sssdss", $_POST["productNameEn"], $_POST["productNameGr"], $_POST["descriptionEn"], $_POST["descriptionGr"], $_POST["price"], $imageLink);
        $sqlInsertProduct->execute();

        print("Product added successfully!");
        print("<br><a href='Admin.php'>Add another product</a>");
    }

    if ($bShowForm) {
        ?>
        <form method="POST" enctype="multipart/form-data">
            <div>Product name (English):</div>
            <input type="text" name="productNameEn">

            <div>Product name (Greek):</div>
            <input type="text" name="productNameGr">

            <div>Description (English):</div>
            <input type="text" name="descriptionEn">

            <div>Description (Greek):</div>
            <input type="text" name="descriptionGr">

            <div>Price:</div>
            <input type="text" name="price">

            <div>Product image:</div>
            <input type="file" name="productImage">

            <br><input type="submit" value="Add Product">
        </form>
        <?php
    }

    $orders = [];
    $orderItemsByOrder = [];
    $ordersLoadError = "";
    $statusUpdateMessage = "";

    if (isset($_POST['update_order_status'], $_POST['order_id'], $_POST['new_status'])) {
        $orderIdToUpdate = (int)$_POST['order_id'];
        $newStatus = trim((string)$_POST['new_status']);

        if ($orderIdToUpdate <= 0 || !in_array($newStatus, ["pending", "delivered"], true)) {
            $statusUpdateMessage = "Invalid order status update request.";
        } else {
            $sqlUpdateOrderStatus = $connection->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            if (!$sqlUpdateOrderStatus) {
                $statusUpdateMessage = "Could not update order status.";
            } else {
                $sqlUpdateOrderStatus->bind_param("si", $newStatus, $orderIdToUpdate);
                if ($sqlUpdateOrderStatus->execute()) {
                    $statusUpdateMessage = "Order #" . $orderIdToUpdate . " status updated to " . $newStatus . ".";
                } else {
                    $statusUpdateMessage = "Could not update order status.";
                }
            }
        }
    }

    $sqlOrders = $connection->prepare(
        "SELECT o.order_id, o.order_date, o.status, o.total_amount, c.Username
         FROM orders o
         INNER JOIN clients c ON c.userID = o.user_id
         ORDER BY o.order_id DESC"
    );

    if ($sqlOrders) {
        $sqlOrders->execute();
        $sqlOrdersResult = $sqlOrders->get_result();
        while ($orderRow = $sqlOrdersResult->fetch_assoc()) {
            $orders[] = $orderRow;
        }

        if (count($orders) > 0) {
            $sqlOrderItems = $connection->prepare(
                "SELECT oi.order_id, oi.quantity, oi.unit_price,
                        p.product_name_en, p.product_name_gr
                 FROM order_items oi
                 INNER JOIN products p ON p.id = oi.product_id
                 ORDER BY oi.order_id DESC, oi.order_item_id ASC"
            );

            if ($sqlOrderItems) {
                $sqlOrderItems->execute();
                $sqlOrderItemsResult = $sqlOrderItems->get_result();
                while ($itemRow = $sqlOrderItemsResult->fetch_assoc()) {
                    $orderId = (int)$itemRow['order_id'];
                    if (!isset($orderItemsByOrder[$orderId])) {
                        $orderItemsByOrder[$orderId] = [];
                    }
                    $orderItemsByOrder[$orderId][] = $itemRow;
                }
            } else {
                $ordersLoadError = "Could not load order items.";
            }
        }
    } else {
        $ordersLoadError = "Could not load orders.";
    }

    ?>

    <h2>Previous Orders</h2>

    <?php if ($statusUpdateMessage) { ?>
        <p><?= htmlspecialchars($statusUpdateMessage) ?></p>
    <?php } ?>

    <?php if ($ordersLoadError) { ?>
        <p><?= htmlspecialchars($ordersLoadError) ?></p>
    <?php } elseif (count($orders) === 0) { ?>
        <p>No orders found yet.</p>
    <?php } else { ?>
        <?php foreach ($orders as $order) {
            $orderId = (int)$order['order_id'];
            ?>
            <div>
                <h3>Order #<?= $orderId ?></h3>
                <p>
                    User: <?= htmlspecialchars($order['Username']) ?> |
                    Date: <?= htmlspecialchars($order['order_date']) ?> |
                    Status: <?= htmlspecialchars($order['status']) ?> |
                    Total: <?= t('currency_symbol') ?><?= number_format((float)$order['total_amount'], 2) ?>
                </p>

                <?php if (strtolower((string)$order['status']) !== 'delivered') { ?>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= $orderId ?>">
                        <input type="hidden" name="new_status" value="delivered">
                        <button type="submit" name="update_order_status" value="1">Mark as delivered</button>
                    </form>
                <?php } ?>

                <?php if (isset($orderItemsByOrder[$orderId]) && count($orderItemsByOrder[$orderId]) > 0) { ?>
                    <ul>
                        <?php foreach ($orderItemsByOrder[$orderId] as $item) { ?>
                            <li>
                                <?= htmlspecialchars(($language === "GR") ? $item['product_name_gr'] : $item['product_name_en']) ?>
                                - Qty: <?= (int)$item['quantity'] ?>
                                - Unit: <?= t('currency_symbol') ?><?= number_format((float)$item['unit_price'], 2) ?>
                                - Line: <?= t('currency_symbol') ?><?= number_format((float)$item['unit_price'] * (int)$item['quantity'], 2) ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>No items in this order.</p>
                <?php } ?>
            </div>
            <hr>
        <?php } ?>
    <?php }
    ?>

</body>
</html>