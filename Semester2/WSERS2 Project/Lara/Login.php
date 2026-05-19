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

        
        $connection = new mysqli("localhost", "root", "", "webshop2025_26");

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        
        $sqlQuery = $connection->prepare("SELECT UserPassword, UserType FROM users WHERE Username = ? LIMIT 1");
        $sqlQuery->bind_param("s", $username);
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();

        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $storedPasswordHash = $row['UserPassword'];
            $storedUserType = $row['UserType'];

            
            if (password_verify($password, $storedPasswordHash)) {
                $loginSuccess = true;

                
                $_SESSION["UserLogged"] = true;
                $_SESSION["Username"] = $username;
                $_SESSION["UserType"] = $storedUserType;
                
                //Redirect to Home
                header("Location: Home.php?lang=" . $language);
                exit(); 
            }
        }

        $sqlQuery->close();
        $connection->close();

        
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