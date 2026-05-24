<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Register</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Register");

    // Handle logout requests from this page
    if (isset($_POST["Logout"])) {
        session_unset();
        session_destroy();
        header("Refresh:0");
    }
    ?>
    <?php

    // Handle registration submission
    if (isset($_POST["username"], $_POST["psw"], $_POST["pswAgain"], $_POST["Email"], $_POST["FirstName"], $_POST["LastName"])) {
        print $arrayOfStrings["RegisterRegistration"];
        if ($_POST["psw"] == $_POST["pswAgain"]) {

            if (userAlreadyExists($_POST["username"])) {
                print $arrayOfStrings["RegisterExists"];
            } else {
                $hashedPassword = password_hash($_POST["psw"], PASSWORD_DEFAULT);
                // Insert the new user record
                $sqlInsert = $connection->prepare("INSERT INTO `user` (pk_username, firstName, lastName, password, email) VALUES (?, ?, ?, ?, ?);");
                $sqlInsert->bind_param("sssss", $_POST["username"], $_POST["FirstName"], $_POST["LastName"], $hashedPassword, $_POST["Email"]);
                $sqlInsert->execute();
                /*$goodPassword = str_replace(";", "#", $_POST["psw"]);
                // print($goodPassword);
                fputs($fileUsers, "\n" . $_POST["username"] . ";" . $goodPassword . ";" . "0" . ";");*/
                print $arrayOfStrings["RegisterSuccesfully"]; ?> <a href="index.php"><?php print $arrayOfStrings["LoginPage"] ?></a></p> <?php
            }
        } else {
            print $arrayOfStrings["RegisterPassMatch"];
        }
    } //Insert Into Clients (pseudo,password,usertype,Email, FirstName, LastName) Values("testuser","testpass",0,"testemail","testfirst","testlast");
    ?>

    <h1> <?php print $arrayOfStrings["RegisterFrom"] ?> </h1>

    <!-- Registration form -->
    <form method="POST">
        <input type="text" name="username" placeholder="<?php print $arrayOfStrings["RegisterFormUser"] ?>" />
        </br>
        <input type="password" name="psw" placeholder="<?php print $arrayOfStrings["RegisterFormPass"] ?>" />
        </br>
        <input type="password" name="pswAgain" placeholder="<?php print $arrayOfStrings["RegisterFormPass2"] ?>" />
        </br>
        <input type="text" name="Email" placeholder="<?php print $arrayOfStrings["Email"] ?>" />
        </br>
        <input type="text" name="FirstName" placeholder="<?php print $arrayOfStrings["FirstName"] ?>" />
        </br>
        <input type="text" name="LastName" placeholder="<?php print $arrayOfStrings["LastName"] ?>" />
        </br></br>
        <input type="submit" value="<?php print $arrayOfStrings["RegisterBotton"] ?>">
        </br></br>
    </form>
</body>
</html>