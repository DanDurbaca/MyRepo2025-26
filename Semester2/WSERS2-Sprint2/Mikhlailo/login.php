<?php
session_start();
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

<h1 class="site-heading" style="margin-top:40px;">
    <?= $arrayOfTranslations['Login'][$language] ?? 'Login' ?>
</h1>

<?php

function checkLogin($username, $password, $secret) {
    $db = getDB();
    $stmt = $db->prepare("SELECT password, secretPassword, adminStatus FROM Clients WHERE username = ? LIMIT 1");
    if (!$stmt) return "file_error";
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $filePass = $row['password'];
        $fileSecret = $row['secretPassword'];
        $fileAdmin = $row['adminStatus'];

        if (!password_verify($password, $filePass)) {
            $stmt->close();
            return "wrong_password";
        }
        if (!password_verify($secret, $fileSecret)) {
            $stmt->close();
            return "wrong_secret";
        }
        //$_SESSSION["username"] = $username;

        if ($fileAdmin === "1" || strtolower($fileAdmin) === '1') {
            $_SESSION["is_admin"] = true;
            $_SESSION["user_is_admin"] = true;
        } else {
            $_SESSION["is_admin"] = false;
            $_SESSION["user_is_admin"] = false;
        }

        $stmt->close();
        return "ok";
    }

    $stmt->close();
    return "not_found";
}

$showForm = true;


if (isset($_SESSION["logged_in_user"])) {
    echo "<p style='text-align:center; font-size:1.2rem; color:green; font-weight:bold;'>You are already logged in as {$_SESSION['logged_in_user']}.</p>";
    $showForm = false;
}

if (isset($_POST["username"], $_POST["password"], $_POST["secretpassword"])) {

    $usernameInput = $_POST["username"];
    $passwordInput = $_POST["password"];
    $secretInput   = $_POST["secretpassword"];

    echo "<div style='text-align:center; margin-top:20px;'>";

    $result = checkLogin($usernameInput, $passwordInput, $secretInput);

    if ($result === "ok") {

        $_SESSION["logged_in_user"] = $usernameInput;

        echo "<p style='color:green; font-size:1.3rem; font-weight:bold;'>✔ You are logged in</p>";
        $showForm = false;

    } elseif ($result === "wrong_password") {
        echo "<p style='color:red; font-size:1.3rem; font-weight:bold;'>✘ Wrong password</p>";

    } elseif ($result === "wrong_secret") {
        echo "<p style='color:red; font-size:1.3rem; font-weight:bold;'>✘ Wrong secret password</p>";

    } else {
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
