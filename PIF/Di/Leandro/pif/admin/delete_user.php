<?php
/*
 * admin/delete_user.php
 * Purpose: Delete a user account (admin only). Expects `u` GET parameter.
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

$user = $_GET['u'] ?? null;

// Validation
if (!$user) {
    die("No user specified");
}

// Prevent admin from deleting themselves
if ($user === $_SESSION['username']) {
    die("You cannot delete your own account");
}

// Check if user exists
$stmt = $pdo->prepare("SELECT pk_username FROM user WHERE pk_username = ?");
$stmt->execute([$user]);

if (!$stmt->fetch()) {
    die("User not found");
}

// Delete user
$stmt = $pdo->prepare("DELETE FROM user WHERE pk_username = ?");
$stmt->execute([$user]);

header("Location: users.php");
exit;