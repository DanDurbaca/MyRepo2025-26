<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="Contact.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">


</head>

<body>
    <?php
    include_once("CommonCode.php");
    includeCSS("Contact.css");

    NavigationBar($arrayOfTranslations["ContactBtn"]);
    ?>

    <section class="contact">
        <h2><?= $arrayOfTranslations["ContactTitle"] ?></h2>
        <p><?= $arrayOfTranslations["ContactText"] ?></p>


        <form class="contact-form">
            <label for="name"><?= $arrayOfTranslations["ContactLabel1"] ?></label>
            <input type="text" id="name" placeholder="<?= $arrayOfTranslations["ContactPlaceholder1"] ?>">

            <label for="email"><?= $arrayOfTranslations["ContactLabel2"] ?></label>
            <input type="email" id="email" placeholder="<?= $arrayOfTranslations["ContactPlaceholder2"] ?>">

            <label for="subject"><?= $arrayOfTranslations["ContactLabel3"] ?></label>
            <input type="text" id="subject" placeholder="<?= $arrayOfTranslations["ContactPlaceholder3"] ?>">

            <label for="message"><?= $arrayOfTranslations["ContactLabel4"] ?></label>
            <textarea id="message" rows="5" placeholder="<?= $arrayOfTranslations["ContactPlaceholder4"] ?>"></textarea>

            <button type="submit"><?= $arrayOfTranslations["ContactSendBtn"] ?></button>
        </form>


        <div class="contact-details">
            <p>🦉 <strong><?= $arrayOfTranslations["ContactAddress"] ?><strong></p>
            <p>🔮 <strong><?= $arrayOfTranslations["ContactAddress"] ?><strong></p>
            <p>🏰 <strong><?= $arrayOfTranslations["ContactLocation"] ?><strong></p>
        </div>
    </section>

    <?php
    //var_dump($_GET);
    //var_dump($_POST);

    if (isset($_GET["name"])) {
    ?>
        <h1>Welcome to our Website <?= $name = $_GET["name"] ?></h1>
    <?php

    } else {
        print "<p>Please fill out the contact form to get in touch with us.</p>";
    }
    ?>

</body>

</html>