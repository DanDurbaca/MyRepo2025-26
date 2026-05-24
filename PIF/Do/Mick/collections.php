<?php
require_once __DIR__ . '/db.php';
require_login();
$mysqli = db_connect();
$uid = current_user_id(); // username string

// create collection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_collection'])) {
  $name = $_POST['name'];
  $start = str_replace('T',' ',$_POST['start_dt']);
  $end = str_replace('T',' ',$_POST['end_dt']);
  $station = $_POST['station_serial'];
  // ensure station belongs to user unless admin
  if (!is_admin()) {
    $stmt_check = $mysqli->prepare("SELECT st_serial FROM env_station WHERE st_serial=? AND st_owner=? LIMIT 1");
    $stmt_check->bind_param('ss', $station, $uid);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if (!$res_check || !$res_check->num_rows) { $msg = 'Invalid station'; }
  }
  if (empty($msg)) {
    $stmt = $mysqli->prepare("INSERT INTO env_collection (col_owner,col_name,col_description,col_station,col_start,col_end) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('ssssss',$uid,$name,$_POST['description'],$station,$start,$end);
    $stmt->execute();
    $msg = 'Collection created';
  }
}

// rename
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_collection'])) {
    $cid = intval($_POST['collection_id']); $new = $_POST['new_name'];
    // owner or admin (admin may only rename their own collections per spec)
    $stmt = $mysqli->prepare("UPDATE env_collection SET col_name=? WHERE col_id=? AND col_owner=?");
    $stmt->bind_param('sis',$new,$cid,$uid);
    $stmt->execute(); $msg = 'Renamed';
}
// delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_collection'])) {
    $cid = intval($_POST['collection_id']);
    if (is_admin()) {
        $mysqli->query("DELETE FROM env_collection WHERE col_id=".intval($cid));
    } else {
        $stmt = $mysqli->prepare("DELETE FROM env_collection WHERE col_id=? AND col_owner=?");
        $stmt->bind_param('is',$cid,$uid);
        $stmt->execute();
    }
    $msg = 'Deleted';
}

// share/unshare via env_access (usr_ref is username)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_collection'])) {
    $cid = intval($_POST['collection_id']); $share_with = $_POST['user_name'];
    // ensure owner
    $res = $mysqli->query("SELECT col_owner FROM env_collection WHERE col_id=".intval($cid).' LIMIT 1');
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        if ($row['col_owner'] == $uid) {
            $stmt = $mysqli->prepare("INSERT IGNORE INTO env_access (usr_ref, col_ref) VALUES (?,?)");
            $stmt->bind_param('si',$share_with,$cid);
            $stmt->execute(); $msg = 'Shared';
        } else $msg = 'Not allowed to share';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unshare_collection'])) {
    $cid = intval($_POST['collection_id']); $share_with = $_POST['user_name'];
    $stmt = $mysqli->prepare("DELETE FROM env_access WHERE usr_ref=? AND col_ref=?");
    $stmt->bind_param('si',$share_with,$cid);
    $stmt->execute();
    $msg = 'Unshared';
}

// list my collections and collections shared with me
$res_my = $mysqli->query("SELECT * FROM env_collection WHERE col_owner='". $mysqli->real_escape_string($uid) ."'");
$res_shared = $mysqli->query("SELECT c.*, u.usr_name as owner_name FROM env_access a JOIN env_collection c ON a.col_ref=c.col_id JOIN env_user u ON c.col_owner=u.usr_name WHERE a.usr_ref='". $mysqli->real_escape_string($uid) ."'");
// list user's stations for creating collection (use serials)
$res_stations = $mysqli->query("SELECT st_serial, st_label FROM env_station WHERE st_owner='". $mysqli->real_escape_string($uid) ."'");
// list friends for sharing (confirmed in env_friend)
$friends = $mysqli->query("SELECT CASE WHEN usr_main='". $mysqli->real_escape_string($uid) ."' THEN usr_friend ELSE usr_main END as friend_name FROM env_friend WHERE usr_main='". $mysqli->real_escape_string($uid) ."' OR usr_friend='". $mysqli->real_escape_string($uid) ."'");
?>
<?php include 'header.php'; ?>

<h2>Your Collections</h2>
<?php if (!empty($msg)) echo '<div class="notice">'.htmlspecialchars($msg).'</div>'; ?>

<h3>Create collection</h3>
<form method="post">
  <label>Name<input name="name" required></label>
  <label>Description<textarea name="description"></textarea></label>
  <label>Station
    <select name="station_serial">
      <?php while ($s = $res_stations->fetch_assoc()): ?>
        <option value="<?php echo htmlspecialchars($s['st_serial']); ?>"><?php echo htmlspecialchars($s['st_serial'].' - '.$s['st_label']); ?></option>
      <?php endwhile; ?>
    </select>
  </label>
  <label>Start (date/time)<input type="datetime-local" name="start_dt" required></label>
  <label>End (date/time)<input type="datetime-local" name="end_dt" required></label>
  <button class="btn" name="create_collection">Create</button>
</form>

<h3>Your collections</h3>
<?php if ($res_my && $res_my->num_rows): ?>
  <table>
    <tr><th>Name</th><th>Station</th><th>Range</th><th>Actions</th></tr>
    <?php while ($c = $res_my->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($c['col_name']); ?></td>
        <td><?php echo htmlspecialchars($c['col_station']); ?></td>
        <td><?php echo htmlspecialchars($c['col_start'].' to '.$c['col_end']); ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="collection_id" value="<?php echo $c['col_id']; ?>">
            <input type="text" name="new_name" placeholder="New name">
            <button class="btn" name="rename_collection">Rename</button>
          </form>
          <form method="post" style="display:inline">
            <input type="hidden" name="collection_id" value="<?php echo $c['col_id']; ?>">
            <button class="btn danger" name="delete_collection">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p class="muted">You have no collections yet.</p>
<?php endif; ?>

<h3>Collections shared with you</h3>
<?php if ($res_shared && $res_shared->num_rows): ?>
  <table>
    <tr><th>Name</th><th>Station</th><th>Range</th><th>Owner</th></tr>
    <?php while ($c = $res_shared->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($c['col_name']); ?></td>
        <td><?php echo htmlspecialchars($c['col_station']); ?></td>
        <td><?php echo htmlspecialchars($c['col_start'].' to '.$c['col_end']); ?></td>
        <td><?php echo htmlspecialchars($c['owner_name']); ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p class="muted">No collections have been shared with you.</p>
<?php endif; ?>

<h3>Share a collection with a friend</h3>
<form method="post">
  <label>Collection
    <select name="collection_id">
      <?php
      $all_my = $mysqli->query("SELECT col_id, col_name FROM env_collection WHERE col_owner='". $mysqli->real_escape_string($uid) ."'");
      while ($c = $all_my->fetch_assoc()) echo '<option value="'.$c['col_id'].'">'.htmlspecialchars($c['col_name'])."</option>";
      ?>
    </select>
  </label>
  <label>Friend
    <select name="user_name">
      <?php while ($f = $friends->fetch_assoc()): ?>
        <option value="<?php echo htmlspecialchars($f['friend_name']); ?>"><?php echo htmlspecialchars($f['friend_name']); ?></option>
      <?php endwhile; ?>
    </select>
  </label>
  <button class="btn" name="share_collection">Share</button>
</form>
