<?php
$language = "en";
if (isset($_GET['language'])) {
    $language = $_GET['language'];
}
$arrayOfTranslations = [];
$fTranslation=fopen("Translation.csv", "r");
$header = fgetcsv($fTranslation, 0, ";");
while(!feof($fTranslation)){
    $piecieOfData=fgetcsv($fTranslation, 0, ";");
    if (!$piecieOfData) {
        continue;
    }
  
  
    if(count($piecieOfData) == count($header))
        {
        $key = $piecieOfData[0];
        $translations = [];
        for($i=1; $i < count($header); $i++){
            $translations[$header[$i]] = $piecieOfData[$i];
        }
        $arrayOfTranslations[$key] = $translations;
    }
}
function NavigationBar($page){
    global $language;
    global $arrayOfTranslations;
  

    $navigationsBarLinks=[
        "Home"=>"index.php",
        "Products"=>"products.php",
        "Contact"=>"contact.php",
        "Login"=>"login.php",
        "Registration"=>"registration.php"
    ];

    ?>
    <div class="navBar">
    <?php

    foreach($navigationsBarLinks as $keyVariable=>$valueVariable){
        ?>
        <a <?=  ($page==$keyVariable) ? 'class="active"' : '' ?> 
        href="<?= $valueVariable; ?>?language=<?=$language?>"><?=$arrayOfTranslations[$keyVariable][$language]; ?></a>
        <?php
    }
    ?>
    <form method="GET">
        <select name="language" onchange="this.form.submit()">
            <option value="en" <?php if(isset($_GET['language']) && $_GET['language'] == 'en') echo 'selected'; ?>>English</option>
            <option value="fr" <?php if(isset($_GET['language']) && $_GET['language'] == 'fr') echo 'selected'; ?>>French</option>
            <option value="de" <?php if(isset($_GET['language']) && $_GET['language'] == 'de') echo 'selected'; ?>>German</option>
        </select>
    </form>
    </div> 
    <?php
}
function userAreg($checkUser){
    $fHandle = fopen("Clients.csv", "r");
    while(!feof($fHandle)){
        $line = fgets($fHandle);
        $userData = explode(";", $line);
        if($userData[0] == $checkUser){
            fclose($fHandle);
            return false;
        }
    }
    fclose($fHandle);
    return true;    
    
    
}


