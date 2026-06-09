<?php
include_once("CommonCode.php");
includeCSS("AdminInvoices.css"); 

//Regular logged-in users only
if (!isset($_SESSION["UserLogged"]) || $_SESSION["UserLogged"] !== true || $_SESSION["UserType"] === "Admin") {
    header("Location: Home.php?lang=" . $language);
    exit();
}

NavigationBar("OrderHistory");

$currentUserID = isset($_SESSION["userid"]) ? (int)$_SESSION["userid"] : 0;

if ($currentUserID === 0 && isset($_SESSION["Username"])) {
    $stmtUser = $connection->prepare("SELECT UserID FROM Users WHERE Username = ?");
    $stmtUser->bind_param("s", $_SESSION["Username"]);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    if ($userRow = $resUser->fetch_assoc()) {
        $currentUserID = (int)$userRow['UserID'];
        $_SESSION["userid"] = $currentUserID; 
    }
    $stmtUser->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ($language === "EN") ? "Your Order History" : "Seu Histórico de Encomendas" ?></title>
</head>
<body>

    <div class="invoice-container" style="margin-top: 50px;">
        <div class="invoice-header">
            <h2>📜 <?= ($language === "EN") ? "YOUR ORDER HISTORY" : "SEU HISTÓRICO DE ENCOMENDAS" ?></h2>
            <div class="invoice-header-line"></div>
        </div>

        <?php
        $stmtOrders = $connection->prepare("SELECT OrderID, OrderStatus FROM Orders WHERE UserID = ? ORDER BY OrderID DESC");
        $stmtOrders->bind_param("i", $currentUserID);
        $stmtOrders->execute();
        $resultOrders = $stmtOrders->get_result();

        if ($resultOrders && $resultOrders->num_rows > 0):
            while ($orderRow = $resultOrders->fetch_assoc()):
                $orderID = $orderRow['OrderID'];
                $status = $orderRow['OrderStatus'];
                
                $cardStatusClass = ($status === 'Pending') ? 'order-card-pending' : 'order-card-delivered';
                $badgeStatusClass = ($status === 'Pending') ? 'status-pending' : 'status-delivered';
        ?>
                <div class="order-card <?= $cardStatusClass ?>">
                    <div class="order-meta-row">
                        <div>
                            <span class="order-id">Order #<?= $orderID ?></span>
                        </div>
                        <div>
                            <span class="status-badge <?= $badgeStatusClass ?>">
                                <?= strtoupper($status) ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-items-wrapper">
                        <p class="items-label"><?= ($language === "EN") ? "Items Ordered:" : "Itens Encomendados:" ?></p>
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
                            Total: <strong><?= number_format($orderTotal, 2) ?> EUR</strong>
                        </div>
                    </div>
                </div>
        <?php 
            endwhile;
        else:
            echo "<p style='text-align: center; color: #bbb; font-size: 1.2rem; padding: 20px;'>" . (($language === "EN") ? "You haven't made any orders yet, traveler..." : "Ainda não fizeste nenhuma encomenda, viajante...") . "</p>";
        endif; 
        $stmtOrders->close();
        ?>
    </div>

</body>
</html>