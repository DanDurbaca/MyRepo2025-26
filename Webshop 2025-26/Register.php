<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Register");
    ?>
    <h1>Registration form:</h1>
    <?php
    $bShowForm = true;
    if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"])) {
        $bShowForm = false;
        print("Registration in process<br>");
        // we need to advance with the registration...
        // check if the passwords match:
        if (($_POST["psw"] == $_POST["pswAgain"]) && (!userAlreadyRegistered($_POST["Username"]))) {
            print("Passwords match AND you have chosen a valid user. You will be registered ...");
            // we need TO SAVE the username and password into the "database"
            $fHandler = fopen("Clients.csv", "a");
            fwrite($fHandler, "\n" . $_POST["Username"] . ";" . $_POST["psw"]);
            fclose($fHandler);
        } else {
            $bShowForm = true;
            print("Error. The two passwords do not match OR the user already exists. Please try again !");
        }
    }

    if ($bShowForm) {
    ?>
        <form method="POST">
            <div>Pick a username:</div>
            <input type="test" name="Username">
            <div>Pick a password</div>
            <input type="password" name="psw">
            <input type="password" name="pswAgain">
            <input type="submit" value="Register">
        </form>

    <?php

    }

    ?>



</body>

</html>