<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - View Collection</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Collection");

    // Require login before viewing collections
    requireLogin();

    // Current logged-in user
    $me = getCurrentUser();

    // Validate collection ID from query string
    $collectionId = (int)($_GET['id'] ?? 0);
    if ($collectionId <= 0) {
        echo "<h1>" . $arrayOfStrings["InvalidCollectionID"] . "</h1>";
        echo "<p><a href=\"MyCollections.php\">" . $arrayOfStrings["MyCollectionsTitle"] . "</a> | <a href=\"SharedCollections.php\">" . $arrayOfStrings["SharedCollectionsTitle"] . "</a></p>";
        exit;
    }

    // Check if user owns the collection
    // Prepare query to verify ownership by collection id and username
    $checkOwner = $connection->prepare("SELECT pk_collection, name, description FROM collection WHERE pk_collection = ? AND fk_user_creates = ?");
    $checkOwner->bind_param("is", $collectionId, $me);
    $checkOwner->execute();
    $ownerResult = $checkOwner->get_result();

    if ($ownerResult->num_rows > 0) {
    //     // User owns this collection
        $collection = $ownerResult->fetch_assoc();
    } else {
        // Check if collection is shared with the user
        $checkShared = $connection->prepare("SELECT c.pk_collection, c.name, c.description FROM collection c INNER JOIN hasaccess h ON c.pk_collection = h.pkfk_collection WHERE c.pk_collection = ? AND h.pkfk_user = ?");
        $checkShared->bind_param("is", $collectionId, $me);
        $checkShared->execute();
        $sharedResult = $checkShared->get_result();

        if ($sharedResult->num_rows > 0) {
            // Collection is shared with user
            $collection = $sharedResult->fetch_assoc();
        } else {
            // Access denied for non-owners without sharing
            echo "<h1>" . $arrayOfStrings["AccessDenied"] . "</h1>";
            echo "<p>" . $arrayOfStrings["NoPermissionViewCollection"] . "</p>";
            echo "<p><a href=\"MyCollections.php\">" . $arrayOfStrings["MyCollectionsTitle"] . "</a> | <a href=\"SharedCollections.php\">" . $arrayOfStrings["SharedCollectionsTitle"] . "</a></p>";
        }
    }

    // Get measurements in this collection
    // Prepare query to list all measurements belonging to the collection
    $getMeasurements = $connection->prepare("SELECT m.pk_measurement, m.temperature, m.humidity, m.pressure, m.light, m.gas, m.timestamp, m.fk_station_records FROM measurement m INNER JOIN contains c ON m.pk_measurement = c.pkfk_measurement WHERE c.pkfk_collection = ? ORDER BY m.timestamp DESC");
    $getMeasurements->bind_param("i", $collectionId);
    $getMeasurements->execute();
    $measurements = $getMeasurements->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>

    <h1><?php echo htmlspecialchars($collection['name']); ?></h1>

    <p><?php echo htmlspecialchars($collection['description'] ?? ''); ?></p>

    <p><a href="MyCollections.php"><?php print $arrayOfStrings["MyCollectionsTitle"] ?></a> | <a href="SharedCollections.php"><?php print $arrayOfStrings["SharedCollectionsTitle"] ?></a></p>

    <h2><?php print $arrayOfStrings["Measurements"] ?></h2>

     <?php renderMeasurementsTable($measurements); ?>
    </div>
</body>

</html>
