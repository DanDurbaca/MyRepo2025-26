<?php
session_start();
include_once("function.php");


session_unset();


session_destroy();


$language = $_GET["language"] ?? "en";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>

<?php NavigationBar("Logout"); ?>

<h1 style="text-align:center; margin-top:40px;">
    You have been logged out successfully.
</h1>

<p style="text-align:center; margin-top:20px; font-size:1.2rem;">
    <a href="login.php?language=<?= $language ?>">Return to Login</a>
</p>

</body>
</html>
