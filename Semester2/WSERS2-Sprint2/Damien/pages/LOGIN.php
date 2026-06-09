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
        $sqlQuery=$connection->prepare("select * from clients");
        $sqlQuery->execute();
        $result=$sqlQuery->get_result();
        while ($row=$result->fetch_assoc()) {
                if ($row["username"] == $_POST["Username"] && password_verify($_POST["psw"], $row["clientPassword"])) {
                $success = true;
                $_SESSION["UserType"] = trim($row["Usertype"]);
                }

            }
        if ($success) {
            print($arrayTranslation["succeslable"]);
            $_SESSION["Userlogged"] = $_POST["Username"];
            header("refresh:0; url=index.php?lang=$language");
        } else {
            print($arrayTranslation["invalidlable"]);
            $showForm = true;
        }
    }
    if ($bShowForm) {
    ?>
        <form class="LOGIN" method="POST">
            <div><?= $arrayTranslation["Userlable"] ?></div>
            <input type="text" name="Username">
            <div><?= $arrayTranslation["1passwordlable"] ?></div>
            <input type="password" name="psw">
            <input type="submit" value=<?= $arrayTranslation["LOGINBtn"] ?>>
        </form>

    <?php
    }
    ?>

</body>
</html>