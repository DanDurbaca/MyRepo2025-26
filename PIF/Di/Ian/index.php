<?php
session_start();
require __DIR__ . '/assets/db.php';

$dbError = null;
$stations = [];
$measurements = [];
$userCount = 0;
$stationCount = 0;
$measurementCount = 0;
$chartLabels = [];
$chartSeries = [
  'temperature' => [],
  'humidity' => [],
  'pressure' => [],
  'light' => [],
  'gas' => [],
];

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
      $user = $_SESSION['username'];
      // Fetch user's stations
      $st = $pdo->prepare('SELECT pk_serialNumber, name FROM station WHERE fk_user_owns = :u ORDER BY name');
      $st->execute([':u' => $user]);
      $stations = $st->fetchAll();

      // Latest 10 measurements across user's stations
      $m = $pdo->prepare(
        'SELECT m.pk_measurement, m.timestamp, m.temperature, m.humidity, m.pressure, m.light, m.gas, m.fk_station_records AS station
         FROM measurement m
         JOIN station s ON s.pk_serialNumber = m.fk_station_records
         WHERE s.fk_user_owns = :u
         ORDER BY m.timestamp DESC
         LIMIT 50'
      );
      $m->execute([':u' => $user]);
      $measurements = $m->fetchAll();

      if ($measurements) {
        $ordered = array_reverse($measurements);
        foreach ($ordered as $row) {
          $chartLabels[] = $row['timestamp'];
          $chartSeries['temperature'][] = (float)$row['temperature'];
          $chartSeries['humidity'][] = (float)$row['humidity'];
          $chartSeries['pressure'][] = (float)$row['pressure'];
          $chartSeries['light'][] = (float)$row['light'];
          $chartSeries['gas'][] = (float)$row['gas'];
        }
      }
    } else {
      // Public dashboard statistics
      $userStmt = $pdo->query('SELECT COUNT(*) as count FROM user');
      $userCount = $userStmt->fetch()['count'] ?? 0;

      $stationStmt = $pdo->query('SELECT COUNT(*) as count FROM station');
      $stationCount = $stationStmt->fetch()['count'] ?? 0;

      $measurementStmt = $pdo->query('SELECT COUNT(*) as count FROM measurement');
      $measurementCount = $measurementStmt->fetch()['count'] ?? 0;
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
  <?php if (isset($_SESSION['username'])): ?>
    <script src="/assets/js/chart.umd.js"></script>
  <?php endif; ?>
  <title><?php echo isset($_SESSION['username']) ? 'Station Measurements' : 'Dashboard'; ?></title>
</head>
<body>
  <?php include 'assets/header.php'; ?>
  <main class="page">
    <?php if (!isset($_SESSION['username'])): ?>
      <section class="card stats-card">
        <h2 class="card-title">Welcome</h2>

        <?php if ($dbError): ?>
          <p class="error-text"><?php echo h($dbError); ?></p>
        <?php else: ?>
          <div class="stats-grid">
            <div class="stat-item">
              <div class="stat-value"><?php echo $userCount; ?></div>
              <div class="stat-label">Registered Users</div>
            </div>
            <div class="stat-item">
              <div class="stat-value"><?php echo $stationCount; ?></div>
              <div class="stat-label">Weather Stations</div>
            </div>
            <div class="stat-item">
              <div class="stat-value"><?php echo $measurementCount; ?></div>
              <div class="stat-label">Measurements</div>
            </div>
          </div>
          <div class="stats-footer">
            <p><a href="login.php" class="primary-link">Login</a> to view your station measurements</p>
            <p>Don't have an account? <a href="signup.php" class="primary-link">Sign up</a></p>
          </div>
        <?php endif; ?>
      </section>
    <?php else: ?>
      <div class="dashboard">
      <section class="card">
        <h2 class="card-title">Your Stations</h2>
        <?php if ($dbError): ?>
          <p class="error-text"><?php echo h($dbError); ?></p>
        <?php else: ?>
          <?php if (!$stations): ?>
            <p class="muted">No stations yet.</p>
          <?php else: ?>
            <ul class="stations-list">
              <?php foreach ($stations as $s): ?>
                <li class="station-card">
                  <div class="station-field">
                    <span class="field-label">Name</span>
                    <span><?php echo h($s['name'] ?: '(unnamed)'); ?></span>
                  </div>
                  <div class="station-field">
                    <span class="field-label">Serial</span>
                    <span class="station-id"><?php echo h($s['pk_serialNumber']); ?></span>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        <?php endif; ?>
      </section>

      <section class="card">
        <h2 class="card-title">Latest Measurements</h2>
        <?php if ($dbError): ?>
          <p class="error-text"><?php echo h($dbError); ?></p>
        <?php else: ?>
          <?php if (!$measurements): ?>
            <p class="muted">No recent measurements.</p>
          <?php else: ?>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Timestamp</th>
                    <th>Station</th>
                    <th>Temp</th>
                    <!--<th>Humidity</th> -->
                    <th>Pressure</th>
                    <th>Light</th>
                    <th>Gas</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($measurements as $m): ?>
                    <tr>
                      <td><?php echo h($m['timestamp']); ?></td>
                      <td class="station-id"><?php echo h($m['station']); ?></td>
                      <td><?php echo h($m['temperature']); ?></td>
                      <!--<td><?php echo h($m['humidity']); ?></td> -->
                      <td><?php echo h($m['pressure']); ?></td>
                      <td><?php echo h($m['light']); ?></td>
                      <td><?php echo h($m['gas']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </section>

      <?php if ($measurements): ?>
      <section class="card">
        <h2 class="card-title">Measurements Charts</h2>
        <div class="chart-grid">
          <canvas id="chart-temp" height="120"></canvas>
          <!--<canvas id="chart-humidity" height="120"></canvas> -->
          <canvas id="chart-pressure" height="120"></canvas>
          <canvas id="chart-light" height="120"></canvas>
          <canvas id="chart-gas" height="120"></canvas>
        </div>
        <script>
          window.addEventListener('load', function() {
            const chartLib = typeof Chart;
            const chartLabels = <?php echo json_encode($chartLabels); ?>;
            const chartSeries = <?php echo json_encode($chartSeries); ?>;
            console.log('home chart init', chartLib, 'labels', chartLabels.length);
            if (chartLib === 'undefined') {
              console.error('Chart.js missing');
              return;
            }
            if (!chartLabels.length) {
              console.warn('No labels for home charts');
              return;
            }
            const build = (id, cfg) => {
              const el = document.getElementById(id);
              if (!el) {
                console.error('Canvas not found', id);
                return null;
              }
              const parentWidth = el.parentElement ? el.parentElement.clientWidth : 800;
              el.width = parentWidth || 800;
              el.height = 220;
              const existing = Chart.getChart(el);
              if (existing) existing.destroy();
              const chart = new Chart(el, cfg);
              console.log('chart built', id, !!chart);
              return chart;
            };
            const commonOptions = {
              responsive: false,
              maintainAspectRatio: false,
              scales: {
                x: { ticks: { maxTicksLimit: 6 } },
                y: { beginAtZero: false }
              },
              plugins: {
                legend: { display: false }
              }
            };
            const lineCfg = (data, label, color) => ({
              type: 'line',
              data: {
                labels: chartLabels,
                datasets: [{
                  label,
                  data,
                  tension: 0.3,
                  borderColor: color,
                  backgroundColor: color + '33',
                  fill: true,
                  pointRadius: 2,
                  pointHoverRadius: 4,
                }]
              },
              options: commonOptions
            });

            build('chart-temp', lineCfg(chartSeries.temperature, 'Temperature', '#4ab846'));
            build('chart-humidity', lineCfg(chartSeries.humidity, 'Humidity', '#3b82f6'));
            build('chart-pressure', lineCfg(chartSeries.pressure, 'Pressure', '#8b5cf6'));
            build('chart-light', lineCfg(chartSeries.light, 'Light', '#f59e0b'));
            build('chart-gas', lineCfg(chartSeries.gas, 'Gas', '#ef4444'));
          });
        </script>
      </section>
      <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>
  <?php include 'assets/footer.php'; ?>
 </body>
 </html>