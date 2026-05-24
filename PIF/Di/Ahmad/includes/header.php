<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>Indoor Feedback</title>
</head>
<body>
<nav>
    <a href="/index.php" style="font-weight:bold; color:white;">Indoor Feedback</a>
    
    <?php if (isset($_SESSION['username'])): ?>
        <a href="/stations.php">My Stations</a>
        <a href="/data.php">Measurement Data</a>
        <a href="/friends.php">Friends</a>
        <a href="/collections.php">My Collections</a>
        <a href="/shared.php">Shared Collections</a>
        
    <?php if (isset($_SESSION['role']) && strcasecmp(trim($_SESSION['role']), 'admin') === 0): ?>
        <div style="display:inline-block; margin-left:10px; padding-left:10px; border-left: 1px solid #475569;">
            <span style="font-size: 0.7em; color: #94a3b8; display: block; margin-top: -10px;">ADMIN</span>
            <a href="/admin_users.php" style="color: #f472b6; font-size: 0.9em; margin-right:5px;">Users</a>
            <a href="/admin_stations.php" style="color: #f472b6; font-size: 0.9em; margin-right:5px;">Stations</a>
            <a href="/admin_measurements.php" style="color: #f472b6; font-size: 0.9em; margin-right:5px;">Data</a>
            <a href="/admin_collections.php" style="color: #f472b6; font-size: 0.9em;">Collections</a>
        </div>
    <?php endif; ?>
            
        <div style="margin-left:auto; display:flex; gap:15px; align-items:center;">
            <a href="/profile.php">👤 Profile</a>
            <a href="/logout.php" style="opacity:0.7; font-size:0.9em;">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        </div>
    <?php else: ?>
        <div style="margin-left:auto;">
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>
        </div>
    <?php endif; ?>
</nav>
<div class="container">