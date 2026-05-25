<?php
session_start();
require __DIR__ . '/assets/db.php';

$error = '';
$inviterName = '';
$inviterUsername = '';
$email = '';
$token = '';

// Get and validate token
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    try {
        $pdo = getDb();
        $stmt = $pdo->prepare('SELECT i.from_username, i.email, i.used_at, i.expires_at, u.firstName, u.lastName 
                               FROM invitation i 
                               JOIN user u ON u.pk_username = i.from_username 
                               WHERE i.pk_invitation_token = :t');
        $stmt->execute([':t' => $token]);
        $invite = $stmt->fetch();
        
        if (!$invite) {
            $error = 'Invalid invitation link.';
        } elseif ($invite['used_at']) {
            $error = 'This invitation has already been used.';
        } elseif (strtotime($invite['expires_at']) < time()) {
            $error = 'This invitation has expired.';
        } else {
            $inviterUsername = $invite['from_username'];
            $inviterName = trim(($invite['firstName'] ?? '') . ' ' . ($invite['lastName'] ?? '')) ?: $inviterUsername;
            $email = $invite['email'];
        }
    } catch (PDOException $e) {
        $error = 'Database error.';
    }
} else {
    $error = 'No invitation token provided.';
}

// Handle signup
$signupError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    $submitToken = trim($_POST['token'] ?? '');

    if ($submitToken !== $token) {
        $signupError = 'Invalid token.';
    } elseif (!$username || !$firstName || !$lastName || !$password) {
        $signupError = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $signupError = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $signupError = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $signupError = 'Passwords do not match.';
    } else {
        try {
            $pdo = getDb();
            $pdo->beginTransaction();

            // Check username availability
            $checkStmt = $pdo->prepare('SELECT pk_username FROM user WHERE pk_username = :u');
            $checkStmt->execute([':u' => $username]);
            if ($checkStmt->fetch()) {
                $signupError = 'Username already exists.';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $insertStmt = $pdo->prepare('INSERT INTO user (pk_username, firstName, lastName, email, password) VALUES (:u, :f, :l, :e, :p)');
                $insertStmt->execute([
                    ':u' => $username,
                    ':f' => $firstName,
                    ':l' => $lastName,
                    ':e' => $email,
                    ':p' => $hashedPassword,
                ]);

                // Mark invitation as used
                $updateInv = $pdo->prepare('UPDATE invitation SET used_at = NOW(), used_by_username = :u WHERE pk_invitation_token = :t');
                $updateInv->execute([':u' => $username, ':t' => $token]);

                // Auto-add friendship
                $addFriend = $pdo->prepare('INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend, isaccepted) VALUES (:a, :b, 1)');
                $addFriend->execute([':a' => $inviterUsername, ':b' => $username]);

                $pdo->commit();

                // Auto-login
                $_SESSION['username'] = $username;
                $_SESSION['firstName'] = $firstName;
                $_SESSION['lastName'] = $lastName;
                header('Location: /index.php?invited=1');
                exit;
            }

            if ($signupError) {
                $pdo->rollBack();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $signupError = 'Database error. Please try again.';
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
  <title>Accept Invitation</title>
</head>
<body>
  <main class="login-page">
    <section class="login-card">
      <?php if ($error): ?>
        <h1 class="login-title">Invalid Invitation</h1>
        <p class="error-text"><?php echo h($error); ?></p>
        <p class="login-footer"><a href="/signup.php">Sign up normally</a> or <a href="/login.php">Login</a></p>
      <?php else: ?>
        <h1 class="login-title">Join Portable Indoor Feedback</h1>
        <p style="text-align:center; margin-bottom:16px;">
          <strong><?php echo h($inviterName); ?></strong> invited you to join!
        </p>

        <?php if ($signupError): ?>
          <p class="error-text"><?php echo h($signupError); ?></p>
        <?php endif; ?>

        <form class="login-form" method="post">
          <input type="hidden" name="token" value="<?php echo h($token); ?>">

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
            <input id="email" name="email" class="input-text" type="email" value="<?php echo h($email); ?>" disabled>
            <p class="help-text">Email provided by invitation</p>
          </div>

          <div class="form-group">
            <label class="field-label" for="password">Password:</label>
            <input id="password" name="password" class="input-text" type="password" required>
          </div>

          <div class="form-group">
            <label class="field-label" for="confirmPassword">Confirm Password:</label>
            <input id="confirmPassword" name="confirmPassword" class="input-text" type="password" required>
          </div>

          <button class="primary-btn" type="submit">Create Account & Connect</button>
        </form>

        <p class="login-footer">Already have an account? <a href="/login.php">Login</a></p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
