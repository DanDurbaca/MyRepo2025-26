<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyModificationGarage Registration</title>
    <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Register");
    ?>
    <h1><?=$arrayOfTranslations["RgH1"]?></h1>
    <?php
    $bShowForm = true;
    if (isset($_POST["userName"], $_POST["psw"], $_POST["pswAgain"], $_POST["email"], $_POST["dob"])) {
        $bShowForm = false;
        print($arrayOfTranslations["Rgprogress"] . "<br>");
        //check if passwords match
        if (($_POST["psw"] == $_POST["pswAgain"]) && (!userAlredyResgistred($_POST["userName"]))) {
            print($arrayOfTranslations["Rgwelcome"]);
            //we need to save the username and password into the "database"(Client.csv)
            $fHandler = fopen("Client.csv", "a");
            fwrite($fHandler, "\n" . $_POST["userName"] . ";" . $_POST["psw"]. ";" . $_POST["email"] . ";" . $_POST["dob"]);
            fclose($fHandler);
        } else {
            $bShowForm = true;
            print($arrayOfTranslations["Rgpssw!"]);//passwords do not match
        }
    }
    if ($bShowForm) {
    ?>
        <form method="POST">
            <div><?=$arrayOfTranslations["Rgusrn"]?></div><br>
            <input type="text" name="userName"><br>
            <div><?=$arrayOfTranslations["Rgpssw"]?></div><br>
            <input type="password" name="psw"><br>
            <div><?=$arrayOfTranslations["Rgpssw2"]?></div><br>
            <input type="password" name="pswAgain"><br>
            <div>Email:</div><br>
            <input type="email" name="email"><br>
            <div><?=$arrayOfTranslations["Rgdob"]?></div><br>
            <input type="date" name="dob"><br>
            <input type="submit" value="Register">
        </form>
    <?php
    }
    ?>
</body>

</html>