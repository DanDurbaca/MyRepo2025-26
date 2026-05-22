<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Admin Station</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("admin");

    $message = '';

    // Allow only logged-in admins
    requireAdmin();

    // Handle station creation (admin only)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_station') {
        $serial = $_POST['serial'] ?? '';
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';

        // Empty owner means unassigned
        if ($serial !== '') {
            // Prepare insert to create a new unassigned station
            $ins = $connection->prepare("INSERT INTO station (pk_serialNumber, name, description, fk_user_owns) VALUES (?, ?, ?, NULL)");
            $ins->bind_param("sss", $serial, $name, $desc);
            if ($ins->execute()) {
                $message =  $arrayOfStrings["MessageStationCreated"];
            } else {
                $message = $arrayOfStrings["MessageStationFailed"];
            }
        } else {
            $message = $arrayOfStrings["MessageStationRequired"];
        }
    }
    ?>

    <h1>Admin</h1>

    <?php if ($message !== '') { echo "<p>" . htmlspecialchars($message) . "</p>"; } ?>

    <h2><?php print $arrayOfStrings["CreateStation"] ?></h2>
    <p><?php print $arrayOfStrings["StationInstructions"] ?></p>
    <form method="POST">
        <input type="hidden" name="action" value="create_station" />
        <p>
            <label for="serial"><?php print $arrayOfStrings["SerialNumber"] ?></label><br />
            <input type="text" id="serial" name="serial" required />
        </p>
        <p>
            <label for="name"><?php print $arrayOfStrings["StationName"] ?></label><br />
            <input type="text" id="name" name="name" />
        </p>
        <p>
            <label for="description"><?php print $arrayOfStrings["StationDescription"] ?></label><br />
            <input type="text" id="description" name="description" />
        </p>
        <p>
            <button type="submit"><?php print $arrayOfStrings["CreateButton"] ?></button>
            <a href="Admin.php"><?php print $arrayOfStrings["Cancel"] ?></a>
        </p>
    </form>


</html>