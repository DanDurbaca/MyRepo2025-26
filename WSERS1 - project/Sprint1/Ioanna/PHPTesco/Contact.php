<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?=time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <?php
    include_once("CommonCode.php");
    NavigationBar("Contact");
    ?>

    <h1><?= t('contact_title') ?></h1>
<p><?= t('contact_info_text') ?></p>
<ul>
    <li><?= t('contact_email') ?>: info@example.com</li>
    <li><?= t('contact_phone') ?>: +1 234 567 890</li>
    <li><?= t('contact_address') ?>: 123 Main Street, City, Country</li>
</ul>

<form method="POST">
    <div><?= t('contact_fname') ?></div>
    <input type="text" name="fname">

    <div><?= t('contact_password') ?></div>
    <input type="password" name="psw">

    <input type="submit" value="<?= t('contact_submit') ?>">
</form>

<?php
var_dump($_GET);
var_dump($_POST);

if (isset($_POST["fname"])) {
    ?>
    <h1><?= t('contact_welcome') ?> <?= $_POST["fname"] ?></h1>
    <?php
}
?>


</html>