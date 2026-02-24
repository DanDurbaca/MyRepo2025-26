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
    NavigationBar(("Register"))
     ?>
 
    <h1><?= $arrayOfTranslations["RegTitle"]?>:</h1>
    <?php
    $bShowForm = true;
    if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"], $_POST["Firstname"], $_POST["Email"])){
        $bShowForm = false;
        if($_POST["psw"] == $_POST["pswAgain"] && (!userAlreadyRegistered(($_POST["Username"])))){
            print $arrayOfTranslations["RegMatch"];
            // we need to save the username and password into a database
            $fHandler = fopen("Clients.csv", "a");
            fwrite($fHandler, "\n".$_POST["Username"].";".$_POST["psw"].";".$_POST["Email"].";".$_POST["Firstname"]);
            fclose($fHandler);
        }else{
            $bShowForm = true;
            print $arrayOfTranslations["RegNoMatch"];
        }
    }

    if ($bShowForm)
    {
    ?>
    <form method="POST">
        <div> <?= $arrayOfTranslations["RegUsername"]?>: </div>
        <input type="text" name="Username"><br>
        <div> <?= $arrayOfTranslations["RegPassword"]?>: </div>
        <input type="password" name="psw"><br>
        <div> <?= $arrayOfTranslations["RegPasswordConfirm"]?>: </div>
        <input type="password" name="pswAgain"><br>
        <div> <?= $arrayOfTranslations["RegFirstName"]?>: </div>
        <input type="text" name="Firstname"><br>
        <div> <?= $arrayOfTranslations["RegEmail"]?>: </div>
        <input type="email" name="Email"><br>
        <input type="submit" value=" <?= $arrayOfTranslations["RegButton"]?>">
    </form> 
    <?php
    }
    ?>

</body>
</html>