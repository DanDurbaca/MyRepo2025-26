<?php
include_once("CommonCode.php");
includeCSS("Admin.css");
includeCSS("AdminInvoices.css");

//Check if user is Admin
if (!isset($_SESSION["UserLogged"]) || !$_SESSION["UserLogged"] || $_SESSION["UserType"] !== "Admin") {
    echo "<p style='text-align:center; margin-top:50px; color:red;'>" . ($arrayOfTranslations["AdminErrAccess"] ?? "Access denied. Admins only.") . "</p>";
    echo "<p style='text-align:center;'><a href='Home.php?lang=$language'>" . ($arrayOfTranslations["CartReturnBtn"] ?? "Return Home") . "</a></p>";
    exit;
}

NavigationBar("Admin");

$message = "";
$imageLink = "";

// --- Order status updater interaction ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ConfirmOrder"])) {
    $targetOrderID = (int)$_POST["order_id"];
    
    // Changed 'Confirmed' to 'Delivered' to follow Sprint instructions perfectly
    $sqlUpdateStatus = $connection->prepare("UPDATE Orders SET OrderStatus = 'Delivered' WHERE OrderID = ?");
    $sqlUpdateStatus->bind_param("i", $targetOrderID);
    
    if ($sqlUpdateStatus->execute()) {
        $message = "<div class='success' style='color: #4caf50; text-align: center; margin-bottom: 10px;'>✨ Order #$targetOrderID successfully updated to Delivered! ✨</div>";
    } else {
        $message = "<div class='error' style='color: #ff4b2b; text-align: center; margin-bottom: 10px;'>Error updating order: " . $connection->error . "</div>";
    }
    $sqlUpdateStatus->close();
}

