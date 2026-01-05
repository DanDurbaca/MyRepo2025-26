<!DOCTYPE html>
<html>

<head>
    <title>Best Holiday Destinations</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include_once("common.php");
    head("Register", "Регистрация");
    ?>
    <main class="register">
        <h1><?= $arrayOfTranslations["RegisterH1"] ?></h1>
        <br>
        <?php
        $showForm = true;
        if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"], $_POST["email"], $_POST["age"])) {
            $showForm = false;
            if ($_POST["psw"] == null || $_POST["Username"] == null || $_POST["email"] == null || $_POST["age"] == null) {
                $showForm = true;
                print($arrayOfTranslations["RegisterOut1"]);
            } else if (userAlreadyRegistered($_POST["Username"])) {
                $showForm = true;
                print($arrayOfTranslations["RegisterOut2"]);
            } else if ($_POST["psw"] != $_POST["pswAgain"]) {
                $showForm = true;
                print($arrayOfTranslations["RegisterOut3"]);
            } else {
                print($arrayOfTranslations["RegisterOut4"]);
                $fHandler = fopen("Clients.csv", "a");
                fwrite($fHandler, "\n" . $_POST["Username"] . ";" . $_POST["psw"] . ";" . $_POST["email"] . ";" . $_POST["age"]);
                fclose($fHandler);
            }
        }
        if ($showForm) {
        ?>
            <form method="POST">
                <label><?= $arrayOfTranslations["RegisterLabel1"] ?></label>
                <input type="test" name="Username">
                <br>
                <br>
                <label><?= $arrayOfTranslations["RegisterLabel2"] ?></label>
                <input type="email" name="email">
                <br>
                <br>
                <label><?= $arrayOfTranslations["RegisterLabel3"] ?></label>
                <input type="number" min="16" max="99" name="age">
                <br>
                <br>
                <label><?= $arrayOfTranslations["RegisterLabel4"] ?></label>
                <input type="password" name="psw">
                <br>
                <br>
                <label><?= $arrayOfTranslations["RegisterLabel5"] ?></label>
                <input type="password" name="pswAgain">
                <br>
                <br>
                <input type="submit" value="<?= $arrayOfTranslations["RegisterLabel6"] ?>">
            </form>
        <?php
        }
        ?>
    </main>
    <?php
    foot();
    ?>
</body>

</html>