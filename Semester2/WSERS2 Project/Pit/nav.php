<?php

session_start();

if (isset($_POST["Logout"])) {
    session_unset();
    session_destroy();
    session_start();

    $_SESSION["cart"] = [];
    $_SESSION["UserLogged"] = false;
    $_SESSION["IsAdmin"] = false;
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

//var_dump($_SESSION);
if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
}
if (!isset($_SESSION["IsAdmin"])) {
    $_SESSION["IsAdmin"] = false;
}

$language = "EN";
if (isset($_GET["lang"])) {
    $language = $_GET["lang"];
}

//print("The current language is " . $language);

$connection = new mysqli("localhost", "root", "", "4PageWebsite");

$arrayOfTranslations = [];

$sqlQuery = $connection->prepare("SELECT * from Translation");
$sqlQuery->execute();
$result = $sqlQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $arrayOfTranslations[$row["KeyWord"]] = ($language == "EN") ? $row["EnglishText"] : $row["DeutschText"];
}

//var_dump($arrayOfTranslations);

$cartCount = 0;

if (isset($_SESSION["cart"])) {
    foreach ($_SESSION["cart"] as $item) {
        $cartCount += $item["quantity"];
    }
}

function NavigationBar($callingPage)
{
    global $language;
    global $arrayOfTranslations;

    $cartCount = 0;
    if (isset($_SESSION["cart"])) {
        foreach ($_SESSION["cart"] as $item) {
            $cartCount += $item["quantity"];
        }
    }

?>
    <div class="nav">

        <a <?= ($callingPage == $arrayOfTranslations["HomeBtn"]) ? "class='highlight'" : ""; ?>
            href="index.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["HomeBtn"] ?> </a>

        <a <?= ($callingPage == $arrayOfTranslations["ProductsBtn"]) ? "class='highlight'" : ""; ?>
            href="products.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ProductsBtn"] ?> </a>

        <?php
        if (!$_SESSION["UserLogged"]) {
        ?>
            <a <?= ($callingPage == $arrayOfTranslations["ProfileBtn"]) ? "class='highlight'" : ""; ?>
                href="profile.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ProfileBtn"] ?> </a>

            <a <?= ($callingPage == $arrayOfTranslations["RegisterBtn"]) ? "class='highlight'" : ""; ?>
                href="register.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["RegisterBtn"] ?> </a>

        <?php
        } else {
        ?>

            <?php
            if (
                !empty($_SESSION["UserLogged"]) &&
                $_SESSION["UserLogged"] === true &&
                (empty($_SESSION["IsAdmin"]) || $_SESSION["IsAdmin"] !== true)
            ) {
            ?>
                <a <?= ($callingPage == ($arrayOfTranslations["CartBtn"] ?? "Cart")) ? "class='highlight'" : ""; ?>
                    href="cart.php?lang=<?= $language ?>">
                    <?= $arrayOfTranslations["CartBtn"] ?? "Cart" ?> (<?= $cartCount ?>)
                </a>
            <?php
            }
            ?>

            <a <?= ($callingPage == ($arrayOfTranslations["ForumBtn"] ?? "Forum")) ? "class='highlight'" : ""; ?>
                href="forum.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ForumBtn"] ?> </a>

            <form method="POST" class="logout-form">
                <button type="submit" name="Logout" class="logout-nav">
                    <?= $arrayOfTranslations["LogoutBtn"]; ?>
                </button>
            </form>
            <?php

            if (!empty($_SESSION["IsAdmin"]) && $_SESSION["IsAdmin"] === true) {
            ?>
                <a <?= ($callingPage == $arrayOfTranslations["AdminBtn"]) ? "class='highlight'" : ""; ?>
                    href="admin.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["AdminBtn"] ?> </a>
        <?php
            }
        }
        ?>
        <a <?= ($callingPage == $arrayOfTranslations["ContactBtn"]) ? "class='highlight'" : ""; ?>
            href="contact.php?lang=<?= $language ?>"> <?= $arrayOfTranslations["ContactBtn"] ?> </a>

        <form name="language">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?php if ($language == "EN") print "selected"; ?>>EN</option>
                <option value="DE" <?php if ($language == "DE") print "selected"; ?>>DE</option>
            </select>
        </form>


    </div>
<?php
}
