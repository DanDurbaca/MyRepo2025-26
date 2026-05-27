<?php
// Display collection metadata and its contained measurements with access checks
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/csrf.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$username = $_SESSION['username'];
$pageTitle = 'View Collection';
require_once __DIR__ . '/../_header.php';
?>

<div class="container">
    <h1>View Collection</h1>
    <?php
    if (isset($_GET['id'])) {
        $collection_id = $_GET['id'];
        if (!ctype_digit($collection_id)) {
            echo '<div class="alert alert-danger">Invalid collection ID.</div>';
            exit;
        }

        // Check if user owns or has access to collection
        $stmt = $pdo->prepare("SELECT c.name, c.description, c.fk_user_creates FROM collection c LEFT JOIN hasaccess h ON c.pk_collection = h.pkfk_collection WHERE c.pk_collection = ? AND (c.fk_user_creates = ? OR h.pkfk_user = ?)");
        $stmt->execute([$collection_id, $username, $username]);
        $collection = $stmt->fetch();

        if ($collection) {
            ?>
            <div class="box">
                <div class="box-header"><?php echo htmlspecialchars($collection['name'] ?? 'Unnamed Collection'); ?></div>
                <p class="text-muted"><?php echo htmlspecialchars($collection['description'] ?? 'No description.'); ?></p>

                <h3>Measurements</h3>
                <?php
                // Fetch measurements in collection
                $stmt = $pdo->prepare("SELECT m.timestamp, m.temperature, m.humidity, m.gas AS air_quality, s.pk_serialNumber AS station_serial, s.name AS station_name FROM measurement m JOIN contains c ON m.pk_measurement = c.pkfk_measurement JOIN station s ON s.pk_serialNumber = m.fk_station_records WHERE c.pkfk_collection = ? ORDER BY m.timestamp DESC");
                $stmt->execute([$collection_id]);
                $measurements = $stmt->fetchAll();

                if (count($measurements) > 0) {
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Timestamp</th>
                                <th>Temperature (°C)</th>
                                <th>Humidity (%)</th>
                                <th>Air Quality (CO2 ppm)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($measurements as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(($row['station_name'] ?? '') !== '' ? ($row['station_name'] . ' (' . $row['station_serial'] . ')') : ($row['station_serial'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($row['timestamp'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['temperature'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['humidity'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['air_quality'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php
                } else {
                    echo '<p class="text-muted">No measurements found in this collection.</p>';
                }
                ?>
            </div>
            <p><a href="collections.php" class="btn btn-secondary">Back to Collections</a></p>
            <?php
        } else {
            echo '<div class="alert alert-warning">Collection not found or access denied.</div>';
        }
    } else {
        echo '<div class="alert alert-info">No collection selected.</div>';
    }
    ?>
</div>

</body>
</html>