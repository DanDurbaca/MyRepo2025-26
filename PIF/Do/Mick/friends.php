<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/email_notifier.php';
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
$msg = '';

// send friend request (by username)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_friend'])) {
  $to = trim($_POST['username']);
  if ($to === $uid) { $msg = htmlspecialchars($t['friends_request_error_self']); }
  else {
    // check user exists
    $stmtc = $mysqli->prepare("SELECT usr_name FROM env_user WHERE usr_name=? LIMIT 1");
    $stmtc->bind_param('s', $to); $stmtc->execute(); $rc = $stmtc->get_result();
    if (!$rc || !$rc->num_rows) { $msg = htmlspecialchars($t['friends_request_error_notfound']); }
    else {
      // create pending request
      $stmt = $mysqli->prepare("INSERT IGNORE INTO env_friend_request (req_from, req_to) VALUES (?,?)");
      $stmt->bind_param('ss',$uid,$to); 
      $stmt->execute();
      $msg = htmlspecialchars($t['friends_request_sent']);
      
      // Send notification
      notify_friend_request($mysqli, $to, $uid);
    }
  }
}

// accept/decline request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond_request'])) {
  $req_id = intval($_POST['req_id']); $action = $_POST['respond_request'];
  if ($action === 'accept') {
    // mark accepted and insert into env_friend
    $stmt = $mysqli->prepare("SELECT req_from, req_to FROM env_friend_request WHERE req_id=? AND req_to=? LIMIT 1");
    $stmt->bind_param('is',$req_id,$uid); $stmt->execute(); $r = $stmt->get_result();
    if ($r && $r->num_rows) {
      $row = $r->fetch_assoc();
      $a = $row['req_from']; $b = $row['req_to'];
      // insert friendship (both directions avoided by storing single entry with requester/main)
      $stmt2 = $mysqli->prepare("INSERT IGNORE INTO env_friend (usr_main, usr_friend) VALUES (?,?)");
      $stmt2->bind_param('ss',$a,$b); $stmt2->execute();
      $mysqli->query("UPDATE env_friend_request SET req_status='accepted' WHERE req_id=".intval($req_id));
      $msg = htmlspecialchars($t['friends_request_accepted']);
      
      // Send notification to the requester
      $title = htmlspecialchars($uid) . " accepted your friend request";
      $mysqli->query("INSERT INTO env_notification (notif_to, notif_type, notif_title, notif_message, notif_related_user) 
                     VALUES ('". $mysqli->real_escape_string($a) ."', 'friend_accepted', '". $mysqli->real_escape_string($title) ."', '', '". $mysqli->real_escape_string($uid) ."')");
    }
  } else {
    $stmt_decline = $mysqli->prepare("UPDATE env_friend_request SET req_status='declined' WHERE req_id=? AND req_to=?");
    $stmt_decline->bind_param('is', $req_id, $uid);
    $stmt_decline->execute();
    $msg = htmlspecialchars($t['friends_request_declined']);
  }
}

// remove friend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_friend'])) {
  $friend = $_POST['friend_name'];
  $stmt = $mysqli->prepare("DELETE FROM env_friend WHERE (usr_main=? AND usr_friend=?) OR (usr_main=? AND usr_friend=?)");
  $stmt->bind_param('ssss',$uid,$friend,$friend,$uid);
  $stmt->execute();
  // when friendship ended, unshare all collections between users
  $stmt2 = $mysqli->prepare("DELETE FROM env_access WHERE (usr_ref=? AND col_ref IN (SELECT col_id FROM env_collection WHERE col_owner=?)) OR (usr_ref=? AND col_ref IN (SELECT col_id FROM env_collection WHERE col_owner=?))");
  $stmt2->bind_param('ssss',$friend,$uid,$uid,$friend); $stmt2->execute();
  $msg = htmlspecialchars($t['friends_removed']);
}

