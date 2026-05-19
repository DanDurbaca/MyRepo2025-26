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
"market" => "/",
"contact" => "../contact",
"forum" => "../forum",
"add-product" => "../add-product"
]
];
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
<link rel="stylesheet" href="../../assets/css/market.css">

<title><?= t("tab-title");?></title>
</head>

<body>
<?php
nav_bar($nav_bar_options);

signup();
signin();

profile_panel($pathToSignOut);
?>

<div id="page-content" style="display: flex; flex-wrap: wrap">
  <?php for ($i = 0; $i < count($products); $i++) {
    if ($products[$i]["quantity"] <= 0) $products[$i]["available"] = 0;

    if (!$products[$i]["available"]) continue;
    else {
  ?>
    <div style="border: 0.2em solid #000; margin: 0 1em 1em 0; flex: 1 1 calc(33.333% - 10px)">
      <div ><img style="width: 35em" src="<?= "../../" . $products[$i]["image_path"] ?>" alt="energizer" /></div>

      <div><h2><?= $products[$i]["product_name"] ?></h2></div>
      <div><h2>Description: <?= $products[$i]["description"] ?></h2></div>

      <div><p>Quantity: <?= $products[$i]["quantity"] ?></p></div>
      <div><p>Price: <?= $products[$i]["price"] ?>$</p></div>

    <?php if ($_SESSION["isLogged"] && !$_SESSION["isAdmin"]) { ?>
      <form action="../../helpers/cart_action.php" method="post">
        <div><input name="item" type="hidden" value="<?= $products[$i]["id"] ?>"></div>
        <div><label for="quantity">How many to buy: <input name="quantity" type="number" value="0"></label></div>
        <div><input type="submit" value="add to cart"></div>
      </form>
    <?php } ?>
    </div>
  <?php
    }
  }
    ?>
</div>

<?php
footer($nav_bar_options["logo"]);
?>

<script src="../../assets/js/home-page.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/profile.js"></script>
</body>

</html>
