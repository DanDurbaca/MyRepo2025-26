<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <link rel="stylesheet" href="style.css?<?= time() ?>">
    <meta charset="utf-8">
    <title>Register</title>

</head>

<body class="restBG">

    <?php
    include_once("function.php");
    NavigationBar("Register");
    $connection = new mysqli("localhost", "root","","daxda083");
    ?>
    <H1 class="RH1">
        <?= $arrayTranslation["Passwordlable"] ?>
    </H1>
    <?php
    $bShowForm = true;
    if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"], $_POST["email"], $_POST["PhoneNumber"])) {
        $bShowForm = false;
        print($arrayTranslation["Progresslable"]);

        if (($_POST["psw"] == $_POST["pswAgain"]) && (!userAlreadyRegistered($_POST["Username"]))) {
            $sqlQuery = $connection->prepare("INSERT INTO clients(username, clientPassword, Email, Phone) values(?,?,?,?);");
            $hashed_password = password_hash($_POST["psw"], PASSWORD_DEFAULT);
            $_POST["Username"] = htmlspecialchars($_POST["Username"]);
            $_POST["email"] = htmlspecialchars($_POST["email"]);
            $_POST["PhoneNumber"] = htmlspecialchars($_POST["PhoneNumber"]);
            $sqlQuery->bind_param("ssss", $_POST["Username"],$hashed_password , $_POST["email"], $_POST["PhoneNumber"] );
            $sqlQuery->execute();
            print($arrayTranslation["PasswordUserErrorlable"]);
        } else {
            $bShowForm = true;
            print($arrayTranslation["PasswordErrorlable"]);
        }
    }
    if ($bShowForm) {
    ?>
        <form class="Register"method="POST">
            <div><?= $arrayTranslation["Userlable"] ?></div>
            <input type="text" name="Username">
            <div><?= $arrayTranslation["1passwordlable"] ?></div>
            <input type="password" name="psw">
            <div><?= $arrayTranslation["2passwordlable"] ?></div>
            <input type="password" name="pswAgain">
            <div><?= $arrayTranslation["EmailLable"] ?></div>
            <input type="email" name="email">
            <div><?= $arrayTranslation["PhoneLable"] ?></div>
            <input type="Phone number" name="PhoneNumber">
            <input type="submit" value=<?= $arrayTranslation["registerlable"] ?>>
        </form>

    <?php
    }
    ?>


</body>

</html>