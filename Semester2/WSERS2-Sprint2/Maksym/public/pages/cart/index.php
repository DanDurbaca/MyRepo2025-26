<?php
  require_once __DIR__ . "/../../../src/core/bootstrap.php";
  require_once __DIR__ . "/../../../src/handlers/captcha_handler.php";

  include __DIR__ . "/../../includes/nav_bar.php";
  include __DIR__ . "/../../includes/footer.php";
  include __DIR__ . "/../../includes/signin.php";
  include __DIR__ . "/../../includes/signup.php";
  include __DIR__ . "/../../includes/profile.php";

  $pathToSignOut = "../../helpers/signout_action.php";

  $nav_bar_options = [
    "languages" => $activeLanguages,
    "current-lang" => $currentLang,
    "logo" => "../../assets/images/logo/logo.png",
    "profile-pic" => "../../assets/images/profile/empty-profile.webp",

    "pages" => [
      "home" => "../home",
      "market" => "../market",
      "contact" => "../contact",
      "forum" => "../forum",
      "order-history" => "../order-history",
      "add-product" => "../add-product"
    ]
  ];

  if (!$_SESSION["isLogged"] || $_SESSION["isAdmin"]) header("Location: ../home");

  $totalPrice = 0;
?>

<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="../../assets/css/reset.css">
  <link rel="stylesheet" href="../../assets/css/nav-bar.css">
  <link rel="stylesheet" href="../../assets/css/footer.css">
  <link rel="stylesheet" href="../../assets/css/auth.css">
  <link rel="stylesheet" href="../../assets/css/auth-errors.css">
  <link rel="stylesheet" href="../../assets/css/brand.css">
  <link rel="stylesheet" href="../../assets/css/cart.css">

  <title><?= t("tab-title");?></title>
</head>

<body>
  <?php
    nav_bar($nav_bar_options);

    signup();
    signin();

    profile_panel($pathToSignOut);
  ?>

  <div><h1>Cart:</h2></div>

  <div id="Cart">
    <?php for ($i = 0; $i < count($_SESSION["Cart"]); $i++) { ?>
    <div id="cart-product">
      <div id="cart-image"><img src="<?= "../../" . $_SESSION["Cart"][$i]["image_path"] ?>" alt="energizer" /></div>

      <div id="cart-title"><h2><?= $_SESSION["Cart"][$i]["product_name"] ?></h2></div>
      <div id="cart-description"><h2>Description: <?= $_SESSION["Cart"][$i]["description"] ?></h2></div>
      <div id="cart-product-quantity"><h2>Quantity: <?= $_SESSION["Cart"][$i]["quantity"] ?></h2></div>
      <div id="cart-price"><h2>Price: <?= $_SESSION["Cart"][$i]["price"] ?>$</h2></div>

      <form action="../../helpers/cart-del_action.php" method="POST">
        <div><input name="ItForDel" type="hidden" value="<?= $_SESSION["Cart"][$i]["id"] ?>"></div>
        <div id="cart-delete_btn"><input type="submit" value="Delete"></div>
      </form>
    </div>
  <?php
    $totalPrice += $_SESSION["Cart"][$i]["price"];
  } ?>

</div>

  </br><div><h2>Total price: <?= $totalPrice ?>$</h2></div>

  <form action="../../helpers/order_action.php" method="POST">
    <div id="Order-btn"><input type="submit" value="Order"></div>
  </form>

  <?php
    footer($nav_bar_options["logo"]);
  ?>

  <script src="../../assets/js/home-page.js"></script>
  <script src="../../assets/js/auth.js"></script>
  <script src="../../assets/js/profile.js"></script>
</body>

</html>
