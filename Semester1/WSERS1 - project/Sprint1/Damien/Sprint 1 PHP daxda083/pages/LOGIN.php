<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link rel="stylesheet" href="style.css?<?=time()?>">
	<meta charset="utf-8">
    <title>Login</title>
	</head>
<body class="restBG">
	
        <?php
        include_once("function.php");
        NavigationBar("LOGIN");
        ?>
	
	<?php
    $bShowForm = true;
    $success = false;
    if (isset($_POST["Username"], $_POST["psw"])) {
        $bShowForm = false;
        $fHandler = fopen("Clients.csv", "r");
        while (!feof($fHandler)) {
                $oneUser = fgets($fHandler);
                $individualUserComponents = explode(";", $oneUser);
                ($individualUserComponents[0] == $_POST["Username"] && $individualUserComponents[1] == $_POST["psw"]) ? $success = true : "";
            }
        if ($success) {
            print($arrayTranslation["succeslable"]);
        } else {
            print($arrayTranslation["invalidlable"]);
            $showForm = true;
        }
    }
    if ($bShowForm) {
    ?>
        <form method="POST">
            <div><?= $arrayTranslation["Userlable"] ?></div>
            <input type="text" name="Username">
            <div><?= $arrayTranslation["1passwordlable"] ?></div>
            <input type="password" name="psw">
            <input type="submit" value=<?= $arrayTranslation["registerlable"] ?>>
        </form>

    <?php
    }
    ?>

</body>
</html>