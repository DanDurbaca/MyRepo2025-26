<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<?php
include_once("commonCode.php");
navBar("Register");
?>

<h1><?= $arrayOfTranslations["RegisterH1"] ?></h1>

<?php
$showForm = true;

if (isset($_POST["Username"], $_POST["psw"], $_POST["pswAgain"])) {
    $showForm = false;

    $user = trim($_POST["Username"]); // Trim username
    $psw = $_POST["psw"];            // Do NOT trim password
    $again = $_POST["pswAgain"];

    if ($user === "" || $psw === "" || $again === "") {
        echo "<p>{$arrayOfTranslations["RegisterFailEmpty"]}</p>";
        $showForm = true;
    } elseif ($psw !== $again) {
        echo "<p>{$arrayOfTranslations["RegisterFailMatch"]}</p>";
        $showForm = true;
    } elseif (userAlreadyRegistered($user)) {
        echo "<p>{$arrayOfTranslations["RegisterFailExists"]}</p>";
        $showForm = true;
    } else {
        $hash = password_hash($psw, PASSWORD_DEFAULT);

        // Append user to CSV without adding extra newlines
        $file = fopen("Clients.csv", "a");
        if (filesize("Clients.csv") > 0) {
            fwrite($file, "\n");
        }
        fwrite($file, "$user;$hash");
        fclose($file);

        echo "<p>{$arrayOfTranslations["RegisterSuccess"]}</p>";
    }
}

if ($showForm):
?>

<form method="POST">
    <label><?= $arrayOfTranslations["RegisterUsername"] ?></label><br>
    <input type="text" name="Username" required><br><br>

    <label><?= $arrayOfTranslations["RegisterPassword"] ?></label><br>
    <input type="password" name="psw" required><br><br>

    <label><?= $arrayOfTranslations["RegisterPasswordAgain"] ?></label><br>
    <input type="password" name="pswAgain" required><br><br>

    <input type="submit" value="<?= $arrayOfTranslations["RegisterSubmit"] ?>">
</form>

<?php endif; ?>

</body>
</html>
