<?php
// reset_password.php - Validate password reset token and update user's password securely
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';

$msg = '';
$token = $_GET['token'] ?? null;
if (!$token) {
    $msg = 'Missing token.';
} else {
    $hashed = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, pkfk_username, expires_at, used FROM password_reset WHERE token = ? AND type = ? LIMIT 1');
    $stmt->execute([$hashed, 'password_reset']);
    $row = $stmt->fetch();
    if (!$row) {
        $msg = 'Invalid or expired token.';
    } else {
        if ($row['used']) {
            $msg = 'This reset token has already been used.';
        } else {
            $expires = new DateTimeImmutable($row['expires_at']);
            if ($expires < new DateTimeImmutable('now')) {
                $msg = 'This reset token has expired.';
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                        $msg = 'Invalid CSRF token.';
                    } else {
                        $pw = $_POST['password'] ?? '';
                        $pw2 = $_POST['password2'] ?? '';
                        if ($pw === '' || strlen($pw) < 8) {
                            $msg = 'Password must be at least 8 characters.';
                        } elseif ($pw !== $pw2) {
                            $msg = 'Passwords do not match.';
                        } else {
                            $hash = password_hash($pw, PASSWORD_DEFAULT);
                            $pdo->prepare('UPDATE `user` SET password = ? WHERE pk_username = ?')->execute([$hash, $row['pkfk_username']]);
                            $pdo->prepare('UPDATE password_reset SET used = 1 WHERE id = ?')->execute([$row['id']]);
                            $msg = 'Password reset successful. You can now <a href="login.php">log in</a>.';
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Indoor Climate Data Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/_header.php'; ?>
    <main>
        <div class="container-small">
            <h2>Reset Password</h2>
            <?php if ($msg) echo '<p class="message ' . (strpos($msg, 'successful') !== false ? 'success' : 'error') . '">' . $msg . '</p>'; ?>
            <?php if ($token && strpos($msg, 'successful') === false): ?>
                <form method="post">
                    <?php echo csrf_input(); ?>
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <label for="password2">Confirm Password:</label>
                    <input type="password" id="password2" name="password2" required minlength="8">
                    <button class="btn btn-success" type="submit">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
