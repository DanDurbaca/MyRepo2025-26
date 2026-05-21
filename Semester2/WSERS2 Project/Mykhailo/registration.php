<?php include_once("function.php"); ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations['reg'][$language] ?? 'Registration' ?></title>
</head>

<body>
<?php
NavigationBar($page="Registration");
?>

<h1 class="site-heading"><?= $arrayOfTranslations['reg'][$language] ?></h1>

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

    
    if (!userAreg($username)) {
        echo "<br>" . $arrayOfTranslations['UserExists'][$language];
        $bForm = true;
    }

    
    if ($password !== $confirmPassword) {
        echo "<br>" . $arrayOfTranslations['RegPswDoNotMatch'][$language];
        $bForm = true;
    } else {
        echo "<br>" . $arrayOfTranslations['PaswConf'][$language];
    }

    
    if ($secretPassword !== $confirmSecretPassword) {
        echo "<br>" . $arrayOfTranslations['SRegPswDoNotMatch'][$language];
        $bForm = true;
    } else {
        echo "<br>" . $arrayOfTranslations['SPaswConf'][$language];
    }

    
    if (!$bForm) {

        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $hashedSecretPassword = password_hash($secretPassword, PASSWORD_DEFAULT);

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Clients (username, email, password, secretPassword, adminStatus) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $adminStatus = 'regular client';
            $stmt->bind_param('sssss', $username, $email, $hashedPassword, $hashedSecretPassword, $adminStatus);
            if ($stmt->execute()) {
                echo "<br>" . $arrayOfTranslations['RegSuccess'][$language];
            } else {
                echo "<br>Registration failed: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            echo "<br>Registration failed: could not prepare statement.";
        }
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
