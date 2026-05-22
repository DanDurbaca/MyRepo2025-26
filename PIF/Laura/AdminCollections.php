<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Admin Collections</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    require_once(__DIR__ . "/CommonCode.php");
    NavigationBar1("admin");

    // Restrict page access to admins only
    requireAdmin();
    $me = getCurrentUser();

    // Handle rename action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rename') {
        $collectionId = $_POST['collection_id'] ?? 0;
        $newName = $_POST['new_name'] ?? '';
        $newDesc = $_POST['new_desc'] ?? '';

        if ($collectionId > 0) {
            updateCollection($collectionId, $newName, $newDesc);
        }

        header("Location: AdminCollections.php");
        exit;
    }

    // Handle delete action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $collectionId = $_POST['collection_id'] ?? 0;

        if ($collectionId > 0) {
            deleteCollectionById($collectionId);
        }

        header("Location: AdminCollections.php");
        exit;
    }

    // Load all collections for the admin table
    $allCollections = getAllCollections();
    ?>

    <h1><?php print $arrayOfStrings["AdminAllCollections"] ?></h1>

    <?php if (empty($allCollections)): ?>
        <p><?php print $arrayOfStrings["NoCollectionsFound"] ?></p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th><?php print $arrayOfStrings["TableName"] ?></th>
                <th><?php print $arrayOfStrings["TableDescription"] ?></th>
                <th><?php print $arrayOfStrings["CreatedBy"] ?></th>
                <th><?php print $arrayOfStrings["TableActions"] ?></th>
            </tr>
            <?php foreach ($allCollections as $col): ?>
                <tr>
                    <td><?php echo htmlspecialchars($col['name']); ?></td>
                    <td><?php echo htmlspecialchars($col['description'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($col['fk_user_creates']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="rename" />
                            <input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>" />
                            <input type="text" name="new_name" value="<?php echo htmlspecialchars($col['name']); ?>" size="15" required />
                            <input type="text" name="new_desc" value="<?php echo htmlspecialchars($col['description'] ?? ''); ?>" size="20" />
                            <button type="submit"><?php print $arrayOfStrings["Rename"] ?></button>
                        </form>
                        |
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>" />
                            <button type="submit"><?php print $arrayOfStrings["Delete"] ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p>
        <a href="Admin.php"><?php print $arrayOfStrings["Cancel"] ?></a>
    </p>

    </div>
</body>

</html>
