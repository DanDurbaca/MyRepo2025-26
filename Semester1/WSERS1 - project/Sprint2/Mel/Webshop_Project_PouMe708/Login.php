<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CS:GO Case Shop</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
<header>
    <img src="pictures/Logo.png" alt="Mystery Box Shop Logo">
    <h1>CS:GO Case Shop</h1>
</header>
<?php
include_once("commoncode.php");
Melnav("Login");
?>
<div class="container">
    <h1><?= $arrayOfTranslations["WelcomeTextLogin"] ?></h1>
    
    <?php
    $bShowForm = true;

function checkUserLogin($username, $password) {
    $file = fopen("Client.csv", "r");

    while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
        if (count($data) >= 5 && trim($data[0]) === $username && password_verify($password, trim($data[1]))) {

            $_SESSION["UserLogged"] = true;
            $_SESSION["UserRole"] = trim($data[4]);

            header("Refresh:0; url=Website.php");
            fclose($file);
            return true;
        }
    }

    fclose($file);
    return false;
}


    if (isset($_POST["Username"], $_POST["Password"])) {
        $bShowForm = false;

        $username = trim($_POST["Username"]);
        $password = trim($_POST["Password"]);

        if (checkUserLogin($username, $password)) {
            echo "<p style='color:green;'>" . $arrayOfTranslations["WelcomeTextLogin2"] . " $username!</p>";
        } else {
            echo "<p style='color:red;'>" . $arrayOfTranslations["WelcomeTextLogin3"] . "</p>";
            $bShowForm = true;
        }
    }

    if ($bShowForm) {
        ?>
        <form method="POST">
            <div><b><?= $arrayOfTranslations["WelcomeTextLogin4"] ?></b>:</div>
            <input type="text" name="Username" style="height: 25px;">
            <div><b><?= $arrayOfTranslations["WelcomeTextLogin5"] ?></b>:</div>
            <input type="password" name="Password" style="height: 25px;"><br><br>

            <input type="submit" value="<?= $arrayOfTranslations["WelcomeTextLogin6"] ?>" style="width: 100px; height: 25px;">
        </form>
        <?php
    }
    ?>
</div>
