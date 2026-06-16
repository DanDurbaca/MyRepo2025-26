<?php
session_start();

$connection = mysqli_connect("localhost", "root", "", "examWSERS2");
if (!$connection) {
    die("Error creating connection");
}

if (isset($_POST["Logout"])) {
    $sqlUserUpdate = $connection->prepare("Update People set Money=? where Name=?");
    $sqlUserUpdate->bind_param("is", $_SESSION["Money"], $_SESSION["User"]);
    $sqlUserUpdate->execute();

    session_unset();
    session_destroy();
    header("Refresh:0");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    if (!isset($_SESSION["UserLoggedIn"])) {
        $_SESSION["UserLoggedIn"] = false;
    }

    if (isset($_POST["PromoCode"])) {
        $sqlUserWin = $connection->prepare("SELECT * from Promotions where Code=?");
        $sqlUserWin->bind_param("s", $_POST["PromoCode"]);
        $sqlUserWin->execute();
        $result = $sqlUserWin->get_result();
        if ($result->num_rows == 0) {
            print("Sry, there is no promotion with that code");
        } else {
            $rowPromo = $result->fetch_assoc();
            if ($rowPromo["Available"] > 0) {
                print("Well done !");
                $sqlUpdatePromo = $connection->prepare("Update Promotions set Available= Available-1 where Code=?");
                $sqlUpdatePromo->bind_param("s", $_POST["PromoCode"]);
                $sqlUpdatePromo->execute();
                $_SESSION["Money"] += $rowPromo["Value"];
            } else
                print("Sry, you just missed our promotion");
        }
    }


    if (isset($_POST["NameOfUser"])) {
        $sqlUserCheck = $connection->prepare("SELECT * from People where Name=?");
        $sqlUserCheck->bind_param("s", $_POST["NameOfUser"]);
        $sqlUserCheck->execute();
        $result = $sqlUserCheck->get_result();
        if ($result->num_rows == 0) {
            $sqlCreateUser = $connection->prepare("Insert into People(Name,Money) Values(?,0)");
            $sqlCreateUser->bind_param("s", $_POST["NameOfUser"]);
            $sqlCreateUser->execute();
            $_SESSION["Money"] = 0;
        } else {
            $rowUser = $result->fetch_assoc();
            $_SESSION["Money"] = $rowUser["Money"];
        }
        $_SESSION["User"] = $_POST["NameOfUser"];
        $_SESSION["UserLoggedIn"] = true;
    }


    if (!$_SESSION["UserLoggedIn"]) {
    ?>
        <form method="POST">
            <input placeholder="Name" name="NameOfUser">
            <input type="submit" value="Login">
        </form>
    <?php
    } else {
    ?>
        <h1>Welcome <?= $_SESSION["User"] ?>. You have <?= $_SESSION["Money"] ?> in your account</h1>
        <form method="POST">
            <input placeholder="enter promo code" name="PromoCode">
            <input type="submit" value="Win">
        </form>

        <form method="POST">
            <input type="submit" name="Logout" value="Logout">
        </form>
    <?php
    }
    ?>

</body>

</html>