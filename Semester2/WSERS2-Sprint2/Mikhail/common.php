<?php
session_start();
$language = "EN";
$connection = new mysqli("localhost", "root", "", "DvoMi866WebShop");

if (isset($_GET["lang"])) {
    $language = $_GET["lang"];
}
$arrayOfTranslations = [];
$sqlQuery= $connection -> prepare("select * from Translations");
$sqlQuery->execute();
$result=$sqlQuery->get_result();
while ($row=$result->fetch_assoc()) {
    if ($language == "EN") {
        $arrayOfTranslations[$row["TranslKey"]] = trim($row["EnglishText"]);
    } else {
        $arrayOfTranslations[$row["TranslKey"]] = trim($row["RussianText"]);
    }
}
if(!isset($_SESSION["Cart"])){
    $_SESSION["Cart"]=[];
}
if(isset($_POST["itemToBuy"], $_POST["quantityToBuy"])){
    if (isset($_SESSION["Cart"][$_POST["itemToBuy"]])){
        $_SESSION["Cart"][$_POST["itemToBuy"]]=$_SESSION["Cart"][$_POST["itemToBuy"]]+$_POST["quantityToBuy"];
    }
    else{
        $_SESSION["Cart"][$_POST["itemToBuy"]]=$_POST["quantityToBuy"];
    }
}
if(isset($_POST["itemToDelete"])){
    unset($_SESSION["Cart"][$_POST["itemToDelete"]]);
    unset($_POST["itemToDelete"]);
}
if(isset($_POST["messagetodelete"])){
    $sqlQuery=$connection->prepare("delete from messages where messageid=?");
    $sqlQuery->bind_param("i", $_POST["messagetodelete"]);
    $sqlQuery->execute();
    unset($_POST["messagetodelete"]);
}
if(isset($_POST["Checkout"])){
    $sqlQuery=$connection->prepare("insert into orders(statusEN, statusRU, username) values('Pending', 'В ожидании',?);");
    $sqlQuery->bind_param("s", $_SESSION["UserLogged"]);
    $sqlQuery->execute();
    $orderid=$connection->insert_id;
    foreach ($_SESSION["Cart"] as $itemID => $itemQuantity) {
        $sqlQuery = $connection->prepare("INSERT INTO boughtitem(quantity, orderid, productid) values(?,?,?);");
        $sqlQuery->bind_param("iii", $itemQuantity, $orderid, $itemID);
        $sqlQuery->execute();
        unset($_SESSION["Cart"]);
        unset($_POST["Checkout"]);
    }
    ?><script>alert("<?=$arrayOfTranslations["CartAlrt"]?>")</script><?php
    header("Refresh:0; url=home.php?lang=".$language);
}
if(isset($_POST["orderToSend"])){
    $sqlQuery=$connection->prepare("update orders set statusEN = 'Delivered', statusRU='Доставленно' where orderid=?;");
    $sqlQuery->bind_param("i", $_POST["orderToSend"]);
    $sqlQuery->execute();
}
if (isset($_POST["Logout"])) {
    session_unset();
    session_destroy();
    session_start();
    header("Refresh:0; url=home.php?lang=".$language);
}

//var_dump($_SESSION);

if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = "false";
    $_SESSION["IsAdmin"] = false;
}

function head($callingPage)
{
    global $language;
    global $arrayOfTranslations;
?>
    <header>
        <img src="images/logo.jpg" alt="Logo">
        <nav>
            <ul>
                <?php
                /* foreach ($navigationBarLinks as $keyVariable => $valueVariable) {
                ?>
                    <li><a href="<?= $valueVariable ?>?lang=<?= $language ?>" <?= ($callingPageEN == $keyVariable || $callingPageRU == $keyVariable) ? " class='active'" : ""; ?>><?= $keyVariable ?></a></li>
                <?php
                }*/
                ?>
                <li><a href="home.php?lang=<?= $language ?>" <?= ($callingPage == "Home") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["HomeBtn"] ?></a></li>
                <li><a href="products.php?lang=<?= $language ?>" <?= ($callingPage == "Products") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["ProductsBtn"] ?></a></li>
                <?php
                if ($_SESSION["IsAdmin"]) {
                ?>
                    <li><a href="admin.php?lang=<?= $language ?>" <?= ($callingPage == "Admin") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["AdminBtn"] ?></a></li>
                <?php
                }
                if ($_SESSION["UserLogged"]=="false") {
                ?>
                    <li><a href="register.php?lang=<?= $language ?>" <?= ($callingPage == "Register") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["RegisterBtn"] ?></a></li>
                    <li><a href="login.php?lang=<?= $language ?>" <?= ($callingPage == "Login") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["LoginBtn"] ?></a></li>
                <?php
                } else {
                    $itemsNum=0;
                    foreach($_SESSION["Cart"] as $itemId => $itemQuantity){$itemsNum+=$itemQuantity;}
                    if (!$_SESSION["IsAdmin"]) {
                ?>
                    <li><a href="shopcart.php?lang=<?= $language ?>" <?= ($callingPage == "Cart") ? " class='active'" : ""; ?>><?= $arrayOfTranslations["CartBtn"] ?> [<?= $itemsNum ?>]</a></li>
                    <?php
                    } 
                    ?>
                    <li><a href="forum.php?lang=<?= $language ?>" <?= ($callingPage == "Forum") ? " class='active'" : ""; ?>><?=$arrayOfTranslations["ForumBtn"]?></a></li>
                    <form method="POST">
                        <input type="submit" value="<?=$arrayOfTranslations["LogoutBtn"]?>" name="Logout">
                    </form>
                <?php
                }
                ?>
                <form>
                    <select name="lang" onchange="this.form.submit()">
                        <option value="EN" <?php if ($language == "EN") print "selected"; ?>><?= $arrayOfTranslations["Language1"] ?></option>
                        <option value="RU" <?php if ($language == "RU") print "selected"; ?>><?= $arrayOfTranslations["Language2"] ?></option>
                    </select>
                </form>
            </ul>
        </nav>
    </header>
<?php
}

function foot()
{
    global $arrayOfTranslations;
?>
    <footer>
        <p><?= $arrayOfTranslations["Footer"] ?></p>
    </footer>
<?php
}

function userAlreadyRegistered($checkedUser)
{
    global $connection;
    /* $bReturnValue = false;
	$sqlQuery= $connection -> prepare("select * from Clients");
	$sqlQuery->execute();
	$result=$sqlQuery->get_result();
    while ($row=$result->fetch_assoc()) {
        if ($row["Username"] == $checkedUser)
            $bReturnValue = true;
    } 
    return $bReturnValue;*/
    $sqlQuery= $connection -> prepare("select * from Clients where Username=?");
    $sqlQuery->bind_param("s", $checkedUser);
    $sqlQuery->execute();
    $result=$sqlQuery->get_result();
    return ($result->num_rows==0) ? false : true;
}
?>