<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Admin</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("admin");
    ?>
    <?php
    // Restrict page access to admins only
    requireAdmin();

        ?>

        <h1><?php print $arrayOfStrings["Management"] ?></h1> 

        <!-- View your stations -->
        <h2><?php print $arrayOfStrings["Stations"] ?></h2>
        <p><a href="StationAdmin.php"><?php print $arrayOfStrings["AddStations"] ?></a></p>
        <p><a href="ManageStationAdmin.php"><?php print $arrayOfStrings["ManageStations"] ?></a></p>
        <!-- View your users -->
        <h2><?php print $arrayOfStrings["Users"] ?></h2>
        <p><a href="UserManagement.php"><?php print $arrayOfStrings["ManageUsers"] ?></a></p>

        <h2><?php print $arrayOfStrings["Collections"] ?></h2>
        <p><a href="AdminCollections.php"><?php print $arrayOfStrings["ManageCollections"] ?></a></p>  

        <h2><?php print $arrayOfStrings["CreateCollection"] ?></h2>
        <p><a href="AdminCollectionsAddMeasurements.php"><?php print $arrayOfStrings["AddMeasurementsToCollection"] ?></a></p>

</html>
</body>