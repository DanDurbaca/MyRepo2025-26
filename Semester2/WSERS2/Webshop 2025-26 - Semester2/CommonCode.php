<?php

session_start();
$connection = new mysqli("localhost", "root", "", "myShop2026");
if (isset($_SESSION["Cart"])) {
    // I have an already existing cart
    // nothing to do here
} else {
    $_SESSION["Cart"] = [];
}

if (isset($_POST["itemToBuy"], $_POST["quantityToBuy"])) {
    $item = $_POST["itemToBuy"];
    if (isset($_SESSION["Cart"][$item])) {
        // we have already ordered an item with this id
        $_SESSION["Cart"][$item] = $_SESSION["Cart"][$item] + $_POST["quantityToBuy"];
    } else {
        $_SESSION["Cart"][$item] =  $_POST["quantityToBuy"];
    }
}



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


$sqlSelectTranslations = $connection->prepare("SELECT * from translations");
$sqlSelectTranslations->execute();
$sqlResult = $sqlSelectTranslations->get_result();
while ($row = $sqlResult->fetch_assoc()) {

    if ($language == "EN")
        $arrayOfTranslations[$row["myKey"]] = $row["english"];
    else
        $arrayOfTranslations[$row["myKey"]] = $row["romanian"];
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
        <a href="ShopCartContents.php"><img width="50px" src="images/ShopCart.webp"></a>
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

    global $connection;

    $sqlSearchUser = $connection->prepare("SELECT * from users where username=?");
    $sqlSearchUser->bind_param("s", $chekedUser);
    $sqlSearchUser->execute();
    $sqlResultUser = $sqlSearchUser->get_result();
    if ($sqlResultUser->num_rows == 0) {
        return false;
    } else {
        return true;
    }
}
