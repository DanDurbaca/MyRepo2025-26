<?php
/*
 * admin/collections.php
 * Purpose: Admin page to view and delete all collections across the system.
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

/* Fetch all collections with owner */
$stmt = $pdo->query("
    SELECT c.pk_collection, c.name, c.fk_user_creates
    FROM collection c
    ORDER BY c.name
");
$collections = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Collections</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">All Collections</h2>

    <?php if (count($collections) === 0): ?>
        <p class="">No collections found.</p>
    <?php else: ?>
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Owner</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($collections as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['fk_user_creates']) ?></td>
                    <td>
                        <a href="../collections/view_collection.php?id=<?= $c['pk_collection'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            View
                        </a>

                        <a href="delete_collections.php?id=<?= $c['pk_collection'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this collection?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
