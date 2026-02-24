<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php
    include_once("nav.php");
    NavigationBar(("Profile"))
    ?>
    <!-- <div class="Login">
        <button type="button">Login</button>
    </div> -->

    <h1><?= $arrayOfTranslations["ProfileLogin"] ?>:</h1>

    <?php
    $bShowForm = true;

    if (isset($_POST["Username"], $_POST["psw"])) {
        $bShowForm = false;
        $file = fopen("Clients.csv", "r");

        while (!feof($file)) {
            $line = fgets($file);
            if (!$line) {
                continue;
            }

            $line_explode = explode(";", $line);

            // accept both:
            //  - username;hash;email;firstname
            //  - username;hash;email;firstname;userType
            if (count($line_explode) >= 4) {
                $userNameInFile = trim($line_explode[0]);
                $hashInFile     = trim($line_explode[1]);

                // default to "regular" if no 5th column
                $userTypeInFile = (count($line_explode) >= 5)
                    ? trim($line_explode[4])
                    : "regular";

                if ($_POST["Username"] === $userNameInFile) {
                    // compare entered password with stored hash
                    if (password_verify($_POST["psw"], $hashInFile)) {
                        $_SESSION["UserLogged"] = true;
                        $_SESSION["IsAdmin"]    = ($userTypeInFile === "admin");

                        fclose($file);
                        header("Location: index.php");
                        exit;
                    } else {
                        echo "Passwords do not match. Please try again.";
                    }
                }
            }
        }

        fclose($file);
    }




    if ($bShowForm) {
    ?>
        <form method="POST">
            <div> <?= $arrayOfTranslations["ProfileUsername"] ?>: </div>
            <input type="text" name="Username"><br>
            <div> <?= $arrayOfTranslations["ProfilePassword"] ?>: </div>
            <input type="password" name="psw"><br><br>
            <input type="submit" value="Login">
        </form>
    <?php
    }
    ?>
</body>

</html>