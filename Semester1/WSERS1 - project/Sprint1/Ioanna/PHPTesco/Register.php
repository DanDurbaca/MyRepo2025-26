<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Register");
    ?>
    
<h1><?= t('register_title') ?></h1>
<?php
$bShowForm = true;

if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"])) {
    $bShowForm = false;
    print(t('register_processing') . "<br>");
    if ($_POST["psw"] == $_POST["pswAgain"]) {
        print(t('register_passwords_match'));
        $fHandler = fopen("Clients.csv", "a");
        fwrite($fHandler, "\n" . $_POST["Username"] . ";" . $_POST["psw"]);
        fclose($fHandler);
    } else {
        $bShowForm = true;
        print(t('register_passwords_error'));
    }
}

if ($bShowForm) {
    ?>
    <form method="POST">
        <div><?= t('register_username') ?></div>
        <input type="text" name="Username">
        
        <div><?= t('register_password') ?></div>
        <input type="password" name="psw">
        
        <div><?= t('register_password_again') ?></div>
        <input type="password" name="pswAgain">
        <input type="submit" value="<?= t('register_submit') ?>">
    </form>
    <?php
}
?>
</body>

</html>