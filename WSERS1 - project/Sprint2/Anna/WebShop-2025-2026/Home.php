<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar($arrayOfTranslations["HomeBtn"]);
    ?>
    <div class="welcome divCentered">
        <h1> <?= $arrayOfTranslations["HomeText"] ?></h1>
        <p class="fontp">
            <?= $arrayOfTranslations["HomeP"] ?>
        </p>
    </div>
</body>

</html>