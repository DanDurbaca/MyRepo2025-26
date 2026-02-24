<?php

session_start();

if(isset($_POST["Logout"])) {
    session_unset();
    session_destroy();
    session_start();
}

if(!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
}

if(!isset($_SESSION["UserRole"])) {
    $_SESSION["UserRole"] = "guest";
}

$language = "EN";
if (isset($_GET["lang"])) {
    $language = $_GET["lang"];
}

//..............

$arrayOfTranslations = [];

$fileTranslations = fopen("Translation.csv", "r");
while (!feof($fileTranslations)) {
    $lineFromFile = fgets($fileTranslations);
    $piecesOfTranslatons = explode(";", $lineFromFile);
    if (count($piecesOfTranslatons) == 3)
        $arrayOfTranslations[$piecesOfTranslatons[0]] = ($language == "EN") ? $piecesOfTranslatons[1] : $piecesOfTranslatons[2];
}

function Melnav($caller)
{
    global $language;
    global $arrayOfTranslations;
?>
    <nav>
        <a <?= ($caller == "Home") ? "class='highlight' " : ""; ?>href="Website.php?lang=<?= $language ?>"><?= $arrayOfTranslations["HomeBtn"] ?></a>
        <a <?= ($caller == "Products") ? "class='highlight' " : ""; ?>href="Products.php?lang=<?= $language ?>"><?= $arrayOfTranslations["ProductBtn"] ?></a>
        <a <?= ($caller == "About Us") ? "class='highlight' " : ""; ?>href="aboutus.php?lang=<?= $language ?>"><?= $arrayOfTranslations["AboutUSBtn"] ?></a>
        <a <?= ($caller == "Contact") ? "class='highlight' " : ""; ?>href="contact.php?lang=<?= $language ?>"><?= $arrayOfTranslations["ContactBtn"] ?></a>

        <?php
        // ADMIN LINK
        if (isset($_SESSION["UserRole"]) && $_SESSION["UserRole"] === "admin") {
        ?>
            <a <?= ($caller == "Admin") ? "class='highlight' " : ""; ?>href="Admin.php?lang=<?= $language ?>"><?= $arrayOfTranslations["Admin"] ?></a>
        <?php
        }
        ?>


        <?php

        if(!$_SESSION["UserLogged"])
        {
        ?>

        <a <?= ($caller == "Registor") ? "class='highlight' " : ""; ?>href="Registor.php?lang=<?= $language ?>"><?= $arrayOfTranslations["RegistrationBtn"] ?></a>
        <a <?= ($caller == "Login") ? "class='highlight' " : ""; ?>href="Login.php?lang=<?= $language ?>"><?= $arrayOfTranslations["LoginBtn"] ?></a>

        <?php
        }   else {
            ?>
            <form method="POST">

            <input type="submit" value="LOGOUT" name="Logout" class="highlight">

            </form>
            <?php
        }
        ?>

        <a <?= ($caller == "Cart") ? "class='highlight' " : ""; ?>href="Cart.php?lang=<?= $language ?>"><?= $arrayOfTranslations["WagenBtn"] ?></a>

        <form>

            <select name="lang" class="highlight" onchange="this.form.submit()">
                <option value="EN" <?php if ($language == "EN") print "selected"; ?>>English</option>
                <option value="GE" <?php if ($language == "GE") print "selected"; ?>>Deutsch</option>
            </select>

        </form>

    </nav>
<?php
}

function userAlreadyRegistered($checkedUser)
{
    $bReturnValue = false;

    $fHandler = fopen("Client.csv", "r");
    while (!feof($fHandler)) {
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if ($items[0] == $checkedUser)
            $bReturnValue = true;
    }
    fclose($fHandler);

    return $bReturnValue;
}
?>