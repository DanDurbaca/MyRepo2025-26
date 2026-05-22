<?php
include_once("CommonCode.php");
NavigationBar1("Measurements");
requireLogin();

$me = getCurrentUser();
$isAdmin = (($_SESSION["role"] ?? "User") === "Admin");

// Read selected filters from GET so the page can be reloaded simply (no JS fetch needed)
$selectedStation = $_GET['station'] ?? ''; //
$selectedRange = $_GET['range'] ?? '';

// Prepare station list for dropdown
if ($isAdmin) {
  $stmt = $connection->prepare("SELECT pk_serialNumber, name FROM station ORDER BY pk_serialNumber ASC");
  $stmt->execute();
  $stations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
  $stations = getUserStations($me);
}

// Load measurements server-side and reduce to latest per 5-minute bucket (max 15 points)
$rows = getMeasurements($me, $isAdmin, $selectedStation, $selectedRange, 2000);
$bucketSeconds = 5 * 60; // 5 minutes
$maxPoints = 15;

// newest first, keep first seen per bucket
usort($rows, fn($a,$b) => strcmp($b['timestamp'],$a['timestamp']));
$buckets = [];
foreach ($rows as $r) {
  $idx = (int)floor(strtotime($r['timestamp']) / $bucketSeconds);
  if (!isset($buckets[$idx])) {
    $r['timestamp'] = date('Y-m-d H:i:s', $idx * $bucketSeconds); // normalize to bucket start
    $buckets[$idx] = $r;
  }
}

$keys = array_keys($buckets);
rsort($keys, SORT_NUMERIC); // newest first
$keys = array_slice($keys, 0, $maxPoints);
sort($keys, SORT_NUMERIC); // oldest->newest for plotting

$rows = [];
foreach ($keys as $k) $rows[] = $buckets[$k];

