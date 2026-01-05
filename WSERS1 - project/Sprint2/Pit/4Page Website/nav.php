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
}
if(!isset($_SESSION["IsAdmin"])){
    $_SESSION["IsAdmin"] = false;
}




$language = "EN";
if (isset($_GET["lang"])) {
    $language = $_GET["lang"];
}

//print("The current language is " . $language);

$arrayOfTranslations = [];

$fileTranslations = fopen("Translation.csv", "r");
while (!feof($fileTranslations)) {
    $lineFromFile = fgets($fileTranslations);
    $piecesOfTranslations = explode(";", $lineFromFile);
    $arrayOfTranslations[$piecesOfTranslations[0]] = ($language == "EN") ? $piecesOfTranslations[1] : $piecesOfTranslations[2];
}

//var_dump($arrayOfTranslations);

function NavigationBar($callingPage)
{
    global $language;
    global $arrayOfTranslations;

?>
    <div class="nav">

        <a <?= ($callingPage == $arrayOfTranslations["HomeBtn"]) ? "class='higlight'" : ""; ?>
            href="index.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["HomeBtn"] ?> </a>

        <a <?= ($callingPage == $arrayOfTranslations["ProductsBtn"]) ? "class='higlight'" : ""; ?>
            href="products.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ProductsBtn"] ?> </a>

        <a <?= ($callingPage == $arrayOfTranslations["ContactBtn"]) ? "class='higlight'" : ""; ?>
            href="contact.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ContactBtn"] ?> </a>

        <?php
        if (!$_SESSION["UserLogged"]) {
        ?>

            <a <?= ($callingPage == $arrayOfTranslations["ProfileBtn"]) ? "class='higlight'" : ""; ?>
                href="profile.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ProfileBtn"] ?> </a>

            <a <?= ($callingPage == $arrayOfTranslations["RegisterBtn"]) ? "class='higlight'" : ""; ?>
                href="register.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["RegisterBtn"] ?> </a>

        <?php
        } else {
        ?>
            <form method="POST" class="logout-form">
                <button type="submit" name="Logout" class="logout-nav">
                    <?= $arrayOfTranslations["LogoutBtn"]; ?>
                </button>
            </form>
        <?php

         if (!empty($_SESSION["IsAdmin"]) && $_SESSION["IsAdmin"] === true) {
        ?>
        <a <?= ($callingPage == $arrayOfTranslations["AdminBtn"]) ? "class='higlight'" : ""; ?>
           href="admin.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["AdminBtn"] ?> </a>
        <?php
        }
    }

        ?>

        <form name="language">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?php if ($language == "EN") print "selected"; ?>> English</option>
                <option value="DE" <?php if ($language == "DE") print "selected"; ?>> Deutsch</option>
            </select>
        </form>


    </div>
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
    fclose($fHandler);

    return $bReturnValue;
}

function passwordOk($checkedUser, $checkedPassword)
{

    $bReturnValue = false;

    $fHandler = fopen("Clients.csv", "r");
    while (!feof($fHandler)) {
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if (count($items) >= 2) {
            $username = trim($items[0]);
            $password = trim($items[1]);

            if ($username === $checkedUser && $password === $checkedPassword) {
                $bReturnValue = true;
                break;
            }
        }
    }
    fclose($fHandler);

    return $bReturnValue;
}
