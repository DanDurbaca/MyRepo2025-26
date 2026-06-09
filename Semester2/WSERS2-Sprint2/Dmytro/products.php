<?php
include_once "ccode.php";
include_once "navbar.php";
navbar($tArray["ProductBtn"]);
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css? <?= time(); ?> ">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Products - Mentorship Shop</title>
</head>

<body>
    <main class="page">
        <h1><?= $tArray["MentorsH1"] ?></h1>
        <?php
        if (!$_SESSION["userLogged"]) {
            echo "<div class='alert alert-error'>" . $tArray["purchaseFailUserNotLogged"] . "</div>";
        } else if ($_SESSION['canAddProducts']) {
            echo "<div class='alert alert-error'>" . $tArray["purchaseFailAdmin"] . "</div>";
        } else {
            $products = $myMentorshipShopDB->query("select * from products;");
            while ($row = $products->fetch_assoc()) {
                if (isset($_POST[$row['ID']]) && $_POST[$row['ID']] !== '') {
                    if (isset($_SESSION[$row['ID'] . 'P'])) {
                        $_SESSION[$row['ID'] . 'P'] += $_POST[$row['ID']];
                    } else {
                        $_SESSION[$row['ID'] . 'P'] = $_POST[$row['ID']];
                    }
                    $name = $lang === 'en' ? $row['productNameEN'] : $row['productNameUA'];
                    $str = $row['price'];
                    $words = explode(" ", $str);
                    $last = end($words);

                    echo "<div class='alert alert-success'>" . $tArray["purchaseSuccess"] . " " . $name . " for " . $_POST[$row['ID']] . " " . $last . '(s)' .
                        "</div>";
                }
            }
        }
        ?>
        <div class="products">
            <?php
            $products = $myMentorshipShopDB->query("SELECT * FROM products;");

            while ($row = $products->fetch_assoc()) {
                ?>
                <div class="prod">
                    <div>
                        <?php $lang == "en" ? print $row['productNameEN'] : print $row['productNameUA']; ?>
                    </div>

                    <img src="pics/<?php print $row['imageLink']; ?>"
                        alt="<?php $lang == "en" ? print $row['productNameEN'] : print $row['productNameUA']; ?>">

                    <div>
                        <?php print $row['price']; ?>
                    </div>
                    <form class="register-form" method="post">
                        <label>
                            <input placeholder='<?= $tArray["amountOfHours"] ?>' type="number" name="<?= $row['ID'] ?>"
                                required>
                        </label>
                        <button type="submit">
                            <?= $tArray["buyBtn"] ?>
                        </button>
                    </form>
                    <div>
                        <span class="price">
                            <?php $lang === "en" ? print $row['descriptionEN'] : print $row['descriptionUA']; ?>
                        </span>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>
</body>

</html>