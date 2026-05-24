<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Admin Create Collection</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("admin");

    // Restrict page access to admins only
    requireAdmin();
    $me = getCurrentUser();
    $error = '';
    $success = '';

    // Load all stations for the dropdown
    $allStations = getAllStations();

    // Handle form submission for creating a collection and adding measurements
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stationSerial = $_POST['station'] ?? '';
        $collectionName = $_POST['collection_name'] ?? '';
        $collectionDesc = $_POST['collection_desc'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        // Validate date range
        if (strtotime($endDate) <= strtotime($startDate)) {
            $error = $arrayOfStrings["EndDateAfterStartError"];
        } else {
            // Create the collection owned by the current admin
            $collectionId = createCollection($collectionName, $collectionDesc, $me);
            
            if ($collectionId > 0) {
                // Get measurement IDs within the selected date range
                $measurementIds = getMeasurementIdsForStationInRange($stationSerial, $startDate, $endDate);
                
                // Add measurements into the new collection
                $count = addMeasurementsToCollection($collectionId, $measurementIds);
                
                $success = $arrayOfStrings["CollectionCreatedWith"] . $count . $arrayOfStrings["MeasurementsSuffix"];
            } else {
                $error = "Collection creation failed.";
            }
        }
    }
    ?>

    <h1><?php print $arrayOfStrings["AdminCreateCollection"] ?></h1>

    <?php if ($error): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p><?php echo htmlspecialchars($success); ?></p>
        <p>
            <a href="AdminCollectionsAddMeasurements.php"><?php print $arrayOfStrings["CreateAnother"] ?></a> | 
            <a href="AdminCollections.php"><?php print $arrayOfStrings["ViewCollections"] ?></a> | 
            <a href="Admin.php"><?php print $arrayOfStrings["BackToAdmin"] ?></a>
        </p>
    <?php else: ?>
        <?php if (empty($allStations)): ?>
            <p><?php print $arrayOfStrings["NoStationsAvailable"] ?></p>
            <p><a href="Admin.php"><?php print $arrayOfStrings["BackToAdmin"] ?></a></p>
        <?php else: ?>
            <form method="POST">
                <p>
                    <label for="station"><?php print $arrayOfStrings["StationColon"] ?></label><br />
                    <select id="station" name="station" required>
                        <option value=""><?php print $arrayOfStrings["SelectStationDots"] ?></option>
                        <?php foreach ($allStations as $station): ?>
                            <option value="<?php echo htmlspecialchars($station['pk_serialNumber']); ?>">
                                <?php echo htmlspecialchars($station['pk_serialNumber'] . ' - ' . ($station['name'] ?? $arrayOfStrings['Unnamed'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label for="collection_name"><?php print $arrayOfStrings["CollectionNameColon"] ?></label><br />
                    <input type="text" id="collection_name" name="collection_name" required />
                </p>

                <p>
                    <label for="collection_desc"><?php print $arrayOfStrings["CollectionDescriptionColon"] ?></label><br />
                    <input type="text" id="collection_desc" name="collection_desc" />
                </p>

                <p>
                    <label for="start_date"><?php print $arrayOfStrings["StartDateTimeColon"] ?></label><br />
                    <input type="datetime-local" id="start_date" name="start_date" required />
                </p>

                <p>
                    <label for="end_date"><?php print $arrayOfStrings["EndDateTimeColon"] ?></label><br />
                    <input type="datetime-local" id="end_date" name="end_date" required />
                </p>

                <p>
                    <button type="submit"><?php print $arrayOfStrings["CreateCollectionButton"] ?></button>
                    <a href="Admin.php"><?php print $arrayOfStrings["Cancel"] ?></a>
                </p>
            </form>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</body>

</html>
