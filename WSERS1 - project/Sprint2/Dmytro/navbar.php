<?php
include_once "ccode.php";
$lang = "EN";
if (isset($_GET["lang"])) {
    $lang = $_GET["lang"];
}

$tFile = fopen("translation.csv", "r");
$tArray = [];

function defLang($l)
{
    switch ($l) {
        case "EN":
            return 1;
        case "UA":
            return 2;
        default:
            return 1;
    }
}

while (!feof($tFile)) {
    $line = fgets($tFile);
    if ($line === false)
        break;
    $columns = explode(";", $line);
    $tArray[$columns[0]] = $columns[defLang($lang)];
}

fclose($tFile);

function navbar($page)
{
    global $lang, $tArray;
    $userLogged = $_SESSION['userLogged'];
    $canAddProducts = !empty($_SESSION['canAddProducts']);

    $navbarTable = [
        $tArray["HomeBtn"] => "welcome.php",
        $tArray["ProductBtn"] => "products.php",
        $tArray["ContactBtn"] => "contact.php",
        $tArray["RegisterBtn"] => "register.php",
        $tArray["LoginBtn"] => "login.php",
        $tArray["addPBtn"] => "addProduct.php"
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

                    $isActive = ($page === $label) ? ' class="highlight"' : '';
                    ?>

                    <li<?php echo $isActive; ?>>
                        <a class="nav-link" href="<?php echo htmlspecialchars($href . '?lang=' . $lang); ?>">
                            <?php echo htmlspecialchars($label); ?>
                        </a>
                        </li>
                    <?php } ?>
            </ul>

            <form id="f" method="get">
                <select name="lang" onchange="this.form.submit()">
                    <option value="EN" <?php if ($lang === "EN") {
                        print "selected";
                    } ?>>English</option>
                    <option value="UA" <?php if ($lang === "UA") {
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
<script src="script.js?<?= time(); ?>"></script>