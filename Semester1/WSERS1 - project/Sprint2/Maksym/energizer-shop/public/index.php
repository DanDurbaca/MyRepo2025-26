<?php
  require_once __DIR__ . "/../src/core/bootstrap.php";
  require_once __DIR__ . "/../src/handlers/captcha_handler.php";

  include __DIR__ . "/includes/nav_bar.php";
  include __DIR__ . "/includes/footer.php";
  include __DIR__ . "/includes/signin.php";
  include __DIR__ . "/includes/signup.php";

  $pathToLogo = "assets/images/logo/logo.png";
?>

<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="assets/css/reset.css">
  <link rel="stylesheet" href="assets/css/nav-bar.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="assets/css/brand.css">
  <link rel="stylesheet" href="assets/css/home-page.css">


  <title><?= t("tab-title");?></title>
</head>

<body>
  <?php
    nav_bar($activeLanguages, $currentLang, $pathToLogo);

    signup();
    signin();
  ?>

  <div id="page-content">
    <div id="glass">
      <div id="page-title"><h1 id="main-title"><?= t("home-page.home-title", ["brand" => renderBrand()]);?></h1></div>

      <div id="page-description"><p><?= t("home-page.home-description");?></p></div>
    </div>
  </div>

  <?php
    footer($pathToLogo);
  ?>

  <script src="assets/js/home-page.js"></script>
  <script src="assets/js/auth.js"></script>
</body>

</html>
