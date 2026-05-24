<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Manage Stations</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
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

    // Initialize state for messages and actions
    $message = "";
    $action = $_POST['action'] ?? '';

// Handle delete station
if ($action === 'delete_station') {
    $serialNumber = $_POST['serial'] ?? '';
    if ($serialNumber !== '') {
        // Prepare delete for station by serial number
        $stmt = $connection->prepare("DELETE FROM station WHERE pk_serialNumber = ?");
        $stmt->bind_param("s", $serialNumber);
        if ($stmt->execute()) {
            $message = "StationDeleted";
        } else {
            $message = "StationDeleteFailed";
        }
    }
}

// Handle update station
if ($action === 'update_station') {
    $serialNumber = $_POST['serial'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $owner = $_POST['owner'] ?? '';
    
    if ($serialNumber !== '') {
        // Check if owner exists or is empty
        if ($owner !== '') {
            // Prepare query to verify owner exists
            $checkStmt = $connection->prepare("SELECT pk_username FROM user WHERE pk_username = ?");
            $checkStmt->bind_param("s", $owner);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows === 0) {
                $message = "UserDoesNotExist";
            } else {
                // Prepare update to change station fields and owner
                $stmt = $connection->prepare("UPDATE station SET name = ?, description = ?, fk_user_owns = ? WHERE pk_serialNumber = ?");
                $stmt->bind_param("ssss", $name, $description, $owner, $serialNumber);
                if ($stmt->execute()) {
                    $message = "StationUpdated";
                } else {
                    $message = "StationUpdateFailed";
                }
            }
        } else {
            // Owner is empty, set to NULL
            // Prepare update to clear owner (set to NULL)
            $stmt = $connection->prepare("UPDATE station SET name = ?, description = ?, fk_user_owns = NULL WHERE pk_serialNumber = ?");
            $stmt->bind_param("sss", $name, $description, $serialNumber);
            if ($stmt->execute()) {
                $message = "StationUpdated";
            } else {
                $message = "StationUpdateFailed";
            }
        }
    }
}

// Get all stations for admin table
$allStations = [];
// Prepare query to load all stations for the table
$stmt = $connection->prepare("SELECT pk_serialNumber, name, description, fk_user_owns FROM station ORDER BY pk_serialNumber ASC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allStations[] = $row;
}

?>
<h1><?php print $arrayOfStrings["ManageStations"] ?></h1>

<?php if ($message !== "") { ?>
    <p><?php echo htmlspecialchars($arrayOfStrings[$message] ?? $message); ?></p>
<?php } ?>

<table>
    <tr>
        <th><?php print $arrayOfStrings["TableSerial"] ?></th>
        <th><?php print $arrayOfStrings["TableName"] ?></th>
        <th><?php print $arrayOfStrings["TableDescription"] ?></th>
        <th><?php print $arrayOfStrings["TableOwner"] ?></th>
        <th><?php print $arrayOfStrings["TableActions"] ?></th>
    </tr>
    <?php foreach ($allStations as $station) { ?>
        <tr>
            <td><?php echo htmlspecialchars($station['pk_serialNumber']); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="update_station">
                    <input type="hidden" name="serial" value="<?php echo htmlspecialchars($station['pk_serialNumber']); ?>">
            <input type="text" name="name" value="<?php echo htmlspecialchars($station['name'] ?? ''); ?>">
            </td>
            <td>
                    <textarea name="description"><?php echo htmlspecialchars($station['description'] ?? ''); ?></textarea>
            </td>
            <td>
                    <input type="text" name="owner" value="<?php echo htmlspecialchars($station['fk_user_owns'] ?? ''); ?>">
            </td>
            <td>
            <button type="submit"><?php print $arrayOfStrings["Update"] ?></button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete_station">
                    <input type="hidden" name="serial" value="<?php echo htmlspecialchars($station['pk_serialNumber']); ?>">
            <button type="submit"><?php print $arrayOfStrings["Delete"] ?></button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>

<p>
    <a href="Admin.php"><?php print $arrayOfStrings["Cancel"] ?></a>
</p>

</body>
</html>