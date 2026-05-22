<?php
require_once __DIR__ . '/db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mysqli = db_connect();
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $email = trim($_POST['email']);
  $first = trim($_POST['first_name']);
  $last = trim($_POST['last_name']);

    if (strlen($username) < 3) $errors[] = 'Username too short';
    if (strlen($password) < 4) $errors[] = 'Password too short';

    if (empty($errors)) {
  $hash = password_hash($password, PASSWORD_DEFAULT);
  // env_user: usr_name, usr_pwd, usr_email, usr_first, usr_last
  $stmt = $mysqli->prepare("INSERT INTO env_user (usr_name,usr_pwd,usr_email,usr_first,usr_last) VALUES (?,?,?,?,?)");
  $stmt->bind_param('sssss', $username, $hash, $email, $first, $last);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            if ($mysqli->errno === 1062) $errors[] = 'Username already taken';
            else $errors[] = 'DB error: ' . $mysqli->error;
        }
    }
}
?>
<?php include 'header.php'; ?>

<h2>Register</h2>
<?php if ($errors): ?>
  <div class="notice"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
<?php endif; ?>
<form method="post">
  <label>Username<input name="username" required></label>
  <label>Password<input name="password" type="password" required></label>
  <label>Email<input name="email" type="email"></label>
  <label>First name<input name="first_name"></label>
  <label>Last name<input name="last_name"></label>
  <button class="btn" type="submit">Create account</button>
</form>
