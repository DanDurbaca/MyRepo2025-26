<?php
/*
 * friends/add_friend.php
 * Purpose: Handle POST requests to send a friend request to another user.
 */
require "../includes/config.php";
require "../includes/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: friends.php");
    exit;
}

$friend = trim($_POST['friend']);
$user = $_SESSION['username'];

// Validation
if (!$friend || $friend === $user) {
    header("Location: friends.php");
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT pk_username FROM user WHERE pk_username = ?");
$stmt->execute([$friend]);
if (!$stmt->fetch()) {
    header("Location: friends.php?error=user_not_found");
    exit;
}

// Create one-direction friend request (INSERT IGNORE prevents duplicates)
$stmt = $pdo->prepare("
    INSERT IGNORE INTO isfriend (pkfk_user_user, pkfk_user_friend)
    VALUES (?, ?)
");
$stmt->execute([$user, $friend]);

header("Location: friends.php");
exit;
?>