<?php
include_once "ccode.php";
include_once "navbar.php";
navbar($tArray["ContactBtn"]);
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css? <?= time(); ?> ">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Contact - Mentorship Shop</title>
</head>

<body>
    <script src="script.js?<?= time(); ?>"></script>

    <main class="page">
        <section class="contact">
            <h2><?= $tArray["Contact"] ?></h2>
            <form action="welcome.php" method="post">
                <label>
                    <?= $tArray["UnameCnt"] ?>
                    <input type="text" name="Cname" required>
                </label>
                <label>
                    <?= $tArray["EmailCnt"] ?>
                    <input type="email" name="Cemail" required>
                </label>
                <label>
                    <?= $tArray["Msg"] ?>
                    <textarea name="Cmessage" required></textarea>
                </label>
                <button type="submit"><?= $tArray["SendCnt"] ?></button>
            </form>
        </section>
    </main>
</body>

</html>