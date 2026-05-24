<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];
$success_message = '';
$error_message = '';

// Add station: claim by serial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_station'])) {
    $serial = isset($_POST['serial_number']) ? trim($_POST['serial_number']) : '';
    $name   = isset($_POST['station_name'])  ? trim($_POST['station_name'])  : '';
    $desc   = isset($_POST['description'])   ? trim($_POST['description'])   : '';

    if ($serial === '') {
        $error_message = 'Serial number is required.';
    } elseif ($name === '') {
        $error_message = 'Station name is required.';
    } else {
        $st = getStationBySerial($serial);
        if (!$st) {
            $error_message = 'No station found with that serial number.';
        } elseif (!is_null($st['fk_user_owns'])) {
            $error_message = 'Station already has an owner.';
        } else {
            if (claimStation($serial, $username)) {
                updateStation($serial, $username, $name, $desc);
                $success_message = 'Station claimed successfully.';
            } else {
                $error_message = 'Database error while claiming station.';
            }
        }
    }
}

// Delete station: release ownership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_station'])) {
    $serial = isset($_POST['serial_number']) ? trim($_POST['serial_number']) : '';
    if ($serial !== '') {
        if (releaseStation($serial, $username)) {
            $success_message = 'Station removed successfully.';
        } else {
            $error_message = 'Unable to remove station (not found or not owned).';
        }
    }
}

$myStations = fetchStationsForUser($username);
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('stations'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
  <?php NavigationBar('stations'); ?>

  <div class="container">
    <div class="page-title"><?php echo t('stations'); ?></div>
    <div class="page-sub"><?php echo t('stations_desc'); ?></div>

    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-title"><?php echo t('register_station'); ?></div>

      <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>
      <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-row">
          <input type="text" name="serial_number" placeholder="<?php echo t('serial_number'); ?> (e.g. SN-1001)" required />
          <input type="text" name="station_name" placeholder="<?php echo t('station_name'); ?>" maxlength="100" required />
          <button type="submit" name="add_station"><?php echo t('register_station'); ?></button>
        </div>
        <div class="form-row">
          <textarea name="description" placeholder="<?php echo t('description'); ?> (<?php echo t('optional'); ?>)" rows="2" style="width:100%;"></textarea>
        </div>
      </form>
    </div>

    <?php if (count($myStations) === 0): ?>
      <div class="card"><div class="empty"><?php echo t('no_stations'); ?></div></div>
    <?php else: ?>
      <div class="list">
        <?php foreach ($myStations as $s): ?>
          <div class="list-item">
            <div>
              <div style="font-weight:600;"><?php echo htmlspecialchars($s['name'] ? $s['name'] : $s['pk_serialNumber']); ?></div>
              <code class="station-sn" style="margin-top:.2rem; display:inline-block;"><?php echo htmlspecialchars($s['pk_serialNumber']); ?></code>
              <?php if (!empty($s['description'])): ?>
                <div style="color:var(--muted);font-size:.78rem;margin-top:.3rem;"><?php echo htmlspecialchars($s['description']); ?></div>
              <?php endif; ?>
            </div>
            <div>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="serial_number" value="<?php echo htmlspecialchars($s['pk_serialNumber']); ?>" />
                <button type="button" onclick="location.href='StationEdit.php?sn=<?php echo urlencode($s['pk_serialNumber']); ?>'" class="btn-sm"><?php echo t('edit'); ?></button>
                <button type="submit" name="delete_station" class="danger btn-sm" onclick="return confirm('Are you sure you want to remove this station?');"><?php echo t('delete'); ?></button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
