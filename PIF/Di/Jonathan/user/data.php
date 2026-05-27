<?php
// Data explorer: filter and display measurements for the user's stations
$pageTitle = 'View Data';
require_once '../config.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../inc/csrf.php';

// Check login
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}
$username = $_SESSION['username'];
?>

<div class="container">
    <h1>Measurement Data</h1>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Filter Data</h3>
        <form method="post">
            <?php echo csrf_input(); ?>

            <div class="form-group">
                <label for="station">Station</label>
                <select id="station" name="station" required>
                    <option value="">Select a station...</option>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT s.pk_serialNumber, s.name, s.fk_user_owns,
                               CASE WHEN s.fk_user_owns = ? THEN 1 ELSE 0 END AS is_owner
                        FROM station s
                        LEFT JOIN station_share ss
                            ON ss.station_serial = s.pk_serialNumber
                           AND ss.shared_with = ?
                           AND ss.status = 'accepted'
                        WHERE s.fk_user_owns = ? OR ss.id IS NOT NULL
                        ORDER BY is_owner DESC, s.pk_serialNumber
                    ");
                    $stmt->execute([$username, $username, $username]);
                    while ($station = $stmt->fetch()) {
                        $label = $station['name'] . ' (' . $station['pk_serialNumber'] . ')';
                        if ((int)$station['is_owner'] !== 1) {
                            $label .= ' - shared by ' . $station['fk_user_owns'];
                        }
                        echo "<option value='" . htmlspecialchars($station['pk_serialNumber']) . "'>" . htmlspecialchars($label) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="datetime-local" id="start_date" name="start_date">
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="datetime-local" id="end_date" name="end_date">
            </div>
            <button class="btn" type="submit">View Data</button>
        </form>
    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['station'])): ?>
        <div class="card">
            <h3>Results</h3>
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Temperature (°C)</th>
                        <th>Humidity (%)</th>
                        <th>Air Quality (CO2 ppm)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                        echo '<tr><td colspan="4">Invalid CSRF token.</td></tr>';
                    } else {
                        $serial = trim($_POST['station']);
                        if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $serial)) {
                            echo '<tr><td colspan="4">Invalid station identifier.</td></tr>';
                        } else {
                            $ok = $pdo->prepare("
                                SELECT 1
                                FROM station s
                                LEFT JOIN station_share ss
                                    ON ss.station_serial = s.pk_serialNumber
                                   AND ss.shared_with = ?
                                   AND ss.status = 'accepted'
                                WHERE s.pk_serialNumber = ?
                                  AND (s.fk_user_owns = ? OR ss.id IS NOT NULL)
                            ");
                            $ok->execute([$username, $serial, $username]);
                            if (!$ok->fetch()) {
                                echo '<tr><td colspan="4">Unauthorized or unknown station.</td></tr>';
                            } else {
                                $start = '1970-01-01 00:00:00';
                                $end = date('Y-m-d H:i:s');
                                if (!empty($_POST['start_date'])) {
                                    $d = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['start_date']);
                                    if ($d) $start = $d->format('Y-m-d H:i:s');
                                }
                                if (!empty($_POST['end_date'])) {
                                    $d2 = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['end_date']);
                                    if ($d2) $end = $d2->format('Y-m-d H:i:s');
                                }

                                $stmt = $pdo->prepare("SELECT timestamp, temperature, humidity, gas AS air_quality FROM measurement WHERE fk_station_records = ? AND timestamp BETWEEN ? AND ? ORDER BY timestamp");
                                $stmt->execute([$serial, $start, $end]);
                                $count = 0;
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['temperature']) . "°C</td>";
                                    echo "<td>" . htmlspecialchars($row['humidity']) . "%</td>";
                                    echo "<td>" . htmlspecialchars($row['air_quality']) . " ppm</td>";
                                    echo "</tr>";
                                    $count++;
                                }
                                if ($count == 0) {
                                    echo '<tr><td colspan="4">No data found for the selected criteria.</td></tr>';
                                }
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>