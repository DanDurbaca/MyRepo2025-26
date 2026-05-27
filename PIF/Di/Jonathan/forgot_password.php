<?php
// forgot_password.php - Reset account using station-bound 6-digit code (session-backed flow)
// Start session FIRST, before any output
session_start();

// Enable error reporting temporarily (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';

$msg = '';
$step = 1;
$station_id = '';
$code = '';

// Step 1: User submits Station ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['station_id'])) {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid CSRF token.';
    } else {
        $station_id = trim($_POST['station_id']);
        if ($station_id === '') {
            $msg = 'Please enter your Station ID.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT pk_serialNumber, fk_user_owns FROM station WHERE pk_serialNumber = ? LIMIT 1');
                $stmt->execute([$station_id]);
                $station = $stmt->fetch();
                if (!$station || empty($station['fk_user_owns'])) {
                    $msg = 'Station not found or not owned.';
                } else {
                    // Generate 6-digit code
                    try {
                        $code = random_int(100000, 999999);
                    } catch (Exception $e) {
                        $code = mt_rand(100000, 999999);
                    }
                    $_SESSION['reset_station_id'] = $station_id;
                    $_SESSION['reset_code'] = $code;
                    $_SESSION['reset_user'] = $station['fk_user_owns'];
                    $_SESSION['reset_code_time'] = time();
                    $step = 2;
                }
            } catch (PDOException $e) {
                $msg = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Step 2: User submits code and new password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_code'])) {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid CSRF token.';
        $step = 2;
    } else {
        $input_code = trim($_POST['reset_code']);
        $pw = $_POST['password'] ?? '';
        $pw2 = $_POST['password2'] ?? '';
        
        if (empty($_SESSION['reset_code']) || empty($_SESSION['reset_station_id']) || empty($_SESSION['reset_user'])) {
            $msg = 'Session expired. Please try again.';
            $step = 1;
        } elseif (time() - $_SESSION['reset_code_time'] > 600) {
            $msg = 'Reset code expired. Please try again.';
            $step = 1;
        } elseif ($_SESSION['reset_code'] != $input_code) {
            $msg = 'Invalid code.';
            $step = 2;
        } elseif (strlen($pw) < 8) {
            $msg = 'Password must be at least 8 characters.';
            $step = 2;
        } elseif ($pw !== $pw2) {
            $msg = 'Passwords do not match.';
            $step = 2;
        } else {
            try {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare('UPDATE `user` SET password = ? WHERE pk_username = ?');
                $updateStmt->execute([$hash, $_SESSION['reset_user']]);
                // Clear session
                unset($_SESSION['reset_code'], $_SESSION['reset_station_id'], $_SESSION['reset_user'], $_SESSION['reset_code_time']);
                $msg = 'Password reset successful. You can now <a href="login.php">log in</a>.';
                $step = 3;
            } catch (PDOException $e) {
                $msg = 'Failed to update password: ' . $e->getMessage();
                $step = 2;
            }
        }
    }
}
?>
<!DOCTYPE html>
<!-- rest of HTML unchanged -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Indoor Climate Data Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/_header.php'; ?>
    <main>
        <div class="container-small">
            <h2>Forgot Password</h2>
            <?php if ($msg) echo '<p class="message info">' . $msg . '</p>'; ?>

            <?php if ($step === 1): ?>
                <form method="post">
                    <?php echo csrf_input(); ?>
                    <label for="station_id">Enter your Station ID:</label>
                    <input type="text" id="station_id" name="station_id" required>
                    <button class="btn btn-success" type="submit">Get Reset Code</button>
                </form>
            <?php elseif ($step === 2): ?>
                <div class="reset-code-box">
                    <p><strong>Your reset code (valid for 10 minutes):</strong></p>
                    <div class="reset-code" style="font-size:2em;letter-spacing:0.2em;"><?php echo htmlspecialchars($_SESSION['reset_code']); ?></div>
                </div>
                <form method="post">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="station_id" value="<?php echo htmlspecialchars($_SESSION['reset_station_id']); ?>">
                    <label for="reset_code">Enter the code above:</label>
                    <input type="text" id="reset_code" name="reset_code" required pattern="[0-9]{6}">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <label for="password2">Confirm Password:</label>
                    <input type="password" id="password2" name="password2" required minlength="8">
                    <button class="btn btn-success" type="submit">Reset Password</button>
                </form>
            <?php elseif ($step === 3): ?>
                <p>Password reset successful. <a href="login.php">Log in</a>.</p>
            <?php endif; ?>
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </main>
</body>
</html>
