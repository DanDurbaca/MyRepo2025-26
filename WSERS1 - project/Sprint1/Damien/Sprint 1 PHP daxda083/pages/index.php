<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <link rel="stylesheet" href="style.css?<?=time()?>">
    <meta charset="utf-8">
    <title>HP</title>
</head>

<body class=HPBG>

    <?php 
    include_once("function.php");
    NavigationBar("Home");
    ?>

    <H1 class="WelcomeTitle">
        <?= $arrayTranslation["Welcomelable"] ?><br>
    </H1>
    

    
</body>

</html>