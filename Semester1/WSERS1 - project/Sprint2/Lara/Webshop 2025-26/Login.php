<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="Login.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Pixel Potion Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">
</head>

<body>

    <?php
    include_once("CommonCode.php");
    includeCSS("Login.css");

    NavigationBar($arrayOfTranslations["LoginBtn"]);

    $message = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        $loginSuccess = false;

        if (($fileHandler = fopen("Clients.csv", "r")) !== false) {

            while (($data = fgetcsv($fileHandler, 1000, ";")) !== false) {


                if (count($data) >= 5) {

                    $storedUsername = trim($data[0]);
                    $storedPasswordHash = trim($data[3]);
                    $storedUserType = trim($data[4]);

                    if ($username === $storedUsername) {

                        if (password_verify($password, $storedPasswordHash)) {

                            $loginSuccess = true;

                            //Set session variables
                            $_SESSION["UserLogged"] = true;
                            $_SESSION["Username"] = $storedUsername;
                            $_SESSION["UserType"] = $storedUserType;
                            header("Location: Home.php?lang=" . $lang);
                        }

                        break;
                    }
                }
            }

            fclose($fileHandler);
        }

        if ($loginSuccess) {
            $message = "<div class='success'>" . $arrayOfTranslations["LoginMessageSuccess"] . "</div>";
        } else {
            $message = "<div class='error'>" . $arrayOfTranslations["LoginMessageError"] . "</div>";
        }
    }
    ?>

    <div class="login-container">
        <h2><?= $arrayOfTranslations["LoginTitle"] ?></h2>

        <form method="POST" action="Login.php?lang=<?= $language ?>">
            <label for="username"><?= $arrayOfTranslations["LoginUsername"] ?></label>
            <input type="text" id="username" name="username"
                placeholder="<?= $arrayOfTranslations["LoginUsernameEnter"] ?>" required>

            <label for="password"><?= $arrayOfTranslations["LoginPassword"] ?></label>
            <input type="password" id="password" name="password"
                placeholder="<?= $arrayOfTranslations["LoginPasswordEnter"] ?>" required>

            <button type="submit"><?= $arrayOfTranslations["LoginBtn"] ?></button>
        </form>

        <?= $message ?>
    </div>

</body>

</html>