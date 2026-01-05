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
Melnav("Cart");
?>
<div class="container">
    <h1>
  <a href="https://www.youtube.com/watch?v=BBJa32lCaaY&t=2s" style="text-decoration:none; color:inherit;">
    <?= $arrayOfTranslations["WelcomeTextCart"] ?>
  </a>
</h1>
</div>
</body>
</html>