<?php
session_start();

if (!isset($_SESSION["UserLogged"])) {
    $_SESSION["UserLogged"] = false;
}
if (!isset($_SESSION["IsAdmin"])) {
    $_SESSION["IsAdmin"] = false;
}

$language = isset($_GET["lang"]) ? $_GET["lang"] : "EN";

define('BASE_DIR', '/HTSTAproject/HTSTA_FinalProject_2024_FerSi630');

function asset($path) {
    return BASE_DIR . '/' . ltrim($path, '/');
}

$arrayOfTranslations = [];
if (($fileTranslations = fopen("Translation.csv", "r")) !== false) {
    fgetcsv($fileTranslations, 0, ";");
    while (($pieces = fgetcsv($fileTranslations, 0, ";")) !== false) {
        if (count($pieces) >= 3) {
            $key = trim($pieces[0]);
            $arrayOfTranslations[$key] =
                ($language == "EN") ? trim($pieces[1]) : trim($pieces[2]);
        }
    }
    fclose($fileTranslations);
}

$pages = [
    "Home"      => ["label" => $arrayOfTranslations["HomeBtn"], "url" => "index.php"],
    "Country1"  => ["label" => $arrayOfTranslations["Country1Btn"], "url" => "country1.php"],
    "Country2"  => ["label" => $arrayOfTranslations["Country2Btn"], "url" => "country2.php"],
    "Register"  => ["label" => $arrayOfTranslations["RegisterBtn"], "url" => "register.php"],
    "Login"     => ["label" => $arrayOfTranslations["LoginBtn"], "url" => "login.php"]
];

if (!empty($_SESSION["IsAdmin"]) && $_SESSION["IsAdmin"] === true) {
    $pages["Admin"] = ["label" => $arrayOfTranslations["AdminBtn"], "url" => "admin.php"];
}

function navBar($currentPage)
{
    global $language, $pages, $arrayOfTranslations;

    $isLogged = $_SESSION["UserLogged"] ?? false;
    $username = $_SESSION["Username"] ?? "";
?>
    <header>
        <img id="logo" src="<?= asset('images/lpem.png') ?>" alt="banner">
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
    if (($fHandler = fopen("Clients.csv", "r")) !== false) {
        while (($items = fgetcsv($fHandler, 0, ";")) !== false) {
            if (trim($items[0]) === trim($checkedUser)) {
                fclose($fHandler);
                return true;
            }
        }
        fclose($fHandler);
    }
    return false;
}
?>
