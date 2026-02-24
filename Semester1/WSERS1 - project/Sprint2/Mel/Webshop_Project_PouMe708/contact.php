<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact - CS:GO Case Shop</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
<header>
    <img src="pictures/Logo.png" alt="Mystery Box Shop Logo">
    <h1>CS:GO Case Shop</h1>
</header>
<?php
include_once("commoncode.php");
Melnav("Contact");
?>
<div class="container">
    <h1><?= $arrayOfTranslations["WelcomeTextContact"] ?></h1>
    <p><?= $arrayOfTranslations["WelcomeTextContact2"] ?></p>
    <form>
        <label><?= $arrayOfTranslations["WelcomeTextContact3"] ?></label><br>
        <input type="text" name="name"><br><br>
        <label><?= $arrayOfTranslations["WelcomeTextContact4"] ?></label><br>
        <input type="email" name="email"><br><br>
        <label><?= $arrayOfTranslations["WelcomeTextContact5"] ?></label><br>
        <textarea name="message"></textarea><br><br>
        <button type="submit"><?= $arrayOfTranslations["WelcomeTextContact6"] ?></button>
    </form>
    <?php
    if (isset($_GET["name"])){
        ?>
    <p style="color:green"><?= $arrayOfTranslations["WelcomeTextContact7"] ?> <?= $_GET["name"] ?> <?= $arrayOfTranslations["WelcomeTextContact8"] ?> </p></color>
    <?php
    $fHandler = fopen("messageslist.csv", "a");
    fwrite($fHandler,"\n".$_GET["name"] . ";" . $_GET["email"] . ";" . $_GET["message"]);
    }
    ?>
</div>
</body>
</html>
