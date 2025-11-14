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
    NavigationBar("Login");
    ?>
    <h1>Login form:</h1>
    <?php
    $bShowForm = true;

    if (isset($_POST["Username"], $_POST["psw"])) {
        $bShowForm = false;
        $file = fopen("Clients.csv", "r");
        while (!feof($file)) {
            $line = fgets($file);
            $line_exploded = explode(";", $line);

            $userNameInFile = trim($line_exploded[0]);
            $pswInFile = str_replace("\n", "", $line_exploded[1]);



            if ($_POST["Username"] == $userNameInFile) {
                print("UserName found .. checking password");
                if ($_POST["psw"] == $pswInFile) {
                    print("You are logged in!");
                } else {
                    print("Passwords do not match.. .please try again");
                }
            }
        }
    }

    if ($bShowForm) {
    ?>
        <form method="POST">
            <div>Username:</div>
            <input type="test" name="Username">
            <div>Password</div>
            <input type="password" name="psw">
            <input type="submit" value="Register">
        </form>

    <?php

    }

    ?>



</body>

</html>