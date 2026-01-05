<!DOCTYPE html>
<html>

<head>
    <title>Best Holiday Destinations</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include_once("common.php");
    head("Login", "Вход");
    ?>
    <main class="register">
        <h1><?= $arrayOfTranslations["LoginH1"] ?></h1>
        <br>
        <?php
        $showForm = true;
        if (isset($_POST["Username"], $_POST["psw"])) {
            $showForm = false;
            $success = false;
            if ($_POST["psw"] == null || $_POST["Username"] == null) {
                $showForm = true;
                print($arrayOfTranslations["LoginOut1"]);
            } else {
                $fHandler = fopen("Clients.csv", "r");
                fgets($fHandler);
                fgets($fHandler);
                while (!feof($fHandler)) {
                    $oneUser = fgets($fHandler);
                    $individualUserComponents = explode(";", $oneUser);
                    ($individualUserComponents[0] == $_POST["Username"] && $individualUserComponents[1] == $_POST["psw"]) ? $success = true : "";
                }
                if ($success) {
                    print($arrayOfTranslations["LoginOut2"]);
                } else {
                    print($arrayOfTranslations["LoginOut3"]);
                    $showForm = true;
                }
            }
        }
        if ($showForm) {
        ?>
            <form method="POST">
                <label><?= $arrayOfTranslations["LoginLabel1"] ?></label>
                <input type="test" name="Username">
                <br>
                <br>
                <label><?= $arrayOfTranslations["LoginLabel2"] ?></label>
                <input type="password" name="psw">
                <br>
                <br>
                <input type="submit" value="<?= $arrayOfTranslations["LoginLabel3"] ?>">
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