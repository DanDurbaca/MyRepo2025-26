<?php
session_start();

if (!isset($_SESSION["UserName"])) {
    header("location:Start.php");
    die();
}

if (!isset($_SESSION["Login"])) $_SESSION["Login"] = false;

if ($_SESSION["Login"]) {
    header("location:Account.php");
    die();
}



if (isset($_POST["RegisterPsw1"], $_POST["RegisterPsw2"])) {
    if ($_POST["RegisterPsw1"] == $_POST["RegisterPsw2"]) {

        $connection = mysqli_connect("localhost", "root", "", "Credits");
        if (!$connection) {
            die("Error creating connection");
        }
        $psw = password_hash($_POST["RegisterPsw1"], PASSWORD_DEFAULT);
        $SqlSetPsw = $connection->prepare("Update Users Set Password=? where Name=?");
        $SqlSetPsw->bind_param("ss", $psw, $_SESSION["UserName"]);
        $SqlSetPsw->execute();
        $_SESSION["Login"] = true;

        header("location:Account.php");
        die();
    }
    print("Passwords do not match");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>

<body>

    <form method="POST">
        <input type="password" placeholder="Register Password" name="RegisterPsw1">
        <input type="password" placeholder="Register Password again" name="RegisterPsw2">
        <input type="submit" value="Register">
    </form>

</body>

</html>