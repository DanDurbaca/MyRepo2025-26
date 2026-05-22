<?php
require_once __DIR__ . '/db.php';
require_login();
if (!is_admin()) { header('Location: welcome.php'); exit; }
$mysqli = db_connect();

// create station
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_station'])) {
  $serial = $_POST['serial']; $label = $_POST['name']; $desc = $_POST['description'];
  $owner = $_POST['owner_name']?:NULL;
  $stmt = $mysqli->prepare("INSERT INTO env_station (st_serial,st_label,st_description,st_owner) VALUES (?,?,?,?)");
  $stmt->bind_param('ssss', $serial, $label, $desc, $owner);
  $stmt->execute(); $msg = 'Station created';
}

// delete station
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_station'])) {
  $serial = $_POST['station_serial'];
  $stmt = $mysqli->prepare("DELETE FROM env_station WHERE st_serial=?");
  $stmt->bind_param('s',$serial); $stmt->execute();
  $msg = 'Station deleted';
}

$users = $mysqli->query("SELECT usr_name FROM env_user ORDER BY usr_name");
$stations = $mysqli->query("SELECT s.*, u.usr_name as owner_name FROM env_station s LEFT JOIN env_user u ON u.usr_name=s.st_owner ORDER BY s.st_created DESC");

?>
<?php include 'header.php'; ?>

<h2>Admin - Stations</h2>
<?php if (!empty($msg)) echo '<div class="notice">'.htmlspecialchars($msg).'</div>'; ?>

<h3>Create station</h3>
<form method="post">
  <label>Serial<input name="serial" required></label>
  <label>Name<input name="name"></label>
  <label>Description<textarea name="description"></textarea></label>
  <label>Owner
    <select name="owner_id">
      <option value="0">-- unassigned --</option>
      <?php while ($u = $users->fetch_assoc()) echo '<option value="'.$u['id'].'">'.htmlspecialchars($u['username'])."</option>"; ?>
    </select>
  </label>
  <button class="btn" name="create_station">Create</button>
</form>

<h3>All stations</h3>
<table>
  <tr><th>#</th><th>Serial</th><th>Name</th><th>Owner</th><th>Action</th></tr>
  <?php while ($s = $stations->fetch_assoc()): ?>
    <tr>
      <td><?php echo $s['id']; ?></td>
      <td><?php echo htmlspecialchars($s['serial']); ?></td>
      <td><?php echo htmlspecialchars($s['name']); ?></td>
      <td><?php echo htmlspecialchars($s['owner_name'] ?? ''); ?></td>
      <td>
        <form method="post" style="display:inline">
          <input type="hidden" name="station_id" value="<?php echo $s['id']; ?>">
          <button class="btn danger" name="delete_station">Delete</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</table>
