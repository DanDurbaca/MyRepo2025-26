<?php
include_once "ccode.php";
include_once "navbar.php";
navbar($tArray["HomeBtn"]);
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css? <?= time(); ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Welcome - Mentorship Shop</title>
</head>

<body>
    <script src="script.js?<?= time(); ?>"></script>

    <main class="page">
        <section class="welcome">
            <div class="welcome-inner">
                <h1><?= $tArray["Mentor"] ?></h1>
                <p><?= $tArray["WelcomeSlogan1"] ?></p>

                <h2><?= $tArray["WelcomeQuestion"] ?></h2>
                <p>
                    <?= $tArray["WelcomeChoose"] ?><br>
                    <?= $tArray["WelcomeFocus"] ?>
                </p>

                <div class="start-call">
                    <a href="products.php?lang=<?= $lang ?>" class="btn">
                        <?= $tArray["WelcomeStartBtn"] ?>
                    </a>
                </div>

                <h3><?= $tArray["WelcomeNoTomorrow"] ?></h3>
                <p><?= $tArray["WelcomeChange"] ?></p>
            </div>
        </section>
    </main>
</body>

</html>