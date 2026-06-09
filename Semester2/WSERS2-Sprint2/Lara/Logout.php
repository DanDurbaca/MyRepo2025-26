<?php
session_start();

//Remove session variables
session_unset();

//Destroy session
session_destroy();

//Remove session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

//Redirect to home (keep language)
$lang = isset($_GET["lang"]) ? $_GET["lang"] : "EN";
header("Location: Home.php?lang=" . $lang);
exit;
