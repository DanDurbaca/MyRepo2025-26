<?php
include 'CommonCode.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['username'])) {
    header("Location: Dashboard.php");
    exit();
}

$login_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username)) $login_errors[] = "Username is required.";
    if (empty($password)) $login_errors[] = "Password is required.";

    if (empty($login_errors)) {
        $user = getUserByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username']  = $user['pk_username'];
            $_SESSION['firstName'] = $user['firstName'];
            $_SESSION['lastName']  = $user['lastName'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['theme']     = $user['theme']    ?? 'dark';
            $_SESSION['language']  = $user['language'] ?? 'en';

            // Apply user prefs as cookies
            setcookie('pif_theme', $_SESSION['theme'],    time() + 60*60*24*365, '/');
            setcookie('pif_lang',  $_SESSION['language'], time() + 60*60*24*365, '/');

            header("Location: Dashboard.php");
            exit();
        } else {
            $login_errors[] = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('login'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-logo">
        <div class="auth-logo-mark"><span class="lp">P</span><span class="li">I</span><span class="lf">F</span></div>
        <div class="auth-logo-sub">Portable Indoor Feedback</div>
        <div style="color:var(--muted);font-size:.72rem;margin-top:.3rem;"><?php echo t('login'); ?></div>
      </div>

      <?php if (!empty($login_errors)): ?>
        <?php foreach ($login_errors as $error): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      <?php endif; ?>

      <form method="POST">
        <div class="form-row">
          <input type="text" name="username" placeholder="<?php echo t('username'); ?>" maxlength="50" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
        </div>
        <div class="form-row">
          <input type="password" name="password" placeholder="<?php echo t('password'); ?>" required />
        </div>
        <div class="form-row">
          <button type="submit" style="width:100%; justify-content:center;"><?php echo t('login'); ?></button>
        </div>
      </form>

      <div class="auth-switch">Don't have an account? <a href="Registration.php"><?php echo t('register'); ?></a></div>

      <!-- Language selector for unauthenticated users -->
      <div style="text-align:center; margin-top:1rem;">
        <select id="loginLang" style="width:auto; padding:.28rem .5rem; font-size:.78rem;">
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
    $(document).ready(function() {
      $('#loginLang').on('change', function() { setLang($(this).val()); });
    });
  </script>
</body>
</html>
