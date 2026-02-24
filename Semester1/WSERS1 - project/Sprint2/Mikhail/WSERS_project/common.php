<?php
session_start();

if (isset($_POST["Logout"])) {
    session_unset();
    session_destroy();
    session_start();
}

//var_dump($_SESSION);

if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
    $_SESSION["IsAdmin"] = false;
}

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

function head($callingPage)
{
    global $language;
    global $arrayOfTranslations;
?>
    <header>
        <img src="images/logo.jpg" alt="Logo">
        <nav>
            <ul>
                <?php
                /* foreach ($navigationBarLinks as $keyVariable => $valueVariable) {
                ?>
                    <li><a href="<?= $valueVariable ?>?lang=<?= $language ?>" <?= ($callingPageEN == $keyVariable || $callingPageRU == $keyVariable) ? " class='active'" : ""; ?>><?= $keyVariable ?></a></li>
                <?php
                }*/
                ?>
                <li><a href="home.php?lang=<?= $language ?>" <?= ($callingPage == "Home") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["HomeBtn"] ?></a></li>
                <li><a href="products.php?lang=<?= $language ?>" <?= ($callingPage == "Products") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["ProductsBtn"] ?></a></li>
                <?php
                if ($_SESSION["IsAdmin"]) {
                ?>
                    <li><a href="admin.php?lang=<?= $language ?>" <?= ($callingPage == "Admin") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["AdminBtn"] ?></a></li>
                <?php
                }
                if (!$_SESSION["UserLogged"]) {
                ?>
                    <li><a href="register.php?lang=<?= $language ?>" <?= ($callingPage == "Register") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["RegisterBtn"] ?></a></li>
                    <li><a href="login.php?lang=<?= $language ?>" <?= ($callingPage == "Login") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["LoginBtn"] ?></a></li>
                <?php
                } else {
                ?>
                    <form method="POST">
                        <input type="submit" value="LOGOUT" name="Logout">
                    </form>
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