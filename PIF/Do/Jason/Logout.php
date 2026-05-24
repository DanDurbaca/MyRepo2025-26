<?php
// handle logout action and show page
include_once("commonphp.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // clear session data
    $_SESSION = [];

    // remove session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // destroy session and redirect to login
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Logout</title>
</head>
<body>
    <h1 class="Title">Logout</h1>
    <p class="Title">Here you can log out.</p>
    <div class="Title">
        <form method="post" >
            <button id="logoutBtn" type="submit">Logout</button>
        </form>
    </div>
</body>
</html>