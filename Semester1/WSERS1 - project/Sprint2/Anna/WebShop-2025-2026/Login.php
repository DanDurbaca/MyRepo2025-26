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
    NavigationBar($arrayOfTranslations["LoginBtn"]);
    ?>
    <div class="text">
        <?php
        $aShowForm = true;

        if (isset($_POST["userName"], $_POST["psw"])) {
            $aShowForm = false;
            print($arrayOfTranslations["logincheck"] . "<br>");
            $user = isset($_POST["userName"]) ? trim($_POST["userName"]) : '';
            $pswd = isset($_POST["psw"]) ? trim($_POST["psw"]) : '';

            if (verifyUserCredentials($user, $pswd) === true) {
    // successful login
        $_SESSION["UserLogged"] = true;
        $_SESSION["Username"] = $user;
        header("Location: Home.php"); //redirect to home page
        exit();
    }
    else {
                // failed login
                print($arrayOfTranslations["loginfail"]);
                $aShowForm = true;
            }
        }
        ?>
    </div>
    <?php

    if ($aShowForm) {
    ?>

        <form method="POST" class="divCentered">
            <h1 class="fonth1"><?= $arrayOfTranslations["LoginT"] ?></h1>
            <div class="fontp"><?= $arrayOfTranslations["loginuser"] ?></div>
            <input type="text" name="userName"><br>
            <div class="fontp"><?= $arrayOfTranslations["loginpassword"] ?></div>
            <input type="password" name="psw"><br>
            <input type="submit" value="Log In">
        </form>
    <?php
    }
    ?>

</body>

</html>