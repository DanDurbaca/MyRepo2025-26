<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Login</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Login");

    // Handle logout request
    if (isset($_POST["Logout"])) {
        session_unset();
        session_destroy();
        header("Refresh:0");
    }
    ?>
    <?php

    // Helper: get a user's role
    function Usertype($user)
    {
        global $connection;
        // Prepare query to fetch the user's role
        $sqlSelect = $connection->prepare("SELECT role FROM `user` WHERE pk_username = ?");
        $sqlSelect->bind_param("s", $user);
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        $row = $result->fetch_assoc();
        return $row["role"];
    }
    // Handle password and profile update
    if (isset($_POST["changePasswordBtn"])) {
        $user = $_SESSION["User"];
        $old = $_POST["oldPassword"];
        $new = $_POST["newPassword"];
        $confirm = $_POST["newPasswordConfirm"];
        $newUsername = $_POST["newUsername"];
        $newEmail = $_POST["newEmail"];
        $newFirstName = $_POST["newFirstName"];
        $newLastName = $_POST["newLastName"];

        if ($new !== $confirm) {
            echo "<p>" . $arrayOfStrings["PasswordsDoNotMatch"] . "</p>";
        } else {
            // Prepare query to fetch current password hash
            $sql = $connection->prepare("SELECT password FROM `user` WHERE pk_username = ?");
            $sql->bind_param("s", $user);
            $sql->execute();
            $result = $sql->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($old, $row['password'])) {
                    $newHashed = password_hash($new, PASSWORD_DEFAULT);
                    // Prepare update to change user profile and password
                    $update = $connection->prepare("UPDATE `user` SET pk_username = ?, password = ?, email = ?, firstName = ?, lastName = ? WHERE pk_username = ?");
                    $update->bind_param("ssssss", $newUsername, $newHashed, $newEmail, $newFirstName, $newLastName, $user);
                    $update->execute();

                    $_SESSION["User"] = $newUsername;

                    echo "<p>" . $arrayOfStrings["PasswordChangedSuccess"] . "</p>";
                } else {
                    echo "<p>" . $arrayOfStrings["OldPasswordWrong"] . "</p>";
                }
            }
        }
    }

    include_once("CommonCode.php");
    //Login();

    // Handle login submission
    if (isset($_POST["username"], $_POST["psw"])) {
        if (userAlreadyExists($_POST["username"])) {
            if (checkUsersPassword($_POST["username"], $_POST["psw"])) {
                print $arrayOfStrings["LoginCorrect"];

                // Save username in session
                $_SESSION["UserLoggedIn"] = true;
                $_SESSION["User"] = $_POST["username"]; // Save username
                $_SESSION["role"] = Usertype($_SESSION["User"]);

                header("Location: Menu.php"); //redirects to the welcome page
                exit;
            } else {
                print $arrayOfStrings["LoginInvalid"];
            }
        } else {
            print $arrayOfStrings["LoginDatabase"];
        }
    }

    //faudra faire un function checkAdmin

    // Render logged-in view (logout + change password)
    if ($_SESSION["UserLoggedIn"]) {
    ?>
        <h1><?php print $arrayOfStrings["Logout"] ?> :</h1>
        <form method="POST">
            <input type="submit" value="Logout" name="Logout">
        </form>
        <?php
        if (isset($_SESSION["UserLoggedIn"]) && $_SESSION["UserLoggedIn"]) {
        ?>
            <h1><?php print $arrayOfStrings["ChangePasswordTitle"] ?> :</h1>
            <form method='POST'>
                <input type="password" name="oldPassword" placeholder=" <?php print $arrayOfStrings["OldPassword"] ?>" /><br />
                <input type="password" name="newPassword" placeholder=" <?php print $arrayOfStrings["NewPassword"] ?>" /><br />
                <input type="password" name="newPasswordConfirm" placeholder=" <?php print $arrayOfStrings["ConfirmNewPassword"] ?> " /><br />
                <input type="text" name="newUsername" placeholder="<?php print $arrayOfStrings["newUsername"] ?>" /></br>
                <input type="text" name="newEmail" placeholder="<?php print $arrayOfStrings["Email"] ?>" /></br>
                <input type="text" name="newFirstName" placeholder="<?php print $arrayOfStrings["FirstName"] ?>" /></br>
                <input type="text" name="newLastName" placeholder="<?php print $arrayOfStrings["LastName"] ?>" /></br>
                <input type="submit" name="changePasswordBtn" value=" <?php print $arrayOfStrings["ChangePasswordButton"] ?> " />
            </form>
        <?php
        }

        // DISPLAY lOGOUT
    } else {
        // Render login form for guests
        ?>
        <h1><?php print $arrayOfStrings["LoginForm"] ?> :</h1>

        <form method="POST">
            <input type="text" name="username" placeholder="<?php print $arrayOfStrings["LoginFormUser"] ?>" />
            </br>
            <input type="password" name="psw" placeholder=" <?php print $arrayOfStrings["LoginFormPass"] ?>" />
            </br></br>
            <input type="submit" value="<?php print $arrayOfStrings["Login"] ?>">
        </form>
        <p><?php print $arrayOfStrings["LoginNoAccount"] ?> <a href="Register.php"><?php print $arrayOfStrings["RegisterHere"] ?></a></p> 
    <?php
    }

    ?>
</body>

</html>