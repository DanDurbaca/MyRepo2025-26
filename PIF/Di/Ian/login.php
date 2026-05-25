<?php
session_start();
require __DIR__ . '/assets/db.php';

$error = '';
$success = '';

// Redirect to index if already logged in
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$password) {
        $error = 'Username and password are required.';
    } else {
        try {
            $pdo = getDb();

            // Check if user exists and get password hash
            $stmt = $pdo->prepare('SELECT pk_username, firstName, lastName, password FROM user WHERE pk_username = :username');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'] ?? '')) {
                $error = 'Invalid username or password.';
            } else {
                // Password verified
                $_SESSION['username'] = $user['pk_username'];
                $_SESSION['firstName'] = $user['firstName'];
                $_SESSION['lastName'] = $user['lastName'];
                header('Location: index.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
        }
    }
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <title>Login</title>
</head>
<body>
    <?php include 'assets/header.php'; ?>
  <main class="login-page">
    <section class="login-card">
      <h1 class="login-title">Login</h1>

      <?php if ($error): ?>
        <p class="error-text"><?php echo h($error); ?></p>
      <?php endif; ?>

      <form class="login-form" method="post">
        <div class="form-group">
          <label class="field-label" for="username">Username:</label>
          <input id="username" name="username" class="input-text" type="text" value="<?php echo h($_POST['username'] ?? ''); ?>" required autofocus>
        </div>

        <div class="form-group">
          <label class="field-label" for="password">Password:</label>
          <input id="password" name="password" class="input-text" type="password" required>
        </div>

        <button class="primary-btn" type="submit">Login</button>
      </form>

      <p class="login-footer">Don't have an account? <a href="signup.php">Sign up</a></p>
    </section>
  </main>
    <?php include 'assets/footer.php'; ?>
</body>
</html>
