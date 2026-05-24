<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?= time(); ?>">
    <title>Stations</title>
</head>

<body class="stations-page">
    <?php
    // Include the common PHP file for database and session
    include_once("commonphp.php");
    ?>
    <div class="container">
        <h1 class="Title">Stations</h1>
        <p class="lead">Manage your stations here.</p>

        <?php
        // Get the logged-in user's ID from the session
        $userId = $_SESSION['user_id'] ?? null;
        // If not logged in, show message and stop
        if (!$userId) {
            echo "<div class='alert'>Please log in.</div>";
            exit;
        }

        // Check if a form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // If updating a station
            if (isset($_POST['update_station'])) {
                // Get the station ID and new name/description from the form
                $stationId = (int)$_POST['station_id'];
                $name = trim($_POST['station_name']);
                $desc = trim($_POST['station_description']);
                // Update the station in the database (only if it belongs to the user)
                $stmt = mysqli_prepare($conn, "UPDATE Station SET station_name = ?, station_description = ? WHERE serial_number = ? AND user_station = ?");
                mysqli_stmt_bind_param($stmt, 'ssii', $name, $desc, $stationId, $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                echo "<div class='alert'>Station updated.</div>";
                // If registering a new station
            } elseif (isset($_POST['register_station'])) {
                // Get the serial number from the form
                $serial = (int)$_POST['serial_number'];
                if ($serial <= 0) {
                    echo "<div class='alert'>Invalid station serial number.</div>";
                } else {
                    $stmt = mysqli_prepare($conn, "SELECT user_station FROM Station WHERE serial_number = ? LIMIT 1");
                    mysqli_stmt_bind_param($stmt, 'i', $serial);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($result && $row = mysqli_fetch_assoc($result)) {
                        mysqli_stmt_close($stmt);
                        if ($row['user_station'] === null || $row['user_station'] === '' || $row['user_station'] == 0) {
                            $stmt2 = mysqli_prepare($conn, "UPDATE Station SET user_station = ? WHERE serial_number = ?");
                            mysqli_stmt_bind_param($stmt2, 'ii', $userId, $serial);
                            mysqli_stmt_execute($stmt2);
                            mysqli_stmt_close($stmt2);
                            echo "<div class='alert'>Station registered.</div>";
                        } else {
                            if ((int)$row['user_station'] === $userId) {
                                echo "<div class='alert'>This station is already assigned to you.</div>";
                            } else {
                                echo "<div class='alert'>Station already assigned.</div>";
                            }
                        }
                    } else {
                        mysqli_stmt_close($stmt);
                        echo "<div class='alert'>Station serial number not found.</div>";
                    }
                }
                // If viewing measurements
            } elseif (isset($_POST['view_measurements'])) {
                // Get the station ID and date range from the form
                $stationId = (int)$_POST['station_id'];
                $start = strtotime($_POST['start_date'] . ' ' . $_POST['start_time']);
                $end = strtotime($_POST['end_date'] . ' ' . $_POST['end_time']);
                // Show the measurements for this station in the date range
                echo "<h2>Measurements for Station $stationId</h2>";
                $stmt = mysqli_prepare($conn, "SELECT timestamp_Measurement, temperature, humidity, airpressure, lightintensity, airquality FROM Measurement WHERE station = ? AND timestamp_Measurement BETWEEN ? AND ? ORDER BY timestamp_Measurement");
                mysqli_stmt_bind_param($stmt, 'iii', $stationId, $start, $end);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result) > 0) {
                    // Display the measurements in a table
                    echo "<table class='measurement-table'>";
                    echo "<tr><th>Timestamp</th><th>Data</th></tr>";
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr><td>{$row['timestamp_Measurement']}</td><td>Temp: {$row['temperature']}°C | Humidity: {$row['humidity']}% | Pressure: {$row['airpressure']} | Light: {$row['lightintensity']} | Air Quality: {$row['airquality']}</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div class='alert'>No measurements found.</div>";
                }
                mysqli_stmt_close($stmt);
                // If adding a measurement
            } elseif (isset($_POST['add_measurement'])) {
                // Get the station ID and data from the form
                $stationId = (int)$_POST['station_id'];
                $data = (int)$_POST['measurement_data'];
                // Check if the station belongs to the user
                $stmt = mysqli_prepare($conn, "SELECT serial_number FROM Station WHERE serial_number = ? AND user_station = ?");
                mysqli_stmt_bind_param($stmt, 'ii', $stationId, $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    mysqli_stmt_close($stmt);
                    // Add the measurement with current timestamp
                    $stmt = mysqli_prepare($conn, "INSERT INTO Measurement (temperature, station) VALUES (?, ?)");
                    mysqli_stmt_bind_param($stmt, 'ii', $data, $stationId);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    echo "<div class='alert'>Measurement added.</div>";
                } else {
                    echo "<div class='alert'>Invalid station.</div>";
                }
            }
        }

        // Display the user's stations
        $stmt = mysqli_prepare($conn, "SELECT serial_number, station_name, station_description FROM Station WHERE user_station = ?");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            echo "<h2>Your Stations</h2>";
            // Loop through each station
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='station-card'>";
                echo "<h3>Station #" . htmlspecialchars($row['serial_number']) . "</h3>";
                echo "<form method='post' class='section-card'>";
                echo "<input type='hidden' name='station_id' value='" . htmlspecialchars($row['serial_number']) . "'>";
                echo "<div class='field-row'><label>Name</label><input type='text' name='station_name' value='" . htmlspecialchars($row['station_name']) . "'></div>";
                echo "<div class='field-row'><label>Description</label><input type='text' name='station_description' value='" . htmlspecialchars($row['station_description']) . "'></div>";
                echo "<div class='button-row'><button type='submit' name='update_station'>Update Station</button></div>";
                echo "</form>";
                echo "<form method='post' class='inline-form'>";
                echo "<input type='hidden' name='station_id' value='" . htmlspecialchars($row['serial_number']) . "'>";
                echo "<label>Measurement data</label><input type='number' name='measurement_data' required>";
                echo "<button type='submit' name='add_measurement'>Add Measurement</button>";
                echo "</form>";
                echo "<form method='get' action='Measurments.php' class='section-card'>";
                echo "<input type='hidden' name='station_id' value='" . htmlspecialchars($row['serial_number']) . "'>";
                echo "<div class='field-row'><label>Start Date</label><input type='date' name='start_date' required> <input type='time' name='start_time' required></div>";
                echo "<div class='field-row'><label>End Date</label><input type='date' name='end_date' required> <input type='time' name='end_time' required></div>";
                echo "<div class='button-row'><button type='submit'>View Measurements</button></div>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<div class='alert'>No stations assigned yet.</div>";
        }
        mysqli_stmt_close($stmt);

        // Form to register a station
        echo "<div class='section-card'>";
        echo "<h2>Register Station</h2>";
        echo "<p class='note'>Enter a serial number for an existing station. New stations cannot be created here.</p>";
        echo "<form method='post'>";
        echo "<div class='field-row'><label>Station Serial Number</label><input type='number' name='serial_number' required></div>";
        echo "<div class='button-row'><button type='submit' name='register_station'>Register Station</button></div>";
        echo "</form>";
        echo "</div>";
        ?>

    </div>

</body>

</html>