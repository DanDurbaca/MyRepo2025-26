<?php
session_start();

if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    session_start();
}
if (!isset($_SESSION["UserLogged"]) && !isset($_SESSION["ADMIN"])) {
    $_SESSION["UserLogged"] = false;
    $_SESSION["ADMIN"] = "no";
}

$language = 'EN';
if (isset($_GET['lang'])) {
    $language = $_GET['lang'];
}

$arrayOfTranslations = [];

$fileTranslations = fopen("Translation.csv", "r");
while (!feof($fileTranslations)) {
    $lineFromFile = fgets($fileTranslations);
    $piecesOfTranslation = explode(";", $lineFromFile);
    $arrayOfTranslations[$piecesOfTranslation[0]] = ($language == "EN") ? $piecesOfTranslation[1] : $piecesOfTranslation[2];
}
function NavigationBar($currentFile)
{
    global $language;
    global $arrayOfTranslations;
?>
    <div class="navBar">
        <?php
        ?>
        <a class="navLink <?= strtolower($currentFile) == "home" ? 'active' : ''; ?>" href="<?= "Home.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["HomeBtn"] ?></a>
        <a class="navLink <?= strtolower($currentFile) == "contact" ? 'active' : ''; ?>" href="<?= "Contact.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["ContactBtn"] ?></a>
        <a class="navLink <?= strtolower($currentFile) == "products" ? 'active' : ''; ?>" href="<?= "Products.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["ProductBtn"] ?></a>
        <?php
        if (!$_SESSION["UserLogged"]) {
        ?>
            <a class="navLink <?= strtolower($currentFile) == "register" ? 'active' : ''; ?>" href="<?= "Register.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["RegisterBtn"] ?></a>
            <a class="navLink <?= strtolower($currentFile)  == "login" ? 'active' : ''; ?>" href="<?= "login.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["LogInBtn"] ?></a>
        <?php
        } else {
        ?>
            <form method="POST">
                <input type="submit" value="Logout" name="logout">
            </form>
        <?php
            if ($_SESSION["ADMIN"] == "yes") {
        ?>
                <a class="navLink <?= (strtolower($currentFile . ".php") == "admin.php") ? 'active' : ''; ?>" href="<?= "admin.php" ?>?lang=<?= $language ?>">Admin Panel</a>
        <?php
            }
            print("welcome " . $_SESSION["LoggedUserName"]);
        }
        ?>
        <div class="navBarRight">
            <a class="cartIcon <?= (strtolower($currentFile . ".php") == strtolower("Cart.php")) ? 'active' : ''; ?>" href="Cart.php?lang=<?= $language ?>" title="Shopping Cart">🛒</a>
            <form>
                <select name=lang onchange="this.form.submit()">
                    <option value="EN" <?php if ($language == "EN") print "selected"; ?>>English</option>
                    <option value="PT" <?php if ($language == "PT") print "selected"; ?>>Portuguese</option>
                </select>
            </form>
        </div>
    </div>
<?php
}
function userAlredyResgistred($checkedUser)
{
    /* this function checks if $chekedUser string is an existing user in client.csv
    if the given user is alredy in the file we return true -> user alredy exists
    if not we return false*/
    $bReturnValue = false;
    $fHandler = fopen("Client.csv", "r");
    while (!feof($fHandler)) {
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if ($items[0] == $checkedUser) {
            $bReturnValue = true;
        }
    }
    fclose($fHandler);
    return $bReturnValue;
}

function verifyUserCredentials($checkedUser, $checkedPsw)
{
    global $admin;
    $fHandler = @fopen("Client.csv", "r");
    if (!$fHandler) return false;
    while (!feof($fHandler)) {
        $newLine = trim(fgets($fHandler));
        if ($newLine === '') continue;
        $items = explode(";", $newLine);
        if (isset($items[0]) && strtolower(trim($items[0])) === 'username') continue;
        $fileUser = isset($items[0]) ? trim($items[0]) : '';
        $filePsw = isset($items[1]) ? trim($items[1]) : '';
        $admin = isset($items[4]) ? trim($items[4]) : 'no';
        if ($fileUser === $checkedUser && password_verify($checkedPsw, $filePsw)) {
            fclose($fHandler);
            return true;
        }
    }
    fclose($fHandler);
    return false;
}
?>