<?php

function NavigationBar($callingPage)
{
?>
    <div class="navBar">
        <a <?php if ($callingPage == "Mickey") print "class='highlight'"; ?> href="Home.php">Home</a>
        <a <?php if ($callingPage == "Donald") print "class='highlight'"; ?> href="Contact.php">Contact</a>
        <a <?php if ($callingPage == "Peppa") print "class='highlight'"; ?> href="Products.php">Products</a>
    </div>
<?php
}
