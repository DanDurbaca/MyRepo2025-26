<?php
require_once __DIR__ . '/db.php';
require_login();
$mysqli = db_connect();
$uid = current_user_id();
// uid contains the username string for env_user (usr_name)
$safe_uid = $mysqli->real_escape_string($uid);
$res = $mysqli->query("SELECT usr_name, usr_email, usr_first, usr_last FROM env_user WHERE usr_name='".$safe_uid."' LIMIT 1");
$user = $res ? $res->fetch_assoc() : null;
?>
<?php include 'header.php'; ?>

<h2>Welcome, <?php echo htmlspecialchars($user['usr_first'] ?: $user['usr_name']); ?></h2>

<p>Your account details:</p>
<table>
  <tr><th>Username</th><td><?php echo htmlspecialchars($user['usr_name']); ?></td></tr>
  <tr><th>Email</th><td><?php echo htmlspecialchars($user['usr_email']); ?></td></tr>
  <tr><th>First / Last</th><td><?php echo htmlspecialchars($user['usr_first']).' '.htmlspecialchars($user['usr_last']); ?></td></tr>
</table>

<p class="muted">Use the navigation to manage stations, collections and friends. You can edit your account below.</p>

<h3>Edit account</h3>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $email = $_POST['email'];
  $first = $_POST['first_name'];
  $last = $_POST['last_name'];
  $pw = $_POST['password'];
  if ($pw) {
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("UPDATE env_user SET usr_email=?, usr_first=?, usr_last=?, usr_pwd=? WHERE usr_name=?");
    $stmt->bind_param('sssss', $email, $first, $last, $hash, $uid);
  } else {
    $stmt = $mysqli->prepare("UPDATE env_user SET usr_email=?, usr_first=?, usr_last=? WHERE usr_name=?");
    $stmt->bind_param('ssss', $email, $first, $last, $uid);
  }
  $stmt->execute();
  echo '<div class="notice">Profile updated</div>';
}
?>
<form method="post">
  <label>Email<input name="email" value="<?php echo htmlspecialchars($user['usr_email']); ?>"></label>
  <label>First name<input name="first_name" value="<?php echo htmlspecialchars($user['usr_first']); ?>"></label>
  <label>Last name<input name="last_name" value="<?php echo htmlspecialchars($user['usr_last']); ?>"></label>
  <label>New password (Don't type anything to leave unchanged)<input name="password" type="password"></label>
  <button class="btn" name="update">Save</button>
</form>

