<?php
include_once "navbar.php";
include_once "ccode.php";
if ($_SESSION["userLogged"]) {
    header("Location: welcome.php?lang=" . $lang);
    exit;
}
navbar($tArray["RegisterBtn"]);
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css? <?= time(); ?> ">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register - Mentorship Shop</title>
</head>

<body>
    <script src="script.js?<?= time(); ?>"></script>

    <main class="page">
        <h1><?= $tArray["RegForm"] ?></h1>
        <?php
        if (isset($_POST["Rusername"], $_POST["Rname"], $_POST["Remail"], $_POST["Rpass"], $_POST["Rpassconf"])) {
            if (userExists($_POST["Rusername"])) {
                echo "<div class='alert alert-error'>" . $tArray["RegTaken"] . "</div>";
            } else if ($_POST["Rpass"] !== $_POST["Rpassconf"]) {
                echo "<div class='alert alert-error'>" . $tArray["RegPassNotConf"] . "</div>";
            } else {
                $fhandler = fopen("clients.csv", "a");
                echo "<div class='alert alert-success'>" . $tArray["RegSuccess"] . "</div>";
                fwrite($fhandler, "\n" . $_POST["Rusername"] . ";" . $_POST["Rname"] . ";"
                    . $_POST["Remail"] . ";" . password_hash($_POST["Rpass"], PASSWORD_DEFAULT) . ";user");
                fclose($fhandler);
            }
        }
        ?>
        <form class="register-form" method="post">
            <label><?= $tArray["UnameReg"] ?>
                <input type="text" name="Rusername" required>
            </label>
            <label><?= $tArray["NameReg"] ?>
                <input type="text" name="Rname" required>
            </label>
            <label><?= $tArray["EmailReg"] ?>
                <input type="email" name="Remail" required>
            </label>
            <label><?= $tArray["Password"] ?>
                <input type="password" name="Rpass" required>
            </label>
            <label><?= $tArray["PasswordConf"] ?>
                <input type="password" name="Rpassconf" required>
            </label>
            <button type="submit"><?= $tArray["SendReg"] ?> </button>
        </form>
    </main>
</body>

</html>