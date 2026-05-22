<?php
require "../includes/config.php";
require "../includes/auth_check.php";

// Fetch collections created by the user
$stmt = $pdo->prepare("
    SELECT *
    FROM collection
    WHERE fk_user_creates = ?
    ORDER BY pk_collection DESC
");
$stmt->execute([$_SESSION['username']]);
$collections = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Collections</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>My Collections</h2>

    <a href="create_collection.php" class="btn btn-primary mb-3">
        Create new collection
    </a>

    <?php if (count($collections) === 0): ?>
        <p>You haven’t created any collections yet.</p>
    <?php else: ?>
        <table class="table table-dark table-striped">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($collections as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['description'] ?? '') ?></td>
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
