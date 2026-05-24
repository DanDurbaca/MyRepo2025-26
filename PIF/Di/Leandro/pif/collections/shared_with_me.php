<?php
require "../includes/config.php";
require "../includes/auth_check.php";

// Fetch collections shared with the user
$stmt = $pdo->prepare("
    SELECT c.*
    FROM collection c
    JOIN hasaccess h ON c.pk_collection = h.pkfk_collection
    WHERE h.pkfk_user = ?
    ORDER BY c.pk_collection DESC
");
$stmt->execute([$_SESSION['username']]);
$collections = $stmt->fetchAll();
?>
/*
 * collections/shared_with_me.php
 * Purpose: List collections that were shared with the logged-in user.
 */

<!DOCTYPE html>
<html>
<head>
    <title>Collections Shared With Me</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>Collections Shared With Me</h2>

    <?php if (count($collections) === 0): ?>
        <p>No collections have been shared with you.</p>
    <?php else: ?>
        <table class="table table-dark table-striped">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Owner</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($collections as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['description'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['fk_user_creates']) ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary"
                           href="view_collection.php?id=<?= $c['pk_collection'] ?>">
                            View
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
