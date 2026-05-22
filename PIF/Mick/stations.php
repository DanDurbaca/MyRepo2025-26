<?php
require_once __DIR__ . '/db.php';
require_login();
$mysqli = db_connect();
$uid = current_user_id(); // username string (env_user.usr_name)

// Register a station by serial (assign unassigned station to current user)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_serial'])) {
  $serial = $mysqli->real_escape_string(trim($_POST['serial']));
  if ($serial === '') {
    $msg = 'Serial required';
  } else {
    $stmt = $mysqli->prepare("SELECT st_serial, st_owner FROM env_station WHERE st_serial=? LIMIT 1");
    $stmt->bind_param('s', $serial);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows) {
      $row = $res->fetch_assoc();
      if (!empty($row['st_owner'])) $msg = 'Station already assigned';
      else {
        $stmt2 = $mysqli->prepare("UPDATE env_station SET st_owner=? WHERE st_serial=?");
        $stmt2->bind_param('ss', $uid, $serial);
        $stmt2->execute();
        $msg = 'Station registered to you';
      }
    } else $msg = 'No station with that serial';
  }
}

// Edit station (label/description) owned by user (or admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_station'])) {
  $serial = $_POST['station_serial'];
  $label = $_POST['name'];
  $desc = $_POST['description'];
  if (is_admin()) {
    $stmt = $mysqli->prepare("UPDATE env_station SET st_label=?, st_description=? WHERE st_serial=?");
    $stmt->bind_param('sss', $label, $desc, $serial);
  } else {
    $stmt = $mysqli->prepare("UPDATE env_station SET st_label=?, st_description=? WHERE st_serial=? AND st_owner=?");
    $stmt->bind_param('ssss', $label, $desc, $serial, $uid);
  }
  $stmt->execute();
  $msg = 'Station updated';
}

// List user's stations and unassigned ones
$res_owned = $mysqli->query("SELECT * FROM env_station WHERE st_owner='". $mysqli->real_escape_string($uid) ."'");
$res_unassigned = $mysqli->query("SELECT * FROM env_station WHERE st_owner IS NULL");

?>
<?php include 'header.php'; ?>

<h2>Your Stations</h2>
<?php if (!empty($msg)) echo '<div class="notice">'.htmlspecialchars($msg).'</div>'; ?>

<?php if ($res_owned->num_rows): ?>
  <table>
    <tr><th>Serial</th><th>Name</th><th>Description</th><th>Actions</th></tr>
    <?php while ($s = $res_owned->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($s['st_serial']); ?></td>
        <td><?php echo htmlspecialchars($s['st_label']); ?></td>
        <td><?php echo htmlspecialchars($s['st_description']); ?></td>
        <td>
          <form style="display:inline" method="post">
            <input type="hidden" name="station_serial" value="<?php echo htmlspecialchars($s['st_serial']); ?>">
            <input type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($s['st_label']); ?>">
            <input type="text" name="description" placeholder="Description" value="<?php echo htmlspecialchars($s['st_description']); ?>">
            <button class="btn" name="edit_station">Save</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p class="muted">You don't own any stations yet.</p>
<?php endif; ?>

<h3>Register an unassigned station by serial</h3>
<form method="post">
  <label>Serial<input name="serial" required></label>
  <button class="btn" name="register_serial">Register</button>
</form>

<h3>Unassigned stations (admins or to register)</h3>
<?php if ($res_unassigned->num_rows): ?>
  <table>
    <tr><th>Serial</th><th>Name</th><th>Description</th></tr>
    <?php while ($s = $res_unassigned->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($s['st_serial']); ?></td>
        <td><?php echo htmlspecialchars($s['st_label']); ?></td>
        <td><?php echo htmlspecialchars($s['st_description']); ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p class="muted">No unassigned stations known.</p>
<?php endif; ?>

