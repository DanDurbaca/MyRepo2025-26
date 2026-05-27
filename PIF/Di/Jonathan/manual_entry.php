<?php
// manual_entry.php - Manual measurement entry form (L5 requirement)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/_header.php';

// Detect AJAX requests (client sets X-Requested-With)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Only allow logged-in users to manually add measurements
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $serial = trim($_POST['station_serial'] ?? '');
        $temperature = isset($_POST['temperature']) ? (float)$_POST['temperature'] : null;
        $humidity = isset($_POST['humidity']) ? (float)$_POST['humidity'] : null;
        $pressure = isset($_POST['pressure']) ? (float)$_POST['pressure'] : null;
        $light = isset($_POST['light']) ? (float)$_POST['light'] : null;
        $gas = isset($_POST['gas']) ? (float)$_POST['gas'] : null;
        $timestamp_raw = trim($_POST['timestamp'] ?? '');

        if ($serial === '') $errors[] = 'Station serial is required.';
        if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $serial)) $errors[] = 'Invalid serial format.';

        // server-side ranges (match API)
        if ($temperature === null || $temperature < -50 || $temperature > 60) $errors[] = 'Temperature out of range (-50..60).';
        if ($humidity === null || $humidity < 0 || $humidity > 100) $errors[] = 'Humidity out of range (0..100).';
        if ($pressure === null || $pressure < 300 || $pressure > 1100) $errors[] = 'Pressure out of range (300..1100).';
        if ($light === null || $light < 0 || $light > 100000) $errors[] = 'Light out of range (0..100000).';
        if ($gas === null || $gas < 0 || $gas > 10000) $errors[] = 'Gas out of range (0..10000).';

        // normalize timestamp
        if ($timestamp_raw !== '') {
            try {
                $dt = new DateTimeImmutable($timestamp_raw);
                $dt = $dt->setTimezone(new DateTimeZone('UTC'));
                $timestamp = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $errors[] = 'Invalid timestamp format.';
            }
        } else {
            $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        }

        if (empty($errors)) {
            try {
                // Ensure station exists
                $chk = $pdo->prepare('SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ? LIMIT 1');
                $chk->execute([$serial]);
                if (!$chk->fetch()) {
                    $errors[] = 'Unknown station serial.';
                } else {
                    $ins = $pdo->prepare('INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $ins->execute([$temperature, $humidity, $pressure, $light, $gas, $timestamp, $serial]);
                    $success = true;
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // If AJAX request, return JSON response and stop rendering the page
    if ($isAjax) {
        header('Content-Type: application/json');
        if (!empty($errors)) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
        } else {
            echo json_encode(['ok' => true, 'message' => 'Measurement saved']);
        }
        exit;
    }
}
?>
<div class="container">
    <h1>Manual Measurement Entry</h1>
    <?php if ($success): ?>
        <div class="alert alert-success">Measurement saved successfully.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul></div>
    <?php endif; ?>

    <form id="manualForm" method="post">
        <?php echo csrf_input(); ?>
        <div class="form-group">
            <label for="station_serial">Station Serial</label>
            <input id="station_serial" name="station_serial" class="form-control" required pattern="[A-Za-z0-9_\-]{1,64}" value="<?php echo htmlspecialchars($_POST['station_serial'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="temperature">Temperature (°C)</label>
            <input id="temperature" name="temperature" type="number" step="0.01" class="form-control" required min="-50" max="60" value="<?php echo htmlspecialchars($_POST['temperature'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="humidity">Humidity (%)</label>
            <input id="humidity" name="humidity" type="number" step="0.01" class="form-control" required min="0" max="100" value="<?php echo htmlspecialchars($_POST['humidity'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="pressure">Pressure (hPa)</label>
            <input id="pressure" name="pressure" type="number" step="0.01" class="form-control" required min="300" max="1100" value="<?php echo htmlspecialchars($_POST['pressure'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="light">Light (lux)</label>
            <input id="light" name="light" type="number" step="0.01" class="form-control" required min="0" max="100000" value="<?php echo htmlspecialchars($_POST['light'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="gas">Gas</label>
            <input id="gas" name="gas" type="number" step="0.01" class="form-control" required min="0" max="10000" value="<?php echo htmlspecialchars($_POST['gas'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="timestamp">Timestamp (optional, ISO 8601)</label>
            <input id="timestamp" name="timestamp" type="text" class="form-control" placeholder="YYYY-MM-DDTHH:MM:SSZ" value="<?php echo htmlspecialchars($_POST['timestamp'] ?? ''); ?>">
        </div>
        <button class="btn btn-primary" id="manualSubmit" type="submit">Submit Measurement</button>
        <span id="manualStatus" style="display:none;margin-left:8px;"></span>
    </form>
</div>

<script>
// Client-side submit handler: validates inputs and sends AJAX POST to save measurement
(function(){
    var form = document.getElementById('manualForm');
    var status = document.getElementById('manualStatus');
    form.addEventListener('submit', function(e){
        e.preventDefault();
        status.style.display = 'inline-block';
        status.textContent = 'Submitting...';
        var temp = parseFloat(document.getElementById('temperature').value);
        if (isNaN(temp) || temp < -50 || temp > 60) { alert('Temperature out of range'); status.style.display='none'; return; }

        var fd = new FormData(form);
        fetch(window.location.href, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
            body: fd,
            credentials: 'same-origin'
        }).then(function(resp){
            return resp.json();
        }).then(function(json){
            if (json.ok) {
                status.textContent = 'Saved.';
                form.reset();
                setTimeout(function(){ status.style.display='none'; }, 2000);
            } else {
                status.style.display = 'inline-block';
                status.textContent = 'Error: ' + (json.errors ? json.errors.join('; ') : (json.error || 'Unknown'));
            }
        }).catch(function(err){
            status.style.display = 'inline-block';
            status.textContent = 'Network error';
        });
    });
})();
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
