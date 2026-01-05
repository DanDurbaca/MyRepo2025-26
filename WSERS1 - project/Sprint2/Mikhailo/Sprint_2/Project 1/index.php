<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    include_once ("function.php");
    NavigationBar($page="Home");
    
    
    ?>

     <h1><?= $arrayOfTranslations['welcomelab'][$language] ?> </h1>
</body>
</html>