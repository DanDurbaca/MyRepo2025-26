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
  <link rel="stylesheet" href="../../assets/css/home-page.css">


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
    <div id="glass">
      <div id="page-title"><h1 id="main-title"><?= t("home-page.home-title", ["brand" => renderBrand()]);?></h1></div>

      <div id="page-description"><p><?= t("home-page.home-description");?></p></div>
    </div>
  </div>

  <?php
    footer($nav_bar_options["logo"]);
  ?>

  <script src="../../assets/js/home-page.js"></script>
  <script src="../../assets/js/auth.js"></script>
  <script src="../../assets/js/profile.js"></script>
</body>

</html>
