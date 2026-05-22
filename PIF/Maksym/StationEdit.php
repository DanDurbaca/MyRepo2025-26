<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];
$serial = '';
$station_name = '';
$description = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_station'])) {
    $serial       = isset($_POST['serial_number']) ? trim($_POST['serial_number']) : '';
    $station_name = isset($_POST['station_name'])  ? trim($_POST['station_name'])  : '';
    $description  = isset($_POST['description'])   ? trim($_POST['description'])   : '';

    if ($serial !== '' && $station_name !== '') {
        $st = getStationBySerial($serial);
        if ($st && $st['fk_user_owns'] === $username) {
            if (updateStation($serial, $username, $station_name, $description)) {
                header("Location: Stations.php");
                exit();
            } else {
                $error_message = 'Database error while saving.';
            }
        } else {
            $error_message = 'You are not the owner of this station.';
        }
    } else {
        $error_message = 'Serial number and station name are required.';
    }
}

if (isset($_GET['sn']) && trim($_GET['sn']) !== '') {
    $serial = trim($_GET['sn']);
    $st = getStationBySerial($serial);
    if ($st && $st['fk_user_owns'] === $username) {
        $station_name = $st['name'] ?? '';
        $description  = $st['description'] ?? '';
    } else {
        $serial = '';
        $error_message = 'Station not found or you are not the owner.';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('edit_station'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
  <?php NavigationBar('stations'); ?>

  <div class="container">
    <div class="card" style="max-width:640px; margin:0 auto;">
      <h1><?php echo t('edit_station'); ?></h1>

      <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>

      <?php if ($serial !== ''): ?>
      <form method="POST" action="StationEdit.php">
        <div class="form-row">
          <label for="serial_number"><?php echo t('serial_number'); ?>:</label>
          <input type="text" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($serial); ?>" readonly />
        </div>

        <div class="form-row">
          <label for="station_name"><?php echo t('station_name'); ?>:</label>
          <input type="text" id="station_name" name="station_name" maxlength="100" value="<?php echo htmlspecialchars($station_name); ?>" required style="flex:1;" />
        </div>

        <div class="form-row">
          <label for="description"><?php echo t('description'); ?>:</label>
          <textarea id="description" name="description" rows="3" style="flex:1;"><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="form-row">
          <button type="submit" name="save_station"><?php echo t('save_changes'); ?></button>
          <button type="button" onclick="location.href='Stations.php'"><?php echo t('cancel'); ?></button>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
