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

try {
$stmt = $pdo->query("SELECT m.id, m.message, COALESCE(m.username, u.username) AS display_name  FROM messages m  LEFT JOIN users u ON m.user_id = u.id");

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch (PDOException $e) {
   exit("Error: " . $e);
}

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
<link rel="stylesheet" href="../../assets/css/forum.css">


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
  <div id="prev-messages">
    <?php for ($i = 0; $i < count($messages); $i++) { ?>
      <div><?= htmlspecialchars($messages[$i]["display_name"] . ": " . $messages[$i]["message"]) ?></div>
    <?php } ?>
  </div>

  <form action="../../helpers/forum_action.php" method="POST">
    <label for="message">Type your message here:</label></br>
    <input id="message-field" name="message" type="text" required><br>

    <input type="submit" value="Send">
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
