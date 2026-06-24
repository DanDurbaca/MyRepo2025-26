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

if (isset($_POST["LoginPassword"])) {


    $connection = mysqli_connect("localhost", "root", "", "Credits");
    if (!$connection) {
        die("Error creating connection");
    }

    $SqlGetetPsw = $connection->prepare("Select * from Users where Name=?");
    $SqlGetetPsw->bind_param("s", $_SESSION["UserName"]);
    $SqlGetetPsw->execute();
    $result = $SqlGetetPsw->get_result();
    $row = $result->fetch_assoc();
    if (password_verify($_POST["LoginPassword"], $row["Password"])) {
        $_SESSION["Login"] = true;
        header("location:Account.php");
        die();
    }
    print("Incorrect password");
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>

    <form method="POST">
        <input type="password" placeholder="Login Password" name="LoginPassword">
        <input type="submit" value="Login">
    </form>

</body>

</html>