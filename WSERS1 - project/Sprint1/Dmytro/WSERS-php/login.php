<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Mentorship Shop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <script src="script.js?<?= time(); ?>"></script>
    <?php
    include_once("navbar.php");
    include_once("ccode.php");
    navbar($tArray["LoginBtn"]);
    ?>
    <main class="page">
        <h1><?= $tArray["LoginLogin"] ?></h1>
        <?php
            if (isset($_POST["login"], $_POST["password"])) {
                if (findUserByLoginAndPassword($_POST["login"], $_POST["password"])) {
                    echo "<div class='alert alert-success'>" . $tArray["LoginSuccess"] . "</div>";
                } else {
                    echo "<div class='alert alert-error'>" . $tArray["LoginError"] . "</div>";
                }
            }
        ?>
        <section class="login-form">
            <form method="post">
                <label>
                    <?= $tArray["UnameEmail"] ?>
                    <input type="text" name="login" required>
                </label>

                <label>
                    <?= $tArray["PasswordLogin"] ?>
                    <input type="password" name="password" required>
                </label>

                <button type="submit"><?= $tArray["LoginBtn"] ?></button>
            </form>
        </section>
    </main>
</body>

</html>