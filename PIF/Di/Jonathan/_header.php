<?php
// _header.php - Site header partial including common CSS/JS and the main navigation
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Indoor Climate Data'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<main>
<nav class="nav">
    <div class="nav-inner">
        <a class="nav-brand" href="/index.php">Indoor Climate</a>
        <div class="nav-links">
            <a href="/index.php">Home</a>
            <a href="/user/dashboard.php">Dashboard</a>
            <a href="/user/stations.php">Stations</a>
            <a href="/user/collections.php">Collections</a>
            <a href="/user/friends.php">Friends</a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="/admin/index.php">Admin</a>
            <?php endif; ?>
            <?php if (!isset($_SESSION['username'])): ?>
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>
            <?php else: ?>
            <a href="/user/edit_profile.php">Profile</a>
            <a href="/logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<script src="/js/app.js"></script>