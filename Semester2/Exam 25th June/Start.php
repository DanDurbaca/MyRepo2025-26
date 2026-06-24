<?php
session_start();

if (count($_SESSION) > 0) {
    session_unset();
    session_destroy();
    header("location:Start.php");
    die();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start</title>
</head>

<body>
    <form method="POST" action="LoginOrRegister.php">
        <input type="text" placeholder="Name" name="UserName">
        <input type="submit" value="Login or register">
    </form>

</body>

</html>