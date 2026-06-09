<?php
session_start();
$connection = new mysqli("localhost", "root", "", "Webshopdb");

//loging out
if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    session_start();
}

//starting cart session
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

//adding to cart
if (isset($_POST["quantity"], $_POST["productid"])) {
    $item = $_POST["productid"];
    if (isset($_SESSION["cart"][$item])) {
        //already have this item in cart
        $_SESSION["cart"][$item] = (int)$_SESSION["cart"][$item] + (int)$_POST["quantity"];
    } else {
        $_SESSION["cart"][$item] = (int)$_POST["quantity"];
    }
}



//
if (!isset($_SESSION["UserLogged"]) && !isset($_SESSION["ADMIN"])) {
    $_SESSION["UserLogged"] = false;
    $_SESSION["ADMIN"] = 0;
}

//setting language
$language = 'EN';
if (isset($_GET['lang'])) {
    $language = $_GET['lang'];
}
$sqlQuery = $connection->prepare("SELECT * FROM translations");
$arrayOfTranslations = [];
$sqlQuery->execute();
$result = $sqlQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $arrayOfTranslations[$row["translation_key"]] = ($language == "EN") ? $row["EnglishText"] : $row["PortugueseText"];
}

//navigation Bar
function NavigationBar($currentFile)
{
    global $language;
    global $arrayOfTranslations;
    global $count;
    foreach ($_SESSION["cart"] as $itemIdc => $itemQuantityc) {
        $count += $itemQuantityc;
    }
?>
    <div class="navBar">
        <?php
        ?>
        <a class="navLink <?= strtolower($currentFile) == "home" ? 'active' : ''; ?>" href="<?= "Home.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["HomeBtn"] ?></a>
        <a class="navLink <?= strtolower($currentFile) == "contact" ? 'active' : ''; ?>" href="<?= "Contact.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["ContactBtn"] ?></a>
        <a class="navLink <?= strtolower($currentFile) == "products" ? 'active' : ''; ?>" href="<?= "Products.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["ProductBtn"] ?></a>
        <?php
        if (!$_SESSION["UserLogged"]) {
        ?>
            <a class="navLink <?= strtolower($currentFile) == "register" ? 'active' : ''; ?>" href="<?= "Register.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["RegisterBtn"] ?></a>
            <a class="navLink <?= strtolower($currentFile)  == "login" ? 'active' : ''; ?>" href="<?= "login.php" ?>?lang=<?= $language ?>"><?= $arrayOfTranslations["LogInBtn"] ?></a>
        <?php
        } else {
        ?>
            <a class="cartIcon <?= (strtolower($currentFile . ".php") == strtolower("Cart.php")) ? 'active' : ''; ?>" href="Cart.php?lang=<?= $language ?>" title="Shopping Cart">🛒(<?= $count ?>)</a>
            <form method="POST" action="Home.php?lang=<?= $language ?>">
                <input type="submit" value="Logout" name="logout" class="logoutBtn">
            </form>
            <a class="navLink" <?= (strtolower($currentFile . ".php") == "forum.php") ? 'active' : ''; ?> href="<?= "forum.php" ?>?lang=<?= $language ?>">Forum</a>
            <?php
            if ($_SESSION["ADMIN"] == 1) {
            ?>
                <a class="navLink <?= (strtolower($currentFile . ".php") == "admin.php") ? 'active' : ''; ?>" href="<?= "admin.php" ?>?lang=<?= $language ?>">Admin Panel</a>
        <?php
            }
            print("welcome " . $_SESSION["LoggedUserName"]);
        }
        ?>
        <div class="navBarRight">
            <form>
                <select name=lang onchange="this.form.submit()">
                    <option value="EN" <?php if ($language == "EN") print "selected"; ?>>English</option>
                    <option value="PT" <?php if ($language == "PT") print "selected"; ?>>Portuguese</option>
                </select>
            </form>
        </div>
    </div>
<?php
}

//cheking is user is already registred
function userAlredyResgistred($checkedUser)
{
    /* this function checks if $chekedUser string is an existing user in client.csv
    if the given user is alredy in the file we return true -> user alredy exists
    if not we return false*/
    $connection = new mysqli("localhost", "root", "", "webshopdb");
    $bReturnValue = false;
    $sqlQuery = $connection->prepare("SELECT * FROM clients;");
    $sqlQuery->execute();
    $result = $sqlQuery->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row["Username"] == $checkedUser) {
            $bReturnValue = true;
        }
    }
    return $bReturnValue;
}

//verefying password and username before login
function verifyUserCredentials($checkedUser, $checkedPsw)
{
    global $admin;
    $connection = new mysqli("localhost", "root", "", "Webshopdb");
    $sqlQuery = $connection->prepare("SELECT * FROM clients");
    $sqlQuery->execute();
    $result = $sqlQuery->get_result();
    while ($row = $result->fetch_assoc()) {
        $fileUser = isset($row["Username"]) ? trim($row["Username"]) : '';
        $filePsw = isset($row["usrpassword"]) ? trim($row["usrpassword"]) : '';
        $admin = isset($row["isadmin"]) ? trim($row["isadmin"]) : 'false';
        if ($fileUser === $checkedUser && password_verify($checkedPsw, $filePsw)) {
            return true;
        }
    }
    return false;
}
?>