// --- Product addition system ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["productNameEN"])) {
    $productNameEN = trim($_POST["productNameEN"]);
    $productNamePT = trim($_POST["productNamePT"]);
    $price         = trim($_POST["price"]);
    $descriptionEN = trim($_POST["descriptionEN"]);
    $effectEN      = trim($_POST["effectEN"]);
    $descriptionPT = trim($_POST["descriptionPT"]);
    $effectPT      = trim($_POST["effectPT"]);
    $rarity        = $_POST["rarity"];

    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/Pictures';
        $fileTmp   = $_FILES['imageFile']['tmp_name'];
        $ext       = pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION);

        $newName   = preg_replace("/[^a-zA-Z0-9_-]/", "", pathinfo($_FILES['imageFile']['name'], PATHINFO_FILENAME)) . "." . $ext;
        $destination = $uploadDir . '/' . $newName;

        if (move_uploaded_file($fileTmp, $destination)) {
            $imageLink = $newName;
        } else {
            $message = "<div class='error' style='color: #ff4b2b; text-align: center; margin-bottom: 10px;'>Error saving file to Pictures folder.</div>";
        }
    }

    if ($productNameEN && $productNamePT && $price && $imageLink && $message === "") {
        $sqlInsert = $connection->prepare("INSERT INTO products (ProductNameEN, ProductNamePT, Price, DescriptionEN, EffectEN, DescriptionPT, EffectPT, Imagelink, Rarity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($sqlInsert) {
            $sqlInsert->bind_param(
                "ssdssssss",
                $productNameEN,
                $productNamePT,
                $price,
                $descriptionEN,
                $effectEN,
                $descriptionPT,
                $effectPT,
                $imageLink,
                $rarity
            );

            if ($sqlInsert->execute()) {
                $message = "<div class='success' style='color: #4caf50; text-align: center; margin-bottom: 10px;'>Success! Item added as " . htmlspecialchars($rarity) . ".</div>";
            } else {
                $message = "<div class='error' style='color: #ff4b2b; text-align: center; margin-bottom: 10px;'>DB Error: " . $sqlInsert->error . "</div>";
            }
            $sqlInsert->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel | Pixel Potion Shop</title>
</head>

<body>
    <div style="max-width: 900px; margin: 20px auto 0 auto;">
        <?= $message ?>
    </div>

    <div class="register" style="max-width:650px; margin:40px auto; background: rgba(26, 15, 46, 0.9); padding: 40px; border-radius: 15px; border: 1px solid #a064ff; color: white; font-family: 'VT323', monospace; text-align: center;">
        <div class="header-banner">
            <h2 class="page-main-title"><?= $arrayOfTranslations["AdminTitle"] ?? "ADMIN PANEL - CREATE PRODUCT" ?></h2>
            <hr class="title-underline">
        </div>

        <form method="POST" enctype="multipart/form-data" style="text-align: left;">
            <label>Product Name (EN)</label>
            <input type="text" name="productNameEN" required style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Product Name (PT)</label>
            <input type="text" name="productNamePT" required style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label>Price (EUR)</label>
                    <input type="number" name="price" step="0.01" required style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">
                </div>
                <div style="flex: 1;">
                    <label>Rarity Tier</label>
                    <select name="rarity" class="rarity-selector" style="width: 100%; padding: 8px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white;">
                        <option value="Common">Common</option>
                        <option value="Rare" style="color: #3498db;">Rare</option>
                        <option value="Epic" style="color: #9b59b6;">Epic</option>
                        <option value="Legendary" style="color: #f1c40f;">Legendary</option>
                    </select>
                </div>
            </div>

            <label>Description (EN)</label>
            <input type="text" name="descriptionEN" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Effect (EN)</label>
            <input type="text" name="effectEN" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Description (PT)</label>
            <input type="text" name="descriptionPT" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Effect (PT)</label>
            <input type="text" name="effectPT" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Product Image</label>
            <input type="file" name="imageFile" accept="image/png, image/jpeg" required style="width: 100%; margin-bottom: 15px; color: white;">

            <button type="submit" style="margin-top: 20px; width: 100%; background: #4caf50; color: white; border: none; padding: 15px; border-radius: 5px; cursor: pointer; font-family: 'Press Start 2P', cursive; font-size: 10px;">
                Add Magical Item
            </button>
        </form>
    </div>

    <!-- Invoice management -->
    <div class="invoice-container">
        <div class="invoice-header">
            <h2>📋 Invoice Management</h2>
            <div class="invoice-header-line"></div>
        </div>

        <?php
        $queryOrders = "SELECT o.OrderID, o.OrderStatus, u.Username 
                        FROM Orders o 
                        JOIN Users u ON o.UserID = u.UserID 
                        ORDER BY o.OrderID DESC";
        $resultOrders = $connection->query($queryOrders);

        if ($resultOrders && $resultOrders->num_rows > 0):
            while ($orderRow = $resultOrders->fetch_assoc()):
                $orderID = $orderRow['OrderID'];
                $buyerName = $orderRow['Username'];
                $status = $orderRow['OrderStatus'];
                
                
                $cardStatusClass = ($status === 'Pending') ? 'order-card-pending' : 'order-card-confirmed';
                $badgeStatusClass = ($status === 'Pending') ? 'status-pending' : 'status-confirmed';
        ?>
                <div class="order-card <?= $cardStatusClass ?>">
                    <div class="order-meta-row">
                        <div>
                            <span class="order-id">Order #<?= $orderID ?></span>
                            <span class="order-buyer">Traveler: <strong><?= htmlspecialchars($buyerName) ?></strong></span>
                        </div>
                        <div>
                            <span class="status-badge <?= $badgeStatusClass ?>">
                                <?= strtoupper($status) ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-items-wrapper">
                        <p class="items-label">Items Ordered:</p>
                        <ul class="items-list">
                            <?php
                            $stmtItems = $connection->prepare("
                                SELECT p.ProductNameEN, p.ProductNamePT, p.Price, COUNT(b.ProductID) AS calculatedQuantity
                                FROM BoughtItems b
                                JOIN products p ON b.ProductID = p.ProductID
                                WHERE b.OrderID = ?
                                GROUP BY b.ProductID
                            ");
                            $stmtItems->bind_param("i", $orderID);
                            $stmtItems->execute();
                            $resItems = $stmtItems->get_result();

                            $orderTotal = 0;
                            while ($itemRow = $resItems->fetch_assoc()) {
                                $name = ($language === 'EN') ? $itemRow['ProductNameEN'] : $itemRow['ProductNamePT'];
                                $qty = $itemRow['calculatedQuantity'];
                                $price = $itemRow['Price'];
                                $subtotal = $price * $qty;
                                $orderTotal += $subtotal;
                                
                                echo "<li>" . htmlspecialchars($name) . " x$qty — <span class='item-price-total'>" . number_format($subtotal, 2) . " EUR</span></li>";
                            }
                            $stmtItems->close();
                            ?>
                        </ul>
                        
                        <div class="grand-total-display">
                            Total Invoice: <strong><?= number_format($orderTotal, 2) ?> EUR</strong>
                        </div>
                    </div>

                    <?php if ($status === 'Pending'): ?>
                        <div class="action-alignment">
                            <form method="POST" style="margin: 0; padding: 0;">
                                <input type="hidden" name="order_id" value="<?= $orderID ?>">
                                <button type="submit" name="ConfirmOrder" class="confirm-order-btn">
                                    ✓ Approve & Confirm Order
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
        <?php 
            endwhile;
        else:
            echo "<p style='text-align: center; color: #888; padding: 20px;'>No invoices found in the archives yet.</p>";
        endif; 
        ?>
    </div>

    <script>
        const selector = document.querySelector('.rarity-selector');
        selector.addEventListener('change', function() {
            if (this.value === 'Rare') this.style.color = '#3498db';
            else if (this.value === 'Epic') this.style.color = '#9b59b6';
            else if (this.value === 'Legendary') this.style.color = '#f1c40f';
            else this.style.color = 'white';
        });
    </script>
</body>

</html>