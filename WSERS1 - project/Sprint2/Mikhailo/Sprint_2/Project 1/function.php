<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$language = $_GET['language'] ?? "en";


if (!isset($_SESSION["is_admin"])){
    $_SESSION["is_admin"] = false;
}
if (!isset($_SESSION["user_is_admin"])) {

    $_SESSION["user_is_admin"] = false;
}


$arrayOfTranslations = [];
if (($file = fopen("Translation.csv", "r")) !== false) {

    $header = fgetcsv($file, 0, ";"); 

    while (($row = fgetcsv($file, 0, ";")) !== false) {
        if (count($row) !== count($header)) {
            continue; 
        }

        $key = $row[0];
        for ($i = 1; $i < count($header); $i++) {
            $arrayOfTranslations[$key][$header[$i]] = $row[$i];
        }
    }

    fclose($file);
}



function NavigationBar($page)
{
    global $language;
    global $arrayOfTranslations;

    
    $navigationsBarLinks = [
        "Home"         => "index.php",
        "Products"     => "products.php",
        "Contact"      => "contact.php",
        "Login"        => "login.php",
        "Registration" => "registration.php"
    ];

    if (isset($_SESSION["logged_in_user"])) {
        unset($navigationsBarLinks["Login"]);
        unset($navigationsBarLinks["Registration"]);
        $navigationsBarLinks["Logout"] = "logout.php";
    }

    ?>
    <div class="navBar">
        <?php foreach ($navigationsBarLinks as $key => $file): ?>
            <a 
                href="<?= $file ?>?language=<?= $language ?>"
                <?= ($page == $key) ? 'class="active"' : '' ?>
            >
                <?= $arrayOfTranslations[$key][$language] ?? $key ?>
            </a>
        <?php endforeach; 
        
        if ($_SESSION["is_admin"]){
            ?>

                <a href="Admin.php">Admin</a>

            <?php
        }
        
        ?>
        

    
        <form method="GET" style="display:inline-block; margin-left:auto;">
            <select name="language" onchange="this.form.submit()">
                <option value="en" <?= ($language == 'en') ? 'selected' : '' ?>>English</option>
                <option value="fr" <?= ($language == 'fr') ? 'selected' : '' ?>>French</option>
                <option value="de" <?= ($language == 'de') ? 'selected' : '' ?>>German</option>
            </select>
        </form>
    </div>
    <?php
}



function userAreg($checkUser)
{
    $file = fopen("Clients.csv", "r");
    if (!$file) {
        return true;
    }

    while (($line = fgets($file)) !== false) {
        $userData = explode(";", trim($line));

        if (isset($userData[0]) && $userData[0] === $checkUser) {
            fclose($file);
            return false; 
        }
    }

    fclose($file);
    return true;
}

?>
