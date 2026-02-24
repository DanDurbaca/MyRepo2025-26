<?php
include_once("function.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations['Login'][$language] ?? 'Login' ?> - OrangeShop</title>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>

<?php NavigationBar($page="Login"); ?>

<h1 style="text-align:center; margin-top:40px;">
    <?= $arrayOfTranslations['Login'][$language] ?? 'Login' ?>
</h1>

<?php

function checkLogin($username, $password, $secret) {

    $file = fopen("Clients.csv", "r");
    if (!$file) return "file_error";

    while (!feof($file)) {
        $line = fgets($file);

        if ($line === false) continue;

        $userData = explode(";", $line);

        if (count($userData) < 3) continue;

        $fileUser   = trim($userData[0]);
        $filePass   = trim($userData[1]);
        $fileSecret = trim($userData[2]);

        if ($fileUser === $username) {
            fclose($file);

            if ($filePass !== $password) {
                return "wrong_password";
            }

            if ($fileSecret !== $secret) {
                return "wrong_secret";
            }

            return "ok";
        }
    }

    fclose($file);
    return "not_found";
}

$showForm = true;

if (isset($_POST["username"], $_POST["password"], $_POST["secretpassword"])) {

    $usernameInput = $_POST["username"];
    $passwordInput = $_POST["password"];
    $secretInput   = $_POST["secretpassword"];

    echo "<div style='text-align:center; margin-top:20px;'>";

    $result = checkLogin($usernameInput, $passwordInput, $secretInput);

    if ($result === "ok") {
        echo "<p style='color:green; font-size:1.3rem; font-weight:bold;'>✔ You are logged in</p>";
        $showForm = false;
    }
    elseif ($result === "wrong_password") {
        echo "<p style='color:red; font-size:1.3rem; font-weight:bold;'>✘ Wrong password</p>";
    }
    elseif ($result === "wrong_secret") {
        echo "<p style='color:red; font-size:1.3rem; font-weight:bold;'>✘ Wrong secret password</p>";
    }
    else {
        echo "<p style='color:red; font-size:1.3rem; font-weight:bold;'>✘ User not found</p>";
    }

    echo "</div>";
}

if ($showForm) {
?>

<form method="POST" class="registration" style="max-width:400px;">
    <div><?= $arrayOfTranslations['name'][$language] ?? 'Username' ?></div>
    <input type="text" name="username" required>

    <div><?= $arrayOfTranslations['pasw'][$language] ?? 'Password' ?></div>
    <input type="password" name="password" required>

    <div><?= $arrayOfTranslations['secret'][$language] ?? 'Secret Password' ?></div>
    <input type="password" name="secretpassword" required>

    <input type="submit" value="<?= $arrayOfTranslations['Login'][$language] ?? 'Login' ?>">
</form>

<?php
}
?>

</body>
</html>
