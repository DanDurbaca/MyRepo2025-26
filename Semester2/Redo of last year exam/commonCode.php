<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<?php


$connection = new mysqli("localhost", "root", "", "ShopExam");

if (isset($_POST["clear"])) {
    unset($_SESSION["ShopCategory"]);
    unset($_SESSION["cssBuild"]);
    unset($_SESSION["shopCart"]);
}

if (!isset($_SESSION["shopCart"])) {
    $_SESSION["shopCart"] = [];
}

if (!isset($_SESSION["ShopCategory"]))
    $_SESSION["ShopCategory"] = 0;


if (!isset($_SESSION["cssBuild"])) {
    if ((int)date("H") >= 7 && (int)date("H") <= 20)
        $_SESSION["cssBuild"] = "Shop-daybuild.css";
    else
        $_SESSION["cssBuild"] = "Shop-nightbuild.css";
}

if (isset($_POST["theme"])) {
    $_SESSION["cssBuild"] = $_POST["theme"];
}


if (isset($_POST["category"])) {
    //print("I am changing the shop category now");
    $_SESSION["ShopCategory"] = $_POST["category"];
}

if (isset($_POST["productId"])) {
    if (!isset($_SESSION["shopCart"][$_POST["productId"]]))
        $_SESSION["shopCart"][$_POST["productId"]] = 1;
    else
        $_SESSION["shopCart"][$_POST["productId"]]++;
}


?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="Shop.css?1751290912" />
    <link rel="stylesheet" href="<?= $_SESSION["cssBuild"] ?>" />
    <title>Welcome to the shop</title>
</head>

<body>


    <div class="NavCenter">
        <div class="NavBar">
            <div class="MainLinks">
                <form method="POST">
                    <select name="category" onchange="this.form.submit()">
                        <option value="0">All Products</option>

                        <?php
                        $selectCat = $connection->prepare("Select * from Categories");
                        $selectCat->execute();
                        $result = $selectCat->get_result();
                        while ($row = $result->fetch_assoc()) {
                        ?>
                            <option value="<?= $row["categoryId"] ?>" <?= $_SESSION["ShopCategory"] == $row["categoryId"] ?  "selected" : ""; ?>><?= $row["categoryName"] ?></option>
                        <?php
                        }

                        ?>



                    </select>
                </form>
                <div>
                    <?php
                    $numItemInCart = 0;
                    foreach ($_SESSION["shopCart"] as $key => $value) {
                        $numItemInCart += $value;
                    }
                    ?>

                    <div class="CartCount">Items in cart: <?= $numItemInCart ?></div>
                </div>
                <?php if (count($_SESSION["shopCart"]) > 0) { ?>
                    <a href="Cart.php" class="CartLink">Go to Cart</a>
                <?php
                } ?>

                <a class="CartLink" href="Products.php"> Products </a>
            </div>
            <div class="Icons">
                <form method="POST">
                    <select name="theme" onchange="this.form.submit()">
                        <option value="Shop-daybuild.css" <?= $_SESSION["cssBuild"] == "Shop-daybuild.css" ? "selected" : "" ?>>Light</option>
                        <option value="Shop-nightbuild.css" <?= $_SESSION["cssBuild"] == "Shop-nightbuild.css" ? "selected" : "" ?>>Dark</option>
                    </select>
                </form>

                <form method="POST">
                    <input type="submit" value="Clear session" name="clear" />
                </form>
            </div>
        </div>
    </div>