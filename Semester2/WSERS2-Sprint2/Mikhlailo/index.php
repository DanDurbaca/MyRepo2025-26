<?php include_once("function.php"); ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations['site_title'][$language] ?? 'OrangeShop' ?></title>
</head>
<body>
    <?php
    NavigationBar($page="Home");
    ?>

   
    <p style="text-align:center;">
    <img src="https://ebentually.wordpress.com/wp-content/uploads/2012/06/eben_orange01.gif?w=696" alt="An Orange GIF" style="max-width:100%;height:auto;border:0;margin-top:20px;">
    </p>
</body>
</html>