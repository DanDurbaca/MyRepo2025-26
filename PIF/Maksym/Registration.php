<?php
include 'CommonCode.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['username'])) {
    header("Location: Dashboard.php");
    exit();
}

// Optional invite token from URL (?invite=...) or hidden POST field
$invite_token   = $_GET['invite'] ?? $_POST['invite_token'] ?? '';
$invite_creator = null;
if ($invite_token) {
    $invite_creator = validateInvite($invite_token);
}

$reg_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username  = isset($_POST['username'])  ? trim($_POST['username'])  : '';
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName  = isset($_POST['lastName'])  ? trim($_POST['lastName'])  : '';
    $email     = isset($_POST['email'])     ? trim($_POST['email'])     : '';
    $password  = isset($_POST['password'])  ? $_POST['password']        : '';
    $password2 = isset($_POST['password2']) ? $_POST['password2']       : '';

    if (empty($username))  $reg_errors[] = "Username is required.";
    if (empty($firstName)) $reg_errors[] = "First name is required.";
    if (empty($lastName))  $reg_errors[] = "Last name is required.";
    if (empty($email)) {
        $reg_errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $reg_errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $reg_errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $reg_errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $password2) {
        $reg_errors[] = "Passwords do not match.";
    }
    if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $reg_errors[] = "Username: 3-50 chars, letters/digits/underscore only.";
    }

    if (empty($reg_errors)) {
        if (userExists($username))  $reg_errors[] = "Username already exists.";
        if (emailExists($email))    $reg_errors[] = "Email already exists.";

        if (empty($reg_errors)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (pk_username, firstName, lastName, password, email, role) VALUES (?, ?, ?, ?, ?, 'User')");
            $stmt->bind_param("sssss", $username, $firstName, $lastName, $passwordHash, $email);
            if ($stmt->execute()) {
                $stmt->close();
                // If a valid invite was used, mark it consumed
                if ($invite_token && $invite_creator) {
                    markInviteUsed($invite_token, $username);
                }
                header("Location: Login.php");
                exit();
            } else {
                $reg_errors[] = "Registration failed. Please try again.";
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('register'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>

  <div class="auth-wrap">
    <div class="auth-card" style="max-width:460px;">
      <div class="auth-logo">
        <div class="auth-logo-mark"><span class="lp">P</span><span class="li">I</span><span class="lf">F</span></div>
        <div class="auth-logo-sub">Portable Indoor Feedback</div>
        <div style="color:var(--muted);font-size:.72rem;margin-top:.3rem;">
          <?php echo t('register'); ?>
          <?php if ($invite_creator): ?>
            &mdash; <span style="color:var(--green);">Invited by <?php echo htmlspecialchars($invite_creator); ?></span>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($reg_errors)): ?>
        <?php foreach ($reg_errors as $error): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="invite_token" value="<?php echo htmlspecialchars($invite_token); ?>" />

        <div class="form-row">
          <input type="text" name="username" placeholder="<?php echo t('username'); ?>" maxlength="50" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
        </div>
        <div class="form-row">
          <input type="text" name="firstName" placeholder="<?php echo t('first_name'); ?>" maxlength="50" required value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" />
        </div>
        <div class="form-row">
          <input type="text" name="lastName" placeholder="<?php echo t('last_name'); ?>" maxlength="50" required value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" />
        </div>
        <div class="form-row">
          <input type="email" name="email" placeholder="<?php echo t('email'); ?>" maxlength="50" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        </div>
        <div class="form-row">
          <input type="password" name="password" placeholder="<?php echo t('password'); ?> (min 6 chars)" required />
        </div>
        <div class="form-row">
          <input type="password" name="password2" placeholder="<?php echo t('confirm_password'); ?>" required />
        </div>
        <div class="form-row">
          <button type="submit" style="width:100%; justify-content:center;"><?php echo t('register'); ?></button>
        </div>
      </form>

      <div class="auth-switch">Already have an account? <a href="Login.php"><?php echo t('login'); ?></a></div>

      <div style="text-align:center; margin-top:1rem;">
        <select style="width:auto; padding:.28rem .5rem; font-size:.78rem;" onchange="setLang(this.value)">
          <option value="en" <?php if (getLang() === 'en') echo 'selected'; ?>>EN</option>
          <option value="uk" <?php if (getLang() === 'uk') echo 'selected'; ?>>UK</option>
          <option value="lb" <?php if (getLang() === 'lb') echo 'selected'; ?>>LB</option>
        </select>
      </div>
    </div>
  </div>

  <script>
    function setLang(l) {
      document.cookie = 'pif_lang=' + l + ';path=/;max-age=' + (60*60*24*365);
      location.reload();
    }
  </script>
</body>
</html>