// Finally: ensure we embed at most $maxPoints total (user requested "only up to 15 output")
$maxPoints = 15;
if (count($rows) > $maxPoints) {
  $n = count($rows);
  $groupSize = (int)ceil($n / $maxPoints);
  $reduced = [];
  $acc = null; $accCount = 0;
  foreach ($rows as $r) {
    if ($acc === null) {
      $acc = [
        'ts_sum' => 0,
        'count' => 0,
        'temperature_sum' => 0.0, 'temperature_count' => 0,
        'humidity_sum' => 0.0, 'humidity_count' => 0,
        'pressure_sum' => 0.0, 'pressure_count' => 0,
        'light_sum' => 0.0, 'light_count' => 0,
        'gas_sum' => 0.0, 'gas_count' => 0,
        'fk_station_records' => $r['fk_station_records'] ?? null,
      ];
    }
    $t = strtotime($r['timestamp']);
    $acc['ts_sum'] += $t;
    $acc['count']++;
    if (isset($r['temperature']) && $r['temperature'] !== null) { $acc['temperature_sum'] += $r['temperature']; $acc['temperature_count']++; }
    if (isset($r['humidity']) && $r['humidity'] !== null)    { $acc['humidity_sum'] += $r['humidity']; $acc['humidity_count']++; }
    if (isset($r['pressure']) && $r['pressure'] !== null)    { $acc['pressure_sum'] += $r['pressure']; $acc['pressure_count']++; }
    if (isset($r['light']) && $r['light'] !== null)          { $acc['light_sum'] += $r['light']; $acc['light_count']++; }
    if (isset($r['gas']) && $r['gas'] !== null)              { $acc['gas_sum'] += $r['gas']; $acc['gas_count']++; }
    $accCount++;

    if ($accCount >= $groupSize) {
      $avgTs = (int)round($acc['ts_sum'] / $acc['count']);
      $reduced[] = [
        'timestamp' => date('Y-m-d H:i:s', $avgTs),
        'fk_station_records' => $acc['fk_station_records'],
        'temperature' => $acc['temperature_count'] ? ($acc['temperature_sum'] / $acc['temperature_count']) : null,
        'humidity' => $acc['humidity_count'] ? ($acc['humidity_sum'] / $acc['humidity_count']) : null,
        'pressure' => $acc['pressure_count'] ? ($acc['pressure_sum'] / $acc['pressure_count']) : null,
        'light' => $acc['light_count'] ? ($acc['light_sum'] / $acc['light_count']) : null,
        'gas' => $acc['gas_count'] ? ($acc['gas_sum'] / $acc['gas_count']) : null,
      ];
      $acc = null; $accCount = 0;
    }
  }
  // leftover
  if ($acc !== null && $acc['count'] > 0) {
    $avgTs = (int)round($acc['ts_sum'] / $acc['count']);
    $reduced[] = [
      'timestamp' => date('Y-m-d H:i:s', $avgTs),
      'fk_station_records' => $acc['fk_station_records'],
      'temperature' => $acc['temperature_count'] ? ($acc['temperature_sum'] / $acc['temperature_count']) : null,
      'humidity' => $acc['humidity_count'] ? ($acc['humidity_sum'] / $acc['humidity_count']) : null,
      'pressure' => $acc['pressure_count'] ? ($acc['pressure_sum'] / $acc['pressure_count']) : null,
      'light' => $acc['light_count'] ? ($acc['light_sum'] / $acc['light_count']) : null,
      'gas' => $acc['gas_count'] ? ($acc['gas_sum'] / $acc['gas_count']) : null,
    ];
  }
  // Ensure we didn't exceed max due to rounding (trim if needed)
  if (count($reduced) > $maxPoints) $reduced = array_slice($reduced, 0, $maxPoints);
  $rows = $reduced;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Portable Indoor Feedback</title>
  <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<h1>Dashboard</h1>

<form method="get" action="dashboard.php">
  <div>
    <label for="stationSelect">Station:</label>
    <select id="stationSelect" name="station">
      <option value="">-- All --</option>
      <?php foreach ($stations as $s): ?>
        <option value="<?php echo htmlspecialchars($s['pk_serialNumber']); ?>" <?php if ($selectedStation === $s['pk_serialNumber']) echo 'selected'; ?>><?php echo htmlspecialchars($s['pk_serialNumber'] . ' - ' . ($s['name'] ?? '')); ?></option>
      <?php endforeach; ?>
    </select>

    <label for="rangeSelect">Date range:</label>
    <select id="rangeSelect" name="range">
      <option value="" <?php if ($selectedRange === '') echo 'selected'; ?>>All</option>
      <option value="today" <?php if ($selectedRange === 'today') echo 'selected'; ?>>Today</option>
      <option value="24h" <?php if ($selectedRange === '24h') echo 'selected'; ?>>Last 24 hours</option>
      <option value="7d" <?php if ($selectedRange === '7d') echo 'selected'; ?>>Last 7 days</option>
    </select>

    <button type="submit">Refresh</button>
  </div>
</form>

<h2>Temperature & Humidity</h2>
<canvas id="tempHumCanvas" height="150"></canvas>

<h2>Pressure, Light & Gas</h2>
<canvas id="otherCanvas" height="250"></canvas>

<script>
const tempCtx = document.getElementById('tempHumCanvas').getContext('2d');
const otherCtx = document.getElementById('otherCanvas').getContext('2d');

let tempChart = new Chart(tempCtx, {
  type: 'line',
  data: { labels: [], datasets: [
    { label: 'Temperature (°C)', data: [], borderColor: 'rgb(255,99,132)', yAxisID: 'yTemp'},
    { label: 'Humidity (%)', data: [], borderColor: 'rgb(54,162,235)', yAxisID: 'yHum'}
  ]},
  options: {
    interaction: { mode: 'index', intersect: false },
    stacked: false,
    scales: {
      yTemp: { type: 'linear', position: 'left', min: 0, max: 35, title: {display:true, text:'°C'} },
      yHum: { type: 'linear', position: 'right', min: 0, max: 60, grid: { drawOnChartArea: false }, title: {display:true, text:'%'} }
    }
  }
});

let otherChart = new Chart(otherCtx, {
  type: 'line',
  data: { labels: [], datasets: [
    { label: 'Pressure (hPa)', data: [], borderColor: 'rgb(75,192,192)', yAxisID: 'yPres' },
    { label: 'Light (lux)', data: [], borderColor: 'rgb(153,102,255)', yAxisID: 'yLight' },
    { label: 'Gas (ppm)', data: [], borderColor: 'rgb(255,159,64)', yAxisID: 'yGas' }
  ]},
  options: {
    interaction: { mode: 'index', intersect: false },
    scales: {
      yPres: { type: 'linear', position: 'left', min: 0, max: 1400, title: {display:true, text:'hPa'} },
      yLight: { type: 'linear', position: 'right', min: 0, max: 1400, grid: { drawOnChartArea: false }, title: {display:true, text:'lux'} },
      yGas: { type: 'linear', position: 'right', min: 0, max: 2000, grid: { drawOnChartArea: false, drawBorder: false }, title: {display:true, text:'ppm'} }
    }
  }
});

function toLocalLabel(ts) {
  // expects 'YYYY-MM-DD HH:MM:SS' — keep it as-is for now
  return ts;
}

function updateCharts(rows) {
  // sort ascending by timestamp
  rows.sort((a,b) => new Date(a.timestamp) - new Date(b.timestamp));

  const labels = rows.map(r => toLocalLabel(r.timestamp));
  tempChart.data.labels = labels;
  tempChart.data.datasets[0].data = rows.map(r => r.temperature === null ? null : Number(r.temperature));
  tempChart.data.datasets[1].data = rows.map(r => r.humidity === null ? null : Number(r.humidity));
  tempChart.update();

  otherChart.data.labels = labels;
  otherChart.data.datasets[0].data = rows.map(r => r.pressure === null ? null : Number(r.pressure));
  otherChart.data.datasets[1].data = rows.map(r => r.light === null ? null : Number(r.light));
  otherChart.data.datasets[2].data = rows.map(r => r.gas === null ? null : Number(r.gas));
  otherChart.update();
}

// Measurements are embedded server-side for simplicity (no client API call).
const embeddedRows = <?php echo json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
updateCharts(embeddedRows);
</script>

</body>
</html>
