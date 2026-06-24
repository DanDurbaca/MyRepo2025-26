<?php
session_start();

if (!isset($_SESSION["UserName"])) {
    header("location:Start.php");
    die();
}

if (!isset($_SESSION["Login"])) $_SESSION["Login"] = false;

if (!$_SESSION["Login"]) {
    header("location:Start.php");
    die();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
</head>

<body>
    <h1>Welcome <?= $_SESSION["UserName"] ?></h1>

</body>

</html>