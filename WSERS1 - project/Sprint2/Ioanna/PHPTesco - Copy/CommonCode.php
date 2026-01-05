<?php

session_start();

if (isset($_POST["Logout"])) {
}



if (isset($_SESSRION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
}

if (isset($_GET['logout'])) {
    unset($_SESSION['logged_in_user']);
    header("Location: Home.php");
    exit();
}


if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}


if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'EN';
}


$language = $_SESSION['language'];

//var_dump($_SESSION);


$translations = [];
$fileTranslations = fopen("Translations.csv", "r");
$headers = fgetcsv($fileTranslations, 1000, ";");
while (!feof($fileTranslations)) {
    $line = fgetcsv($fileTranslations, 1000, ";");
    if ($line && count($line) >= 3) {

        $translations[$line[0]] =  ($_SESSION['language'] == "EN") ? $line[1] : $line[2];
    }
}
fclose($fileTranslations);


function t($key)
{
    global $translations;
    if (isset($translations[$key])) {
        return $translations[$key];
    }
    return $key;
}

function NavigationBar($callingPage)
{
    global $language, $translations;

    $navigationBarLinks = [
        "nav_home" => "Home.php",
        "nav_contact" => "Contact.php",
        "nav_products" => "Products.php",
        "nav_register" => "Register.php",
        "nav_login" => "Login.php",
        "nav_admin" => "Admin.php",
        
    ];

?>
    <div class="navBar">

        <a <?= ($callingPage == $translations["HomeBtn"]) ? "class='highlight'" : ""; ?>
            href="Home.php?lang=<?= $language ?>"><?= $translations["HomeBtn"] ?></a>
        <a <?= ($callingPage == $translations["ContactBtn"]) ? "class='highlight'" : ""; ?>
            href="Contact.php?lang=<?= $language ?>"><?= $translations["ContactBtn"] ?></a>
        <a <?= ($callingPage == $translations["ProductBtn"]) ? "class='highlight'" : ""; ?>
            href="Products.php?lang=<?= $language ?>"><?= $translations["ProductBtn"] ?></a>
        <a <?= ($callingPage == $translations["RegisterBtn"]) ? "class='highlight'" : ""; ?>
            href="Register.php?lang=<?= $language ?>"><?= $translations["RegisterBtn"] ?></a>
        <a <?= ($callingPage == $translations["LoginBtn"]) ? "class='highlight'" : ""; ?>
            href="Login.php?lang=<?= $language ?>"><?= $translations["LoginBtn"] ?></a>
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin_user') {   ?>
        <a <?= ($callingPage == $translations["AdminBtn"]) ? "class='highlight'" : ""; ?>
            href="Admin.php?lang=<?= $language ?>"><?= $translations["AdminBtn"] ?></a> 
        <?php } ?>
        <form method="GET">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?= ($language == "EN") ? "selected" : "" ?>>English</option>
                <option value="GR" <?= ($language == "GR") ? "selected" : "" ?>>Ελληνικά</option>
            </select>
        </form>

    </div>
<?php
}


if (isset($_SESSION['logged_in_user'])) {
?>
    <a href="?logout=1" style="color: rgb(180, 42, 42);"><?= t('logout') ?> (<?= $_SESSION['logged_in_user'] ?> <?= $_SESSION['user_type'] ?>)</a> 
<?php
}


function checkIfUserExists($userName)
{

    $bReturnValue = false;

    $fHandler = fopen("Clients.csv", "r");
    while (!feof($fHandler)) {
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if ($items[0] == $userName)
            $bReturnValue = true;
    }
    fclose($fHandler);

    return $bReturnValue;
}
?>



</div>