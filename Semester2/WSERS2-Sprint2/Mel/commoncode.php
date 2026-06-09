<?php
/** @var array $arrayOfTranslations */
session_start();
$connection = new mysqli("localhost", "root", "", "DatabasePouMe708");







if (isset($_SESSION["Cart"])) {
} else {
    $_SESSION["Cart"] = [];
}

if (isset($_POST["itemToBuy"], $_POST["quantityToBuy"])) {
    $item = $_POST["itemToBuy"];
    if (isset($_SESSION["Cart"][$item])) {
        $_SESSION["Cart"][$item] = $_SESSION["Cart"][$item] + $_POST["quantityToBuy"];
    } else {
        $_SESSION["Cart"][$item] = $_POST["quantityToBuy"];
    }
}
if (isset($_POST["itemToDelete"])) {
    unset($_SESSION["Cart"][$_POST["itemToDelete"]]);
}





if (isset($_POST["Logout"])) {
    session_unset();
    session_destroy();
    session_start();
    header("Refresh:0; url=index.php");
}

if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = "false";
}

if (!isset($_SESSION["UserRole"])) {
    $_SESSION["UserRole"] = "guest";
}

$language = "EN";
if (isset($_GET["lang"])) {
    $language = $_GET["lang"];
}

/*

$connection = new mysqli("localhost","root","","DatabasePouMe708");

$sqlQuery = $connection -> prepare("SELECT * from translation");

$sqlQuery->execute();

$result = $sqlQuery->get_result();

*/

$arrayOfTranslations = [];
$sqlQuery = $connection->prepare("SELECT * from translation");
$sqlQuery->execute();
$result = $sqlQuery->get_result();
while ($row = $result->fetch_assoc()) {
    $arrayOfTranslations[$row["TranslationKey"]] = ($language == "EN") ? $row["EnglishText"] : $row["GermanText"];
}

function Melnav(string $caller)
{
    global $language;
    global $arrayOfTranslations;
?>
    <nav>
        <a <?= ($caller == "Home") ? "class='highlight' " : ""; ?>href="index.php?lang=<?= $language ?>"><?= $arrayOfTranslations["HomeBtn"] ?></a>
        <a <?= ($caller == "Products") ? "class='highlight' " : ""; ?>href="Products.php?lang=<?= $language ?>"><?= $arrayOfTranslations["ProductBtn"] ?></a>
        <a <?= ($caller == "About Us") ? "class='highlight' " : ""; ?>href="aboutus.php?lang=<?= $language ?>"><?= $arrayOfTranslations["AboutUSBtn"] ?></a>
        <a <?= ($caller == "Contact") ? "class='highlight' " : ""; ?>href="contact.php?lang=<?= $language ?>"><?= $arrayOfTranslations["ContactBtn"] ?></a>

        <?php
        // ADMIN LINK


        function userAlreadyRegistered(string $checkedUser)
        {
            global $connection;

            $sqlSearchUser = $connection->prepare("Select * from Client where Username=?");
            $sqlSearchUser->bind_param("s", $checkedUser);
            $sqlSearchUser->execute();
            $sqlResultUser = $sqlSearchUser->get_result();
            if ($sqlResultUser->num_rows == 0) {
                return false;
            } else {
                return true;
            }
        }








        if (isset($_SESSION["UserRole"]) && $_SESSION["UserRole"] === "admin") {
        ?>
            <a <?= ($caller == "Admin") ? "class='highlight' " : ""; ?>href="Admin.php?lang=<?= $language ?>"><?= $arrayOfTranslations["Admin"] ?></a>
        <?php
        }
        ?>


        <?php

        if ($_SESSION["UserLogged"]=="false") {
        ?>

            <a <?= ($caller == "Registor") ? "class='highlight' " : ""; ?>href="Registor.php?lang=<?= $language ?>"><?= $arrayOfTranslations["RegistrationBtn"] ?></a>
            <a <?= ($caller == "Login") ? "class='highlight' " : ""; ?>href="Login.php?lang=<?= $language ?>"><?= $arrayOfTranslations["LoginBtn"] ?></a>
        <?php
        } else {
        ?>

            <a <?= ($caller == "Forum") ? "class='highlight' " : ""; ?>href="Forum.php?lang=<?= $language ?>"><?= $arrayOfTranslations["ForumBtn"] ?></a>
            <?php
            if ($_SESSION["UserRole"] != "admin"){
            ?>
            <a <?= ($caller == "Cart") ? "class='highlight' " : ""; ?>href="Cart.php?lang=<?= $language ?>"><?= $arrayOfTranslations["WagenBtn"] ?></a>
            <?php
            }
            ?>

            <form method="POST">

                <input type="submit" value="<?= $arrayOfTranslations["LogoutBtn"] ?>" name="Logout" class="highlight">

            </form>
        <?php
        }
        ?>

        <form>

            <select name="lang" class="highlight" onchange="this.form.submit()">
                <option value="EN" <?php if ($language == "EN") print "selected"; ?>>English</option>
                <option value="GE" <?php if ($language == "GE") print "selected"; ?>>Deutsch</option>
            </select>

        </form>

    </nav>
<?php
}
?>