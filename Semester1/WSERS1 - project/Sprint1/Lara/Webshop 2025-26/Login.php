<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Pixel Potion Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar($arrayOfTranslations["LoginBtn"]);

    $message = "";


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
        $foundUser = false;


        if (($fileHandler = fopen("Clients.csv", "r")) !== false) {
            while (($data = fgetcsv($fileHandler, 1000, ";")) !== false) {

                if (count($data) >= 4) {
                    $storedUsername = trim($data[0]);
                    $storedPassword = trim($data[3]);

                    if ($username === $storedUsername && $password === $storedPassword) {
                        $foundUser = true;
                        break;
                    }
                }
            }
            fclose($fileHandler);
        }


        if ($username === $storedUsername && $password === $storedPassword) {
            $message = "<div class='success'>" . $arrayOfTranslations["LoginMessageSuccess"] . "</div>";
        } else {
            $message = "<div class='error'>" . $arrayOfTranslations["LoginMessageError"] . "</div>";
        }
    }
    ?>

    <div class="login-container">
        <h2><?= $arrayOfTranslations["LoginTitle"] ?></h2>

        <form method="POST" action="Login.php">
            <label for="username"><?= $arrayOfTranslations["LoginUsername"] ?></label>
            <input type="text" id="username" name="username" placeholder="<?= $arrayOfTranslations["LoginUsernameEnter"] ?>" required>

            <label for="password"><?= $arrayOfTranslations["LoginPassword"] ?></label>
            <input type="password" id="password" name="password" placeholder="<?= $arrayOfTranslations["LoginPasswordEnter"] ?>" required>

            <button type="submit"><?= $arrayOfTranslations["LoginBtn"] ?></button>
        </form>

        <?= $message ?>
    </div>

</body>

</html>