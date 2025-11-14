<?php
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
    $navigationBarLinks = [
        $arrayOfTranslations["HomeBtn"] => "Home.php",
        $arrayOfTranslations["ContactBtn"] => "Contact.php",
        $arrayOfTranslations["ProductBtn"] => "Products.php",
        $arrayOfTranslations["RegisterBtn"] => "Register.php",
        $arrayOfTranslations["LoginBtn"] => "Login.php"
    ];

?>
    <div class="navBar">

        <?php
        //for ($i = 0; $i < count($navigationBarItems); $i++) 
        // Another method of going through the items of an array in PHP:
        foreach ($navigationBarLinks as $keyVariable => $valueVariable) {
        ?>
            <a <?= ($callingPage == $keyVariable) ? "class='highlight'" : ""; ?>
                href="<?= $valueVariable ?>?lang=<?= $language ?>"> <?= $keyVariable ?> </a>

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
