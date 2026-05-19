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
    $products = $myMentorshipShopDB->query("select * from products;");
    $price = 0;

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

    $out = ($price === 0) ? "nothing" : "total: " . $price . "$";
    echo '<div class="cart-total">' . $out . '</div>';
    echo '</div>';
    ?>
</body>

</html>