<?php
session_start();

//Initialize session
if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
}

//Default usertype
if (!isset($_SESSION["UserType"])) {
    $_SESSION["UserType"] = "regular";
}

//Language
$language = isset($_GET["lang"]) ? $_GET["lang"] : "EN";

$arrayOfTranslations = [];
$fileTranslations = fopen("Translations.csv", "r");

while (($line = fgets($fileTranslations)) !== false) {
    $line = trim($line);
    if ($line === "") continue;
    $pieces = explode(";", $line);
    if (count($pieces) < 3) continue;
    $key = $pieces[0];
    $arrayOfTranslations[$key] = ($language === "EN") ? $pieces[1] : $pieces[2];
}

fclose($fileTranslations);

//Navigation Bar
function NavigationBar($callingPage)
{
    global $arrayOfTranslations, $language;
?>
    <div class="navBar">
        <a href="Home.php?lang=<?= $language ?>"
            <?= ($callingPage === $arrayOfTranslations["HomeBtn"]) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["HomeBtn"] ?>
        </a>

        <a href="Contact.php?lang=<?= $language ?>"
            <?= ($callingPage === $arrayOfTranslations["ContactBtn"]) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["ContactBtn"] ?>
        </a>

        <a href="Products.php?lang=<?= $language ?>"
            <?= ($callingPage === $arrayOfTranslations["ProductBtn"]) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["ProductBtn"] ?>
        </a>

        <?php if (!$_SESSION["UserLogged"]) : ?>
            <a href="Register.php?lang=<?= $language ?>"
                <?= ($callingPage === $arrayOfTranslations["RegisterBtn"]) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["RegisterBtn"] ?>
            </a>
            <a href="Login.php?lang=<?= $language ?>"
                <?= ($callingPage === $arrayOfTranslations["LoginBtn"]) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["LoginBtn"] ?>
            </a>
        <?php else: ?>
            <span><?= $arrayOfTranslations["WelcomeLabel"] ?><?= htmlspecialchars($_SESSION["Username"]) ?>!</span>
            <?php if ($_SESSION["UserType"] === "Admin") : ?>
                <a href="Admin.php?lang=<?= $language ?>"
                    <?= ($callingPage === "Admin") ? "class='highlight'" : "" ?>>
                    Admin Panel
                </a>
            <?php endif; ?>
            <a href="Logout.php?lang=<?= $language ?>" class="logout-btn">
                <?= $arrayOfTranslations["LogoutBtn"] ?? "Logout" ?>
            </a>
        <?php endif; ?>

        <!-- Language Selector -->
        <form method="get" style="display:inline-block; margin-left:20px;">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?= ($language == "EN") ? "selected" : "" ?>>English</option>
                <option value="PT" <?= ($language == "PT") ? "selected" : "" ?>>Portuguese</option>
            </select>
        </form>
    </div>
<?php
}

//Check if user already registered
function userAlreadyRegistered($checkedUser)
{
    $fHandler = fopen("Clients.csv", "r");
    while (!feof($fHandler)) {
        $line = trim(fgets($fHandler));
        if ($line == "") continue;
        $items = explode(";", $line);
        if ($items[0] == $checkedUser) {
            fclose($fHandler);
            return true;
        }
    }
    fclose($fHandler);
    return false;
}

// Function to include CSS
function includeCSS($pageCSS = "")
{
    // Global CSS (always included)
    echo '<link rel="stylesheet" type="text/css" href="global.css?v=' . time() . '">' . PHP_EOL;

    // Page-specific CSS (optional)
    if ($pageCSS != "") {
        echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($pageCSS) . '?v=' . time() . '">' . PHP_EOL;
    }

    // Google Fonts
    echo '<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">' . PHP_EOL;
}

?>