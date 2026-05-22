<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';
require_login();
$mysqli = db_connect();
$uid = current_user_id(); // username string

// Get current language from settings
$res_lang = $mysqli->query("SELECT language FROM env_user_settings WHERE usr_ref='". $mysqli->real_escape_string($uid) ."' LIMIT 1");
$lang = 'en';
if ($res_lang && $res_lang->num_rows) {
    $row = $res_lang->fetch_assoc();
    $lang = $row['language'];
}
$t = get_translations($lang);

// simplified: use env_record (rec_*) and env_station (st_serial)
$station_serial = isset($_GET['station_serial']) ? $mysqli->real_escape_string($_GET['station_serial']) : '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// fetch user's stations for selector
$res_stations = $mysqli->query("SELECT st_serial, st_label FROM env_station WHERE st_owner='". $mysqli->real_escape_string($uid) ."'");

$where = [];
if ($station_serial) {
    $where[] = "r.rec_station='" . $station_serial . "'";
} else {
    // limit to user's stations
    $serials = [];
    while ($r = $res_stations->fetch_assoc()) $serials[] = $mysqli->real_escape_string($r['st_serial']);
    if ($serials) {
        $where[] = "r.rec_station IN ('".implode("','", $serials)."')";
    } else {
        $where[] = '1=0';
    }
    // re-query stations for form
    $res_stations = $mysqli->query("SELECT st_serial, st_label FROM env_station WHERE st_owner='". $mysqli->real_escape_string($uid) ."'");
}
if ($from) $where[] = "r.rec_timestamp >= '".$mysqli->real_escape_string($from)."'";
if ($to) $where[] = "r.rec_timestamp <= '".$mysqli->real_escape_string($to)."'";

$sql = "SELECT r.rec_id, r.rec_timestamp, r.rec_temperature, r.rec_pressure, r.rec_humidity, r.rec_light, r.rec_gas, s.st_serial, s.st_label FROM env_record r LEFT JOIN env_station s ON s.st_serial = r.rec_station";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY r.rec_timestamp DESC LIMIT 200';

$res = $mysqli->query($sql);

// Get data for charts
$chart_data = [];
if ($res && $res->num_rows) {
    $res->data_seek(0);
    $temperatures = [];
    $humidities = [];
    $timestamps = [];
    
    while ($m = $res->fetch_assoc()) {
        if ($m['rec_temperature'] !== null) {
            $temperatures[] = $m['rec_temperature'];
            $humidities[] = $m['rec_humidity'];
            $timestamps[] = $m['rec_timestamp'];
        }
    }
    
    if (!empty($temperatures)) {
        // Reverse to show chronological order
        $temperatures = array_reverse($temperatures);
        $humidities = array_reverse($humidities);
        $timestamps = array_reverse($timestamps);
        
        $chart_data = [
            'labels' => json_encode($timestamps),
            'temperatures' => json_encode($temperatures),
            'humidities' => json_encode($humidities)
        ];
    }
    
    // Re-query for table display
    $res = $mysqli->query($sql);
}
?>
<?php include 'header.php'; ?>

<h2><?php echo htmlspecialchars($t['measurements_heading']); ?></h2>

<form method="get">
  <label><?php echo htmlspecialchars($t['measurements_station']); ?>
    <select name="station_serial">
      <option value=""><?php echo htmlspecialchars($t['measurements_station_all']); ?></option>
      <?php 
      $res_stations = $mysqli->query("SELECT st_serial, st_label FROM env_station WHERE st_owner='". $mysqli->real_escape_string($uid) ."'");
      while ($s = $res_stations->fetch_assoc()): ?>
        <option value="<?php echo htmlspecialchars($s['st_serial']); ?>" <?php if ($station_serial===$s['st_serial']) echo 'selected'; ?>><?php echo htmlspecialchars($s['st_serial'].' - '.$s['st_label']); ?></option>
      <?php endwhile; ?>
    </select>
  </label>
  <label><?php echo htmlspecialchars($t['measurements_from']); ?>
    <input type="datetime-local" name="from" value="<?php echo htmlspecialchars($from); ?>">
  </label>
  <label><?php echo htmlspecialchars($t['measurements_to']); ?>
    <input type="datetime-local" name="to" value="<?php echo htmlspecialchars($to); ?>">
  </label>
  <button class="btn" type="submit"><?php echo htmlspecialchars($t['measurements_filter']); ?></button>
