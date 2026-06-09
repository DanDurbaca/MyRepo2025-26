<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($arrayOfTranslations["RegisterBtn"] ?? "Register", ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
    <?php 
    include_once("nav.php");
    NavigationBar($arrayOfTranslations["RegisterBtn"] ?? "Register");
     ?>
 
    <h1><?= $arrayOfTranslations["RegTitle"]?>:</h1>
    <?php
    $bShowForm = true;

    $connection = new mysqli("localhost", "root", "", "4PageWebsite");


    if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"], $_POST["Firstname"], $_POST["Email"])){
        $bShowForm = false;

        $username = trim($_POST["Username"]);
        $password = $_POST["psw"];
        $password2 = $_POST["pswAgain"];
        $email = trim($_POST["Email"]);
        $firstname = trim($_POST["Firstname"]);

        if ($password == $password2) {

            // STEP 2: check if user already exists
            $sqlCheck = $connection->prepare("SELECT Username FROM Clients WHERE Username = ?");
            $sqlCheck->bind_param("s", $username);
            $sqlCheck->execute();
            $resultCheck = $sqlCheck->get_result();

            if ($resultCheck->num_rows == 0) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userType = "regular";

                // STEP 3: insert new user
                $sqlInsert = $connection->prepare(
                    "INSERT INTO Clients (Username, UserPassword, Email, Firstname, UserType) VALUES (?, ?, ?, ?, ?)"
                );
                $sqlInsert->bind_param("sssss", $username, $hashedPassword, $email, $firstname, $userType);
                $sqlInsert->execute();

                print htmlspecialchars($arrayOfTranslations["RegMatch"], ENT_QUOTES, 'UTF-8');
            } else {
                $bShowForm = true;
                print htmlspecialchars($arrayOfTranslations["RegNoMatch"], ENT_QUOTES, 'UTF-8');
            }

        } else {
            $bShowForm = true;
            print htmlspecialchars($arrayOfTranslations["RegNoMatch"], ENT_QUOTES, 'UTF-8');
        }
    }

    if ($bShowForm)
    {
    ?>
    <form method="POST">
        <div> <?= $arrayOfTranslations["RegUsername"]?>: </div>
        <input type="text" name="Username"><br>
        <div> <?= $arrayOfTranslations["RegPassword"]?>: </div>
        <input type="password" name="psw"><br>
        <div> <?= $arrayOfTranslations["RegPasswordConfirm"]?>: </div>
        <input type="password" name="pswAgain"><br>
        <div> <?= $arrayOfTranslations["RegFirstName"]?>: </div>
        <input type="text" name="Firstname"><br>
        <div> <?= $arrayOfTranslations["RegEmail"]?>: </div>
        <input type="email" name="Email"><br>
        <input type="submit" value=" <?= $arrayOfTranslations["RegButton"]?>">
    </form> 
    <?php
    }
    ?>

</body>
</html>