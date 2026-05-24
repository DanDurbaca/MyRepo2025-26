<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Homepage</title>
</head>
<body>
    <?php
     include_once("commonphp.php");
     $username = htmlspecialchars($_SESSION['User'] ?? $_SESSION['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8');
     $isAdmin = !empty($_SESSION['is_admin']);
    ?>
    <div class="container">
        <h1 class="Title">Welcome, <?= $username ?></h1>
        <p class="lead">This is your dashboard. Use the navigation to manage stations, measurements, collections, friends, and account settings.</p>
        <?php if ($isAdmin): ?>
            <p class="lead">Administrator mode is active. Use the <a href="Admin.php">Admin page</a> to manage users, stations, measurements, and collections.</p>
        <?php endif; ?>

    </div>
</body>
</html>