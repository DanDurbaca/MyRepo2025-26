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


$language = "EN";
if (isset($_GET["lang"])) {
    $language = $_GET["lang"];
}

//print("The current language is" . $language);

$arrayOfTranslations = [];

$fileTranslations = fopen("Translation.csv", "r");
while (!feof($fileTranslations)) {
    $lineFromFile = fgets($fileTranslations);
    $piecesOfTranslations = explode(";", $lineFromFile);
    //$arrayOfTranslations[$piecesOfTranslations[0]] = ($language == "EN") ? $piecesOfTranslations[1] : $piecesOfTranslations[2];
    // doing the same thing with a full if statement:
    if ($language == "EN")
        $arrayOfTranslations[$piecesOfTranslations[0]] = $piecesOfTranslations[1];
    else
        $arrayOfTranslations[$piecesOfTranslations[0]] = $piecesOfTranslations[2];
}

//var_dump($arrayOfTranslations);


function NavigationBar($callingPage)
{
    global $language;
    global $arrayOfTranslations;

?>
    <div class="navBar">

        <a <?= ($callingPage == $arrayOfTranslations["HomeBtn"]) ? "class='highlight'" : ""; ?>
            href="Home.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["HomeBtn"] ?> </a>

        <a <?= ($callingPage == $arrayOfTranslations["ContactBtn"]) ? "class='highlight'" : ""; ?>
            href="Contact.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ContactBtn"] ?> </a>

        <a <?= ($callingPage == $arrayOfTranslations["ProductBtn"]) ? "class='highlight'" : ""; ?>
            href="Products.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ProductBtn"] ?> </a>

        <?php
        if (!$_SESSION["UserLogged"]) {
        ?>

            <a <?= ($callingPage == $arrayOfTranslations["RegisterBtn"]) ? "class='highlight'" : ""; ?>
                href="Register.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["RegisterBtn"] ?> </a>


            <a <?= ($callingPage == $arrayOfTranslations["LoginBtn"]) ? "class='highlight'" : ""; ?>
                href="Login.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["LoginBtn"] ?> </a>
        <?php
        } else {
            print("Welcome " . $_SESSION["Username"]);
        ?>

            <form method="POST">
                <input type="submit" value="LOGOUT" name="Logout">
            </form>

        <?php

        }

        ?>
        <form>
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?php if ($language == "EN") print "selected"; ?>>English</option>
                <option value="RO" <?php if ($language == "RO") print "selected"; ?>>Romanian</option>
            </select>
        </form>


    </div>
<?php
}

function userAlreadyRegistered($chekedUser)
{
    // this function checks if $checkUser string is an existing user in the Clients.csv
    // IF the given user IS already in the file we will return TRUE ->user already registers
    // IF NOT (the user did not register) we will return from this function FALSE

    $bReturnValue = false; // the user NOT in our database

    // searching:
    $fHandler = fopen("Clients.csv", "r");
    while (!feof($fHandler)) {
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if ($items[0] == $chekedUser)
            $bReturnValue = true;
    }
    fclose($fHandler);

    return $bReturnValue;
}
