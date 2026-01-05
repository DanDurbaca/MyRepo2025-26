<?php
$language = "EN";
if (isset($_GET["lang"])){
    $language = $_GET["lang"]; 
}

//print("The current language is " . $language);

$arrayOfTranslations = [];

$fileTranslations = fopen("Translation.csv", "r");
while(!feof($fileTranslations)) {
    $lineFromFile = fgets($fileTranslations);
    $piecesOfTranslations = explode(";", $lineFromFile);
    $arrayOfTranslations[$piecesOfTranslations[0]] = ($language=="EN")?$piecesOfTranslations[1]:$piecesOfTranslations[2];
}

//var_dump($arrayOfTranslations);

function NavigationBar($callingPage)
{
    global $language;
    global $arrayOfTranslations;
    $navigationBarLinks = [
        $arrayOfTranslations["HomeBtn"] => "index.php",
        $arrayOfTranslations["ProductsBtn"] => "products.php",
        $arrayOfTranslations["ProfileBtn"] => "profile.php",
        $arrayOfTranslations["RegisterBtn"] => "register.php",
        $arrayOfTranslations["ContactBtn"] => "contact.php"
    ];
?>
    <div class="nav">

        <?php
        foreach($navigationBarLinks as $keyVariable => $valueVariable){
        ?>
             <a <?= ($callingPage == $keyVariable) ? "class='higlight'" : ""; ?>
                href="<?= $valueVariable ?>?lang=<?= $language ?>"> <?= $keyVariable ?> </a>

        <?php
        }
        ?>

        <form name="language">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?php if ($language=="EN") print "selected"; ?>> English</option>
                <option value="DE" <?php if ($language=="DE") print "selected"; ?>> Deutsch</option>
            </select>
        </form>
    </div>
<?php
}


function userAlreadyRegistered($checkedUser){

    $bReturnValue = false;

    $fHandler = fopen("Clients.csv","r");
    while(!feof($fHandler)){
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if($items[0] == $checkedUser)
            $bReturnValue = true;
    }
    fclose($fHandler);

    return $bReturnValue;
}

function passwordOk($checkedUser, $checkedPassword){
    
    $bReturnValue = false;

    $fHandler = fopen("Clients.csv","r");
    while(!feof($fHandler)){
        $newLine = fgets($fHandler);
        $items = explode(";", $newLine);
        if(count($items) >=2){
            $username = trim($items[0]);
            $password = trim($items[1]);

            if($username === $checkedUser && $password === $checkedPassword) {
            $bReturnValue = true;
            break;
            }
        }
        
    }
    fclose($fHandler);

    return $bReturnValue;
}
