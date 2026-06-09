<?php
include_once "ccode.php";
include_once "navbar.php";
navbar($imgTegArr[0]);
if ($_SESSION['userType'] === 'admin') {
    header("Location: welcome.php?lang=" . $lang);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css? <?= time(); ?> ">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Cart - Mentorship Shop</title>
</head>

<body>
    <?php
    $products1 = $myMentorshipShopDB->query("select * from products;");
    $clients = $myMentorshipShopDB->prepare("select ID from clients where ? = email;");
    $clients->bind_param("s", $_SESSION['email']);
    $clients->execute();
    $result = $clients->get_result();

    $price = 0;
    $flag = false;

    while ($row = $products1->fetch_assoc()) {
        if (isset($_SESSION[$row['ID'] . 'P'])) {
            $flag = true;
            break;
        }
    }
    $products = $myMentorshipShopDB->query("select * from products;");

    if ($flag && isset($_POST['book'])) {
        $order = $myMentorshipShopDB->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?);");
        $order->bind_param("id", $result->fetch_assoc()['ID'], $_SESSION['price']);
        $order->execute();

        $order_id = $myMentorshipShopDB->insert_id;

        while ($row = $products->fetch_assoc()) {
            if (isset($_SESSION[$row['ID'] . 'P'])) {
                $item = $myMentorshipShopDB->prepare(
                    "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
                VALUES (?, ?, ?, ?)"
                );

                $item->bind_param("iiid", $order_id, $row['ID'], $_SESSION[$row['ID'] . 'P'], $row['price']);
                $item->execute();
                unset($_SESSION[$row['ID'] . 'P']);
            }
        }
    }

    echo '<div class="cart-page">';
    echo '<h1 class="cart-title">You booked:</h1>';
    echo '<div class="cart-list">';

    while ($row = $products->fetch_assoc()) {
        if (isset($_SESSION[$row['ID'] . 'P'])) {
            if (isset($_POST[$row['ID']])) {
                unset($_SESSION[$row['ID'] . 'P']);
                continue;
            }
            $str = $row['price'];
            $words = explode(" ", $str);
            $tmpprice = (double) $words[0] * $_SESSION[$row['ID'] . 'P'];

            echo '<div class="cart-item">';
            ?>
            <form method="post" class="cart-item-form">
                <input type="hidden" name="<?= $row['ID'] ?>">
                <button type="submit" name="remove_product" value="1" class="cart-item-btn">
                    Remove
                </button>
            </form>
            <?php
            echo '<div class="cart-item-name">' . $row[$lang === 'en' ? 'productNameEN' : 'productNameUA'] . '</div>';
            echo '<div class="cart-item-meta">for ' . $_SESSION[$row['ID'] . 'P'] . ' ' . end($words) . '(s)</div>';
            echo '<div class="cart-item-price">' . $tmpprice . '$</div>';
            echo '</div>';

            $price += $tmpprice;
        }
    }

    echo '</div>';

    $out = ($price === 0) ? "no one" : "total: " . $price . "$";
    $_SESSION['price'] = $price;
    echo '<div class="cart-total">' . $out . '</div>';
    ?>
    </div>
    <form method="post" style="text-align: center; margin-top: 10px;">
        <button type="submit" name="book" value="1"
            style="display: inline-block !important; width: auto !important; padding: 6px 14px; font-size: 14px;">
            Book
        </button>
    </form>
</body>

</html>