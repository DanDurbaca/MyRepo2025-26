<?php
  include ("includes/init.php");
  include ("includes/navBar.php");
  include ("includes/footer.php");
  include ("includes/signing.php");
  include ("includes/captcha.php");

  $pathToLogo = "images/logo/logo.png";
  $mainTitle = t("home-page.home-title", ["brand" => $brand]);
?>

<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/nav-bar.css">
  <link rel="stylesheet" href="css/footer.css">
  <link rel="stylesheet" href="css/signing.css">
  <link rel="stylesheet" href="css/home-page.css">

  <title><?= t("tab-title");?></title>
</head>

<body>
  <?php
    NavBar($pathToLogo);

    SignUpSignIn();
  ?>

  <div id="page-content">
    <div id="glass">
      <div id="page-title"><h1 id="main-title"><?= $mainTitle;?></h1></div>

      <div id="page-description"><p><?= t("home-page.home-description");?></p></div>
    </div>
  </div>

  <?php
    Footer($pathToLogo);
  ?>

  <script src="scripts/homePage.js"></script>
  <script src="scripts/signing.js"></script>
</body>

</html>
