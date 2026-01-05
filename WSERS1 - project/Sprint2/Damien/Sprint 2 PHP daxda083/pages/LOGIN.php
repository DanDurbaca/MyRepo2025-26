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
        NavigationBar("Log in");
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
                if ($individualUserComponents[0] == $_POST["Username"] && password_verify($_POST["psw"], $individualUserComponents[1])) {
                $success = true;
                $_SESSION["UserType"] = trim($individualUserComponents[4]);
                }

            }
        if ($success) {
            print($arrayTranslation["succeslable"]);
            $_SESSION["Userlogged"] = true;
            header("Refresh: 0");
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