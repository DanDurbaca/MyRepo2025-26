<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CS:GO Case Shop</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
<header>
    <img src="pictures/Logo.png" alt="Logo"> 
    <h1>CS:GO Case Shop</h1>
</header>
<?php
include_once("commoncode.php");
Melnav("Home");
?>
<div class="container">
    <h1><?= $arrayOfTranslations["WelcomeText1"] ?></h1>
    <p><?= $arrayOfTranslations["WelcomeText2"] ?></p>
</div>
</body>
</html>
