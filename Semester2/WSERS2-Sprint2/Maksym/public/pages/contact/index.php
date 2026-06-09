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
  <link rel="stylesheet" href="../../assets/css/contact.css">

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
    <div id="contact-form">
      <h1>Contact us</h1>

      <form action="../../../src/handlers/email_handler.php" method="POST">
        <div>
          <label for="name">Name:</label><br>
          <input name="name" type="text" required>
        </div>

        <div>
          <label for="email">Email:</label><br>
          <input name="email" type="email" required>
        </div>

        <div>
          <label for="subject">Subject:</label><br>
          <input name="subject" type="text" required>
        </div>

        <div>
          <label for="message">Message:</label><br>
          <textarea name="message" required></textarea><br>
        </div>

        <div>
          <input type="submit" value="Submit">
        </div>
      </form>
    </div>

    <div id="contac-data">
      <ul>
        <li>Address: IDK</li>
        <li>Phone number: +7-148-814-8888</li>
        <li>Email: altmarket@inbox.ru</li>
      </ul>
    </div>
  </div>

  <?php
    footer($pathToLogo);
  ?>

  <script src="../assets/js/home-page.js"></script>
  <script src="../assets/js/auth.js"></script>
  <script src="../assets/js/profile.js"></script>
</body>

</html>
