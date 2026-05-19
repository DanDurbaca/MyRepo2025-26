<?php
include_once "navbar.php";
include_once "ccode.php";
if ($_SESSION["userLogged"]) {
    header("Location: welcome.php?lang=" . $lang);
    exit;
}
navbar($tArray["LoginBtn"]);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Mentorship Shop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main class="page">
        <h1><?= $tArray["LoginLogin"] ?></h1>
        <?php
        if (isset($_POST["Llogin"], $_POST["Lpassword"])) {
            validate_user_login($_POST["Llogin"], $_POST["Lpassword"]);
        }
        ?>
        <section class="login-form">
            <form method="post">
                <label>
                    <?= $tArray["UEmail"] ?>
                    <input type="text" name="Llogin" required>
                </label>

                <label>
                    <?= $tArray["PasswordLogin"] ?>
                    <input type="password" name="Lpassword" required>
                </label>

                <button type="submit"><?= $tArray["LoginBtn"] ?></button>
            </form>
        </section>
    </main>
</body>

</html>