<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Measurements</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
<?php
// Load shared utilities and navigation
require_once(__DIR__ . "/CommonCode.php");
NavigationBar1("Measurements");
requireLogin();

// Identify current user and role
$me = getCurrentUser();
$isAdmin = (($_SESSION["role"] ?? "User") === "Admin");

// Read filters from GET
$selectedStation = $_GET['station'] ?? '';
$selectedRange   = $_GET['range'] ?? '';

// Only show measurements after Apply
$filtersApplied = ($selectedStation !== '' || $selectedRange !== '');

// Admin delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($isAdmin) && (($_POST['action'] ?? '') === 'delete_measurement')) {
    $pk = (int)($_POST['pk_measurement'] ?? 0);

    if ($pk > 0) {
        // Prepare delete for a single measurement by primary key
        $del = $connection->prepare("DELETE FROM measurement WHERE pk_measurement = ?");
        $del->bind_param("i", $pk);
        $del->execute();
    }

    header("Location: measurements.php");
    exit;
}

// Station dropdown list (admin: all stations, user: owned stations)
if ($isAdmin) {
    // Prepare query to load all stations for admin dropdown
    $stmt = $connection->prepare("SELECT pk_serialNumber, name FROM station ORDER BY pk_serialNumber ASC");
    $stmt->execute();
    $stationsForDropdown = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Your helper returns description too; that's fine
    $stationsForDropdown = getUserStations($me);
}
?>

<h1><?php print $arrayOfStrings["MeasurementsTitle"] ?></h1>

<form method="get" action="measurements.php">
    <p>
        <label for="station"><?php print $arrayOfStrings["StationColon"] ?></label>
        <select id="station" name="station">
            <option value=""><?php echo $isAdmin ? $arrayOfStrings["AllStations"] : $arrayOfStrings["AllMyStations"]; ?></option>
            <?php foreach ($stationsForDropdown as $st): ?>
                <option value="<?php echo htmlspecialchars($st['pk_serialNumber']); ?>"
                    <?php if ($selectedStation === $st['pk_serialNumber']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($st['pk_serialNumber'] . ' - ' . ($st['name'] ?? $arrayOfStrings['Unnamed'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="range"><?php print $arrayOfStrings["DateRangeLabel"] ?></label>
        <select id="range" name="range">
            <option value=""><?php print $arrayOfStrings["ChooseDateRange"] ?></option>
            <option value="today" <?php if ($selectedRange === 'today') echo 'selected'; ?>><?php print $arrayOfStrings["Today"] ?></option>
            <option value="24h" <?php if ($selectedRange === '24h') echo 'selected'; ?>><?php print $arrayOfStrings["Last24Hours"] ?></option>
            <option value="7d" <?php if ($selectedRange === '7d') echo 'selected'; ?>><?php print $arrayOfStrings["Last7Days"] ?></option>
        </select>
    </p>

    <p>
        <button type="submit"><?php print $arrayOfStrings["Apply"] ?></button>
        <a href="dashboard.php">View on dashboard</a>
    </p>
</form>

<?php
if (!$filtersApplied) {
    echo "<p>" . $arrayOfStrings["SelectStationOrRange"] . "</p>";
} else {
    $rows = getMeasurements($me, $isAdmin, $selectedStation, $selectedRange, 100);

    if (empty($rows)) {
        echo "<p>" . $arrayOfStrings["NoMeasurementsFound"] . "</p>";
    } else {
        // Non-admin: use common renderer
        if (!$isAdmin) {
            renderMeasurementsTable($rows);
        } else {
            // Admin: render with Delete column (keep simple)
            echo '<table border="1" cellpadding="5" cellspacing="0">';
            echo '<tr>';
            echo '<th>' . $arrayOfStrings["Station"] . '</th>';
            echo '<th>' . $arrayOfStrings["Timestamp"] . '</th>';
            echo '<th>' . $arrayOfStrings["Temperature"] . '</th>';
            echo '<th>' . $arrayOfStrings["Humidity"] . '</th>';
            echo '<th>' . $arrayOfStrings["Pressure"] . '</th>';
            echo '<th>' . $arrayOfStrings["Light"] . '</th>';
            echo '<th>' . $arrayOfStrings["Gas"] . '</th>';
            echo '<th>' . $arrayOfStrings["Delete"] . '</th>';
            echo '</tr>';

            foreach ($rows as $m) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($m['fk_station_records'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($m['timestamp'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($m['temperature'] ?? '') . ' °C</td>';
                echo '<td>' . htmlspecialchars($m['humidity'] ?? '') . ' %</td>';
                echo '<td>' . htmlspecialchars($m['pressure'] ?? '') . ' hPa</td>';
                echo '<td>' . htmlspecialchars($m['light'] ?? '') . ' lux</td>';
                echo '<td>' . htmlspecialchars($m['gas'] ?? '') . ' ppm</td>';

                echo '<td>';
                echo '<form method="post">';
                echo '<input type="hidden" name="action" value="delete_measurement" />';
                echo '<input type="hidden" name="pk_measurement" value="' . htmlspecialchars($m['pk_measurement']) . '" />';
                echo '<input type="hidden" name="keep_station" value="' . htmlspecialchars($selectedStation) . '" />';
                echo '<input type="hidden" name="keep_range" value="' . htmlspecialchars($selectedRange) . '" />';
                echo '<button type="submit">' . $arrayOfStrings["Delete"] . '</button>';
                echo '</form>';
                echo '</td>';

                echo '</tr>';
            }

            echo '</table>';
        }
    }
}
?>

</div>
</body>
</html>
