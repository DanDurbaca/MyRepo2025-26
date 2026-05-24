<?php
/*
 * admin/users.php
 * Purpose: Admin interface to list users, toggle admin role and delete users.
 * Sections:
 *  - Includes: config, auth and admin checks
 *  - Fetch: load all users from `user` table
 *  - Renders: table with actions to toggle admin and delete accounts
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

/* Fetch all users */
$users = $pdo->query("
    SELECT pk_username, email, role
    FROM user
    ORDER BY pk_username
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Users</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Users</h2>

    <?php if (count($users) === 0): ?>
        <p class="">No users found.</p>
    <?php else: ?>
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th style="width: 260px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['pk_username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td class="d-flex gap-2">

                        <!-- TOGGLE ADMIN -->
                        <form method="post" action="toogle_admin.php">
    <input type="hidden" name="username"
           value="<?= htmlspecialchars($u['pk_username']) ?>">

    <?php if ($u['role'] === 'Admin'): ?>
        <button class="btn btn-sm btn-outline-danger">
            Remove admin
        </button>
    <?php else: ?>
        <button class="btn btn-sm btn-outline-success">
            Make admin
        </button>
    <?php endif; ?>
</form>


                        <!-- DELETE USER -->
                        <?php if ($u['pk_username'] !== $_SESSION['username']): ?>
                            <form method="post" action="delete_user.php"
                                  onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="username"
                                       value="<?= htmlspecialchars($u['pk_username']) ?>">
                                <button class="btn btn-sm btn-outline-danger">
                                    Delete
                                </button>
                            </form>
                        <?php else: ?>
                            <span class=" small">You</span>
                        <?php endif; ?>

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
