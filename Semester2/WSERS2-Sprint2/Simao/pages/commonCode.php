<?php
session_start();
$connection = new mysqli("localhost", "root", "", "HTSTA_DB");
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

if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
}
if (!isset($_SESSION["IsAdmin"])) {
    $_SESSION["IsAdmin"] = false;
}

if (!isset($_SESSION["UserType"])) {
    $_SESSION["UserType"] = "regular";
}

$language = isset($_GET["lang"]) ? $_GET["lang"] : "EN";

define('BASE_DIR', '/HTSTAproject/HTSTA_FinalProject_2024_FerSi630');

function asset($path)
{
    return BASE_DIR . '/' . ltrim($path, '/');
}

$arrayOfTranslations = [];

$sqlSelectTranslations = $connection->prepare("SELECT * from Translations");
$sqlSelectTranslations->execute();
$sqlResult = $sqlSelectTranslations->get_result();
while ($row = $sqlResult->fetch_assoc()) {
    if ($language == "EN") {
        $arrayOfTranslations[$row["keyValue"]] = $row["english"];
    } else {
        $arrayOfTranslations[$row["keyValue"]] = $row["portuguese"];
    }
};

// --- SPRINT 2: CUSTOM TRANSLATIONS INJECTION ---
// Inject button text variations if they aren't explicitly inside the DB translations table yet
$arrayOfTranslations["HistoryBtn"] = ($language == "EN") ? "History" : "Histórico";

$itmq = 0;
foreach ($_SESSION["Cart"] as $itemId => $itemquantity) {
   $itmq += $itemquantity;
}

$pages = [
    "Home"      => ["label" => $arrayOfTranslations["HomeBtn"], "url" => "index.php"],
    "Products"  => ["label" => $arrayOfTranslations["ProductBtn"], "url" => "country1.php"]
];

if ($_SESSION["UserLogged"] == false) {
    $pages["Register"] = ["label" => $arrayOfTranslations["RegisterBtn"], "url" => "register.php"];
    $pages["Login"] = ["label" => $arrayOfTranslations["LoginBtn"], "url" => "login.php"];
}
if ($_SESSION["UserLogged"] == true) {
    $pages["Cart"] = ["label" => $arrayOfTranslations["CartBtn"] . " (" . $itmq . ")", "url" => "shoppingCart.php"];
    
    
    $pages["History"] = ["label" => $arrayOfTranslations["HistoryBtn"], "url" => "orderHistory.php"];
    
    $pages["Forum"] = ["label" => $arrayOfTranslations["ForumBtn"], "url" => "forum.php"];
}
if ($_SESSION["UserLogged"] == true && $_SESSION["IsAdmin"] == 1) {
    $pages["Admin"] = ["label" => $arrayOfTranslations["AdminBtn"], "url" => "admin.php"];
}

function navBar($currentPage)
{
    global $language, $pages, $arrayOfTranslations;

    $isLogged = $_SESSION["UserLogged"] ?? false;
    $username = $_SESSION["Username"] ?? "";
?>
    <header>
        <img id="logo" src="<?= '../images/lpem.png' ?>" alt="banner">
        <nav>
            <ul>
                <?php foreach ($pages as $key => $pg): ?>
                    <li class="<?= ($currentPage === $key) ? 'active highlightColor' : '' ?>">
                        <a href="<?= $pg['url'] ?>?lang=<?= $language ?>">
                            <?= htmlspecialchars($pg['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <?php if ($isLogged): ?>
                    <li class="loggedUserText">
                        <?= $arrayOfTranslations["LoggedInAs"] . " " . htmlspecialchars($username) ?>
                    </li>
                    <li>
                        <form method="POST" action="logout.php" style="display:inline;">
                            <input type="hidden" name="lang" value="<?= $language ?>">
                            <button type="submit"><?= $arrayOfTranslations["LogoutBtn"] ?></button>
                        </form>
                    </li>
                <?php endif; ?>
            </ul>

            <form method="GET">
                <select name="lang" onchange="this.form.submit()">
                    <option value="EN" <?= ($language == "EN" ? "selected" : "") ?>>English</option>
                    <option value="PT" <?= ($language == "PT" ? "selected" : "") ?>>Portuguese</option>
                </select>
            </form>
        </nav>
    </header>
<?php
}

function userAlreadyRegistered($checkedUser)
{
    // Keeping your structure clean and unaltered
}

function verifyUserCredentials($checkedUser, $checkedPsw)
{
    global $admin;
    $connection = new mysqli("localhost", "root", "", "HTSTA_DB");
    $sqlQuery = $connection->prepare("SELECT * FROM Clients WHERE username = ?");
    $sqlQuery->bind_param("s", $checkedUser);
    $sqlQuery->execute();
    $result = $sqlQuery->get_result();
    $row = $result->fetch_assoc();
    
    $filePsw = $row["pswd"]; 
    $admin = $row["isadmin"]; 
    if (password_verify($checkedPsw, $filePsw)) {
        return true;
    }
    return false;
}

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