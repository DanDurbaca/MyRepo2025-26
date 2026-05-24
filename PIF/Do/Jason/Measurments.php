<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Measurements</title>
</head>
<body class="stations-page">
    <?php
    // Include the common PHP file for database and session
    include_once("commonphp.php");

    // Get the logged-in user's ID from the session
    $userId = $_SESSION['user_id'] ?? null;
    // If not logged in, redirect to login
    if (!$userId) {
        header('Location: index.php');
        exit;
    }

    // Read filter values from GET (sent from Stations.php) or POST (sent from this page's own form)
    $stationId  = (int)(($_GET['station_id']  ?? $_POST['station_id']  ?? 0));
    $startDate  = $_GET['start_date']  ?? $_POST['start_date']  ?? '';
    $startTime  = $_GET['start_time']  ?? $_POST['start_time']  ?? '00:00';
    $endDate    = $_GET['end_date']    ?? $_POST['end_date']    ?? '';
    $endTime    = $_GET['end_time']    ?? $_POST['end_time']    ?? '23:59';

    // Load all stations that belong to this user for the filter dropdown
    $stationsResult = mysqli_query($conn, "SELECT serial_number, station_name FROM Station WHERE user_station = " . (int)$userId . " ORDER BY station_name");
    $stations = [];
    while ($s = mysqli_fetch_assoc($stationsResult)) {
        $stations[] = $s;
    }
    ?>

    <div class="container">
        <h1 class="Title">Measurements</h1>
        <p class="lead">View and filter measurement data from your stations.</p>

        <!-- Filter form -->
        <div class="section-card">
            <h2>Filter</h2>
            <form method="post">
                <!-- Station selector -->
                <div class="field-row">
                    <label>Station</label>
                    <select name="station_id" required>
                        <option value="">Select a station</option>
                        <?php foreach ($stations as $s): ?>
                            <option value="<?= (int)$s['serial_number'] ?>"
                                <?= $stationId === (int)$s['serial_number'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['station_name'], ENT_QUOTES, 'UTF-8') ?>
                                (#<?= (int)$s['serial_number'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Start date/time -->
                <div class="field-row">
                    <label>Start Date &amp; Time</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>" required>
                    <input type="time" name="start_time" value="<?= htmlspecialchars($startTime, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <!-- End date/time -->
                <div class="field-row">
                    <label>End Date &amp; Time</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>" required>
                    <input type="time" name="end_time" value="<?= htmlspecialchars($endTime, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="button-row">
                    <button type="submit">Show Measurements</button>
                </div>
            </form>
        </div>

        <?php
        // Only query measurements if we have a station and date range
        if ($stationId > 0 && $startDate !== '' && $endDate !== '') {

            // Build full datetime strings from the date and time inputs
            $startDatetime = $startDate . ' ' . $startTime . ':00';
            $endDatetime   = $endDate   . ' ' . $endTime   . ':59';

            // Verify this station belongs to the logged-in user before showing data
            $checkStmt = mysqli_prepare($conn, "SELECT station_name FROM Station WHERE serial_number = ? AND user_station = ?");
            mysqli_stmt_bind_param($checkStmt, 'ii', $stationId, $userId);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $stationRow  = mysqli_fetch_assoc($checkResult);
            mysqli_stmt_close($checkStmt);

            if (!$stationRow) {
                // Station not found or does not belong to user
                echo "<div class='alert'>Invalid station selected.</div>";
            } else {
                $stationName = htmlspecialchars($stationRow['station_name'], ENT_QUOTES, 'UTF-8');
                echo "<h2>Results for $stationName (#$stationId)</h2>";
                echo "<p class='note'>From $startDatetime to $endDatetime</p>";

                // Query measurements for this station within the date/time range
                $stmt = mysqli_prepare($conn,
                    "SELECT measurement_ID, timestamp_Measurement, temperature, humidity, airpressure, lightintensity, airquality
                     FROM Measurement
                     WHERE station = ? AND timestamp_Measurement BETWEEN ? AND ?
                     ORDER BY timestamp_Measurement ASC"
                );
                mysqli_stmt_bind_param($stmt, 'iss', $stationId, $startDatetime, $endDatetime);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    // Display results in a table
                    echo "<table class='measurement-table'>";
                    echo "<tr>
                            <th>Timestamp</th>
                            <th>Temperature (°C)</th>
                            <th>Humidity (%)</th>
                            <th>Air Pressure</th>
                            <th>Light Intensity</th>
                            <th>Air Quality</th>
                          </tr>";
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['timestamp_Measurement'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['temperature'],           ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['humidity'],              ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['airpressure'],           ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['lightintensity'],        ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['airquality'],            ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    // No measurements found in this range
                    echo "<div class='alert'>No measurements found for this station in the selected date/time range.</div>";
                }
                mysqli_stmt_close($stmt);
            }
        }
        ?>

    </div>
</body>
</html>
