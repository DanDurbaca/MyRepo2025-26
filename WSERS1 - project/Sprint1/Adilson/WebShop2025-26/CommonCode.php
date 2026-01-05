<?php
$language = 'EN';
if (isset($_GET['lang'])) {
    $language = $_GET['lang'];
}

$arrayOfTranslations = [];

$fileTranslations = fopen("Translation.csv", "r");
while (!feof($fileTranslations)) {
    $lineFromFile = fgets($fileTranslations);
    $piecesOfTranslation = explode(";", $lineFromFile);
    $arrayOfTranslations[$piecesOfTranslation[0]]=($language=="EN") ? $piecesOfTranslation[1] : $piecesOfTranslation[2];
}

function NavigationBar($currentFile)
{
    global $language;
    global $arrayOfTranslations;
    $navigationBarLinks = [
        $arrayOfTranslations["HomeBtn"] => "Home.php",
        $arrayOfTranslations["ContactBtn"] => "Contact.php",
        $arrayOfTranslations["ProductBtn"] => "Products.php",
        $arrayOfTranslations["RegisterBtn"] => "Register.php",
        $arrayOfTranslations["LogInBtn"] => "login.php",
    ]
?>
    <div class="navBar">
        <?php
        foreach ($navigationBarLinks as $keyVariable => $valueVariable) {
        ?>
            <a class="navLink <?= (strtolower($currentFile . ".php") == strtolower($valueVariable)) ? 'active' : ''; ?>" href="<?= $valueVariable ?>?lang=<?= $language?>"><?= $keyVariable ?></a>
        <?php
        }
        ?>
        <div class="navBarRight">
            <a class="cartIcon <?= (strtolower($currentFile . ".php") == strtolower("Cart.php")) ? 'active' : ''; ?>" href="Cart.php?lang=<?= $language?>" title="Shopping Cart">🛒</a>
            <form>
                <select name= lang onchange="this.form.submit()">
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

    $fHandler = @fopen("Client.csv", "r");
    if (!$fHandler) return false;
    while (!feof($fHandler)) {
        $newLine = trim(fgets($fHandler));
        if ($newLine === '') continue;
        $items = explode(";", $newLine);
        if (isset($items[0]) && strtolower(trim($items[0])) === 'username') continue;
        $fileUser = isset($items[0]) ? trim($items[0]) : '';
        $filePsw = isset($items[1]) ? trim($items[1]) : '';
        if ($fileUser === $checkedUser && $filePsw === $checkedPsw) {
            fclose($fHandler);
            return true;
        }
    }
    fclose($fHandler);
    return false;
}
?>