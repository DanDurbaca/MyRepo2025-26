<?php
//admin/users_readonly_view.php
// Include the site header (HTML <head>, navigation, etc.)
include __DIR__ . '/../includes/header.php'; 
?>

<h1>All Users (Read-Only)</h1>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loop through all users and display their information -->
            <?php foreach ($users as $u): ?>
            <tr>
                <!-- Escape output to prevent XSS attacks -->
                <td><?= htmlspecialchars($u['pk_username']) ?></td>
                <td><?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
// Include the site footer (closing HTML, scripts)
include __DIR__ . '/../includes/footer.php'; 
?>