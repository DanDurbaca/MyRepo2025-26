<?php
include_once("function.php");


session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations['Logout'][$language] ?? 'Logout' ?></title>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>

<?php NavigationBar("Logout"); ?>

<h1 class="site-heading" style="margin-top:40px;">
    <?= $arrayOfTranslations['LoggedOut'][$language] ?? 'You have been logged out successfully.' ?>
</h1>

<p style="text-align:center; margin-top:20px; font-size:1.2rem;">
    <a href="login.php?language=<?= htmlspecialchars($language ?? 'en') ?>"><?= $arrayOfTranslations['ReturnToLogin'][$language] ?? 'Return to Login' ?></a>
</p>

</body>
</html>
