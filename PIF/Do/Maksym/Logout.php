<?php
include 'CommonCode.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_destroy();
    header("Location: Login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('logout'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
  <?php NavigationBar('logout'); ?>

  <div class="container">
    <div class="card" style="max-width:480px; margin:0 auto;">
      <h1 style="text-align:center;"><?php echo t('logout'); ?></h1>
      <div style="text-align: center;">
        <p style="margin-bottom: 1.5rem; color: var(--muted);">Are you sure you want to logout?</p>
        <form method="POST" style="display: inline;">
          <button type="submit"><?php echo t('logout'); ?></button>
          <button type="button" onclick="location.href='Dashboard.php'"><?php echo t('cancel'); ?></button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
