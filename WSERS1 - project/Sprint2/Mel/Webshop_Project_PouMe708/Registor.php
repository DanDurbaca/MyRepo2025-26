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
Melnav("Registor");
?>
<div class="container">
    <h1><?= $arrayOfTranslations["WelcomeTextRegistor"] ?></h2>
    
    <?php
    $bShowForm = true;
    if (isset($_POST["Username"], $_POST["Password"], $_POST["PswAgain"], $_POST["email"], $_POST["country"])) {
        $bShowForm = false;
        print($arrayOfTranslations["WelcomeTextRegistor2"]);
        if (($_POST["Password"] == $_POST["PswAgain"]) && (!userAlreadyRegistered($_POST["Username"]))) {
            print($arrayOfTranslations["WelcomeTextRegistor3"]);
            $fHandler = fopen("Client.csv", "a");
            fwrite($fHandler,"\n".$_POST["Username"].";".password_hash($_POST["Password"], PASSWORD_DEFAULT).";".$_POST["email"].";".$_POST["country"].";" . "user");
            fclose($fHandler);
        }
        else {
            $bSlowForm = true;
            print($arrayOfTranslations["WelcomeTextRegistor4"]);
        }
    }

    if($bShowForm){
        ?>
        <form method="POST">
            <div><?= $arrayOfTranslations["WelcomeTextRegistor5"] ?></div>
            <input type="test" name="Username" style="height: 25px; width: 200px">
            <div><?= $arrayOfTranslations["WelcomeTextRegistor6"] ?></div>
            <input type="email" name="email" style="height: 25px; width: 200px">
            <div><?= $arrayOfTranslations["WelcomeTextRegistor7"] ?></div>
            <input type="password" name="Password" style="height: 25px; width: 200px">
            <input type="password" name="PswAgain" style="height: 25px; width: 200px">
            <div><?= $arrayOfTranslations["WelcomeTextRegistor8"] ?></div>
            <input type="test" name="country" style="height: 25px; width: 200px"><br><br>

            <button type="submit"><?= $arrayOfTranslations["WelcomeTextRegistor9"] ?></button>
        </form>
        <?php
    }
    ?>
    
    
</div>
</body>
</html>
