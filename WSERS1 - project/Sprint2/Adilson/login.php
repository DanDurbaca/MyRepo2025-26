<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyModificationGarage LogIn</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("login");
    ?>
    <?php
    $aShowForm = true;

    if (isset($_POST["userName"], $_POST["psw"])) {
        $aShowForm = false;
        print($arrayOfTranslations["lgncheck"] . "<br>");
        $user = isset($_POST["userName"]) ? trim($_POST["userName"]) : '';
        $psw = isset($_POST["psw"]) ? trim($_POST["psw"]) : '';

        if (verifyUserCredentials($user, $psw) === true) {
            // successful login
            print($arrayOfTranslations["lgnsuccess"] . htmlspecialchars($user));
            $_SESSION["UserLogged"] = true;
            $_SESSION["LoggedUserName"] = $user;
            $_SESSION["ADMIN"] = $admin;
            header("Refresh:0; url=Home.php"); //redirect to home page
        } else {
            // failed login
            print($arrayOfTranslations["lgnfail"]);
            $aShowForm = true;
            $_SESSION["ADMIN"] = "no";
        }
    }

    if ($aShowForm) {
    ?>
        <form method="POST">
            <div><?= $arrayOfTranslations["lgnusr"] ?></div><br>
            <input type="text" name="userName"><br>
            <div><?= $arrayOfTranslations["lgnpssw"] ?></div><br>
            <input type="password" name="psw"><br>
            <input type="submit" value="Log In">
        </form>
    <?php
    }
    ?>
</body>

</html>