<?php
// Include the site header (HTML <head>, navigation, etc.)
include __DIR__ . '/../includes/header.php'; 
?>

<div class="container admin-users">
    <h1>Admin – Users</h1>

    <!-- Show success message if a user was created/updated/deleted -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Show error message if something went wrong -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Form to create a new user -->
    <div class="card admin-create-user">
        <h2>Create User</h2>

        <form method="post" class="create-user-form" autocomplete="off">
            <!-- Hidden input to tell the controller this is a 'create' action -->
            <input type="hidden" name="action" value="create">

            <div class="form-row">
                <div class="form-group">
                    <label>Username</label>
                    <input name="username" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label>First Name</label>
                    <input name="firstName" autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input name="lastName" autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" autocomplete="off">
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>

            <!-- Submit button for creating the user -->
            <div class="create-user-actions">
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>

    <h2 class="mt-3">All Users</h2>

    <!-- Loop through all users and provide editable forms for each -->
    <?php foreach ($users as $u): ?>
    <div class="card mb-2 admin-user-card">

        <!-- Display the username as the card header -->
        <div class="admin-item-header">
            <?= htmlspecialchars($u['pk_username']) ?>
        </div>

        <!-- Form to update or delete the user -->
        <form method="post" class="admin-user-form">
            <!-- Pass username as hidden input to identify the user -->
            <input type="hidden" name="username" value="<?= htmlspecialchars($u['pk_username']) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input name="firstName" value="<?= htmlspecialchars($u['firstName']) ?>">
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input name="lastName" value="<?= htmlspecialchars($u['lastName']) ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input name="email" value="<?= htmlspecialchars($u['email']) ?>">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option value="User" <?= $u['role'] === 'User' ? 'selected' : '' ?>>User</option>
                        <option value="Admin" <?= $u['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
            </div>

            <!-- Buttons for updating or deleting the user -->
            <div class="admin-user-actions">
                <button name="action" value="update" class="btn btn-primary btn-sm">Save</button>
                <button name="action" value="delete"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this user?')">
                    Delete
                </button>
            </div>
        </form>

    </div>
    <?php endforeach; ?>

</div>

<?php 
// Include the site footer (closing HTML, scripts)
include __DIR__ . '/../includes/footer.php'; 
?>