// list confirmed friends for current user
$friends = [];
$rf = $mysqli->query("SELECT CASE WHEN usr_main='". $mysqli->real_escape_string($uid) ."' THEN usr_friend ELSE usr_main END as friend_name FROM env_friend WHERE usr_main='". $mysqli->real_escape_string($uid) ."' OR usr_friend='". $mysqli->real_escape_string($uid) ."'");
if ($rf) while ($r = $rf->fetch_assoc()) {
  $stmt = $mysqli->prepare("SELECT usr_name, usr_first, usr_last FROM env_user WHERE usr_name=? LIMIT 1");
  $stmt->bind_param('s', $r['friend_name']); $stmt->execute(); $rr = $stmt->get_result();
  if ($rr && $rr->num_rows) $friends[] = $rr->fetch_assoc();
}

// incoming friend requests
$incoming = $mysqli->query("SELECT req_id, req_from, req_created FROM env_friend_request WHERE req_to='". $mysqli->real_escape_string($uid) ."' AND req_status='pending'");

// list all other users to send request (exclude self)
$all = $mysqli->query("SELECT usr_name, usr_first, usr_last FROM env_user WHERE usr_name<> '". $mysqli->real_escape_string($uid) ."' ORDER BY usr_name LIMIT 200");

?>
<?php include 'header.php'; ?>

<h2><?php echo htmlspecialchars($t['friends_heading']); ?></h2>
<?php if (!empty($msg)) echo '<div class="notice">'.htmlspecialchars($msg).'</div>'; ?>

<h3><?php echo htmlspecialchars($t['friends_your_friends']); ?></h3>
<?php if ($friends): ?>
  <table>
    <tr><th><?php echo htmlspecialchars($t['friends_username']); ?></th><th><?php echo htmlspecialchars($t['friends_name']); ?></th><th><?php echo htmlspecialchars($t['friends_action']); ?></th></tr>
    <?php foreach ($friends as $f): ?>
      <tr>
        <td><?php echo htmlspecialchars($f['usr_name']); ?></td>
        <td><?php echo htmlspecialchars($f['usr_first'].' '.$f['usr_last']); ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="friend_name" value="<?php echo htmlspecialchars($f['usr_name']); ?>">
            <button class="btn danger" name="remove_friend"><?php echo htmlspecialchars($t['friends_remove']); ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p class="muted"><?php echo htmlspecialchars($t['friends_no_friends']); ?></p>
<?php endif; ?>

<h3><?php echo htmlspecialchars($t['friends_incoming_requests']); ?></h3>
<?php if ($incoming && $incoming->num_rows): ?>
  <table>
    <tr><th><?php echo htmlspecialchars($t['friends_from']); ?></th><th><?php echo htmlspecialchars($t['friends_action']); ?></th></tr>
    <?php while ($req = $incoming->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($req['req_from']); ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="req_id" value="<?php echo $req['req_id']; ?>">
            <button class="btn" name="respond_request" value="accept"><?php echo htmlspecialchars($t['friends_accept']); ?></button>
            <button class="btn danger" name="respond_request" value="decline"><?php echo htmlspecialchars($t['friends_decline']); ?></button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p class="muted"><?php echo htmlspecialchars($t['friends_no_requests']); ?></p>
<?php endif; ?>

<h3><?php echo htmlspecialchars($t['friends_add_friend']); ?></h3>
<form method="post">
  <label><?php echo htmlspecialchars($t['friends_add_label']); ?><input name="username" required></label>
  <button class="btn" name="add_friend"><?php echo htmlspecialchars($t['friends_add_submit']); ?></button>
</form>

<h3><?php echo htmlspecialchars($t['friends_all_users']); ?></h3>
<?php if ($all && $all->num_rows): ?>
  <table>
    <tr><th><?php echo htmlspecialchars($t['friends_username']); ?></th><th><?php echo htmlspecialchars($t['friends_name']); ?></th></tr>
    <?php while ($u = $all->fetch_assoc()): ?>
      <tr><td><?php echo htmlspecialchars($u['usr_name']); ?></td><td><?php echo htmlspecialchars($u['usr_first'].' '.$u['usr_last']); ?></td></tr>
    <?php endwhile; ?>
  </table>
<?php endif; ?>

</main>
<?php include 'footer.php'; ?>
