<?php
// data_transfer.php for PIF — manual measurement entry form (admin testing).
// The Pi station POSTs to receive_data.php instead.
include 'CommonCode.php';
requireAdmin();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $station_serial = isset($_POST['station_serial']) ? trim($_POST['station_serial']) : '';
    $timestamp      = isset($_POST['timestamp'])      ? trim($_POST['timestamp'])      : '';
    $temperature    = isset($_POST['temperature'])    ? $_POST['temperature']          : '';
    $humidity       = isset($_POST['humidity'])       ? $_POST['humidity']             : '';
    $pressure       = isset($_POST['pressure'])       ? $_POST['pressure']             : '';
    $light          = isset($_POST['light'])          ? $_POST['light']                : '';
    $gas            = isset($_POST['gas'])            ? $_POST['gas']                  : '';

    $errors = [];

    // Validation
    if ($station_serial === '') $errors[] = 'station is required';
    if ($timestamp === '')      $errors[] = 'timestamp is required';
    if (!is_numeric($temperature)) $errors[] = 'temperature must be numeric';
    if (!is_numeric($humidity))    $errors[] = 'humidity must be numeric';
    if (!is_numeric($pressure))    $errors[] = 'pressure must be numeric';
    if (!is_numeric($light))       $errors[] = 'light must be numeric';
    if (!is_numeric($gas))         $errors[] = 'gas must be numeric';

    // Normalize timestamp to MySQL DATETIME format
    $tsFormatted = '';
    if ($timestamp !== '') {
        $t = strtotime(str_replace('T', ' ', $timestamp));
        if ($t === false) {
            $errors[] = 'timestamp format is invalid';
        } else {
            $tsFormatted = date('Y-m-d H:i:s', $t);
        }
    }

    if (empty($errors)) {
        // Verify station exists
        $stmt = $conn->prepare("SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?");
        $stmt->bind_param("s", $station_serial);
        $stmt->execute();
        $result = $stmt->get_result();
        $stationExists = $result->num_rows > 0;
        $stmt->close();

        if (!$stationExists) {
            $errors[] = 'Unknown station serial';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO measurement
                    (temperature, humidity, pressure, light, gas, timestamp, fk_station_records)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $tempF = (float)$temperature;
            $humF  = (float)$humidity;
            $prsF  = (float)$pressure;
            $lgtF  = (float)$light;
            $gasF  = (float)$gas;
            $stmt->bind_param("dddddss", $tempF, $humF, $prsF, $lgtF, $gasF, $tsFormatted, $station_serial);

            if ($stmt->execute()) {
                $newID = $conn->insert_id;
                $stmt->close();
                $message = 'Data inserted successfully (id ' . $newID . ').';
                $message_type = 'success';
            } else {
                $stmt->close();
                $errors[] = 'Database error during insert';
            }
        }
    }

    if (!empty($errors)) {
        $message = implode(' / ', $errors);
        $message_type = 'error';
    }
}

// List stations for the dropdown
$stations = [];
$stmtAll = $conn->prepare("SELECT pk_serialNumber, name FROM station ORDER BY pk_serialNumber");
if ($stmtAll) {
    $stmtAll->execute();
    $r = $stmtAll->get_result();
    while ($row = $r->fetch_assoc()) $stations[] = $row;
    $stmtAll->close();
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - Manual Data Entry</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
  <?php NavigationBar('admin'); ?>

  <div class="container">
    <div class="card" style="max-width:560px; margin:1rem auto;">
      <h1>Manual Measurement Entry</h1>
      <p style="color:var(--muted); font-size:.82rem; margin-bottom:1rem;">
        Admin-only test entry. Stations should POST to <code>receive_data.php</code> with
        <code>station_serial</code>, <code>timestamp</code>, <code>temperature</code>,
        <code>humidity</code>, <code>pressure</code>, <code>light</code>, <code>gas</code>.
      </p>

      <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-row">
          <label>Station:</label>
          <select name="station_serial" required style="flex:1;">
            <option value="">-- Select station --</option>
            <?php foreach ($stations as $st): ?>
              <option value="<?php echo htmlspecialchars($st['pk_serialNumber']); ?>"><?php echo htmlspecialchars($st['pk_serialNumber'] . ($st['name'] ? ' - ' . $st['name'] : '')); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <label>Timestamp:</label>
          <input type="datetime-local" name="timestamp" value="<?php echo date('Y-m-d\TH:i'); ?>" required style="flex:1;" />
        </div>

        <div class="form-row">
          <label>Temperature (&deg;C):</label>
          <input type="number" step="0.01" name="temperature" placeholder="22.50" required style="flex:1;" />
        </div>

        <div class="form-row">
          <label>Humidity (%):</label>
          <input type="number" step="0.01" name="humidity" placeholder="45.00" required style="flex:1;" />
        </div>

        <div class="form-row">
          <label>Pressure (hPa):</label>
          <input type="number" step="0.01" name="pressure" placeholder="1013.25" required style="flex:1;" />
        </div>

        <div class="form-row">
          <label>Light (lux):</label>
          <input type="number" step="0.01" name="light" placeholder="350.00" required style="flex:1;" />
        </div>

        <div class="form-row">
          <label>Gas/CO2 (ppm):</label>
          <input type="number" step="0.01" name="gas" placeholder="450.00" required style="flex:1;" />
        </div>

        <div class="form-row">
          <button type="submit">Submit Measurement</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
