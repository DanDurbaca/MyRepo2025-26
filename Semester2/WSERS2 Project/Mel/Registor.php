<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact - CS:GO Case Shop</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body class="contact-page">
<header>
    <img src="pictures/Logo.png" alt="Mystery Box Shop Logo">
    <h1>CS:GO Case Shop</h1>
</header>
<?php
include_once("commoncode.php");
Melnav("Registor");
?>
<div class="container">
    <div class="contact-content">
        <h1><?= $arrayOfTranslations["WelcomeTextRegistor"] ?></h1>
    
    <?php 
    $bShowForm = true;
    if (isset($_POST["Username"], $_POST["Password"], $_POST["PswAgain"], $_POST["email"], $_POST["country"])) {
        $bShowForm = false;
        print($arrayOfTranslations["WelcomeTextRegistor2"]);
        if (($_POST["Password"] == $_POST["PswAgain"]) && (!userAlreadyRegistered($_POST["Username"]))) {
            print($arrayOfTranslations["WelcomeTextRegistor3"]);
           
            // $fHandler = fopen("Client.csv", "a");
            // fwrite($fHandler,"\n".$_POST["Username"].";".password_hash($_POST["Password"], PASSWORD_DEFAULT).";".$_POST["email"].";".$_POST["country"].";" . "user");
            // fclose($fHandler);

            $sqlInsert = $connection->prepare("insert into Client(Username,HashedPassword,email,country,websiteRole) VALUES(?,?,?,?, 'user')");
            $hashedPassword = password_hash($_POST["Password"],PASSWORD_DEFAULT);
            $sqlInsert->bind_param("ssss",$_POST["Username"],$hashedPassword,$_POST["email"],$_POST["country"]);
            $sqlInsert-> execute();

        }
        else {
            $bSlowForm = true;
            print($arrayOfTranslations["WelcomeTextRegistor4"]);
        }
    }

    if($bShowForm){
        ?>
            <form method="POST">
                <label><?= $arrayOfTranslations["WelcomeTextRegistor5"] ?></label>
                <input type="text" name="Username">

                <label><?= $arrayOfTranslations["WelcomeTextRegistor6"] ?></label>
                <input type="email" name="email">

                <label><?= $arrayOfTranslations["WelcomeTextRegistor7"] ?></label>
                <input type="password" name="Password">

                <label>Repeat Password</label>
                <input type="password" name="PswAgain">

                <label><?= $arrayOfTranslations["WelcomeTextRegistor8"] ?></label>
                <input type="text" name="country">

                <button type="submit"><?= $arrayOfTranslations["WelcomeTextRegistor9"] ?></button>
            </form>
        <?php
    }
    ?>
    
    </div>
</div>
</body>
</html>
