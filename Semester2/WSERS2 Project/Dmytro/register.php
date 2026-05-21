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
    <main class="page">
        <h1><?= $tArray["RegForm"] ?></h1>
        <?php
        if (isset($_POST["Rusername"], $_POST["Remail"], $_POST["Rpass"], $_POST["Rpassconf"]))
            validate_registration_and_add($_POST["Rusername"], $_POST["Remail"], $_POST["Rpass"], $_POST["Rpassconf"]);

        ?>
        <form class="register-form" method="post">
            <label><?= $tArray["UnameReg"] ?>
                <input type="text" name="Rusername" required>
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