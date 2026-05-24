<?php
/*
 * friends/remove_friend.php
 * Purpose: Remove a mutual friendship and revoke collection access between two users.
 * Sections:
 *  - Deletes `isfriend` rows in both directions
 *  - Removes `hasaccess` entries related to collections between the users
 */
require "../includes/config.php";
require "../includes/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: friends.php");
    exit;
}

$user   = $_SESSION['username'];
$friend = $_POST['friend'] ?? null;

if (!$friend || $friend === $user) {
    header("Location: friends.php");
    exit;
}

/* 1️⃣ Remove friendship BOTH directions */
$stmt = $pdo->prepare("
    DELETE FROM isfriend
    WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?)
       OR (pkfk_user_user = ? AND pkfk_user_friend = ?)
");
$stmt->execute([
    $user, $friend,
    $friend, $user
]);

/* 2️⃣ Remove collection access BOTH ways */
$stmt = $pdo->prepare("
    DELETE h
    FROM hasaccess h
    JOIN collection c ON c.pk_collection = h.pkfk_collection
    WHERE (c.fk_user_creates = ? AND h.pkfk_user = ?)
       OR (c.fk_user_creates = ? AND h.pkfk_user = ?)
");
$stmt->execute([
    $user, $friend,
    $friend, $user
]);

header("Location: friends.php");
exit;
