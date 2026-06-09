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



<ul>
    <li><?= t('contact_email') ?>: tesco@tesco.gb</li>
    <li><?= t('contact_phone') ?>: +44 234 567 890</li>
    <li><?= t('contact_address') ?>: 15 Wheatlands, Portland, Dorset</li>
</ul>




</html>