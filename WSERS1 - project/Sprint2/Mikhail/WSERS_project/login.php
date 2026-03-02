<!DOCTYPE html>
<html>

<head>
    <title>Best Holiday Destinations</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include_once("common.php");
    head("Login");
    ?>
    <main class="register">
        <h1><?= $arrayOfTranslations["LoginH1"] ?></h1>
        <br>
        <?php
        $showForm = true;
        if (isset($_POST["Username"], $_POST["psw"])) {
            $showForm = false;
            $success = false;
            $admin = false;
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
                    if ($individualUserComponents[0] == $_POST["Username"] && password_verify($_POST["psw"], $individualUserComponents[1])){
                        (trim($individualUserComponents[4]) == "admin") ? $admin = true : "";
                        $success = true;
                        break;
                    }
                }
                if ($success) {
                    print($arrayOfTranslations["LoginOut2"]);
                    $_SESSION["UserLogged"] = true;
                    $_SESSION["IsAdmin"] = $admin;
                    header("Refresh:0; url=home.php");
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