<?php
session_start();
require __DIR__ . '/assets/db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$dbError = null;
$stations = [];
$measurements = [];
$userCount = 0;
$stationCount = 0;
$measurementCount = 0;

function parseInputDate(?string $value): ?DateTime
{
  if (!$value) {
    return null;
  }
  $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value);
  if ($dt instanceof DateTime) {
    return $dt;
  }
  $dt = DateTime::createFromFormat(DateTime::ATOM, $value);
  return $dt ?: null;
}

try {
    $pdo = getDb();

    if (isset($_SESSION['username'])) {
        $stationsStmt = $pdo->prepare(
            'SELECT pk_serialNumber, COALESCE(name, pk_serialNumber) AS name 
             FROM station 
             WHERE fk_user_owns = :user 
             ORDER BY name'
        );
        $stationsStmt->execute([':user' => $_SESSION['username']]);
        $stations = $stationsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Handle inputs
        $startInput = $_GET['start'] ?? '';
        $endInput = $_GET['end'] ?? '';

        // Parse dates safely, fall back to a wider range
        $start = parseInputDate($startInput) ?: new DateTime('2025-01-01');
        $end = parseInputDate($endInput) ?: new DateTime('2026-12-31');
        if ($start > $end) {
          $tmp = $start;
          $start = $end;
          $end = $tmp;
        }

        $selectedStation = $_GET['station'] ?? ($stations[0]['pk_serialNumber'] ?? null);

        if ($selectedStation) {
            $measureStmt = $pdo->prepare(
                 'SELECT timestamp, temperature, humidity, pressure, light, gas
                  FROM measurement
                  WHERE fk_station_records = :station AND timestamp BETWEEN :start AND :end
                  ORDER BY timestamp DESC
                  LIMIT 25'
            );
            $measureStmt->execute([
                ':station' => $selectedStation,
                ':start' => $start->format('Y-m-d H:i:s'),
                ':end' => $end->format('Y-m-d H:i:s'),
            ]);
            $measurements = $measureStmt->fetchAll(PDO::FETCH_ASSOC);
            if ($measurements) {
              $chartLabels = [];
              $chartSeries = [
                'temperature' => [],
                'humidity' => [],
                'pressure' => [],
                'light' => [],
                'gas' => [],
              ];
              foreach (array_reverse($measurements) as $row) {
                $chartLabels[] = $row['timestamp'];
                $chartSeries['temperature'][] = (float)$row['temperature'];
                $chartSeries['humidity'][] = (float)$row['humidity'];
                $chartSeries['pressure'][] = (float)$row['pressure'];
                $chartSeries['light'][] = (float)$row['light'];
                $chartSeries['gas'][] = (float)$row['gas'];
              }
            }
        }
    }
} catch (PDOException $e) {
    $dbError = 'Database connection failed. Update credentials in assets/db.php.';
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <script src="/assets/js/chart.umd.js"></script>
  <title><?php echo isset($_SESSION['username']) ? 'Station Measurements' : 'Dashboard'; ?></title>
</head>
<body>
  <?php include 'assets/header.php'; ?>
  <main class="page">
      <section class="card measurements-card">
        <h2 class="card-title">Station Measurements (latest 25)</h2>

        <?php if ($dbError): ?>
          <p class="error-text"><?php echo h($dbError); ?></p>
        <?php elseif (empty($stations)): ?>
          <p class="muted">You have no stations yet. <a href="stations.php">Register a station</a> to view measurements.</p>
        <?php else: ?>
          <form class="form-grid" method="get">
            <label class="field-label" for="station-select">Station:</label>
            <select id="station-select" name="station" class="input-select">
              <?php foreach ($stations as $station): ?>
                <option value="<?php echo h($station['pk_serialNumber']); ?>" <?php echo ($station['pk_serialNumber'] === $selectedStation) ? 'selected' : ''; ?>>
                  <?php echo h($station['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label class="field-label" for="start-date">Start:</label>
            <input id="start-date" name="start" class="input-text" type="datetime-local" value="<?php echo h($start->format('Y-m-d\TH:i')); ?>">

            <label class="field-label" for="end-date">End:</label>
            <input id="end-date" name="end" class="input-text" type="datetime-local" value="<?php echo h($end->format('Y-m-d\TH:i')); ?>">
            <button class="primary-btn" type="submit">Show</button>
          </form>

          <div class="table-wrapper">
            <h3 class="table-title">
              <?php echo $selectedStation ? 'Measurements for Station ' . h($selectedStation) : 'No station selected'; ?>
            </h3>
            <?php if (!$selectedStation): ?>
              <p class="muted">Select a station to view measurements.</p>
            <?php elseif (empty($measurements)): ?>
              <p class="muted">No measurements found for this range.</p>
            <?php else: ?>
              <div class="chart-grid" style="margin-bottom:16px;">
                <h1>Temperature</h1>
                <canvas id="chart-temp" height="140"></canvas>
     <!--           <h1>Humidity</h1>
                <canvas id="chart-humidity" height="140"></canvas> -->
                <h1>Pressure</h1>
                <canvas id="chart-pressure" height="140"></canvas>
                <h1>Light</h1>
                <canvas id="chart-light" height="140"></canvas>
                <h1>Gas</h1>
                <canvas id="chart-gas" height="140"></canvas>
              </div>

              <script>
                window.addEventListener('load', function() {
                  const chartLib = typeof Chart;
                  const labels = <?php echo json_encode($chartLabels ?? []); ?>;
                  const series = <?php echo json_encode($chartSeries ?? []); ?>;
                  console.log('measurements chart init', chartLib, 'labels', labels.length);
                  if (chartLib === 'undefined') {
                    console.error('Chart.js missing');
                    return;
                  }
                  if (!labels.length) {
                    console.warn('No labels for measurements chart');
                    return;
                  }
                  const commonOptions = {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                      x: { ticks: { maxTicksLimit: 8 } },
                      y: { beginAtZero: false }
                    },
                    plugins: { legend: { display: false } }
                  };
                  const lineCfg = (data, label, color) => ({
                    type: 'line',
                    data: { labels, datasets: [{
                      label,
                      data,
                      tension: 0.25,
                      borderColor: color,
                      backgroundColor: color + '33',
                      fill: true,
                      pointRadius: 2,
                      pointHoverRadius: 4,
                    }]},
                    options: commonOptions
                  });
                  const build = (id, cfg) => {
                    const el = document.getElementById(id);
                    if (!el) {
                      console.error('Canvas not found', id);
                      return null;
                    }
                    const parentWidth = el.parentElement ? el.parentElement.clientWidth : 800;
                    el.width = parentWidth || 800;
                    el.height = 240;
                    const existing = Chart.getChart(el);
                    if (existing) existing.destroy();
                    const chart = new Chart(el, cfg);
                    console.log('chart built', id, !!chart);
                    return chart;
                  };
                  build('chart-temp', lineCfg(series.temperature, 'Temperature', '#4ab846'));
                  //build('chart-humidity', lineCfg(series.humidity, 'Humidity', '#3b82f6'));
                  build('chart-pressure', lineCfg(series.pressure, 'Pressure', '#8b5cf6'));
                  build('chart-light', lineCfg(series.light, 'Light', '#f59e0b'));
                  build('chart-gas', lineCfg(series.gas, 'Gas', '#ef4444'));
                });
              </script>

              <table class="data-table">
                <thead>
                  <tr>
                    <th>Timestamp</th>
                    <th>Temperature (°C)</th>
                    <!--<th>Humidity (%)</th> -->
                    <th>Pressure (hPa)</th>
                    <th>Light (lux)</th>
                    <th>Gas (ppm)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($measurements as $row): ?>
                    <tr>
                      <td><?php echo h($row['timestamp']); ?></td>
                      <td><?php echo h($row['temperature']); ?></td>
                      <!--<td><?php echo h($row['humidity']); ?></td> -->
                      <td><?php echo h($row['pressure']); ?></td>
                      <td><?php echo h($row['light']); ?></td>
                      <td><?php echo h($row['gas']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </section>
  </main>
  <?php include 'assets/footer.php'; ?>
 </body>
 </html>