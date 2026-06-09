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
    include_once("Database.php");
    include_once("CommonCode.php");
    NavigationBar($arrayOfTranslations["RegisterBtn"]);
    ?>
    <div class="text">
        <?php
        $bShowForm = true;
        if (isset($_POST["userName"], $_POST["psw"], $_POST["pswAgain"])) {
            $bShowForm = false;
            print("Registration in progress...<br>");
            // sanitize inputs for HTML output and basic validation
            $_POST['userName'] = htmlspecialchars(trim($_POST['userName']), ENT_QUOTES, 'UTF-8');
            $_POST['Email'] = filter_var(trim($_POST['Email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $_POST['PhoneNumber'] = htmlspecialchars(trim($_POST['PhoneNumber'] ?? ''), ENT_QUOTES, 'UTF-8');
            // trim passwords (do NOT htmlspecialchars passwords before hashing)
            $_POST['psw'] = trim($_POST['psw']);
            $_POST['pswAgain'] = trim($_POST['pswAgain']);

            // basic validation: require user and password, validate email if provided
            if ($_POST['userName'] === '' || $_POST['psw'] === '') {
                print("Username and password are required.<br>");
                // force a mismatch so the existing check below will fall into the error branch
                $_POST['pswAgain'] = '__INVALID__';
            } elseif ($_POST['Email'] !== '' && !filter_var($_POST['Email'], FILTER_VALIDATE_EMAIL)) {
                print("Invalid email address.<br>");
                $_POST['pswAgain'] = '__INVALID__';
            }
            if (($_POST["psw"] == $_POST["pswAgain"]) && (!userAlreadyRegistered($_POST["userName"]))) {
                print("Welcome you are now registered!");
                $psw = password_hash($_POST["psw"], PASSWORD_DEFAULT);
                $adminUser = "no";
                //append new user to Client.csv
                $connection = new mysqli("localhost", "root","","webshop");
                $sqlQuery = $connection->prepare("INSERT INTO client (Username, UserPassword, UserAdmin) VALUES(?, ?, ?);");
                $sqlQuery->bind_param("sss", $_POST["userName"], $psw, $adminUser);
                $sqlQuery->execute();
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