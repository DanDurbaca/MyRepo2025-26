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
    <?php
    include_once "navbar.php";
    navbar($tArray["ContactBtn"]);
    ?>

    <main class="page">
        <section class="contact">
            <h2><?= $tArray["Contact"] ?></h2>
            <form action="" method="post">
                <label>
                    <?= $tArray["UnameCnt"] ?>
                    <input type="text" name="name" required />
                </label>
                <label>
                    <?= $tArray["EmailCnt"] ?>
                    <input type="email" name="email" required />
                </label>
                <label>
                    <?= $tArray["Msg"] ?>
                    <textarea name="message" required></textarea>
                </label>
                <button type="submit"><?= $tArray["SendCnt"] ?></button>
            </form>
        </section>
    </main>
</body>

</html>