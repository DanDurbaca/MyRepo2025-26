<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
    <?php 
    include_once("nav.php");
    NavigationBar(("Contact"))
     ?>

    <section>
        <h3>
            <br>
        <?= $arrayOfTranslations["ContactText"]?> <br>
        schpi505@school.lu
            <br>
        </h3>
    </section>
    </body>
</html>