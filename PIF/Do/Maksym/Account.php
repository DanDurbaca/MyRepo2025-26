<?php include 'CommonCode.php'; requireLogin(); ?>
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
      <h1><?php echo t('account'); ?></h1>

      <p style="margin-bottom: .8rem;"><strong><?php echo t('username'); ?>:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
      <p style="margin-bottom: .8rem;"><strong><?php echo t('first_name'); ?>:</strong> <?php echo htmlspecialchars($_SESSION['firstName']); ?></p>
      <p style="margin-bottom: .8rem;"><strong><?php echo t('last_name'); ?>:</strong> <?php echo htmlspecialchars($_SESSION['lastName']); ?></p>
      <p style="margin-bottom: .8rem;"><strong><?php echo t('email'); ?>:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
      <p style="margin-bottom: .8rem;"><strong><?php echo t('role'); ?>:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
      <p style="margin-bottom: .8rem;"><strong><?php echo t('theme'); ?>:</strong> <?php echo htmlspecialchars($_SESSION['theme'] ?? 'dark'); ?></p>
      <p style="margin-bottom: 1.5rem;"><strong><?php echo t('language'); ?>:</strong> <?php echo htmlspecialchars(strtoupper($_SESSION['language'] ?? 'en')); ?></p>

      <a href="AccountEdit.php"><button type="button"><?php echo t('edit'); ?></button></a>
    </div>
  </div>
</body>
</html>
