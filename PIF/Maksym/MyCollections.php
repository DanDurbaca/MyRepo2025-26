<?php
// MyCollections.php — list of collections created by the current user
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_collection'])) {
    $collectionID = isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : 0;
    if ($collectionID > 0) {
        if (deleteUserCollection($username, $collectionID)) {
            $success_message = 'Collection deleted.';
        } else {
            $error_message = 'Unable to delete collection.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_collection'])) {
    $collectionID = isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : 0;
    $usernames = isset($_POST['usernames']) && is_array($_POST['usernames']) ? $_POST['usernames'] : [];
    if ($collectionID > 0) {
        list($ok, $msg) = setCollectionAccessByUsernames($collectionID, $username, $usernames);
        if ($ok) $success_message = $msg; else $error_message = $msg;
    } else {
        $error_message = 'Invalid collection id for sharing.';
    }
}

$myCollections = getUserCollections($username);
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('my_collections'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
  <?php NavigationBar('collections'); ?>

  <div class="container">
    <div class="page-title"><?php echo t('my_collections'); ?></div>
    <div class="page-sub"><?php echo t('collections_desc'); ?></div>

    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (count($myCollections) === 0): ?>
      <div class="card"><div class="empty"><?php echo t('no_collections'); ?> <a href="Measurements.php" style="color:var(--accent);"><?php echo t('measurements'); ?></a>.</div></div>
    <?php else: ?>
      <?php $friends = getFriends($username); ?>
      <div class="grid-2">
        <?php foreach ($myCollections as $c): ?>
          <?php $sharedUsers = getCollectionAccessUsernames((int)$c['pk_collection']); ?>
          <div class="card card-hover">
            <div style="font-weight:600; font-size:1rem; margin-bottom:.3rem;"><?php echo htmlspecialchars($c['name']); ?></div>
            <?php if (!empty($c['description'])): ?>
              <div style="color:var(--muted); font-size:.82rem; margin-bottom:.3rem;"><?php echo htmlspecialchars($c['description']); ?></div>
            <?php endif; ?>
            <div style="color:var(--accent2); font-size:.78rem; margin-bottom:1rem;"><?php echo (int)$c['measurement_count']; ?> <?php echo t('measurements_unit'); ?></div>

            <?php if (!empty($sharedUsers)): ?>
              <div style="margin-bottom:.7rem; font-size:.78rem; color:var(--muted);">
                <?php echo t('shared_with'); ?>:
                <?php foreach ($sharedUsers as $su): ?>
                  <span class="badge badge-user" style="margin:.1rem;"><?php echo htmlspecialchars($su); ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
              <form method="POST" style="display:inline;">
                <input type="hidden" name="collection_id" value="<?php echo (int)$c['pk_collection']; ?>" />
                <button type="button" class="btn-sm" onclick="location.href='Collection.php?cid=<?php echo (int)$c['pk_collection']; ?>'"><?php echo t('open'); ?></button>
                <button type="button" class="btn-sm" onclick="location.href='EditCollection.php?cid=<?php echo (int)$c['pk_collection']; ?>'"><?php echo t('edit'); ?></button>
                <button type="submit" name="delete_collection" class="danger btn-sm" onclick="return confirm('Are you sure you want to delete this collection?');"><?php echo t('delete'); ?></button>
                <button type="button" class="btn-sm share-btn" data-cid="<?php echo (int)$c['pk_collection']; ?>" data-name="<?php echo htmlspecialchars($c['name']); ?>"><?php echo count($sharedUsers) ? t('shared_with') : t('share_collection'); ?></button>
              </form>
            </div>

            <div id="friends-template-<?php echo (int)$c['pk_collection']; ?>" style="display:none;">
              <?php if (count($friends) === 0): ?>
                <div style="color:var(--muted); font-size:.85rem;"><?php echo t('no_friends'); ?> <?php echo t('add_friend'); ?>.</div>
              <?php else: ?>
                <?php foreach ($friends as $fr): ?>
                  <?php $isShared = in_array($fr, $sharedUsers); ?>
                  <div style="padding:6px 4px; display:flex; justify-content:space-between; align-items:center;">
                    <div><?php echo htmlspecialchars($fr); ?></div>
                    <input type="checkbox" name="usernames[]" value="<?php echo htmlspecialchars($fr); ?>" <?php if ($isShared) echo 'checked'; ?> />
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div id="shareModal" class="modal" style="display:none;">
    <div class="modal-content">
      <h3 id="modalTitle"><?php echo t('share_collection'); ?></h3>
      <form id="shareForm" method="POST">
        <input type="hidden" name="collection_id" id="modalCollectionId" value="0" />
        <input type="hidden" name="share_collection" value="1" />
        <div id="modalCheckboxes" style="max-height:300px; overflow:auto; margin:8px 0;"></div>
        <div style="display:flex; gap:.5rem; justify-content:flex-end; margin-top:1rem;">
          <button type="button" id="shareCancel"><?php echo t('cancel'); ?></button>
          <button type="submit"><?php echo t('save'); ?></button>
        </div>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      function openModal(cid, name) {
        $('#modalTitle').text('<?php echo t('share_collection'); ?>: ' + name);
        var tpl = $('#friends-template-' + cid);
        if (!tpl.length) {
          $('#modalCheckboxes').html('<div style="color:var(--red);">No friends template available.</div>');
        } else {
          $('#modalCheckboxes').html(tpl.html());
        }
        $('#modalCollectionId').val(cid);
        $('#shareModal').css('display', 'flex');
      }

      function closeModal() {
        $('#shareModal').hide();
        $('#modalCheckboxes').empty();
        $('#modalCollectionId').val(0);
      }

      $('#shareModal').on('click', function(e) { if (e.target === this) closeModal(); });
      $('#shareCancel').on('click', closeModal);

      $('.share-btn').on('click', function() {
        openModal($(this).data('cid'), $(this).data('name'));
      });
    });
  </script>
</body>
</html>
