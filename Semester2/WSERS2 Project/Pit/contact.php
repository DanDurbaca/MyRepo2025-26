<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($arrayOfTranslations["ContactBtn"] ?? "Contact", ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
    <?php 
    include_once("nav.php");
    NavigationBar($arrayOfTranslations["ContactBtn"] ?? "Contact");
     ?>

    <section>
        <h3>
            <br>
        <?= htmlspecialchars($arrayOfTranslations["ContactText"], ENT_QUOTES, 'UTF-8') ?> <br>
        schpi505@school.lu
            <br>
        </h3>
    </section>
    </body>
</html>