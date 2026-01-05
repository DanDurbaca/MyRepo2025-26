<?php
// Start session for language handling
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['logged_in_user']);
    header("Location: Home.php");
    exit();
}

// Handle language change
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Set default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'EN';
}

$language = $_SESSION['language'];

// Load translations
$translations = [];
$fileTranslations = fopen("Translations.csv", "r");
$headers = fgetcsv($fileTranslations, 1000, ";");
while (!feof($fileTranslations)) {
    $line = fgetcsv($fileTranslations, 1000, ";");
    if ($line && count($line) >= 3) {
        $translations[$line[0]] = [
            'EN' => $line[1],
            'GR' => $line[2]
        ];
    }
}
fclose($fileTranslations);

// Translation helper function
function t($key) {
    global $translations, $language;
    if (isset($translations[$key][$language])) {
        return $translations[$key][$language];
    }
    return $key;
}

function NavigationBar($callingPage)
{
    global $language;
    
    $navigationBarLinks = [
        "nav_home" => "Home.php",
        "nav_contact" => "Contact.php",
        "nav_products" => "Products.php",
        "nav_register" => "Register.php",
        "nav_login" => "Login.php"
    ];

    ?>
    <div class="navBar">
        <?php
        foreach ($navigationBarLinks as $keyVariable => $valueVariable) {
            $isActive = (
                ($callingPage == "Home" && $keyVariable == "nav_home") ||
                ($callingPage == "Contact" && $keyVariable == "nav_contact") ||
                ($callingPage == "Products" && $keyVariable == "nav_products") ||
                ($callingPage == "Register" && $keyVariable == "nav_register") ||
                ($callingPage == "Login" && $keyVariable == "nav_login")
            );
            ?>

            <a <?= $isActive ? "class='highlight'" : ""; ?> 
                href="<?= $valueVariable ?>"> <?= t($keyVariable) ?>
            </a>
        <?php 
        }
        
        // Show logout button if user is logged in
        if (isset($_SESSION['logged_in_user'])) {
            ?>
            <a href="?logout=1" style="color: rgb(180, 42, 42);"><?= t('logout') ?> (<?= $_SESSION['logged_in_user'] ?>)</a>
            <?php
        }
        ?>

        <form method="GET">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?= ($language == "EN") ? "selected" : "" ?>>English</option>
                <option value="GR" <?= ($language == "GR") ? "selected" : "" ?>>Ελληνικά</option>
            </select>
        </form>

    </div>
    <?php
}
