<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Account</title>
</head>
<body class="stations-page">
<?php
include_once("commonphp.php");
 
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: index.php');
    exit;
}
 
// Load current user data
$res  = mysqli_query($conn, 'SELECT * FROM `User` WHERE user_ID = ' . (int)$userId . ' LIMIT 1');
$user = $res ? mysqli_fetch_assoc($res) : [];
$nameParts = explode(' ', $user['full_name'] ?? '', 2);
$firstName = $nameParts[0] ?? '';
$lastName  = $nameParts[1] ?? '';
 
$errors = [];
$msg    = '';
 
// ── HANDLE POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUName  = trim($_POST['UName']          ?? '');
    $newEmail  = trim($_POST['email_address']  ?? '');
    $newFirst  = trim($_POST['first_name']     ?? '');
    $newLast   = trim($_POST['last_name']      ?? '');
    $newFull   = trim($newFirst . ' ' . $newLast);
    $newPass   = $_POST['password']  ?? '';
    $newPass2  = $_POST['password2'] ?? '';
 
    // Validate
    if ($newUName === '')                             $errors[] = 'Username cannot be empty.';
    if ($newEmail === '')                             $errors[] = 'Email cannot be empty.';
    if ($newFirst === '' || $newLast === '')          $errors[] = 'First and last name cannot be empty.';
    if ($newPass !== '' && $newPass !== $newPass2)    $errors[] = 'Passwords do not match.';
 
    // Check duplicate username
    if (empty($errors) && $newUName !== ($user['UName'] ?? '')) {
        $chk = mysqli_prepare($conn, 'SELECT user_ID FROM `User` WHERE UName = ? AND user_ID != ? LIMIT 1');
        mysqli_stmt_bind_param($chk, 'si', $newUName, $userId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) $errors[] = 'Username already taken.';
        mysqli_stmt_close($chk);
    }
 
    // Check duplicate email
    if (empty($errors) && $newEmail !== ($user['email_address'] ?? '')) {
        $chk = mysqli_prepare($conn, 'SELECT user_ID FROM `User` WHERE email_address = ? AND user_ID != ? LIMIT 1');
        mysqli_stmt_bind_param($chk, 'si', $newEmail, $userId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) $errors[] = 'Email already in use.';
        mysqli_stmt_close($chk);
    }
 
    if (empty($errors)) {
        if ($newUName !== ($user['UName'] ?? '')) {
            $s = mysqli_prepare($conn, 'UPDATE `User` SET UName = ? WHERE user_ID = ?');
            mysqli_stmt_bind_param($s, 'si', $newUName, $userId);
            mysqli_stmt_execute($s); mysqli_stmt_close($s);
            $_SESSION['username'] = $newUName;
            $_SESSION['User']     = $newUName;
        }
        if ($newEmail !== ($user['email_address'] ?? '')) {
            $s = mysqli_prepare($conn, 'UPDATE `User` SET email_address = ? WHERE user_ID = ?');
            mysqli_stmt_bind_param($s, 'si', $newEmail, $userId);
            mysqli_stmt_execute($s); mysqli_stmt_close($s);
        }
        if ($newFull !== ($user['full_name'] ?? '')) {
            $s = mysqli_prepare($conn, 'UPDATE `User` SET full_name = ? WHERE user_ID = ?');
            mysqli_stmt_bind_param($s, 'si', $newFull, $userId);
            mysqli_stmt_execute($s); mysqli_stmt_close($s);
        }
        if ($newPass !== '') {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $s = mysqli_prepare($conn, 'UPDATE `User` SET Upswd = ? WHERE user_ID = ?');
            mysqli_stmt_bind_param($s, 'si', $hash, $userId);
            mysqli_stmt_execute($s); mysqli_stmt_close($s);
        }
 
        // Reload user data
        $res  = mysqli_query($conn, 'SELECT * FROM `User` WHERE user_ID = ' . (int)$userId . ' LIMIT 1');
        $user = $res ? mysqli_fetch_assoc($res) : [];
        $nameParts = explode(' ', $user['full_name'] ?? '', 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';
        $msg = 'Account updated successfully.';
    }
}
?>
 
<div class="container">
    <h1 class="Title">Account</h1>
    <p class="lead">Update your personal details and password.</p>
 
    <?php if ($msg !== ''): ?>
        <div class="alert" style="background:#d1fae5;border-color:#6ee7b7;color:#065f46;"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert" style="background:#fee2e2;border-color:#fca5a5;color:#991b1b;">
            <ul style="margin:0;padding-left:1.2rem;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
 
    <div class="section-card">
        <h2>Edit Details</h2>
        <form method="post">
            <div class="field-row">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="field-row">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="field-row">
                <label>Username</label>
                <input type="text" name="UName" value="<?= htmlspecialchars($user['UName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="field-row">
                <label>Email</label>
                <input type="email" name="email_address" value="<?= htmlspecialchars($user['email_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="field-row">
                <label>New Password <span class="note" style="font-weight:400;">(leave blank to keep current)</span></label>
                <input type="password" name="password">
            </div>
            <div class="field-row">
                <label>Confirm New Password</label>
                <input type="password" name="password2">
            </div>
            <div class="button-row">
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>