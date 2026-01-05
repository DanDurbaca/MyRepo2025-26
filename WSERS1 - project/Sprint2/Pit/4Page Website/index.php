<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>

<img src= https://media.tenor.co/images/a8716b9fe0080e3e38c186e9699c204f/tenor.gif alt="Left side GIF" class="side-gif left-gif">
<img src= https://media.giphy.com/media/3oKIPEnIVoeC3iq1Y4/giphy.gif alt="Right side GIF" class="side-gif right-gif">

<body>
    <?php 
    include_once("nav.php");
    NavigationBar(("Home"))
     ?>

    <header>
        <h1><?= $arrayOfTranslations["HomeTextTitle"]?></h1>
        <h2><?= $arrayOfTranslations["HomeTextH2"]?><h/2>
    </header>

    <section>
        <h3>
        <?= $arrayOfTranslations["HomeTextH3F"]?><br>
        <?= $arrayOfTranslations["HomeTextH3S"]?><br>
        <?= $arrayOfTranslations["HomeTextH3T"]?><br>
        </h3>
    </section>
</body>
</html>
