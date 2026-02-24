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
    <?php
    include_once("navbar.php");
    include_once("ccode.php");
    navbar($tArray["RegisterBtn"]);
    ?>

    <main class="page">
        <h1><?= $tArray["RegForm"] ?></h1>
        <?php
        if (isset($_POST["username"], $_POST["name"], $_POST["email"], $_POST["pass"], $_POST["passconf"])) {
            if (userExists($_POST["username"])) {
                echo "<div class='alert alert-error'>" . $tArray["RegTaken"] . "</div>";
            } else if ($_POST["pass"] !== $_POST["passconf"]) {
                echo "<div class='alert alert-error'>" . $tArray["RegPassNotConf"] . "</div>";
            } else {
                $fhandler = fopen("clients.csv", "a");
                echo "<div class='alert alert-success'>" . $tArray["RegSuccess"] . "</div>";
                fwrite($fhandler, "\n" . $_POST["username"] . ";" . $_POST["name"] . ";" . $_POST["email"] . ";" . $_POST["pass"]);
                fclose($fhandler);
            }
        }
        ?>
        <form class="register-form" method="post">
            <label><?= $tArray["UnameReg"] ?>
                <input name="username" required>
            </label>
            <label><?= $tArray["NameReg"] ?>
                <input name="name" required>
            </label>
            <label><?= $tArray["EmailReg"] ?>
                <input type="email" name="email" required>
            </label>
            <label><?= $tArray["Password"] ?>
                <input type="password" name="pass" required>
            </label>
            <label><?= $tArray["PasswordConf"] ?>
                <input type="password" name="passconf" required>
            </label>
            <button type="submit"><?= $tArray["SendReg"] ?> </button>
        </form>
    </main>
</body>

</html>