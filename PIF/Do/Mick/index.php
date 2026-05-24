<?php
require_once __DIR__ . '/db.php';
// Login page
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mysqli = db_connect();
  $username = $mysqli->real_escape_string($_POST['username']);
  $password = $_POST['password'];
  // env_user: usr_name, usr_pwd, usr_role
  $res = $mysqli->query("SELECT usr_name, usr_pwd, usr_role FROM env_user WHERE usr_name='". $username ."' LIMIT 1");
  if ($res && $res->num_rows) {
    $row = $res->fetch_assoc();
    if (password_verify($password, $row['usr_pwd'])) {
      // store username in session (env_user uses username as PK)
      $_SESSION['user_id'] = $row['usr_name'];
      $_SESSION['is_admin'] = ($row['usr_role'] === 'Admin') ? 1 : 0;
      header('Location: welcome.php');
      exit;
    }
  }
    $err = 'Invalid username or password';
}
?>
<?php include 'header.php'; ?>

<h2>Login</h2>
<?php if ($err): ?><div class="notice"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
<form method="post">
  <label>Username
    <input name="username" required>
  </label>
  <label>Password
    <input name="password" type="password" required>
  </label>
  <button class="btn" type="submit">Login</button>
</form>

<p class="muted">No external login — local username/password only. <a href="register.php">Register</a> if you don't have an account.</p>

