<?php
// Short: Handles verification links and confirms user email addresses.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/inc/mail.php';

$msg = '';
if (!isset($_GET['token'])) {
    $msg = 'Missing token.';
} else {
    $token = $_GET['token'];
    $hashed = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, pkfk_username, expires_at, used FROM password_reset WHERE token = ? AND type = ? LIMIT 1');
    $stmt->execute([$hashed, 'email_verification']);
    $row = $stmt->fetch();
    if (!$row) {
        $msg = 'Invalid or expired token.';
    } else {
        if ($row['used']) {
            $msg = 'This verification link has already been used.';
        } else {
            $expires = new DateTimeImmutable($row['expires_at']);
            if ($expires < new DateTimeImmutable('now')) {
                $msg = 'This verification link has expired.';
            } else {
                // mark user as verified and mark token used
                $pdo->prepare('UPDATE `user` SET email_verified = 1 WHERE pk_username = ?')->execute([$row['pkfk_username']]);
                $pdo->prepare('UPDATE password_reset SET used = 1 WHERE id = ?')->execute([$row['id']]);
                $msg = 'Email verified! You can now log in.';
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
    <title>Email Verification - Indoor Climate Data Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/_header.php'; ?>
    <main>
        <div class="container-small">
            <h2>Email Verification</h2>
            <p class="message <?php echo strpos($msg, 'verified') !== false ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($msg); ?></p>
            <p><a href="login.php">Go to Login</a></p>
        </div>
    </main>
</body>
</html>
