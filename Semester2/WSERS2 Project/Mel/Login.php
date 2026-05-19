<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CS:GO Case Shop</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body class="contact-page">
    <header>
        <img src="pictures/Logo.png" alt="Mystery Box Shop Logo">
        <h1>CS:GO Case Shop</h1>
    </header>
    <?php
    include_once("commoncode.php");
    Melnav("Login");
    ?>
    <div class="container">
        <div class="contact-content">
            <h1><?= $arrayOfTranslations["WelcomeTextLogin"] ?></h1>

        <?php
        $bShowForm = true;

        function checkUserLogin($username, $password)
        {
            $connection = new mysqli("localhost", "root", "", "DatabasePouMe708");
            $sqlQuery = $connection->prepare("SELECT * from Client");
            $sqlQuery->execute();
            $result = $sqlQuery->get_result();
            while ($row = $result->fetch_assoc()) {
                if (trim($row["Username"]) === $username && password_verify($password, trim($row["HashedPassword"]))) {

                    $_SESSION["UserLogged"] = $_POST["Username"];
                    $_SESSION["UserRole"] = trim($row["websiteRole"]);

                    header("Refresh:0; url=index.php");
                    return true;
                }
            }

            return false;
        }


        if (isset($_POST["Username"], $_POST["Password"])) {
            $bShowForm = false;

            $username = trim($_POST["Username"]);
            $password = trim($_POST["Password"]);

            if (checkUserLogin($username, $password)) {
                echo "<p style='color:green;'>" . $arrayOfTranslations["WelcomeTextLogin2"] . " $username!</p>";
            } else {
                echo "<p style='color:red;'>" . $arrayOfTranslations["WelcomeTextLogin3"] . "</p>";
                $bShowForm = true;
            }
        }

        if ($bShowForm) {
        ?>
            <form method="POST">
                <label><?= $arrayOfTranslations["WelcomeTextLogin4"] ?></label>
                <input type="text" name="Username">

                <label><?= $arrayOfTranslations["WelcomeTextLogin5"] ?></label>
                <input type="password" name="Password">

                <button type="submit"><?= $arrayOfTranslations["WelcomeTextLogin6"] ?></button>
            </form>
        <?php
        }
        ?>
    </div>
</div>