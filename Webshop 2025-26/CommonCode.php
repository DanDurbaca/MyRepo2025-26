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
