<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <?php
    include_once("commonCode.php");
    navBar("Login");
    ?>

    <h1><?= $arrayOfTranslations["LoginH1"] ?></h1>

    <?php

    $adminUser = "simi";
    $adminPswd = "123";

    $showForm = true;

    if (isset($_POST["Username"], $_POST["psw"])) {
        $showForm = false;

        $username = trim($_POST["Username"]);
        $password = $_POST["psw"];

        if ($username === "" || $password === "") {
            echo "<p>{$arrayOfTranslations["LoginOut1"]}</p>";
            $showForm = true;
        } else {

            if ($username === $adminUser && $password === $adminPswd) {
                $_SESSION["UserLogged"] = true;
                $_SESSION["Username"] = $adminUser;
                $_SESSION["IsAdmin"] = true;

                echo "<p>{$arrayOfTranslations["LoginOut2"]}</p>";
                echo "<meta http-equiv='refresh' content='1; url=admin.php?lang=$language'>";
                exit;
            }

            $success = false;

            if (($file = fopen("Clients.csv", "r")) !== false) {
                while (($line = fgetcsv($file, 0, ";")) !== false) {
                    if (count($line) < 2) continue;

                    $csvUser = trim($line[0]);
                    $csvHash = rtrim($line[1], "\r\n");

                    if ($csvUser === $username && password_verify($password, $csvHash)) {
                        $success = true;
                        break;
                    }
                }
                fclose($file);
            }

            if ($success) {
                $_SESSION["UserLogged"] = true;
                $_SESSION["Username"] = $username;
                $_SESSION["IsAdmin"] = false;

                echo "<p>{$arrayOfTranslations["LoginOut2"]}</p>";
                echo "<meta http-equiv='refresh' content='1; url=index.php?lang=$language'>";
            } else {
                echo "<p>{$arrayOfTranslations["LoginOut3"]}</p>";
                $showForm = true;
            }
        }
    }

    if ($showForm):
    ?>

        <form method="POST">
            <label><?= $arrayOfTranslations["LoginLabel1"] ?></label><br>
            <input type="text" name="Username" required><br><br>

            <label><?= $arrayOfTranslations["LoginLabel2"] ?></label><br>
            <input type="password" name="psw" required><br><br>

            <input type="submit" value="<?= $arrayOfTranslations["LoginLabel3"] ?>">
        </form>

    <?php endif; ?>

</body>

</html>
