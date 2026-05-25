<?php
session_start();
require __DIR__ . '/assets/db.php';
require_once __DIR__ . '/assets/mailer.php';

$error = '';
$success = '';

// Redirect to index if already logged in
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    // Validation
    if (!$username || !$firstName || !$lastName || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        try {
            $pdo = getDb();

            // Check if username already exists
            $checkStmt = $pdo->prepare('SELECT pk_username FROM user WHERE pk_username = :username');
            $checkStmt->execute([':username' => $username]);
            if ($checkStmt->fetch()) {
                $error = 'Username already exists.';
            } else {
                // Check if email already exists
                $checkEmailStmt = $pdo->prepare('SELECT pk_username FROM user WHERE email = :email');
                $checkEmailStmt->execute([':email' => $email]);
                if ($checkEmailStmt->fetch()) {
                    $error = 'Email already registered.';
                } else {
                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    // Create new user with hashed password
                    $insertStmt = $pdo->prepare(
                        'INSERT INTO user (pk_username, firstName, lastName, email, password)
                         VALUES (:username, :firstName, :lastName, :email, :password)'
                    );
                    $insertStmt->execute([
                        ':username' => $username,
                        ':firstName' => $firstName,
                        ':lastName' => $lastName,
                        ':email' => $email,
                        ':password' => $hashedPassword,
                    ]);

                    // Try to send welcome email (non-blocking if it fails)
                    if (!send_welcome_email($email, $firstName, $username)) {
                      // Do not expose to user; just log
                      error_log('Welcome email could not be sent to ' . $email);
                    }

                    // Auto-login after signup
                    $_SESSION['username'] = $username;
                    $_SESSION['firstName'] = $firstName;
                    $_SESSION['lastName'] = $lastName;
                    header('Location: index.php');
                    exit;
                }
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
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <title>Sign Up</title>
</head>
<body>
  <main class="login-page">
    <section class="login-card">
      <h1 class="login-title">Create Account</h1>

      <?php if ($error): ?>
        <p class="error-text"><?php echo h($error); ?></p>
      <?php endif; ?>

      <form class="login-form" method="post">
        <div class="form-group">
          <label class="field-label" for="username">Username:</label>
          <input id="username" name="username" class="input-text" type="text" value="<?php echo h($_POST['username'] ?? ''); ?>" required autofocus>
        </div>

        <div class="form-group">
          <label class="field-label" for="firstName">First Name:</label>
          <input id="firstName" name="firstName" class="input-text" type="text" value="<?php echo h($_POST['firstName'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label class="field-label" for="lastName">Last Name:</label>
          <input id="lastName" name="lastName" class="input-text" type="text" value="<?php echo h($_POST['lastName'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label class="field-label" for="email">Email:</label>
          <input id="email" name="email" class="input-text" type="email" value="<?php echo h($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label class="field-label" for="password">Password:</label>
          <input id="password" name="password" class="input-text" type="password" required>
        </div>

        <div class="form-group">
          <label class="field-label" for="confirmPassword">Confirm Password:</label>
          <input id="confirmPassword" name="confirmPassword" class="input-text" type="password" required>
        </div>

        <button class="primary-btn" type="submit">Sign Up</button>
      </form>

      <p class="login-footer">Already have an account? <a href="login.php">Login</a></p>
    </section>
  </main>
</body>
</html>
