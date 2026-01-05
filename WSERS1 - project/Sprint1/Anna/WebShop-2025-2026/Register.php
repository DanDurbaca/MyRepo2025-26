<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar($arrayOfTranslations["RegisterBtn"]);
    ?>
    <div class="text">
        <?php
        $bShowForm = true;
        if (isset($_POST["userName"], $_POST["psw"], $_POST["pswAgain"])) {
            $bShowForm = false;
            print("Registration in progress...<br>");
            //check if passwords match
            if (($_POST["psw"] == $_POST["pswAgain"]) && (!userAlreadyRegistered($_POST["userName"]))) {
                print("Welcome you are now registered!");
                $fHandler = fopen("Client.csv", "a");
                fwrite($fHandler, "\n" . $_POST["userName"] . ";" . $_POST["psw"]);
                fclose($fHandler);
            } else {
                $bShowForm = true;
                print("Passwords do not match or user alredy exists, please try again.");
            }
        }
        if ($bShowForm) {
        ?>
    </div>
    <form method="POST" class="divCentered">
        <h1 class="fonth1"><?= $arrayOfTranslations["RegistrationForm"] ?></h1>
        <p class="fontp"><?= $arrayOfTranslations["RegistrationName"] ?></div><br>
            <input type="test" name="userName">
        <p class="fontp"><?= $arrayOfTranslations["RegistrationPassword"] ?></div><br>
            <input type="password" name="psw"><br>
            <input type="password" name="pswAgain">
        <p class="fontp"><?= $arrayOfTranslations["RegistrationEmail"] ?></div><br>
            <input type="email" name="Email">
        <p class="fontp"><?= $arrayOfTranslations["RegistrationPhone"] ?></div><br>
            <input type="tel" name="PhoneNumber"><br>
            <input type="submit" value="Register"><br>
    </form>
<?php
        }
?>
</body>

</html>