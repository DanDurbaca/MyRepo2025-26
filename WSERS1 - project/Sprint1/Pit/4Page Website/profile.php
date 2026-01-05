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
    NavigationBar(("Profile"))
     ?>
   <!-- <div class="Login">
        <button type="button">Login</button>
    </div> -->

    <h1><?= $arrayOfTranslations["ProfileLogin"]?>:</h1>

    <?php
    $bShowForm = true;
    if (isset($_POST["Username"], $_POST["psw"])){
        $bShowForm = false;
        if(!userAlreadyRegistered(($_POST["Username"]))){
            print $arrayOfTranslations["ProfileUserNotExist"];
        }else if(passwordOk(($_POST["Username"]), $_POST["psw"])){
            $bShowForm = false;
            print $arrayOfTranslations["ProfileLoginOk"];
        }
        else{
            $bShowForm = true;
            print $arrayOfTranslations["ProfileError"];
        }
    }

    if ($bShowForm){
        ?>
      <form method="POST">
        <div> <?= $arrayOfTranslations["ProfileUsername"]?>: </div>
        <input type="text" name="Username"><br>
        <div> <?= $arrayOfTranslations["ProfilePassword"]?>: </div>
        <input type="password" name="psw"><br><br>
        <input type="submit" value="Login">
    </form>   
    <?php
    }
    ?>  
</body>
</html>