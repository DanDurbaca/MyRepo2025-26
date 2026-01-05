<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width= , initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    include_once("function.php");
    NavigationBar($page="Registration");
    ?>
    <h1><?= $arrayOfTranslations['reg'][$language] ?></h1>
    <?php 
    $bForm = true;
    if(isset($_POST["username"], $_POST["email"], $_POST["password"], $_POST["confirmpassword"], $_POST["secretpassword"], $_POST["confirmsecretpassword"]))
     
    {
      $bForm = false;
       print ($arrayOfTranslations['RegMessage'][$language] ."<br>". htmlspecialchars($_POST["username"]));//htmlspecialchars — Convert special characters to HTML entities

       if($_POST["password"] == $_POST["confirmpassword"] && userAreg(htmlspecialchars($_POST["username"]))){
        echo $arrayOfTranslations['PaswConf'][$language] ;
        $fHandle = fopen("Clients.csv", "a");
       }
       else{
        $bForm="true";
        print ("<br> ".$arrayOfTranslations['RegPswDoNotMatch'][$language]);
       }
         if($_POST["secretpassword"] == $_POST["confirmsecretpassword"] &&  userAreg(htmlspecialchars($_POST["username"]))){
          print ("<br>".$arrayOfTranslations['SPaswConf'][$language] );
          $fHandle = fopen("Clients.csv", "a");
          fwrite($fHandle, htmlspecialchars($_POST["username"]) . ";" . htmlspecialchars($_POST["password"]) . ";" . htmlspecialchars($_POST["secretpassword"]) . "\n");
         }
         else{
            $bForm="true";
          print ("<br>". $arrayOfTranslations['SRegPswDoNotMatch'][$language]);
         }

    }
    if($bForm)
    {
    ?>
      <form method="Post" class="registration">
        <div><?= $arrayOfTranslations['name'][$language] ?></div>
        <input type="text" name="username" required>
        <div><?= $arrayOfTranslations['email'][$language] ?></div>
        <input type="email" name="email" required>
        <div><?= $arrayOfTranslations['pasw'][$language] ?></div>
        <input type="password" name="password" required>
        <div><?= $arrayOfTranslations['conf'][$language] ?></div>
        <input type="password" name="confirmpassword" required>
        <div><?= $arrayOfTranslations['secret'][$language] ?></div>
        <input type="password" name="secretpassword" required>
        <div><?= $arrayOfTranslations['confsecretp'][$language] ?></div>
        <input type="password" name="confirmsecretpassword" required>
        <br><br>
        <input type="submit" value="<?= $arrayOfTranslations['regbutton'][$language] ?>">  


      </form>
    <?php
    }
    ?>

    
</body>
</html>