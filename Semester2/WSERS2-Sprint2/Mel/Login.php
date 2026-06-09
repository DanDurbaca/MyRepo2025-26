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
    /** @var array $arrayOfTranslations */
    include_once("commoncode.php");
    Melnav("Login");
    ?>
    <div class="container">
        <div class="contact-content">
            <h1><?= $arrayOfTranslations["WelcomeTextLogin"] ?></h1>

        <?php
        $bShowForm = true;

        function checkUserLogin(string $username, string $password)
        {
            $connection = new mysqli("localhost", "root", "", "DatabasePouMe708");
            $sqlQuery = $connection->prepare("SELECT * from Client");
            $sqlQuery->execute();
            $result = $sqlQuery->get_result();
            while ($row = $result->fetch_assoc()) {
                if (trim($row["Username"]) === $username && password_verify($password, trim($row["HashedPassword"]))) {

                    // Save session login markers
                    $_SESSION["UserLogged"] = $_POST["Username"];
                    $_SESSION["UserRole"] = trim($row["websiteRole"]);
                    
                    // NEW ADDITION: Saves username for Cart tracking and Admin display
                    $_SESSION["username"] = trim($row["Username"]);

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
                <input type="text" name="Username" required>

                <label><?= $arrayOfTranslations["WelcomeTextLogin5"] ?></label>
                <input type="password" name="Password" required>

                <button type="submit"><?= $arrayOfTranslations["WelcomeTextLogin6"] ?></button>
            </form>
        <?php
        }
        ?>
    </div>
</div>
</body>
</html>