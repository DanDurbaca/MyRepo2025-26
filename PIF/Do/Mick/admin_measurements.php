<?php
require_once __DIR__ . '/db.php';
require_login();
if (!is_admin()) { header('Location: welcome.php'); exit; }
$mysqli = db_connect();

// admin view/add measurements using env_record and env_station
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_measurement'])) {
  $station_serial = $_POST['station_serial'] ?? '';
  $measured_at = str_replace('T',' ',$_POST['measured_at']);
  $temperature = $_POST['temperature'] !== '' ? floatval($_POST['temperature']) : null;
  $airpressure = $_POST['airpressure'] !== '' ? floatval($_POST['airpressure']) : null;
  $humidity = $_POST['humidity'] !== '' ? floatval($_POST['humidity']) : null;
  $light = $_POST['light'] !== '' ? floatval($_POST['light']) : null;
  $gas = $_POST['gas'] !== '' ? floatval($_POST['gas']) : null;
  $stmt = $mysqli->prepare("INSERT INTO env_record (rec_station,rec_timestamp,rec_temperature,rec_airpressure,rec_humidity,rec_light,rec_gas) VALUES (?,?,?,?,?,?,?)");
  $stmt->bind_param('ssddddd', $station_serial, $measured_at, $temperature, $airpressure, $humidity, $light, $gas);
  $stmt->execute(); $msg = 'Measurement added';
}

// view and filter
$station_serial = isset($_GET['station_serial']) ? $mysqli->real_escape_string($_GET['station_serial']) : '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$where = [];
if ($station_serial) $where[] = "r.rec_station='".$station_serial."'";
if ($from) $where[] = "r.rec_timestamp >= '".$mysqli->real_escape_string($from)."'";
if ($to) $where[] = "r.rec_timestamp <= '".$mysqli->real_escape_string($to)."'";

$sql = "SELECT r.rec_id, r.rec_timestamp, r.rec_temperature, r.rec_airpressure, r.rec_humidity, r.rec_light, r.rec_gas, s.st_serial FROM env_record r LEFT JOIN env_station s ON s.st_serial = r.rec_station";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY r.rec_timestamp DESC LIMIT 500';
$res = $mysqli->query($sql);

$stations = $mysqli->query("SELECT st_serial FROM env_station ORDER BY st_serial");
?>
<?php include 'header.php'; ?>

<h2>Admin - Measurements</h2>
<?php if (!empty($msg)) echo '<div class="notice">'.htmlspecialchars($msg).'</div>'; ?>

<h3>Add measurement</h3>
<form method="post">
  <label>Station
    <?php if ($has_station_serial): ?>
      <select name="station_serial">
        <?php while ($s = $stations->fetch_assoc()) echo '<option value="'.htmlspecialchars($s['serial']).'">'.htmlspecialchars($s['serial'])."</option>"; ?>
      </select>
    <?php else: ?>
      <select name="station_id">
        <?php while ($s = $stations->fetch_assoc()) echo '<option value="'.intval($s['id']).'">'.htmlspecialchars($s['serial'])."</option>"; ?>
      </select>
    <?php endif; ?>
  </label>
  <?php if ($has_sensor_fields): ?>
    <label>Temperature<input type="number" step="any" name="temperature"></label>
    <label>Air pressure<input type="number" step="any" name="airpressure"></label>
    <label>Humidity<input type="number" step="any" name="humidity"></label>
    <label>Light<input type="number" step="any" name="light"></label>
    <label>Gas<input type="number" step="any" name="gas"></label>
  <?php else: ?>
    <label>Value<input type="number" step="any" name="value" required></label>
  <?php endif; ?>
  <label>Measured at<input type="datetime-local" name="measured_at" required></label>
  <button class="btn" name="add_measurement">Add</button>
</form>

<h3>View/Delete measurements</h3>
<form method="get">
  <label>Station
    <?php if ($has_station_serial): ?>
      <select name="station_serial">
        <option value="">All</option>
        <?php while ($s = $stations->fetch_assoc()) echo '<option value="'.htmlspecialchars($s['serial']).'"'.($station_serial===$s['serial']? ' selected':'').'>'.htmlspecialchars($s['serial'])."</option>"; ?>
      </select>
    <?php else: ?>
      <select name="station_id">
        <option value="0">All</option>
        <?php while ($s = $stations->fetch_assoc()) echo '<option value="'.intval($s['id']).'"'.($station_id===$s['id']? ' selected':'').'>'.htmlspecialchars($s['serial'])."</option>"; ?>
      </select>
    <?php endif; ?>
  </label>
  <label>From<input type="datetime-local" name="from"></label>
  <label>To<input type="datetime-local" name="to"></label>
  <button class="btn" type="submit">Filter</button>
</form>

<?php if ($res && $res->num_rows): ?>
  <table>
    <?php if ($has_sensor_fields): ?>
      <tr><th>ID</th><th>Station</th><th>Measured at</th><th>Temp</th><th>Airpress</th><th>Humidity</th><th>Light</th><th>Gas</th><th>Action</th></tr>
      <?php while ($m = $res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $m['id']; ?></td>
          <td><?php echo htmlspecialchars($m['serial']); ?></td>
          <td><?php echo htmlspecialchars($m['measured_at']); ?></td>
          <td><?php echo htmlspecialchars($m['temperature']); ?></td>
          <td><?php echo htmlspecialchars($m['airpressure']); ?></td>
          <td><?php echo htmlspecialchars($m['humidity']); ?></td>
          <td><?php echo htmlspecialchars($m['light']); ?></td>
          <td><?php echo htmlspecialchars($m['gas']); ?></td>
          <td>
            <form method="post" action="admin_measurements_delete.php" style="display:inline">
              <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
              <button class="btn danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><th>ID</th><th>Station</th><th>Measured at</th><th>Value</th><th>Action</th></tr>
      <?php while ($m = $res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $m['id']; ?></td>
          <td><?php echo htmlspecialchars($m['serial']); ?></td>
          <td><?php echo htmlspecialchars($m['measured_at']); ?></td>
          <td><?php echo htmlspecialchars($m['value']); ?></td>
          <td>
            <form method="post" action="admin_measurements_delete.php" style="display:inline">
              <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
              <button class="btn danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php endif; ?>
  </table>
<?php else: ?>
  <p class="muted">No measurements found.</p>
<?php endif; ?>
