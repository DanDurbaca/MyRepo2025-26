<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Create Collection</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head> 

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Collection");

    // Require login before creating collections
    requireLogin();

    // Current logged-in user
    $me = getCurrentUser();
    $error = '';
    $success = '';

    // Load user's stations for the dropdown
    $myStations = getUserStations($me);

    
    // Handle form submission to create a collection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stationSerial = $_POST['station'] ?? '';
        $collectionName = $_POST['collection_name'] ?? '';
        $collectionDesc = $_POST['collection_desc'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        // Validate dates and ownership
        if (strtotime($endDate) <= strtotime($startDate)) {
            $error = "EndDateAfterStart";
        } elseif (!isStationOwnedByUser($stationSerial, $me)) {
            $error = "NotYourStation";
        } else {
            // Create collection owned by current user
            $collectionId = createCollection($collectionName, $collectionDesc, $me);
            
            if ($collectionId > 0) {
                // Get measurement IDs in the selected date range
                $measurementIds = getMeasurementIdsForStationInRange($stationSerial, $startDate, $endDate);
                
                // Add measurements to the new collection
                $count = addMeasurementsToCollection($collectionId, $measurementIds);
                
                $success = ($arrayOfStrings["CollectionCreatedPrefix"] ?? "Collection created with ") . $count . ($arrayOfStrings["CollectionCreatedSuffix"] ?? " measurements.");
            } else {
                $error = "CreationFailed";
            }
        }
    }
    ?>

    <h1><?php print $arrayOfStrings["CreateCollection"] ?></h1>

    <?php if ($error): ?>
        <p><?php echo htmlspecialchars($arrayOfStrings[$error] ?? $error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p><?php echo htmlspecialchars($success); ?></p>
        <p>
            <a href="MyCollections.php"><?php print $arrayOfStrings["ViewMyCollections"] ?></a> | 
            <a href="Collection.php"><?php print $arrayOfStrings["CollectionsTitle"] ?></a>
        </p>
    <?php else: ?>
        <?php if (empty($myStations)): ?>
            <p><?php print $arrayOfStrings["NeedAtLeastOneStation"] ?></p>
            <p><a href="MyStation.php"><?php print $arrayOfStrings["GoToStations"] ?></a></p>
        <?php else: ?>
            <form method="POST">
                <p>
                    <label for="station"><?php print $arrayOfStrings["StationLabel"] ?></label><br />
                    <select id="station" name="station" required>
                        <option value=""><?php print $arrayOfStrings["SelectStationOption"] ?></option>
                        <?php foreach ($myStations as $station): ?>
                            <option value="<?php echo htmlspecialchars($station['pk_serialNumber']); ?>">
                                <?php echo htmlspecialchars($station['pk_serialNumber'] . ' - ' . ($station['name'] ?? $arrayOfStrings['Unnamed'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label for="collection_name"><?php print $arrayOfStrings["CollectionNameLabel"] ?></label><br />
                    <input type="text" id="collection_name" name="collection_name" required />
                </p>

                <p>
                    <label for="collection_desc"><?php print $arrayOfStrings["CollectionDescLabel"] ?></label><br />
                    <input type="text" id="collection_desc" name="collection_desc" />
                </p>

                <p>
                    <label for="start_date"><?php print $arrayOfStrings["StartDateTime"] ?></label><br />
                    <input type="datetime-local" id="start_date" name="start_date" required />
                </p>

                <p>
                    <label for="end_date"><?php print $arrayOfStrings["EndDateTime"] ?></label><br />
                    <input type="datetime-local" id="end_date" name="end_date" required />
                </p>

                <p>
                    <button type="submit"><?php print $arrayOfStrings["Create"] ?></button>
                    <a href="Collection.php"><?php print $arrayOfStrings["Cancel"] ?></a>
                </p>
            </form>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</body>

</html>
