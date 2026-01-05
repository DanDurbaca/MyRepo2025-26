<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
</head>

<body>
<?php
include_once("function.php");
NavigationBar($page="Registration");
?>

<h1><?= $arrayOfTranslations['reg'][$language] ?></h1>

<?php 
$bForm = true;

if (
    isset(
        $_POST["username"], 
        $_POST["email"], 
        $_POST["password"], 
        $_POST["confirmpassword"], 
        $_POST["secretpassword"], 
        $_POST["confirmsecretpassword"]
    )
) {
    $bForm = false;

    $username = htmlspecialchars($_POST["username"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmpassword"];
    $secretPassword = $_POST["secretpassword"];
    $confirmSecretPassword = $_POST["confirmsecretpassword"];

    echo $arrayOfTranslations['RegMessage'][$language] . "<br>" . $username;

    // Check if username is free
    if (!userAreg($username)) {
        echo "<br>" . $arrayOfTranslations['UserExists'][$language];
        $bForm = true;
    }

    // Validate main password
    if ($password !== $confirmPassword) {
        echo "<br>" . $arrayOfTranslations['RegPswDoNotMatch'][$language];
        $bForm = true;
    } else {
        echo "<br>" . $arrayOfTranslations['PaswConf'][$language];
    }

    // Validate secret password
    if ($secretPassword !== $confirmSecretPassword) {
        echo "<br>" . $arrayOfTranslations['SRegPswDoNotMatch'][$language];
        $bForm = true;
    } else {
        echo "<br>" . $arrayOfTranslations['SPaswConf'][$language];
    }

    // If all checks passed → write to CSV securely
    if (!$bForm) {

        // Hash both passwords securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $hashedSecretPassword = password_hash($secretPassword, PASSWORD_DEFAULT);

        // Default role/status for new users: "regular client"
        // Note: login.php treats the 5th field as admin flag ("1" => admin).
        // Writing the text "regular client" here ensures the user is not an admin
        // while also making the role human-readable in the CSV.
        $fHandle = fopen("Clients.csv", "a");
        fwrite(
            $fHandle,
            $username . ";" . $email . ";" . $hashedPassword . ";" . $hashedSecretPassword . ";regular client" . "\n"
        );
        fclose($fHandle);

        echo "<br>" . $arrayOfTranslations['RegSuccess'][$language];
    }
}

if ($bForm) {
?>
    <form method="POST" class="registration">

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
