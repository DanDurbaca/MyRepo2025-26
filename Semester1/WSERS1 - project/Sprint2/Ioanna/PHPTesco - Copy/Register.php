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
    //var_dump(checkIfUserExists("Jumbo"));
    ?>
    
<h1><?= t('Register') ?></h1>
<?php
$bShowForm = true;

if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"])) {
    $bShowForm = false;
    print(t('register_processing') . "<br>");
    //if ($_POST["Username"] == )  //add if statement so new usernames cant be the same as ones that already exist 
    if ($_POST["psw"] == $_POST["pswAgain"] && (!checkIfUserExists($_POST["Username"]))) {
        print(t('register_passwords_match'));
        $fHandler = fopen("Clients.csv", "a");
        $hashOfPsw = password_hash($_POST["psw"],PASSWORD_DEFAULT);
        fwrite($fHandler, "\n" . $_POST["Username"] . ";" . $hashOfPsw . ";regular_customer");
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