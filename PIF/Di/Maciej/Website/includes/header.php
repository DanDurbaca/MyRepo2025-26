<?php
// includes/header.php
// Navigation bar and page header for all pages

// Start session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection to allow queries in header (e.g., pending friends)
require_once __DIR__ . '/../config/database.php';

// Get current theme from session, default to 'light'
$theme = $_SESSION['theme'] ?? 'light';

// Get session info for current user
$username   = $_SESSION['username'] ?? '';            // Logged-in username
$firstName  = $_SESSION['firstName'] ?? $username;   // Display name fallback to username
$role       = $_SESSION['role'] ?? 'User';           // User role, default 'User'
$is_admin   = ($role === 'Admin');                   // Boolean indicating admin privileges

// Full path of current script, used for marking active navigation links
$script_path = $_SERVER['SCRIPT_NAME'];

// Count pending friend requests for notifications
$pending_friend_count = 0;
if ($username) {
    $stmt = getDbConnection()->prepare("
        SELECT COUNT(*)
        FROM isfriend f
        WHERE f.pkfk_user_friend = :me
          AND NOT EXISTS (
              SELECT 1 FROM isfriend
              WHERE pkfk_user_user = :me
                AND pkfk_user_friend = f.pkfk_user_user
          )
    ");
    $stmt->execute([':me' => $username]);
    $pending_friend_count = (int)$stmt->fetchColumn(); // Number of friend requests waiting for user's response
}

// Determine if page has extra CSS based on filename
$current_page = basename($script_path);
$page_specific_css = [
    'station_measurements_view.php' => 'measurements.css',
    'collections_view.php' => 'collections.css',
    'shared_view.php'      => 'shared.css',
];
$extra_css = $page_specific_css[$current_page] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portable Indoor Feedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Main stylesheets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <?php if ($extra_css): ?>
        <!-- Page-specific CSS -->
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= $extra_css ?>">
    <?php endif; ?>
</head>
<body class="<?= htmlspecialchars($theme) ?>"> <!-- Apply light/dark theme -->

<nav class="main-nav">
    <div class="nav-logo">
        <a href="<?= BASE_URL ?>/<?= $is_admin ? 'admin/dashboard.php' : 'controller/dashboard.php' ?>">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo"> <!-- Logo links to dashboard -->
        </a>
    </div>

    <?php if ($username): ?>
        <!-- Dashboard navigation link -->
        <a href="<?= BASE_URL ?>/<?= $is_admin ? 'admin/dashboard.php' : 'controller/dashboard.php' ?>"
           class="<?= $script_path === '/PIF/Website/admin/dashboard.php' || $script_path === '/PIF/Website/controller/dashboard.php' ? 'active' : '' ?>">
           Dashboard
        </a>

        <!-- Stations navigation link -->
        <a href="<?= BASE_URL ?>/controller/stations.php"
           class="<?= $script_path === '/PIF/Website/controller/stations.php' ? 'active' : '' ?>">
           Stations
        </a>

        <!-- Measurements navigation link -->
        <a href="<?= BASE_URL ?>/station_measurements.php"
           class="<?= $script_path === '/PIF/Website/station_measurements.php' ? 'active' : '' ?>">
           Measurements
        </a>

        <!-- Profile navigation link -->
        <a href="<?= BASE_URL ?>/controller/profile.php"
           class="<?= $script_path === '/PIF/Website/controller/profile.php' ? 'active' : '' ?>">
           Profile
        </a>

        <!-- Friends navigation link with pending request badge -->
        <a href="<?= BASE_URL ?>/controller/friends.php"
           class="<?= $script_path === '/PIF/Website/controller/friends.php' ? 'active' : '' ?>">
           Friends
            <?php if ($pending_friend_count > 0): ?>
                <span class="badge-notification"><?= $pending_friend_count ?></span> <!-- Notification badge -->
            <?php endif; ?>
        </a>

        <!-- Collections navigation link -->
        <a href="<?= BASE_URL ?>/controller/collections.php"
           class="<?= $script_path === '/PIF/Website/controller/collections.php' ? 'active' : '' ?>">
           Collections
        </a>

        <!-- Shared collections link -->
        <a href="<?= BASE_URL ?>/controller/shared.php"
           class="<?= $script_path === '/PIF/Website/controller/shared.php' ? 'active' : '' ?>">
           Shared
        </a>

        <?php if ($is_admin): ?>
            <!-- Admin-only nav links -->
            <a href="<?= BASE_URL ?>/admin/users.php"
               class="<?= $script_path === '/PIF/Website/admin/users.php' ? 'active' : '' ?>">
               Manage Users
            </a>
            <a href="<?= BASE_URL ?>/admin/stations.php"
               class="<?= $script_path === '/PIF/Website/admin/stations.php' ? 'active' : '' ?>">
               Manage Stations
            </a>
        <?php endif; ?>

        <!-- User dropdown for preferences and logout -->
        <div class="nav-username-wrapper" tabindex="0">
            <span class="nav-username">Hello, <?= htmlspecialchars($firstName) ?> &#x25BC;</span>
            <div class="nav-dropdown">
                <a href="<?= BASE_URL ?>/controller/preferences.php">Preferences</a>
                <a href="<?= BASE_URL ?>/logout.php" class="logout">Logout</a>
            </div>
        </div>

    <?php else: ?>
        <!-- Links for unauthenticated users -->
        <a href="<?= BASE_URL ?>/login.php"
           class="<?= $script_path === '/PIF/Website/login.php' ? 'active' : '' ?>">Login</a>
        <a href="<?= BASE_URL ?>/signup.php"
           class="<?= $script_path === '/PIF/Website/signup.php' ? 'active' : '' ?>">Sign Up</a>
    <?php endif; ?>
</nav>

<main class="main-content"> <!-- Main content container begins -->