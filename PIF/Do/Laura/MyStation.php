<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - My Station</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
<?php
// Load shared utilities and note: delete currently unassigns station
include_once("CommonCode.php");

// Require login before managing stations
requireLogin();

// Current logged-in user
$me = getCurrentUser();
$message = "";

// Handle station actions (register, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];

    // REGISTER (only if unassigned)
    if ($action === 'register') {
        $serial = trim($_POST['serial'] ?? '');

        if ($serial === '') {
            $message = "Please enter a serial number.";
        } else {
            // Prepare update to claim an unassigned station
            $upd = $connection->prepare("UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ? AND fk_user_owns IS NULL");
            $upd->bind_param("ss", $me, $serial);
            $upd->execute();

            if ($upd->affected_rows === 1) {
                header("Location: MyStation.php");
                exit;
            } else {
                $message = "Serial not found or already assigned.";
            }
        }
    }

    // EDIT station details
    if ($action === 'edit') {
        $serial = $_POST['serial'] ?? '';
        $name   = $_POST['name'] ?? '';
        $desc   = $_POST['description'] ?? '';

        // Prepare update to edit station name/description for owner
        $upd = $connection->prepare("UPDATE station SET name = ?, description = ? WHERE pk_serialNumber = ? AND fk_user_owns = ?");
        $upd->bind_param("ssss", $name, $desc, $serial, $me);
        $upd->execute();

        // If nothing updated, show a message instead of redirecting
        if ($upd->affected_rows === 1) {
            header("Location: MyStation.php");
            exit;
        } else {
            $message = "Save did not change anything (check ownership or try changing the text).";
        }
    }

    // DELETE (unassign station from user)
    if ($action === 'delete') {
        $serial = $_POST['serial'] ?? '';

        // Prepare update to unassign station and clear fields
        $upd = $connection->prepare("UPDATE station SET fk_user_owns = NULL, name = '', description = '' WHERE pk_serialNumber = ? AND fk_user_owns = ?");
        $upd->bind_param("ss", $serial, $me);
        $upd->execute();

        header("Location: MyStation.php");
        exit;
    }
}

// ---------- LOAD USER STATIONS ----------
$myStations = [];
if (isset($_SESSION["UserLoggedIn"]) && $_SESSION["UserLoggedIn"]) {
    $myStations = getUserStations($me);
}
?>

<?php
NavigationBar1("MyStation");

// Secondary guard for unauthenticated users
if (!isset($_SESSION["UserLoggedIn"]) || !$_SESSION["UserLoggedIn"]) {
    echo "<h1>" . $arrayOfStrings["UserOnly"] . "</h1>";
    exit;
}
?>

<h1><?php echo $arrayOfStrings['Station'] ?></h1>

<?php if ($message !== "") { ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php } ?>

<section>
    <h2><?php echo $arrayOfStrings['YourStations'] ?></h2>

    <?php if (empty($myStations)) { echo "<p>" . $arrayOfStrings['NoStations'] . "</p>"; } else { ?>
        <ul>
        <?php foreach ($myStations as $s) { ?>
            <li>
                <strong><?php echo htmlspecialchars($s['pk_serialNumber']); ?></strong>

                <form method="POST" style="display:inline;margin-left:8px">
                    <input type="hidden" name="action" value="edit" />
                    <input type="hidden" name="serial" value="<?php echo htmlspecialchars($s['pk_serialNumber']); ?>" />
                    <input type="text" name="name" value="<?php echo htmlspecialchars($s['name'] ?? ''); ?>" placeholder="Name" />
                    <input type="text" name="description" value="<?php echo htmlspecialchars($s['description'] ?? ''); ?>" placeholder="Description" />
                    <button type="submit">Save</button>
                </form>

                <form method="POST" style="display:inline;margin-left:6px" onsubmit="return confirm('Delete?');">
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="serial" value="<?php echo htmlspecialchars($s['pk_serialNumber']); ?>" />
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php } ?>
        </ul>
    <?php } ?>
</section>

<section>
    <h2><?php echo $arrayOfStrings['RegisterStation'] ?></h2>

    <form method="POST">
        <input type="hidden" name="action" value="register" />
        <input type="text" name="serial" placeholder="Serial" required />
        <button type="submit"><?php echo $arrayOfStrings['Register'] ?></button>
    </form>
</section>

</body>
</html>