</form>

<?php if (!empty($chart_data)): ?>
<!-- Charts -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 24px; max-height: 400px; overflow: hidden;">
  <div class="chart-container" style="height: 350px; position: relative;">
    <h3><?php echo htmlspecialchars($t['measurements_chart_temperature']); ?></h3>
    <canvas id="temperatureChart" style="max-height: 300px;"></canvas>
  </div>
  <div class="chart-container" style="height: 350px; position: relative;">
    <h3><?php echo htmlspecialchars($t['measurements_chart_humidity']); ?></h3>
    <canvas id="humidityChart" style="max-height: 300px;"></canvas>
  </div>
</div>

<script>
const chartLabels = <?php echo $chart_data['labels']; ?>;
const temperatureData = <?php echo $chart_data['temperatures']; ?>;
const humidityData = <?php echo $chart_data['humidities']; ?>;

// Temperature Chart
const tempCtx = document.getElementById('temperatureChart').getContext('2d');
new Chart(tempCtx, {
  type: 'line',
  data: {
    labels: chartLabels,
    datasets: [{
      label: '<?php echo htmlspecialchars($t['measurements_chart_temperature']); ?>',
      data: temperatureData,
      borderColor: '#ff6b6b',
      backgroundColor: 'rgba(255, 107, 107, 0.1)',
      borderWidth: 2,
      tension: 0.4,
      fill: true,
      pointRadius: 3,
      pointHoverRadius: 5
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
      padding: 0
    },
    plugins: {
      legend: { display: true, position: 'top' }
    },
    scales: {
      x: { display: true },
      y: { beginAtZero: false }
    }
  }
});

// Humidity Chart
const humidityCtx = document.getElementById('humidityChart').getContext('2d');
new Chart(humidityCtx, {
  type: 'line',
  data: {
    labels: chartLabels,
    datasets: [{
      label: '<?php echo htmlspecialchars($t['measurements_chart_humidity']); ?>',
      data: humidityData,
      borderColor: '#4ecdc4',
      backgroundColor: 'rgba(78, 205, 196, 0.1)',
      borderWidth: 2,
      tension: 0.4,
      fill: true,
      pointRadius: 3,
      pointHoverRadius: 5
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
      padding: 0
    },
    plugins: {
      legend: { display: true, position: 'top' }
    },
    scales: {
      x: { display: true },
      y: { beginAtZero: true, max: 100 }
    }
  }
});
</script>
<?php endif; ?>

<?php if ($res && $res->num_rows): ?>
  <h3 style="margin-top: 24px;">Data Table</h3>
  <table>
    <tr>
      <th><?php echo htmlspecialchars($t['measurements_table_station']); ?></th>
      <th><?php echo htmlspecialchars($t['measurements_table_recorded']); ?></th>
      <th><?php echo htmlspecialchars($t['measurements_table_temp']); ?></th>
      <th><?php echo htmlspecialchars($t['measurements_table_pressure']); ?></th>
      <th><?php echo htmlspecialchars($t['measurements_table_humidity']); ?></th>
      <th><?php echo htmlspecialchars($t['measurements_table_light']); ?></th>
      <th><?php echo htmlspecialchars($t['measurements_table_gas']); ?></th>
    </tr>
    <?php while ($m = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($m['st_serial'] ?: 'Unknown'); ?></td>
        <td><?php echo htmlspecialchars($m['rec_timestamp']); ?></td>
        <td><?php echo $m['rec_temperature'] !== null ? htmlspecialchars($m['rec_temperature']) : '—'; ?></td>
        <td><?php echo $m['rec_pressure'] !== null ? htmlspecialchars($m['rec_pressure']) : '—'; ?></td>
        <td><?php echo $m['rec_humidity'] !== null ? htmlspecialchars($m['rec_humidity']) : '—'; ?></td>
        <td><?php echo $m['rec_light'] !== null ? htmlspecialchars($m['rec_light']) : '—'; ?></td>
        <td><?php echo $m['rec_gas'] !== null ? htmlspecialchars($m['rec_gas']) : '—'; ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p class="muted"><?php echo htmlspecialchars($t['measurements_no_records']); ?></p>
<?php endif; ?>
