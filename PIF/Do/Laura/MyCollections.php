<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - My Collections</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Collection");

    // Require login before managing collections
    requireLogin();

    // Current logged-in user
    $me = getCurrentUser();

    // Handle rename action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rename') {
        $collectionId = $_POST['collection_id'] ?? 0;
        $newName = $_POST['new_name'] ?? '';
        $newDesc = $_POST['new_desc'] ?? '';

        if (collectionBelongsToUser($collectionId, $me)) {
            updateCollection($collectionId, $newName, $newDesc);
        }

        header("Location: MyCollections.php");
        exit;
    }

    // Handle delete action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $collectionId = $_POST['collection_id'] ?? 0;

        if (collectionBelongsToUser($collectionId, $me)) {
            deleteCollectionById($collectionId);
        }

        header("Location: MyCollections.php");
        exit;
    }

    // Handle share action - add friend access to collection
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'share') {
        $collectionId = $_POST['collection_id'] ?? 0;
        $friendUsername = $_POST['friend_username'] ?? '';

        if ($collectionId > 0 && $friendUsername !== '' && collectionBelongsToUser($collectionId, $me)) {
            // Verify friendship is mutual
            // Prepare query to verify mutual friendship between users
            $checkFriend = $connection->prepare("SELECT 1 FROM isfriend WHERE ((pkfk_user_user = ? AND pkfk_user_friend = ?) OR (pkfk_user_user = ? AND pkfk_user_friend = ?)) AND EXISTS (SELECT 1 FROM isfriend r WHERE (r.pkfk_user_user = ? AND r.pkfk_user_friend = ?) OR (r.pkfk_user_user = ? AND r.pkfk_user_friend = ?))");
            $checkFriend->bind_param("ssssssss", $me, $friendUsername, $friendUsername, $me, $me, $friendUsername, $friendUsername, $me);
            $checkFriend->execute();

            if ($checkFriend->get_result()->num_rows > 0) {
                // Check if already shared
                // Prepare query to prevent duplicate sharing rows
                $checkExists = $connection->prepare("SELECT 1 FROM hasaccess WHERE pkfk_user = ? AND pkfk_collection = ?");
                $checkExists->bind_param("si", $friendUsername, $collectionId);
                $checkExists->execute();

                if ($checkExists->get_result()->num_rows === 0) {
                    // Insert access
                    // Prepare insert to share collection with friend
                    $insert = $connection->prepare("INSERT INTO hasaccess (pkfk_user, pkfk_collection) VALUES (?, ?)");
                    $insert->bind_param("si", $friendUsername, $collectionId);
                    $insert->execute();
                }
            }
        }

        header("Location: MyCollections.php");
        exit;
    }

    // Handle unshare action - remove friend access to collection
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unshare') {
        $collectionId = $_POST['collection_id'] ?? 0;
        $friendUsername = $_POST['friend_username'] ?? '';

        if ($collectionId > 0 && $friendUsername !== '' && collectionBelongsToUser($collectionId, $me)) {
            // Delete access
            // Prepare delete to remove friend access
            $delete = $connection->prepare("DELETE FROM hasaccess WHERE pkfk_user = ? AND pkfk_collection = ?");
            $delete->bind_param("si", $friendUsername, $collectionId);
            $delete->execute();
        }

        header("Location: MyCollections.php");
        exit;
    }

    
    // Load mutual friends for dropdown
    $myFriends = getMutualFriends($me);

    // Load collections created by this user
    $myCollections = getCollectionsCreatedByUser($me);

    // Load who each collection is shared with
    $sharedWith = [];
    foreach ($myCollections as $col) {
        $sharedWith[$col['pk_collection']] = getUsersSharedWithCollection($col['pk_collection']);
    }
    ?>

    <h1><?php print $arrayOfStrings["MyCollectionsTitle"] ?></h1>

    <p><a href="collections_create.php"><?php print $arrayOfStrings["CreateNewCollection"] ?></a></p>

    <?php if (empty($myCollections)): ?>
        <p><?php print $arrayOfStrings["NoCollectionsYet"] ?></p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th><?php print $arrayOfStrings["TableName"] ?></th>
                <th><?php print $arrayOfStrings["TableDescription"] ?></th>
                <th><?php print $arrayOfStrings["TableActions"] ?></th>
            </tr>
            <?php foreach ($myCollections as $col): ?>
                <tr>
                    <td><?php echo htmlspecialchars($col['name']); ?></td>
                    <td><?php echo htmlspecialchars($col['description'] ?? ''); ?></td>
                    <td>
                        <a href="collection_view.php?id=<?php echo $col['pk_collection']; ?>"><?php print $arrayOfStrings["View"] ?></a>
                        |
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="rename" />
                            <input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>" />
                            <input type="text" name="new_name" value="<?php echo htmlspecialchars($col['name']); ?>" size="15" required />
                            <input type="text" name="new_desc" value="<?php echo htmlspecialchars($col['description'] ?? ''); ?>" size="20" />
                            <button type="submit"><?php print $arrayOfStrings["Rename"] ?></button>
                        </form>
                        |
                        <form method="POST" style="display:inline;" onsubmit="return confirm(<?php echo json_encode($arrayOfStrings['ConfirmDeleteCollection']); ?>);">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>" />
                            <button type="submit"><?php print $arrayOfStrings["Delete"] ?></button>
                        </form>
                        
                        <div style="margin-top:10px; padding-top:10px; border-top:1px solid #ccc;">
                            <strong><?php print $arrayOfStrings["ShareWithFriends"] ?></strong><br/>
                            <?php if (!empty($myFriends)): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="share" />
                                    <input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>" />
                                    <select name="friend_username" required>
                                        <option value=""><?php print $arrayOfStrings["SelectFriendDots"] ?></option>
                                        <?php foreach ($myFriends as $f): ?>
                                            <option value="<?php echo htmlspecialchars($f['pk_username']); ?>">
                                                <?php echo htmlspecialchars($f['pk_username'] . ' (' . ($f['firstName'] ?? '') . ' ' . ($f['lastName'] ?? '') . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit"><?php print $arrayOfStrings["Share"] ?></button>
                                </form>
                            <?php else: ?>
                                <p><?php print $arrayOfStrings["NoFriendsToShare"] ?></p>
                            <?php endif; ?>
                            
                            <strong><?php print $arrayOfStrings["CurrentlySharedWith"] ?></strong><br/>
                            <?php 
                            $currentAccess = $sharedWith[$col['pk_collection']] ?? [];
                            if (empty($currentAccess)): ?>
                                <p><?php print $arrayOfStrings["NotSharedWithAnyone"] ?></p>
                            <?php else: ?>
                                <ul style="margin:5px 0;">
                                    <?php foreach ($currentAccess as $access): ?>
                                        <li>
                                            <?php echo htmlspecialchars($access['pkfk_user']); ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="unshare" />
                                                <input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>" />
                                                <input type="hidden" name="friend_username" value="<?php echo htmlspecialchars($access['pkfk_user']); ?>" />
                                                <button type="submit"><?php print $arrayOfStrings["Unshare"] ?></button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    </div>
</body>

</html>
