<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$language = $_GET['language'] ?? "en";


if (!isset($_SESSION["is_admin"])){
    $_SESSION["is_admin"] = false;
}
if (!isset($_SESSION["user_is_admin"])) {

    $_SESSION["user_is_admin"] = false;
}


$arrayOfTranslations = [];

function getDB()
{
    static $db = null;
    if ($db === null) {
        $db = new mysqli("localhost", "root", "", "OrangeShopDB");
        if ($db->connect_error) {
            die("Database connection failed: " . $db->connect_error);
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}


$mysqli = getDB();
$stmt = $mysqli->prepare("SELECT translationKey, en, fr, de FROM Translations");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $key = $row['translationKey'];
        $arrayOfTranslations[$key]['en'] = $row['en'];
        $arrayOfTranslations[$key]['fr'] = $row['fr'];
        $arrayOfTranslations[$key]['de'] = $row['de'];
    }
    $stmt->close();
}


    // Provide safe fallbacks for a few keys that may be referenced in templates.
    $defaults = [
        'LoginToAdd' => ['en' => 'Please log in to add items to your cart.', 'fr' => 'Veuillez vous connecter pour ajouter des articles à votre panier.', 'de' => 'Bitte melden Sie sich an, um Artikel in den Warenkorb zu legen.'],
    ];
    foreach ($defaults as $k => $langs) {
        foreach ($langs as $langKey => $text) {
            if (!isset($arrayOfTranslations[$k][$langKey])) {
                $arrayOfTranslations[$k][$langKey] = $text;
            }
        }
    }


/**
 * Translate a key to the current language with safe fallback.
 * Returns an HTML-escaped string safe for insertion into templates.
 */
function tr($key)
{
    global $arrayOfTranslations, $language;
    $lang = $language ?? 'en';
    if (!in_array($lang, ['en', 'fr', 'de'], true)) {
        $lang = 'en';
    }
    $val = $arrayOfTranslations[$key][$lang] ?? $arrayOfTranslations[$key]['en'] ?? $key;
    return htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}



function NavigationBar($page)
{
    global $language;
    global $arrayOfTranslations;

    
    $navigationsBarLinks = [
        "Home"         => "index.php",
        "Products"     => "products.php",
        "Forum"        => "forum.php",
        "Contact"      => "contact.php",
        "Login"        => "login.php",
        "Registration" => "registration.php",
    ];

    if (isset($_SESSION["logged_in_user"])) {
        unset($navigationsBarLinks["Login"]);
        unset($navigationsBarLinks["Registration"]);
        $navigationsBarLinks["Logout"] = "logout.php";
    }

   
    if (isset($_SESSION["logged_in_user"]) && empty($_SESSION['is_admin'])) {
       
        $navigationsBarLinks["Cart"] = "cart.php";
    }
   
    $cartCount = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $citem) {
            $cartCount += intval($citem['quantity'] ?? 0);
        }
    }
    ?>
    <div class="navBar">
        <?php foreach ($navigationsBarLinks as $key => $file): ?>
            <?php
                $label = $arrayOfTranslations[$key][$language] ?? $key;
                if ($key === 'Cart') {
                
                    $label .= ' (' . intval($cartCount) . ')';
                }
            ?>
            <a 
                href="<?= $file ?>?language=<?= $language ?>"
                <?= ($page == $key) ? 'class="active"' : '' ?>
            >
                <?= $label ?>
            </a>
        <?php endforeach; 
        
        if ($_SESSION["is_admin"]){
            ?>

                <a href="Admin.php">Admin</a>

            <?php
        }
        
        ?>
        

    
        <form method="GET" style="display:inline-block; margin-left:auto;">
            <select name="language" onchange="this.form.submit()">
                <option value="en" <?= ($language == 'en') ? 'selected' : '' ?>>English</option>
                <option value="fr" <?= ($language == 'fr') ? 'selected' : '' ?>>French</option>
                <option value="de" <?= ($language == 'de') ? 'selected' : '' ?>>German</option>
            </select>
        </form>
    </div>
    <?php
}



function userAreg($checkUser)
{
    
    $db = getDB();
    $stmt = $db->prepare("SELECT username FROM Clients WHERE username = ? LIMIT 1");
    if (!$stmt) return true; 
    $stmt->bind_param('s', $checkUser);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res->num_rows > 0;
    $stmt->close();
    return !$exists; 
}



function handleAddToCartPost()
{
    
    if (!empty($_SESSION['user_is_admin'])) {
        return;
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
        if (!isset($_SESSION['logged_in_user'])) {
          
            $loginUrl = 'login.php?language=' . urlencode($GLOBALS['language'] ?? 'en') . '&return=products.php';
            header('Location: ' . $loginUrl);
            exit;
        }
        $productId = intval($_POST['productId'] ?? 0);
        $qty = max(1, intval($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            return;
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT productId, productName, price, productPicture FROM Products WHERE productId = ? LIMIT 1");
        if (!$stmt) {
            return; 
        }
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();
        $stmt->close();

        if (!$product) {
            return; 
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

     
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if (isset($item['productId']) && intval($item['productId']) === intval($product['productId'])) {
                $item['quantity'] = ($item['quantity'] ?? 0) + $qty;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $_SESSION['cart'][] = [
                'productId' => intval($product['productId']),
                'productName' => $product['productName'],
                'price' => floatval($product['price']),
                'quantity' => $qty,
                'productPicture' => $product['productPicture']
            ];
        }

       
        $redirect = 'products.php';
        header("Location: $redirect");
        exit;
    }
}


handleAddToCartPost();

?>
