<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Mystery Box Shop</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
<header>
    <img src="pictures/Logo.png" alt="Mystery Box Shop Logo">
    <h1>CS:GO Case Shop</h1>
</header>
<?php
include_once("commoncode.php");
Melnav("About Us");
?>
<div class="container">
    <h1><?= $arrayOfTranslations["WelcomeTextaboutus"] ?></h1>


    <p><?= $arrayOfTranslations["WelcomeTextaboutus2"] ?></p>
        
    <p><?= $arrayOfTranslations["WelcomeTextaboutus3"] ?></p>
        
    <p><?= $arrayOfTranslations["WelcomeTextaboutus4"] ?></p>
</div>
</body>
</html>
