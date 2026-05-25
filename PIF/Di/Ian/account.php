<?php
session_start();
require __DIR__ . '/assets/db.php';
require_once __DIR__ . '/assets/mailer.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$dbError = '';
$successMsg = '';
$user = [];

try {
    $pdo = getDb();

    // Handle profile updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update-profile') {
            $firstName = trim($_POST['firstName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (!$firstName || !$lastName) {
                $dbError = 'First name and last name are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $dbError = 'Invalid email address.';
            } else {
                // Check if email is already taken by another user
                $checkStmt = $pdo->prepare(
                    'SELECT pk_username FROM user WHERE email = :email AND pk_username != :username'
                );
                $checkStmt->execute([
                    ':email' => $email,
                    ':username' => $_SESSION['username'],
                ]);
                
                if ($checkStmt->fetch()) {
                    $dbError = 'Email already in use by another account.';
                } else {
                    $updateStmt = $pdo->prepare(
                        'UPDATE user SET firstName = :firstName, lastName = :lastName, email = :email WHERE pk_username = :username'
                    );
                    $updateStmt->execute([
                        ':firstName' => $firstName,
                        ':lastName' => $lastName,
                        ':email' => $email,
                        ':username' => $_SESSION['username'],
                    ]);
                    $_SESSION['firstName'] = $firstName;
                    $_SESSION['lastName'] = $lastName;
                    $successMsg = 'Profile updated successfully.';
                    $dbError = '';
                    // Send account-changed notification
                    @send_account_changed_email($email, $firstName ?: $_SESSION['username'], 'account details');
                }
            }
        } elseif ($_POST['action'] === 'change-password') {
            $currentPassword = $_POST['currentPassword'] ?? '';
            $newPassword = trim($_POST['newPassword'] ?? '');
            $confirmPassword = trim($_POST['confirmPassword'] ?? '');

            if (!$newPassword) {
                $dbError = 'New password is required.';
            } elseif (strlen($newPassword) < 6) {
                $dbError = 'Password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $dbError = 'Passwords do not match.';
            } else {
                // Verify current password
                $stmt = $pdo->prepare('SELECT password FROM user WHERE pk_username = :username');
                $stmt->execute([':username' => $_SESSION['username']]);
                $userRecord = $stmt->fetch();

                if (!$userRecord || !password_verify($currentPassword, $userRecord['password'] ?? '')) {
                    $dbError = 'Current password is incorrect.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    $updateStmt = $pdo->prepare(
                        'UPDATE user SET password = :password WHERE pk_username = :username'
                    );
                    $updateStmt->execute([
                        ':password' => $hashedPassword,
                        ':username' => $_SESSION['username'],
                    ]);
                    $successMsg = 'Password changed successfully.';
                    $dbError = '';
                    // Fetch email for notification
                    try {
                      $eStmt = $pdo->prepare('SELECT firstName, email FROM user WHERE pk_username = :u');
                      $eStmt->execute([':u' => $_SESSION['username']]);
                      if ($rec = $eStmt->fetch(PDO::FETCH_ASSOC)) {
                        @send_account_changed_email($rec['email'], $rec['firstName'] ?: $_SESSION['username'], 'password');
                      }
                    } catch (Exception $e) {
                      error_log('password change email failed: ' . $e->getMessage());
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete-account') {
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            // Verify password before deletion
            $stmt = $pdo->prepare('SELECT password FROM user WHERE pk_username = :username');
            $stmt->execute([':username' => $_SESSION['username']]);
            $userRecord = $stmt->fetch();

            if (!$userRecord || !password_verify($confirmPassword, $userRecord['password'] ?? '')) {
                $dbError = 'Password is incorrect. Account not deleted.';
            } else {
                // Delete user and all related data (cascading deletes)
                $deleteStmt = $pdo->prepare('DELETE FROM user WHERE pk_username = :username');
                $deleteStmt->execute([':username' => $_SESSION['username']]);

                session_destroy();
                header('Location: /login.php?deleted=1');
                exit;
            }
        }
    }

    // Fetch user data
    $stmt = $pdo->prepare('SELECT pk_username, firstName, lastName, email FROM user WHERE pk_username = :username');
    $stmt->execute([':username' => $_SESSION['username']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $dbError = 'Database error. Please try again later.';
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
  <title>Account Settings</title>
</head>
<body>
  <?php include 'assets/header.php'; ?>

  <main class="page">
    <div class="account-container">
      <h1 class="account-title">Account Settings</h1>

      <?php if ($successMsg): ?>
        <p class="success-text"><?php echo h($successMsg); ?></p>
      <?php endif; ?>

      <?php if ($dbError): ?>
        <p class="error-text"><?php echo h($dbError); ?></p>
      <?php endif; ?>

      <!-- Profile Information -->
      <section class="card account-card">
        <h2 class="card-title">Profile Information</h2>

        <?php if ($user): ?>
          <form method="post" class="account-form">
            <input type="hidden" name="action" value="update-profile">

            <div class="form-group">
              <label class="field-label" for="username">Username:</label>
              <input id="username" class="input-text" type="text" value="<?php echo h($user['pk_username']); ?>" disabled>
              <p class="help-text">Username cannot be changed.</p>
            </div>

            <div class="form-group">
              <label class="field-label" for="firstName">First Name:</label>
              <input id="firstName" name="firstName" class="input-text" type="text" value="<?php echo h($user['firstName']); ?>" required>
            </div>

            <div class="form-group">
              <label class="field-label" for="lastName">Last Name:</label>
              <input id="lastName" name="lastName" class="input-text" type="text" value="<?php echo h($user['lastName']); ?>" required>
            </div>

            <div class="form-group">
              <label class="field-label" for="email">Email:</label>
              <input id="email" name="email" class="input-text" type="email" value="<?php echo h($user['email']); ?>" required>
            </div>

            <button type="submit" class="primary-btn">Update Profile</button>
          </form>
        <?php endif; ?>
      </section>

      <!-- Change Password -->
      <section class="card account-card">
        <h2 class="card-title">Change Password</h2>

        <form method="post" class="account-form">
          <input type="hidden" name="action" value="change-password">

          <div class="form-group">
            <label class="field-label" for="currentPassword">Current Password:</label>
            <input id="currentPassword" name="currentPassword" class="input-text" type="password" required>
          </div>

          <div class="form-group">
            <label class="field-label" for="newPassword">New Password:</label>
            <input id="newPassword" name="newPassword" class="input-text" type="password" required>
          </div>

          <div class="form-group">
            <label class="field-label" for="confirmPassword">Confirm Password:</label>
            <input id="confirmPassword" name="confirmPassword" class="input-text" type="password" required>
          </div>

          <button type="submit" class="primary-btn">Change Password</button>
        </form>
      </section>

      <!-- Danger Zone -->
      <section class="card account-card danger-zone">
        <h2 class="card-title" style="color: #c0392b;">Danger Zone</h2>

        <p class="help-text" style="color: #c0392b; margin-bottom: 16px;">
          <strong>Warning:</strong> Deleting your account is permanent and cannot be undone. All your stations, measurements, and collections will be deleted.
        </p>

        <form method="post" class="account-form" id="deleteForm">
          <input type="hidden" name="action" value="delete-account">

          <div class="form-group">
            <label class="field-label" for="deletePassword">Confirm Password to Delete:</label>
            <input id="deletePassword" name="confirmPassword" class="input-text" type="password" required>
          </div>

          <button type="submit" class="danger-btn" onclick="return confirm('Are you absolutely sure? This cannot be undone.');">Delete Account</button>
        </form>
      </section>
    </div>
  </main>

  <?php include 'assets/footer.php'; ?>
</body>
</html>
