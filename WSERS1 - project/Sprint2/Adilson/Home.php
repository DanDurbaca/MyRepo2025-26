<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyModificationGarage</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Home");
    ?>

    <h2><?=$arrayOfTranslations["HomeTextH1"]?></h2>
    <p><?=$arrayOfTranslations["HomeText"]?></p>
</body>

</html>