<?php
$pageTitle = 'Edit Profile';
require_once '../config.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../inc/csrf.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}
$username = $_SESSION['username'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT pk_username, firstName, lastName, email FROM `user` WHERE pk_username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash'] = 'Invalid CSRF token.';
        header('Location: edit_profile.php');
        exit;
    }
    $newUsername = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rawPassword = (string)($_POST['password'] ?? '');

    // Basic validation
    if ($newUsername === '' || $fullname === '' || $email === '') {
        $_SESSION['flash'] = 'Username, full name, and email are required.';
        header('Location: edit_profile.php');
        exit;
    }
    if (!preg_match('/^[A-Za-z0-9_\-]{3,64}$/', $newUsername)) {
        $_SESSION['flash'] = 'Invalid username. Use 3-64 letters, numbers, underscore or dash.';
        header('Location: edit_profile.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = 'Invalid email address.';
        header('Location: edit_profile.php');
        exit;
    }
    if ($rawPassword !== '' && strlen($rawPassword) < 8) {
        $_SESSION['flash'] = 'Password must be at least 8 characters.';
        header('Location: edit_profile.php');
        exit;
    }

    // Split fullname into first and last name
    $parts = preg_split('/\s+/', $fullname, 2);
    $firstName = trim($parts[0] ?? '');
    $lastName = trim($parts[1] ?? '');
    if ($firstName === '') {
        $_SESSION['flash'] = 'Please enter a valid full name.';
        header('Location: edit_profile.php');
        exit;
    }

    // Uniqueness checks
    if ($newUsername !== $username) {
        $chk = $pdo->prepare('SELECT 1 FROM `user` WHERE pk_username = ? LIMIT 1');
        $chk->execute([$newUsername]);
        if ($chk->fetchColumn()) {
            $_SESSION['flash'] = 'That username is already taken.';
            header('Location: edit_profile.php');
            exit;
        }
    }
    $chkEmail = $pdo->prepare('SELECT 1 FROM `user` WHERE email = ? AND pk_username <> ? LIMIT 1');
    $chkEmail->execute([$email, $username]);
    if ($chkEmail->fetchColumn()) {
        $_SESSION['flash'] = 'That email address is already in use.';
        header('Location: edit_profile.php');
        exit;
    }

    $password = ($rawPassword !== '') ? password_hash($rawPassword, PASSWORD_DEFAULT) : null;

    // Update query
    if ($password) {
        $stmt = $pdo->prepare("UPDATE `user` SET pk_username = ?, firstName = ?, lastName = ?, password = ?, email = ? WHERE pk_username = ?");
        $stmt->execute([$newUsername, $firstName, $lastName, $password, $email, $username]);
    } else {
        $stmt = $pdo->prepare("UPDATE `user` SET pk_username = ?, firstName = ?, lastName = ?, email = ? WHERE pk_username = ?");
        $stmt->execute([$newUsername, $firstName, $lastName, $email, $username]);
    }
    // If username changed, update session
    $_SESSION['username'] = $newUsername;
    $_SESSION['flash'] = 'Profile updated successfully!';
    header('Location: edit_profile.php');
    exit;
}
?>

<div class="container">
    <h1>Edit Profile</h1>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Edit Your Account Details</h3>
        <form action="edit_profile.php" method="post">
            <?php echo csrf_input(); ?>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['pk_username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password">
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
</div>
</body>
</html>