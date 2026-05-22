<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];
$name = '';
$description = '';
$collectionID = 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_collection'])) {
    $collectionID = isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : 0;
    $name         = isset($_POST['name'])          ? trim($_POST['name'])          : '';
    $description  = isset($_POST['description'])   ? trim($_POST['description'])   : '';

    if ($collectionID > 0 && $name !== '') {
        $col = getCollectionByID($collectionID);
        if ($col && $col['fk_user_creates'] === $username) {
            if (updateUserCollection($username, $collectionID, $name, $description)) {
                header('Location: MyCollections.php');
                exit();
            } else {
                $error = 'Unable to update collection.';
            }
        } else {
            $error = 'Collection not found or you are not the owner.';
        }
    } else {
        $error = 'Invalid input. Name cannot be empty.';
    }
}

if (isset($_GET['cid']) && (int)$_GET['cid'] > 0) {
    $collectionID = (int)$_GET['cid'];
    $col = getCollectionByID($collectionID);
    if (!$col || $col['fk_user_creates'] !== $username) {
        $error = "Collection not found or you are not allowed to edit it.";
        $collectionID = 0;
    } else {
        $name = $col['name'];
        $description = $col['description'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('edit_collection'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
  <?php NavigationBar('collections'); ?>

  <div class="container">
    <div class="card" style="max-width:640px; margin:0 auto;">
      <h1><?php echo t('edit_collection'); ?></h1>

      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($collectionID > 0): ?>
      <form method="POST" action="EditCollection.php">
        <input type="hidden" name="collection_id" value="<?php echo (int)$collectionID; ?>" />

        <div class="form-row">
          <label for="name"><?php echo t('name'); ?>:</label>
          <input id="name" name="name" type="text" maxlength="50" value="<?php echo htmlspecialchars($name); ?>" required style="flex:1;" />
        </div>

        <div class="form-row">
          <label for="description"><?php echo t('description'); ?>:</label>
          <textarea id="description" name="description" rows="3" style="flex:1;"><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="form-row" style="margin-top:12px;">
          <button type="submit" name="save_collection"><?php echo t('save_changes'); ?></button>
          <button type="button" onclick="location.href='MyCollections.php'"><?php echo t('cancel'); ?></button>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
