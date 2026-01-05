<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Guild | Pixel Potion Shop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Cinzel+Decorative&family=VT323&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar($arrayOfTranslations["RegisterBtn"]);

    $showForm = true;

    if (isset($_POST['username'], $_POST['displayname'], $_POST['email'], $_POST['password'], $_POST['passwordAgain'])) {
        $showForm = false;
        print "<div class='register'><p>✨ Registration in progress...</p>";

        if ($_POST['password'] == $_POST['passwordAgain'] && !userAlreadyRegistered($_POST['username'])) {
            print "<p>✅ Passwords match! Welcome, apprentice brewer <strong>{$_POST['displayname']}</strong>.<br>";
            print "Your scroll of membership is being recorded in the guild archives...</p>";

            
            $fileHandler = fopen("Clients.csv", "a");
            fwrite(
                $fileHandler,
                "\n" .
                    $_POST['username'] . ";" .
                    $_POST['displayname'] . ";" .
                    $_POST['email'] . ";" .
                    $_POST['password']
            );
            fclose($fileHandler);

            print "<p>Registration successful!!</p></div>";
        } else {
            $showForm = true;
            print "<p>❌ Error: Either your passwords do not match, or the adventurer name already exists.<br>";
            print "Please try again, noble brewer!</p></div>";
        }
    }

    if ($showForm) {
    ?>
        <section class="register">
            <h2><?= $arrayOfTranslations["RegisterTitle"] ?></h2>
            <p><?= $arrayOfTranslations["RegisterText"] ?></p>

            <form class="register-form" method="POST">
                
                <label for="username"><?= $arrayOfTranslations["RegisterUsername"] ?></label>
                <input type="text" id="username" name="username" placeholder="<?= $arrayOfTranslations["RegisterUsernamePlaceholder"] ?>" required>

                
                <label for="displayname"><?= $arrayOfTranslations["DisplayName"] ?></label>
                <input type="text" id="displayname" name="displayname" placeholder="<?= $arrayOfTranslations["DisplayNamePlaceholder"] ?>" required>

                
                <label for="email"><?= $arrayOfTranslations["RegisterEmail"] ?></label>
                <input type="email" id="email" name="email" placeholder="<?= $arrayOfTranslations["RegisterEmailPlaceholder"] ?>" required>

                
                <label for="password"><?= $arrayOfTranslations["RegisterSecretPassword"] ?></label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>

                
                <label for="passwordAgain"><?= $arrayOfTranslations["RegisterSecretPasswordRepeat"] ?></label>
                <input type="password" id="passwordAgain" name="passwordAgain" placeholder="••••••••" required>

                
                <button type="submit"><?= $arrayOfTranslations["RegisterPageButton"] ?></button>
            </form>
        </section>
    <?php
    }
    ?>
</body>

</html>
