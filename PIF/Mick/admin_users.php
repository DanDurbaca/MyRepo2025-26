<?php
require_once __DIR__ . '/db.php';
require_login();
if (!is_admin()) { header('Location: welcome.php'); exit; }
$mysqli = db_connect();

// create user (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
  $username = $_POST['username']; $pw = $_POST['password']; $email = $_POST['email'];
  $first = $_POST['first_name']; $last = $_POST['last_name']; $role = isset($_POST['is_admin'])? 'Admin':'User';
  $hash = password_hash($pw, PASSWORD_DEFAULT);
  $stmt = $mysqli->prepare("INSERT INTO env_user (usr_name,usr_pwd,usr_email,usr_first,usr_last,usr_role) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param('ssssss',$username,$hash,$email,$first,$last,$role);
  $stmt->execute(); $msg = 'User created';
}

// delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
  $username = $_POST['user_name'];
  $stmt = $mysqli->prepare("DELETE FROM env_user WHERE usr_name=?");
  $stmt->bind_param('s',$username); $stmt->execute();
  $msg = 'User deleted';
}

$users = $mysqli->query("SELECT usr_name,usr_email,usr_first,usr_last,usr_role,usr_created FROM env_user ORDER BY usr_created DESC");

?>
<?php include 'header.php'; ?>

<h2>Admin - Users</h2>
<?php if (!empty($msg)) echo '<div class="notice">'.htmlspecialchars($msg).'</div>'; ?>

<h3>Create new user</h3>
<form method="post">
  <label>Username<input name="username" required></label>
  <label>Password<input name="password" required></label>
  <label>Email<input name="email"></label>
  <label>First name<input name="first_name"></label>
  <label>Last name<input name="last_name"></label>
  <label><input type="checkbox" name="is_admin"> Is admin</label>
  <button class="btn" name="create_user">Create</button>
</form>

<h3>All users</h3>
<table>
  <tr><th>#</th><th>Username</th><th>Email</th><th>Name</th><th>Admin</th><th>Action</th></tr>
  <?php while ($u = $users->fetch_assoc()): ?>
    <tr>
      <td><?php echo $u['id']; ?></td>
      <td><?php echo htmlspecialchars($u['username']); ?></td>
      <td><?php echo htmlspecialchars($u['email']); ?></td>
      <td><?php echo htmlspecialchars($u['first_name'].' '.$u['last_name']); ?></td>
      <td><?php echo $u['is_admin']? 'Yes':'No'; ?></td>
      <td>
        <form method="post" style="display:inline">
          <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
          <button class="btn danger" name="delete_user">Delete</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

