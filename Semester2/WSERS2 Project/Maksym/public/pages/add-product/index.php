<?php
require_once __DIR__ . "/../../../src/core/bootstrap.php";
require_once __DIR__ . "/../../../src/handlers/captcha_handler.php";

include __DIR__ . "/../../includes/nav_bar.php";
include __DIR__ . "/../../includes/footer.php";
include __DIR__ . "/../../includes/signin.php";
include __DIR__ . "/../../includes/signup.php";
include __DIR__ . "/../../includes/profile.php";

$pathToLogo = "../../assets/images/logo/logo.png";
$pathToProfilePic = "../../assets/images/profile/empty-profile.webp";
$pathToSignOut = "../../helpers/signout_action.php";

$homeLink = "../index.php";
$marketLink = "market.php";
$contactLink = "contact.php";
$forumLink = "forum.php";
$addPrd = "#";

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
<link rel="stylesheet" href="../../assets/css/add-prod.css">

<title><?= t("tab-title");?></title>
</head>

<body>
<?php
nav_bar($nav_bar_options);

signup();
signin();

profile_panel($pathToSignOut);
?>

<div id="page-content">
  <form action="../../helpers/product-add_action.php" method="POST" enctype="multipart/form-data">
    <label for="product-name">
      Product name
      <input name="product-name" type="text" required>
    </label>

    <label for="description">
      Description
      <input name="description" type="text" required>
    </label>

    <label for="quantity">
      Quantity
      <input name="quantity" type="number" required>
    </label>

    <label for="price">
      Price($)
    <input name="price" type="number" min="0" max="100000" step="0.01" required>
    </label>

    <label for="image-path">
      Image path
      <input name="image-path" type="file" required>
    </label>

    <input name="submit" type="submit" value="Add">
  </form>
</div>

<?php
footer($pathToLogo);
?>

<script src="../../assets/js/home-page.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/profile.js"></script>
</body>

</html>
