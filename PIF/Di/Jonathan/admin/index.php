<?php
// Admin dashboard: overview and quick links to management pages
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/_header.php';
?>
<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>. Use the links below to manage the system.</p>

    <div class="card">
        <h3>Management</h3>
        <p><a href="users.php" class="btn">Manage Users</a> - View, edit, and manage user accounts.</p>
        <p><a href="stations.php" class="btn">Manage Stations</a> - Oversee station registrations and data.</p>
        <p><a href="secret_audit.php" class="btn">Secret Audit</a> - Monitor station secret access and usage.</p>
        <p><a href="measurements.php" class="btn">Manage Measurements</a> - View, filter, and delete measurements.</p>
        <p><a href="collections.php" class="btn">Manage Collections</a> - Create and manage collections.</p>
    </div>

    <div class="card">
        <h3>Quick Actions</h3>
        <p>
            <a href="../user/dashboard.php" class="btn">User Dashboard</a>
            <a href="../logout.php" class="btn">Logout</a>
            <a href="test_smtp.php" class="btn">Test SMTP</a>
            <a href="purge_secret_audit.php" class="btn btn-danger">Purge Audit</a>
        </p>
    </div>
</div>
</body>
</html>
