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

        $sqlFindUser = $connection->prepare("SELECT * from users where username=?");
        $sqlFindUser->bind_param("s", $_POST["Username"]);
        $sqlFindUser->execute(); // pray that this works

        $sqlResult = $sqlFindUser->get_result();
        // there can be either 0 or 1 result in the sql result
        if ($sqlResult->num_rows == 0) {
            print("Username not found");
        } else {
            $row = $sqlResult->fetch_assoc();
            if (password_verify($_POST["psw"], $row["password"])) {
                //print("You are logged in!");
                $_SESSION["UserLogged"] = true;
                $_SESSION["Username"] = $row["username"];
                // force page refresh.... + redirect to home
                header("Refresh:0; url=Home.php");
            } else {
                print("Passwords do not match.. .please try again");
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
            <input type="submit" value="Login">
        </form>

    <?php
    }

    ?>



</body>

</html>