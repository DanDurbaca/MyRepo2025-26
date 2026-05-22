<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Collections</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Collection");
    ?>

    <?php
    // Require a logged-in user before showing collection options
    requireLogin();

        // User is logged in - show the collections menu
        ?>

    <h1><?php print $arrayOfStrings["CollectionsTitle"] ?></h1>

    <!-- Create new collection -->
    <h2><?php print $arrayOfStrings["Create"] ?></h2>
    <p><a href="collections_create.php"><?php print $arrayOfStrings["CreateNewCollection"] ?></a></p>

    <!-- View your collections -->
    <h2><?php print $arrayOfStrings["MyCollectionsTitle"] ?></h2>
    <p><a href="MyCollections.php"><?php print $arrayOfStrings["ViewMyCollections"] ?></a></p>

    <!-- View shared collections -->
    <h2><?php print $arrayOfStrings["SharedCollectionsTitle"] ?></h2>
    <p><a href="SharedCollections.php"><?php print $arrayOfStrings["ViewSharedCollections"] ?></a></p>

    </div>
</body>

</html>