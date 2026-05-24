<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Menu</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Menu");
    ?>
    <?php
    // Require login before showing the menu
    requireLogin();
    ?>
    <h1><?php print $arrayOfStrings["Welcome"] ?></h1>
    <ul>
        <li><h2><?php print $arrayOfStrings["Explenation1"] ?></h2></li>
        <li><h2><?php print $arrayOfStrings["Explenation2"] ?></h2></li>
    </ul>

    <?php
    // Load latest measurements for the logged-in user
    $me = getCurrentUser();
    $rows = getLatestMeasurementPerStation($me);
    ?>

    <section>
        <h2><?php print $arrayOfStrings["LatestMeasurements"] ?></h2>
        <?php
        if (empty($rows)) {
            echo '<p>' . $arrayOfStrings["NoMeasurementsFound"] . '</p>';
        } else {
            renderMeasurementsTable($rows);
        }
        ?>
    </section>
    
    </div>
</body>

</html>