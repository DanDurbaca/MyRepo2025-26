<?php
session_start();
$connection = new mysqli("localhost", "root", "", "webshop2025_26");

//Initialize Cart
if (!isset($_SESSION["Cart"])) {
    $_SESSION["Cart"] = [];
}

//Initialize session login
if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
}

//Default usertype
if (!isset($_SESSION["UserType"])) {
    $_SESSION["UserType"] = "regular";
}

//Add to Cart
if (isset($_POST["itemToBuy"], $_POST["quantityToBuy"])) {
    if ($_SESSION["UserType"] !== "Admin") {
        $item = $_POST["itemToBuy"];
        $qty = (int)$_POST["quantityToBuy"];
        if (isset($_SESSION["Cart"][$item])) {
            $_SESSION["Cart"][$item] += $qty;
        } else {
            $_SESSION["Cart"][$item] = $qty;
        }
    }
}

//Language
$language = isset($_GET["lang"]) ? $_GET["lang"] : "EN";

//Load Translations
$arrayOfTranslations = [];
$sqlQuery = $connection->prepare("SELECT * FROM Translations");
$sqlQuery->execute();
$result = $sqlQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $key = $row["TranslationKey"];
    $arrayOfTranslations[$key] = ($language === "EN") ? $row["EnglishText"] : $row["PortugueseText"];
}

//Navigation Bar Function
function NavigationBar($callingPage)
{
    global $arrayOfTranslations, $language;
?>
    <div class="navBar">
        <a href="Home.php?lang=<?= $language ?>" <?= ($callingPage === ($arrayOfTranslations["HomeBtn"] ?? "Home")) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["HomeBtn"] ?? "Home" ?>
        </a>

        <a href="Contact.php?lang=<?= $language ?>" <?= ($callingPage === ($arrayOfTranslations["ContactBtn"] ?? "Contact")) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["ContactBtn"] ?? "Contact" ?>
        </a>

        <a href="Products.php?lang=<?= $language ?>" <?= ($callingPage === ($arrayOfTranslations["ProductBtn"] ?? "Products")) ? "class='highlight'" : "" ?>>
            <?= $arrayOfTranslations["ProductBtn"] ?? "Products" ?>
        </a>

        <?php if ($_SESSION["UserLogged"]) : ?>
            <a href="Forum.php?lang=<?= $language ?>" <?= ($callingPage === ($arrayOfTranslations["ForumTitle"] ?? "Forum")) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["ForumBtn"] ?? "Forum" ?>
            </a>

            <?php if ($_SESSION["UserType"] === "Admin") : ?>
                <a href="Admin.php?lang=<?= $language ?>" <?= ($callingPage === "Admin") ? "class='highlight'" : "" ?>>
                    <?= $arrayOfTranslations["AdminBtn"] ?? "Admin Panel" ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$_SESSION["UserLogged"]) : ?>
            <a href="Register.php?lang=<?= $language ?>" <?= ($callingPage === ($arrayOfTranslations["RegisterBtn"] ?? "Register")) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["RegisterBtn"] ?? "Register" ?>
            </a>

            <a href="Login.php?lang=<?= $language ?>" <?= ($callingPage === ($arrayOfTranslations["LoginBtn"] ?? "Login")) ? "class='highlight'" : "" ?>>
                <?= $arrayOfTranslations["LoginBtn"] ?? "Login" ?>
            </a>

        <?php else: ?>
            <span class="welcome-text">
                <?= $arrayOfTranslations["WelcomeLabel"] ?? "Welcome, " ?><?= htmlspecialchars($_SESSION["Username"] ?? "Alchemist") ?>!
            </span>

            <?php if ($_SESSION["UserType"] !== "Admin") : ?>
                <a href="ShopCartContents.php?lang=<?= $language ?>" class="cart-link">
                    <img width="30px" src="Pictures/cart.png" style="vertical-align: middle;">
                    <span class="cart-badge"><?= getCartCount() ?></span>
                </a>
            <?php endif; ?>

            <a href="Logout.php?lang=<?= $language ?>" class="logout-btn">
                <?= $arrayOfTranslations["LogoutBtn"] ?? "Logout" ?>
            </a>
        <?php endif; ?>

        <form method="get" style="display:inline-block; margin-left:20px;">
            <select name="lang" onchange="this.form.submit()">
                <option value="EN" <?= ($language == "EN") ? "selected" : "" ?>>English</option>
                <option value="PT" <?= ($language == "PT") ? "selected" : "" ?>>Português</option>
            </select>
        </form>

    </div>
<?php
}

//Function to check if username is already registered
function userAlreadyRegistered($checkedUser)
{
    global $connection;
    $stmt = $connection->prepare("SELECT Username FROM Users WHERE Username = ?");
    $stmt->bind_param("s", $checkedUser);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
}

//Function to include CSS
function includeCSS($pageCSS = "")
{
    echo '<link rel="stylesheet" type="text/css" href="global.css?v=' . time() . '">' . PHP_EOL;
    if ($pageCSS != "") {
        echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($pageCSS) . '?v=' . time() . '">' . PHP_EOL;
    }
    echo '<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">' . PHP_EOL;
}

//Function to count total items in cart
function getCartCount()
{
    $count = 0;
    if (isset($_SESSION["Cart"]) && !empty($_SESSION["Cart"])) {
        foreach ($_SESSION["Cart"] as $qty) {
            $count += $qty;
        }
    }
    return $count;
}
?>