<?php
$language = "EN";
if (isset($_GET["lang"])) {
    $language = $_GET["lang"];
}

$arrayOfTranslations = [];
$fileTranslations = fopen("Translation.csv", "r");
fgets($fileTranslations);
fgets($fileTranslations);
while (!feof($fileTranslations)) {
    $lineFromFile = fgets($fileTranslations);
    $piecesOfTranslation = explode(";", $lineFromFile);
    //$arrayOfTranslations[$piecesOfTranslation[0]]=($language=="EN")?$piecesOfTranslation[1]:$piecesOfTranslation[2];
    if ($language == "EN") {
        $arrayOfTranslations[$piecesOfTranslation[0]] = trim($piecesOfTranslation[1]);
    } else {
        $arrayOfTranslations[$piecesOfTranslation[0]] = trim($piecesOfTranslation[2]);
    }
}

function head($callingPageEN, $callingPageRU)
{
    global $language;
    global $arrayOfTranslations;
    $navigationBarLinks = [$arrayOfTranslations["HomeBtn"] => "home.php", $arrayOfTranslations["ProductsBtn"] => "products.php",  $arrayOfTranslations["RegisterBtn"] => "register.php", $arrayOfTranslations["LoginBtn"] => "login.php"];
?>
    <header>
        <img src="images/logo.jpg" alt="Logo">
        <nav>
            <ul>
                <?php
                foreach ($navigationBarLinks as $keyVariable => $valueVariable) {
                ?>
                    <li><a href="<?= $valueVariable ?>?lang=<?= $language ?>" <?= ($callingPageEN == $keyVariable || $callingPageRU == $keyVariable) ? " class='active'" : ""; ?>><?= $keyVariable ?></a></li>
                <?php
                }
                ?>
                <form>
                    <select name="lang" onchange="this.form.submit()">
                        <option value="EN" <?php if ($language == "EN") print "selected"; ?>><?= $arrayOfTranslations["Language1"] ?></option>
                        <option value="RU" <?php if ($language == "RU") print "selected"; ?>><?= $arrayOfTranslations["Language2"] ?></option>
                    </select>
                </form>
            </ul>
        </nav>
    </header>
<?php
}

function foot()
{
global $arrayOfTranslations;
?>
    <footer>
        <p><?= $arrayOfTranslations["Footer"] ?></p>
    </footer>
<?php
}

function userAlreadyRegistered($checkedUser)
{
    $bReturnValue = false;
    $fHandler = fopen("Clients.csv", "r");
    while (!feof($fHandler)) {
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if ($items[0] == $checkedUser)
            $bReturnValue = true;
    }
    return $bReturnValue;
}
?>