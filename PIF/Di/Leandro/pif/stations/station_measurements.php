<?php
/*
 * stations/station_measurements.php
 * Purpose: Display measurements for a specific station (chart + table).
 * Sections:
 *  - Includes: config and auth check
 *  - Access check: ensure current user is owner or admin
 *  - Loads: recent measurements and prepares data for Chart.js
 */
require "../includes/config.php";
require "../includes/auth_check.php";

if (!isset($_GET['sn'])) {
    die("Station not specified");
}

$sn = $_GET['sn'];

// Verify access: owner or admin
$stmt = $pdo->prepare("
    SELECT *
    FROM station
    WHERE pk_serialNumber = ?
      AND (fk_user_owns = ? OR ? = 'Admin')
");
$stmt->execute([$sn, $_SESSION['username'], $_SESSION['role']]);
$station = $stmt->fetch();

if (!$station) {
    die("Access denied");
}

// Fetch measurements
$stmt = $pdo->prepare("
    SELECT *
    FROM measurement
    WHERE fk_station_records = ?
    ORDER BY timestamp DESC
    LIMIT 100
");
$stmt->execute([$sn]);
$data = $stmt->fetchAll();

// Prepare data for Chart.js
$timestamps = [];
$temperatures = [];

foreach ($data as $m) {
    $timestamps[] = $m['timestamp'];
    $temperatures[] = $m['temperature'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Station <?= htmlspecialchars($sn) ?> – Measurements</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>Station <?= htmlspecialchars($sn) ?> – Measurements</h2>

    <?php if (count($data) === 0): ?>
        <p>No measurements available yet.</p>
    <?php else: ?>

        <!-- Temperature chart -->
        <div class="mb-4">
            <canvas id="tempChart" height="120"></canvas>
        </div>

        <script>
        const ctx = document.getElementById('tempChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_reverse($timestamps)) ?>,
                datasets: [{
                    label: 'Temperature (°C)',
                    data: <?= json_encode(array_reverse($temperatures)) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.25)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                plugins: {
    legend: {
        position: 'top',
        align: 'start',
        labels: { color: '#ffffff' }
    }
},
                scales: {
                    x: {
                        ticks: {
                            color: '#cccccc'
                        }
                    },
                    y: {
    ticks: {
        color: '#cccccc',
        callback: value => value + ' °C'
    }
}

                }
            }
        });
        </script>

        <!-- Measurements table -->
        <table class="table table-dark table-striped">
            <tr>
                <th>Time</th>
                <th>Temp</th>
                <th>Humidity</th>
                <th>Pressure</th>
                <th>Light</th>
                <th>Gas</th>
            </tr>
            <?php foreach ($data as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['timestamp']) ?></td>
                    <td><?= $m['temperature'] ?></td>
                    <td><?= $m['humidity'] ?></td>
                    <td><?= $m['pressure'] ?></td>
                    <td><?= $m['light'] ?></td>
                    <td><?= $m['gas'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
