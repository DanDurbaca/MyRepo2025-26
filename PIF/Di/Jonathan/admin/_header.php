<?php
// Shared admin header include
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/csrf.php';
// Require admin rights
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['flash'] = 'Admin access required.';
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Panel'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<nav class="nav nav-admin">
    <div class="nav-inner">
        <a class="nav-brand" href="/admin/index.php">Admin Panel</a>
        <div class="nav-links">
            <a href="/admin/index.php">Dashboard</a>
            <a href="/admin/users.php">Users</a>
            <a href="/admin/stations.php">Stations</a>
            <a href="/admin/measurements.php">Measurements</a>
            <a href="/admin/backups.php">Backups</a>
            <a href="/admin/transfer_logs.php">Transfer Logs</a>
            <a href="/admin/collections.php">Collections</a>
            <a href="/admin/secret_audit.php">Secret Audit</a>
            <a href="/user/dashboard.php">Back to Site</a>
            <a href="/logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        </div>
    </div>
</nav>
<?php if (isset($_SESSION['flash'])): ?>
<div class="container">
    <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
</div>
<?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<script src="../js/app.js"></script>
