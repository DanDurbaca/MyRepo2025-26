<?php
session_start();
include_once("Database.php");
$connection = new mysqli("localhost", "root", "", "webshop");
if (!isset($_SESSION["Cart"])) {
    $_SESSION["Cart"] = [];
}
if (isset($_POST["productId"], $_POST["quantity"])) {
    if (isset($_SESSION["Cart"][$_POST["productId"]])) {
        $_SESSION["Cart"][$_POST["productId"]] += $_POST["quantity"];
    } else {
        $_SESSION["Cart"][$_POST["productId"]] = $_POST["quantity"];
    }
}

if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    session_start();
}



if (!isset($_SESSION["UserLogged"]) && !isset($_SESSION["Admin"])) {
    $_SESSION["UserLogged"] = false;
    $_SESSION["Admin"] = "no";
}


$language = isset($_GET["lang"]) ? $_GET["lang"] : "english";
$arrayOfTranslations = [];
$sqlQuery = $connection->prepare("SELECT * FROM translations;");
$sqlQuery->execute();
$result = $sqlQuery->get_result();
while ($row = $result->fetch_assoc()) {
    if (count($row) < 3) continue;
    $key = $row["TranslationsKey"];
    $arrayOfTranslations[$key] = ($language === "english")
        ? $row["EnglishText"]
        : $row["RussianText"];
}

function NavigationBar($currentPage)
{
    global $arrayOfTranslations, $language;
?>
    <div class="navBar">
        <a href="Home.php?lang=<?= $language ?>"
            <?= ($currentPage === $arrayOfTranslations["HomeBtn"]) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["HomeBtn"] ?>
        </a>

        <a href="Contact.php?lang=<?= $language ?>"
            <?= ($currentPage === $arrayOfTranslations["ContactBtn"]) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["ContactBtn"] ?>
        </a>

        <a href="Products.php?lang=<?= $language ?>"
            <?= ($currentPage === $arrayOfTranslations["ProductBtn"]) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["ProductBtn"] ?>
        </a>

        <?php if (!$_SESSION["UserLogged"]) : ?>
            <a href="Register.php?lang=<?= $language ?>"
                <?= ($currentPage === $arrayOfTranslations["RegisterBtn"]) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["RegisterBtn"] ?>
            </a>
            <a href="Login.php?lang=<?= $language ?>"
                <?= ($currentPage === $arrayOfTranslations["LoginBtn"]) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["LoginBtn"] ?>
            </a>
        <?php else : ?>
            <span> <?= $arrayOfTranslations["WelcomeBtn"] . $_SESSION["Username"] ?>!</span>
        <?php endif; ?>
        <?php if ($_SESSION["UserLogged"]) { ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="logout" value="1">
                <button class="btnLogout" type="submit">Logout</button>
            </form>
            <a href="Forum.php?lang=<?= $language ?>" <?= ($currentPage === $arrayOfTranslations["ForumText"]) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["ForumText"] ?>
            </a>
            <?php
            if ($_SESSION["Admin"] === "yes") {
            ?>

                <a href="Admin.php?lang=<?= $language ?>"
                    <?= ($currentPage === $arrayOfTranslations["AdminBtn"]) ? "class='highlight'" : "" ?>>
                    <?= $arrayOfTranslations["AdminBtn"] ?>
                </a>
            <?php
            } else {
            ?>
                <?php
                $cartCount = 0;
                if (isset($_SESSION["Cart"])) {
                    foreach ($_SESSION["Cart"] as $qty) {
                        $cartCount += $qty;
                    }
                }
                ?>

                <a href="Cart.php?lang=<?= $language ?>" class="cartIcon">
                    <img src="Images/Cart.jpg" alt="Cart">
                    <?php if ($cartCount > 0): ?>
                        <span class="cartCount"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
        <?php
            }
        } ?>

        <!-- Language selector -->
        <form method="get" class="langForm" style="display:inline-block; margin-left:20px;">
            <select name="lang" onchange="this.form.submit()">
                <option value="english" <?= ($language == "english") ? "selected" : "" ?>>English</option>
                <option value="russian" <?= ($language == "russian") ? "selected" : "" ?>>Russian</option>
            </select>
        </form>

    </div>
<?php

}



function verifyUserCredentials($checkedUser, $checkedPsw)
{
    global $adminUser;
    $connection = new mysqli("localhost", "root", "", "webshop");
    $sqlQuery = $connection->prepare("SELECT * FROM client;");
    $sqlQuery->execute();
    $result = $sqlQuery->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row === "") continue;
        if (strtolower($row["Username"]) === "username") continue;

        $fileUser = trim($row["Username"] ?? "");
        $filePsw  = trim($row["UserPassword"] ?? "");

        if ($fileUser === $checkedUser) {


            // If CSV contains plaintext password — allow direct match
            if ($filePsw === $checkedPsw) {
                if ($row["UserAdmin"] == "yes") {
                    $_SESSION["Admin"] = $row["UserAdmin"];
                } else {
                    $_SESSION["Admin"] = "no";
                }
                return true;
            }

            // Otherwise check bcrypt hash
            if (password_verify($checkedPsw, $filePsw)) {
                if ($row["UserAdmin"] == "yes") {
                    $_SESSION["Admin"] = $row["UserAdmin"];
                } else {
                    $_SESSION["Admin"] = "no";
                }
                return true;
            }
            // Username found but password wrong
            return false;
        }
    }

    return false;
}


function userAlreadyRegistered($checkedUser)
{
    $connection = new mysqli("localhost", "root", "", "webshop");
    $sqlQuery = $connection->prepare("SELECT * FROM client;");
    $sqlQuery->execute();
    $result = $sqlQuery->get_result();
    while (($row = $result->fetch_assoc())) {
        if ($row["Username"] === $checkedUser) {
            return true;
        }
    }
    return false;
}

?>