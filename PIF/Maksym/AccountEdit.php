<?php
include 'CommonCode.php';
requireLogin();

$user = getUserByUsername($_SESSION['username']);
$currentUsername     = $user['pk_username'];
$currentFirstName    = $user['firstName'];
$currentLastName     = $user['lastName'];
$currentEmail        = $user['email'];
$currentPasswordHash = $user['password'];
$currentTheme        = $user['theme']    ?? 'dark';
$currentLanguage     = $user['language'] ?? 'en';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $newFirstName    = trim($_POST['firstName'] ?? '');
    $newLastName     = trim($_POST['lastName']  ?? '');
    $newEmail        = trim($_POST['email']     ?? '');
    $newPassword     = $_POST['newPassword']    ?? '';
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newTheme        = isset($_POST['theme'])    && in_array($_POST['theme'],    ['dark','light']) ? $_POST['theme']    : $currentTheme;
    $newLanguage     = isset($_POST['language']) && in_array($_POST['language'], ['en','uk','lb']) ? $_POST['language'] : $currentLanguage;

    if (!verifyPassword($currentUsername, $currentPassword)) {
        $errors[] = "Current password is incorrect.";
    }
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($newFirstName === '') $errors[] = "First name is required.";
    if ($newLastName === '')  $errors[] = "Last name is required.";
    if ($newPassword !== '' && strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters.";
    }
    if ($newEmail !== $currentEmail && emailExists($newEmail)) {
        $errors[] = "Email already exists.";
    }

    if (empty($errors)) {
        $newPasswordHash = !empty($newPassword) ? password_hash($newPassword, PASSWORD_DEFAULT) : $currentPasswordHash;

        if (updateUser($currentUsername, $newFirstName, $newLastName, $newEmail, $newPasswordHash, $newTheme, $newLanguage)) {
            updateSessionUser($newFirstName, $newLastName, $newEmail, $newTheme, $newLanguage);
            // Apply prefs as cookies
            setcookie('pif_theme', $newTheme,    time() + 60*60*24*365, '/');
            setcookie('pif_lang',  $newLanguage, time() + 60*60*24*365, '/');

            $currentFirstName = $newFirstName;
            $currentLastName  = $newLastName;
            $currentEmail     = $newEmail;
            $currentTheme     = $newTheme;
            $currentLanguage  = $newLanguage;

            $success = "Account updated successfully.";
        } else {
            $errors[] = "Failed to update account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('account'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
  <?php NavigationBar('account'); ?>

  <div class="container">
    <div class="card" style="max-width:540px; margin:0 auto;">
      <h1><?php echo t('edit'); ?> <?php echo t('account'); ?></h1>
      <p style="color:var(--muted);font-size:.82rem; margin-bottom:1rem;"><?php echo t('account_desc'); ?></p>

      <form method="POST">

        <div class="form-row">
          <label><?php echo t('username'); ?>: <span style="color:var(--muted);font-weight:400;"><?php echo t('cannot_be_changed'); ?></span></label>
          <input type="text" value="<?php echo htmlspecialchars($currentUsername); ?>" disabled />
        </div>

        <div class="form-row">
          <label><?php echo t('first_name'); ?>:</label>
          <input type="text" name="firstName" maxlength="50" value="<?php echo htmlspecialchars($currentFirstName); ?>" required />
        </div>

        <div class="form-row">
          <label><?php echo t('last_name'); ?>:</label>
          <input type="text" name="lastName" maxlength="50" value="<?php echo htmlspecialchars($currentLastName); ?>" required />
        </div>

        <div class="form-row">
          <label><?php echo t('email'); ?>:</label>
          <input type="email" name="email" maxlength="50" value="<?php echo htmlspecialchars($currentEmail); ?>" required />
        </div>

        <hr />

        <div class="form-row">
          <label><?php echo t('theme'); ?>:</label>
          <select name="theme">
            <option value="dark"  <?php if ($currentTheme === 'dark')  echo 'selected'; ?>><?php echo t('dark'); ?></option>
            <option value="light" <?php if ($currentTheme === 'light') echo 'selected'; ?>><?php echo t('theme_light'); ?></option>
          </select>
        </div>

        <div class="form-row">
          <label><?php echo t('language'); ?>:</label>
          <select name="language">
            <option value="en" <?php if ($currentLanguage === 'en') echo 'selected'; ?>>English</option>
            <option value="uk" <?php if ($currentLanguage === 'uk') echo 'selected'; ?>>Українська</option>
            <option value="lb" <?php if ($currentLanguage === 'lb') echo 'selected'; ?>>Lëtzebuergesch</option>
          </select>
        </div>

        <hr />

        <p style="color:var(--muted); font-size:.82rem; margin-bottom:1rem;"><?php echo t('leave_pwd_blank'); ?></p>

        <div class="form-row">
          <label><?php echo t('new_password'); ?>:</label>
          <input type="password" name="newPassword" placeholder="<?php echo t('leave_pwd_blank'); ?>" />
        </div>

        <div class="form-row">
          <label><?php echo t('current_password'); ?>:</label>
          <input type="password" name="currentPassword" required placeholder="<?php echo t('current_password'); ?>" />
        </div>

        <div class="form-row">
          <button type="submit"><?php echo t('save_changes'); ?></button>
          <button type="button" onclick="location.href='Account.php'"><?php echo t('cancel'); ?></button>
        </div>

        <?php
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<div class='alert alert-error'>" . htmlspecialchars($error) . "</div>";
            }
        }
        if (!empty($success)) {
            echo "<div class='alert alert-success'>" . htmlspecialchars($success) . "</div>";
        }
        ?>

      </form>
    </div>
  </div>
</body>
</html>
