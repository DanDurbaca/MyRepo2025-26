<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];
$shared = getCollectionsSharedWithUser($username);
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('shared_with_me'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
  <?php NavigationBar('shared'); ?>

  <div class="container">
    <div class="page-title"><?php echo t('shared_with_me'); ?></div>
    <div class="page-sub"><?php echo t('shared_desc'); ?></div>

    <?php if (count($shared) === 0): ?>
      <div class="card"><div class="empty"><?php echo t('no_shared'); ?></div></div>
    <?php else: ?>
      <div class="grid-2">
        <?php foreach ($shared as $s): ?>
          <div class="card card-hover">
            <div style="font-weight:600; font-size:1rem; margin-bottom:.3rem;"><?php echo htmlspecialchars($s['name']); ?></div>
            <div style="color:var(--muted); font-size:.82rem; margin-bottom:.3rem;">By <?php echo htmlspecialchars($s['firstName'] . ' ' . $s['lastName']); ?></div>
            <?php if (!empty($s['description'])): ?>
              <div style="color:var(--muted); font-size:.82rem; margin-bottom:.5rem;"><?php echo htmlspecialchars($s['description']); ?></div>
            <?php endif; ?>
            <div style="color:var(--accent2); font-size:.78rem; margin-bottom:1rem;"><?php echo (int)$s['measurement_count']; ?> <?php echo t('measurements_unit'); ?></div>
            <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
              <button type="button" class="btn-sm" onclick="location.href='Collection.php?cid=<?php echo (int)$s['pk_collection']; ?>'"><?php echo t('open'); ?></button>
              <button type="button" class="btn-sm" onclick="location.href='Collection.php?cid=<?php echo (int)$s['pk_collection']; ?>&chart=1'"><?php echo t('chart_view'); ?></button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
