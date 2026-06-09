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

  try {
    if ($_SESSION["isAdmin"]) {
      $usernames = [];

      $stmt = $pdo->query("SELECT order_id, user_id, ord_product_name, ord_product_description, ord_product_quantity, ord_product_price, ord_product_img, ord_product_status FROM orders");
      $_SESSION["Order-history"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      for ($i = 0; $i < count($_SESSION["Order-history"]); $i++) {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$_SESSION["Order-history"][$i]["user_id"]]);

        $usernames[$i] = $stmt->fetch();
      }
    } else {
      $stmt = $pdo->prepare("SELECT order_id, ord_product_name, ord_product_description, ord_product_quantity, ord_product_price, ord_product_img, ord_product_status FROM orders WHERE user_id = ?");
      $stmt->execute([$_SESSION["user_id"]]);

      $_SESSION["Order-history"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (PDOException $e) {
      echo "DB Error: " . $e;
      exit(1);
  }

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
  <link rel="stylesheet" href="../../assets/css/orders.css">

  <title><?= t("tab-title");?></title>
</head>

<body>
  <?php
    nav_bar($nav_bar_options);

    signup();
    signin();

    profile_panel($pathToSignOut);
  ?>

  <div><h1>Order history:</h2></div>

  <div id="orders">
    <?php for ($i = 0; $i < count($_SESSION["Order-history"]); $i++) { ?>
    <div id="order">
      <div id="order-image"><img src="<?= "../../" . $_SESSION["Order-history"][$i]["ord_product_img"] ?>" alt="energizer" /></div>

      <div id="order-title"><h2><?= $_SESSION["Order-history"][$i]["ord_product_name"] ?></h2></div>
      <div id="order-description"><h2>Description: <?= $_SESSION["Order-history"][$i]["ord_product_description"] ?></h2></div>
      <div id="order-quantity"><h2>Quantity: <?= $_SESSION["Order-history"][$i]["ord_product_quantity"] ?></h2></div>
      <div id="order-price"><h2>Price: <?= $_SESSION["Order-history"][$i]["ord_product_price"] ?>$</h2></div>
      <div id="order-status"><h2>Status: <?= $_SESSION["Order-history"][$i]["ord_product_status"] ?><?= $_SESSION["Order-history"][$i]["ord_product_status"] === "pending" ? "..." : "" ?></h2></div>
      <?= $_SESSION["isAdmin"] ? "<div id='order-username'><h2>User: " . $usernames[$i]["username"] . "</h2></div>" : "" ?>

      <?php if ($_SESSION["isAdmin"]) { ?>
        <form action="../../helpers/approve_action.php" method="POST">
          <div><input name="item_for_approve" type="hidden" value="<?= $_SESSION["Order-history"][$i]["order_id"] ?>"></div>

          <?= $_SESSION["Order-history"][$i]["ord_product_status"] === "pending" ? "<div id='approve-btn'><input type='submit' value='Approve'></div>" : "" ?>
        </form>
      <?php } ?>

    </div>
  <?php } ?>
</div>

  <?php
    footer($nav_bar_options["logo"]);
  ?>

  <script src="../../assets/js/home-page.js"></script>
  <script src="../../assets/js/auth.js"></script>
  <script src="../../assets/js/profile.js"></script>
</body>

</html>
