<?php
include_once("function.php");

if (empty($_SESSION['logged_in_user'])) {
 
    $loc = 'login.php?language=' . urlencode($language);
    header("Location: $loc");
    exit;
}
if (!empty($_SESSION['is_admin'])) {
 
    header('Location: Admin.php');
    exit;
}


$purchaseMessage = '';
// Handle the legacy "buy" button (client-side fake purchase) - keep for backward compatibility
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy'])) {
    if (empty($_SESSION['cart'])) {
        $purchaseMessage = $arrayOfTranslations['cart_empty'][$language] ?? 'Your cart is empty.';
    } else {
        // Simply clear the cart (no DB order saved)
        $_SESSION['cart'] = [];
        $purchaseMessage = $arrayOfTranslations['purchase_success'][$language] ?? 'Thank you for your purchase!';
    }
}

// New: Make Order - persist cart contents into Orders and BoughtProducts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_order'])) {
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        $purchaseMessage = $arrayOfTranslations['cart_empty'][$language] ?? 'Your cart is empty.';
    } else {
        $db = getDB();
        // Find clientId from session username
        $username = $_SESSION['logged_in_user'] ?? null;
        $clientId = null;
        if ($username) {
            $stmt = $db->prepare('SELECT clientId FROM Clients WHERE username = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $clientId = intval($row['clientId']);
                }
                $stmt->close();
            }
        }

        $orderId = null;
        $orderDate = date('Y-m-d H:i:s');
        $status = 'pending';
        // Use two variants so we can explicitly insert NULL for clientId when needed
        if ($clientId === null) {
            $stmt = $db->prepare('INSERT INTO Orders (username, clientId, orderDate, status) VALUES (?, NULL, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('sss', $username, $orderDate, $status);
                if ($stmt->execute()) {
                    $orderId = $stmt->insert_id;
                }
                $stmt->close();
            }
        } else {
            $stmt = $db->prepare('INSERT INTO Orders (username, clientId, orderDate, status) VALUES (?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('siss', $username, $clientId, $orderDate, $status);
                if ($stmt->execute()) {
                    $orderId = $stmt->insert_id;
                }
                $stmt->close();
            }
        }

   
        if ($orderId !== null) {
            $stmt = $db->prepare('INSERT INTO BoughtProducts (orderId, productId, quantity) VALUES (?, ?, ?)');
            if ($stmt) {
                foreach ($cart as $item) {
                    $pid = intval($item['productId'] ?? 0);
                    $qty = max(0, intval($item['quantity'] ?? 1));
                    if ($pid > 0 && $qty > 0) {
                        $stmt->bind_param('iii', $orderId, $pid, $qty);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }
        }

        $_SESSION['cart'] = [];
        if ($orderId !== null) {
            $purchaseMessage = $arrayOfTranslations['purchase_success'][$language] ?? 'Thank you for your purchase! Your order has been placed.';
        } else {
            $purchaseMessage = 'Could not create order. Please try again later.';
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $posted = $_POST['quantities'] ?? [];
    $cart = $_SESSION['cart'] ?? [];
    foreach ($posted as $idx => $q) {
        $qty = intval($q);
        if (isset($cart[$idx])) {
            if ($qty <= 0) {
              
                unset($cart[$idx]);
            } else {
                $cart[$idx]['quantity'] = $qty;
            }
        }
    }
 
    $_SESSION['cart'] = array_values($cart);

    header('Location: cart.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $removeIdx = intval($_POST['remove']);
    $cart = $_SESSION['cart'] ?? [];
    if (isset($cart[$removeIdx])) {
        unset($cart[$removeIdx]);
        $_SESSION['cart'] = array_values($cart);
    }
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $arrayOfTranslations['cart_title'][$language] ?? 'OrangeShop - Cart' ?></title>
<link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>
    

<?php NavigationBar($page="Cart"); ?>


<section class="cart">
    <h2><?= $arrayOfTranslations['cart_title'][$language] ?? 'Shopping Cart' ?></h2>
    <?php
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        echo '<p>' . ($arrayOfTranslations['cart_empty'][$language] ?? 'Your cart is empty.') . '</p>';
    } else {
              
                echo '<form method="POST">';
                echo '<div class="panel">';
                echo '<table class="cart-table">';
                echo '<thead><tr>';
                echo '<th>' . ($arrayOfTranslations['product'][$language] ?? 'Product') . '</th>';
                echo '<th>' . ($arrayOfTranslations['price'][$language] ?? 'Price') . '</th>';
                echo '<th>' . ($arrayOfTranslations['qty'][$language] ?? 'Qty') . '</th>';
                echo '<th>' . ($arrayOfTranslations['subtotal'][$language] ?? 'Subtotal') . '</th>';
                echo '</tr></thead><tbody>';
            $total = 0;
            $totalQty = 0;
                foreach ($cart as $i => $item) {
                        $name = htmlspecialchars($item['productName'] ?? '');
                        $priceNum = floatval($item['price'] ?? 0);
                        $price = number_format($priceNum, 2);
                        $qty = max(0, intval($item['quantity'] ?? 1));
                       
                        $subtotal = $priceNum * max(0, $qty);
                        $total += $subtotal;
                        $totalQty += $qty;
                        echo '<tr>';
                        $img = htmlspecialchars($item['productPicture'] ?? '');
                        if ($img !== '') {
                            echo '<td><img src="' . $img . '" alt="' . $name . '" style="max-width:4rem; vertical-align:middle; margin-right:0.5rem;">' . $name . '</td>';
                        } else {
                            echo '<td>' . $name . '</td>';
                        }
                        echo '<td>$' . $price . '</td>';

                        echo '<td><input type="number" name="quantities[' . intval($i) . ']" value="' . intval($qty) . '" min="0" style="width:4rem;"></td>';

                      
                        echo '<td>$' . number_format($subtotal,2) . '<div style="display:inline-block; margin-left:0.8rem;">';
                        echo '<form method="POST" style="display:inline-block; margin:0;">';
                        echo '<input type="hidden" name="remove" value="' . intval($i) . '">';
                        echo '<button type="submit" class="remove-button" title="Remove item">&times;</button>';
                        echo '</form>';
                        echo '</div></td>';
                        echo '</tr>';
                }
                echo '</tbody>';

                echo '<tfoot><tr><td colspan="2">' . ($arrayOfTranslations['total'][$language] ?? 'Total') . '</td><td>' . $totalQty . '</td><td>$' . number_format($total,2) . '</td></tr></tfoot>';
                echo '</table>';
                echo '</div>'; 

                echo '<div style="margin-top:1rem; display:flex; gap:1rem;">';
                echo '<button type="submit" name="update_cart" class="update-button">' . ($arrayOfTranslations['update_cart'][$language] ?? 'Update cart') . '</button>';
                echo '</form>';

                // Remove legacy Buy button; only provide Make Order which persists the order in the DB
                echo '<form method="POST">';
                echo '<button type="submit" name="make_order" class="make-order-button buy-button">' . ($arrayOfTranslations['make_order'][$language] ?? 'Make Order') . '</button>';
                echo '</form>';
                echo '</div>';
       
    }
    ?>
    <?php if (!empty($purchaseMessage)) : ?>
        <p class="purchase-message"><?php echo htmlspecialchars($purchaseMessage); ?></p>
    <?php endif; ?>
</section>



</body>
</html>