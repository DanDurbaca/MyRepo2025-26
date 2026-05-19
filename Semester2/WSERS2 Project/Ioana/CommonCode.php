<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

$connection = new mysqli("localhost", "root", "", "TescoDB");

if ($connection->connect_errno) {
    die("Database connection failed: " . $connection->connect_error);
}

$connection->set_charset("utf8mb4");

if (isset($_GET['logout'])) {
    unset($_SESSION['logged_in_user']);
    unset($_SESSION['userType']);
    header("Location: Home.php");
    exit();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ["EN", "GR"], true)) {
    $_SESSION['language'] = $_GET['lang'];
}

if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'EN';
}

$language = $_SESSION['language'];
$translations = [];

$sqlSelectTranslations = $connection->prepare("SELECT myKey, english, greek FROM Translations");
if ($sqlSelectTranslations) {
    $sqlSelectTranslations->execute();
    $sqlResult = $sqlSelectTranslations->get_result();

    while ($row = $sqlResult->fetch_assoc()) {
        $translations[$row['myKey']] = ($language === 'EN') ? $row['english'] : $row['greek'];
    }
}

function t($key)
{
    global $translations;

    return $translations[$key] ?? $key;
}

function NavigationBar($callingPage)
{
    global $language;

    ?>
    <div class="navBar">
        <a <?= ($callingPage === "Home") ? "class='highlight'" : ""; ?>
            href="Home.php?lang=<?= $language ?>"><?= t("HomeBtn") ?></a>
        <a <?= ($callingPage === "Contact") ? "class='highlight'" : ""; ?>
            href="Contact.php?lang=<?= $language ?>"><?= t("ContactBtn") ?></a>
        <a <?= ($callingPage === "Products") ? "class='highlight'" : ""; ?>
            href="Products.php?lang=<?= $language ?>"><?= t("ProductBtn") ?></a>
        <a <?= ($callingPage === "Forum") ? "class='highlight'" : ""; ?>
            href="Forum.php?lang=<?= $language ?>"><?= t("ForumBtn") ?></a>
        <?php if (isset($_SESSION['logged_in_user']) && (!isset($_SESSION['userType']) || $_SESSION['userType'] != "administrator")) { ?>
        <a <?= ($callingPage === "ShopCart") ? "class='highlight'" : ""; ?>
            href="ShopCartContents.php?lang=<?= $language ?>"><?= ($language === "GR") ? "Καλάθι" : "Cart" ?> (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a>
        <?php } ?>
        <?php if (!isset($_SESSION['logged_in_user'])) { ?>
        <a <?= ($callingPage === "Register") ? "class='highlight'" : ""; ?>
            href="Register.php?lang=<?= $language ?>"><?= t("RegisterBtn") ?></a>
        <?php } ?>
        <?php if (isset($_SESSION['logged_in_user'])) { ?>
        <a href="?logout=1"><?= t('logout') ?> (<?= htmlspecialchars($_SESSION['logged_in_user']) ?>)</a>
        <?php } else { ?>
        <a <?= ($callingPage === "Login") ? "class='highlight'" : ""; ?>
            href="Login.php?lang=<?= $language ?>"><?= t("LoginBtn") ?></a>
        <?php } ?>
        <?php if (isset($_SESSION['userType']) && $_SESSION['userType'] == "administrator") { ?>
        <a <?= ($callingPage === "Admin") ? "class='highlight'" : ""; ?>
            href="Admin.php?lang=<?= $language ?>"><?= t("AdminBtn") ?></a>
        <?php } ?>
        <form method="GET">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?= ($language === "EN") ? "selected" : "" ?>>English</option>
                <option value="GR" <?= ($language === "GR") ? "selected" : "" ?>>Ελληνικά</option>
            </select>
        </form>
    </div>
    <?php
}

function checkIfUserExists($checkedUser)
{
    global $connection;

    $sqlSearchUser = $connection->prepare("SELECT userID FROM Clients WHERE Username = ?");
    if (!$sqlSearchUser) {
        return false;
    }

    $sqlSearchUser->bind_param("s", $checkedUser);
    $sqlSearchUser->execute();
    $sqlResultUser = $sqlSearchUser->get_result();

    return $sqlResultUser->num_rows > 0;
}

function userAlreadyRegisted($checkedUser)
{
    return checkIfUserExists($checkedUser);
}
