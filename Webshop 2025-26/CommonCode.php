<?php

function NavigationBar($callingPage)
{
    $navigationBarLinks = [
        "Home" => "Home.php",
        "Contact" => "Contact.php",
        "Products" => "Products.php",
        "Register" => "Register.php"
    ];

?>
    <div class="navBar">

        <?php
        //for ($i = 0; $i < count($navigationBarItems); $i++) 
        // Another method of going through the items of an array in PHP:
        foreach ($navigationBarLinks as $keyVariable => $valueVariable) {
        ?>
            <a <?= ($callingPage == $keyVariable) ? "class='highlight'" : ""; ?>
                href="<?= $valueVariable ?>"> <?= $keyVariable ?> </a>

        <?php
        }

        ?>


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
