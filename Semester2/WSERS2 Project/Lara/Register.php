<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="Register.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Guild | Pixel Potion Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Cinzel+Decorative&family=VT323&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    include_once("CommonCode.php");
    includeCSS("Register.css");;


    NavigationBar($arrayOfTranslations["RegisterBtn"]);

    $showForm = true;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $username = trim($_POST['username']);
        $displayname = trim($_POST['displayname']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $passwordAgain = trim($_POST['passwordAgain']);

        echo "<div class='register'><p>✨ Registration in progress...</p>";

        if ($password !== $passwordAgain) {
            echo "<p>❌ Passwords do NOT match. Please try again!</p></div>";
        } elseif (userAlreadyRegistered($username)) {
            echo "<p>❌ Adventurer name already exists in the guild registry.</p></div>";
        } else {

        //Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        
        $userType = "regular";

        
        $connection = new mysqli("localhost", "root", "", "webshop2025_26");
        $sqlQuery = $connection->prepare("INSERT INTO users (Username, DisplayName, Email, UserPassword, UserType) VALUES (?, ?, ?, ?, ?);");

        
        $sqlQuery->bind_param("sssss", $username, $displayname, $email, $hashedPassword, $userType);
        $sqlQuery->execute();

            echo "<p>✅ Welcome, <strong>$displayname</strong>!<br>";
            echo "Your information has been added to the guild archives.</p></div>";

            $showForm = false;
        }
    }

    if ($showForm):
    ?>
    <section class="register">
    <h2><?= $arrayOfTranslations["RegisterTitle"] ?? "Register as an Apprentice Brewer" ?></h2>
    <p><?= $arrayOfTranslations["RegisterText"] ?? "Join the guild!" ?></p>

    <form class="register-form" method="POST">

        <label for="username"><?= $arrayOfTranslations["RegisterUsername"] ?? "Adventurer Name:" ?></label>
        <input type="text" id="username" name="username"
            placeholder="<?= $arrayOfTranslations["RegisterUsernamePlaceholder"] ?? "e.g., Merlin" ?>" required>

        <label for="displayname"><?= $arrayOfTranslations["DisplayName"] ?? "Display Name:" ?></label>
        <input type="text" id="displayname" name="displayname"
            placeholder="<?= $arrayOfTranslations["DisplayNamePlaceholder"] ?? "Your public name" ?>" required>

        <label for="email"><?= $arrayOfTranslations["RegisterEmail"] ?? "Email:" ?></label>
        <input type="email" id="email" name="email"
            placeholder="<?= $arrayOfTranslations["RegisterEmailPlaceholder"] ?? "you@example.com" ?>" required>

        <label for="password"><?= $arrayOfTranslations["RegisterSecretPassword"] ?? "Password:" ?></label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <label for="passwordAgain"><?= $arrayOfTranslations["RegisterSecretPasswordRepeat"] ?? "Repeat Password:" ?></label>
        <input type="password" id="passwordAgain" name="passwordAgain" placeholder="••••••••" required>

        <button type="submit"><?= $arrayOfTranslations["RegisterPageButton"] ?? "Join the Guild! ✨" ?></button>
    </form>
</section>
    <?php endif; ?>

</body>

</html>