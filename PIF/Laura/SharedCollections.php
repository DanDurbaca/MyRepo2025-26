<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Shared Collections</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
    <?php
    // Load shared utilities and navigation
    include_once("CommonCode.php");
    NavigationBar1("Collection");

    // Require login before viewing shared collections
    requireLogin();

    // Current logged-in user
    $me = getCurrentUser();

    // Get collections shared with this user via hasaccess
    $sharedCollections = getSharedCollections($me);
     ?>

     <h1><?php print $arrayOfStrings["CollectionsSharedWithMe"] ?></h1>

     <p><?php print $arrayOfStrings["SharedCollectionsDescription"] ?></p>

     <?php if (empty($sharedCollections)): ?>
         <p><?php print $arrayOfStrings["NoCollectionsShared"] ?></p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th><?php print $arrayOfStrings["TableName"] ?></th>
                <th><?php print $arrayOfStrings["TableDescription"] ?></th>
                 <th><?php print $arrayOfStrings["TableOwner"] ?></th>
                 <th><?php print $arrayOfStrings["TableActions"] ?></th>
            </tr>
            <?php foreach ($sharedCollections as $col): ?>
                <tr>
                    <td><?php echo htmlspecialchars($col['name']); ?></td>
                    <td><?php echo htmlspecialchars($col['description'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($col['fk_user_creates']); ?></td>
                    <td>
                        <a href="collection_view.php?id=<?php echo $col['pk_collection']; ?>"><?php print $arrayOfStrings["View"] ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    </div>
</body>

</html>
