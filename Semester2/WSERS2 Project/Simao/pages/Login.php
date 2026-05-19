<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css?<?= time() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPEM - HTSTA Final Project 2024</title>
</head>
 
<body>
    <?php
    include_once("CommonCode.php");
    navBar("Login");
    ?>
    <?php
    $aShowForm = true;
 
    if (isset($_POST["username"], $_POST["pswd"])) {
        $aShowForm = false;
        print($arrayOfTranslations["LoginOut2"] . "<br>");
        $user = isset($_POST["username"]) ? trim($_POST["username"]) : '';
        $psw = isset($_POST["pswd"]) ? trim($_POST["pswd"]) : '';
 
        if (verifyUserCredentials($user, $psw) === true) {
            // successful login
            print($arrayOfTranslations["LoginOut2"] . htmlspecialchars($user));
            $_SESSION["UserLogged"] = true;
            $_SESSION["Username"] = $user;
            $_SESSION["IsAdmin"] = $admin;
            header("Refresh:0; url=index.php"); //redirect to home page
        } else {
            // failed login
            print($arrayOfTranslations["LoginOut3"]);
            $aShowForm = true;
            $_SESSION["IsAdmin"] = 0;
        }
    }
 
    if ($aShowForm) {
    ?>
        <form method="POST">
            <div><?= $arrayOfTranslations["LoginLabel1"] ?></div><br>
            <input type="text" name="username"><br>
            <div><?= $arrayOfTranslations["LoginLabel2"] ?></div><br>
            <input type="password" name="pswd"><br>
            <input type="submit" value="Login">
        </form>
    <?php
    }
    ?>
</body>
 
</html>
 