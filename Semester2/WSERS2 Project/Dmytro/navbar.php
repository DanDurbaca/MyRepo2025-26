<?php
include_once "ccode.php";
$lang = $_GET["lang"] ?? "en";

$prepTranslations = $myMentorshipShopDB->prepare("select contentID, content from translations where languageCode = ?;");
$prepTranslations->bind_param("s", $lang);
$prepTranslations->execute();

$translations = $prepTranslations->get_result();
$tArray = [];

while ($row = $translations->fetch_assoc()) {
    $tArray[$row['contentID']] = $row['content'];
}

function navbar($page)
{
    global $lang, $tArray, $imgTegArr;
    $userLogged = $_SESSION['userLogged'];
    $canAddProducts = !empty($_SESSION['canAddProducts']);

    $navbarTable = [
        $tArray["HomeBtn"] => "welcome.php",
        $tArray["ProductBtn"] => "products.php",
        $tArray["ContactBtn"] => "contact.php",
        $tArray["RegisterBtn"] => "register.php",
        $tArray["LoginBtn"] => "login.php",
        $tArray["addPBtn"] => "addProduct.php",
        $tArray["forumBtn"] => "forum.php",
        $imgTegArr[0] => "productCart.php"
    ];
    ?>
    <nav>
        <div class="nav-inner">
            <div class="logo">
                <a href="pics/logo.png">
                    <span class="logo-orb"></span>
                    Mentorship Shop
                </a>
            </div>

            <ul>
                <?php foreach ($navbarTable as $label => $href) {
                    if (!$userLogged && $label === $tArray["addPBtn"]) {
                        continue;
                    }
                    if ($userLogged && ($label === $tArray["RegisterBtn"] || $label === $tArray["LoginBtn"])) {
                        continue;
                    }
                    if ($label === $tArray["addPBtn"] && !$canAddProducts) {
                        continue;
                    }
                    if ((!$userLogged || $canAddProducts) && $label === $imgTegArr[0]) {
                        continue;
                    }
                    if (!$userLogged && $label === $tArray["forumBtn"]) {
                        continue;
                    }

                    $isActive = ($page === $label) ? ' class="highlight"' : '';
                    ?>

                    <li<?php echo $isActive; ?>>
                        <a class="nav-link" href="<?php echo htmlspecialchars($href . '?lang=' . $lang); ?>">
                            <?php echo $label; ?>
                        </a>
                        </li>
                    <?php } ?>
            </ul>

            <form id="f" method="get">
                <select name="lang" onchange="this.form.submit()">
                    <option value="en" <?php if ($lang === "en") {
                        print "selected";
                    } ?>>English</option>
                    <option value="uk" <?php if ($lang === "uk") {
                        print "selected";
                    } ?>>Ukrainian</option>
                </select>
            </form>
            <?php if ($_SESSION["userLogged"]) { ?>
                <a id="logout" href="logout.php?lang=<?= $lang ?>">
                    <?= $tArray["LogoutBtn"] ?>
                </a>
            <?php } ?>
        </div>
    </nav>
<?php } ?>