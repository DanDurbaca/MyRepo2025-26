<?php
/*
 * collections/share_collection.php
 * Purpose: Grant another user access to a collection by inserting into `hasaccess`.
 */
require "../includes/config.php";
require "../includes/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: collections.php");
    exit;
}

$cid = $_POST['collection_id'] ?? null;
$friend = $_POST['friend_username'] ?? null;

if (!$cid || !$friend) {
    header("Location: collections.php");
    exit;
}

// Verify ownership
$stmt = $pdo->prepare("
    SELECT pk_collection
    FROM collection
    WHERE pk_collection = ?
      AND fk_user_creates = ?
");
$stmt->execute([$cid, $_SESSION['username']]);

if (!$stmt->fetch()) {
    die("Access denied - You don't own this collection");
}

// Check friendship
$stmt = $pdo->prepare("
    SELECT *
    FROM isfriend
    WHERE pkfk_user_user = ?
      AND pkfk_user_friend = ?
");
$stmt->execute([$_SESSION['username'], $friend]);

if (!$stmt->fetch()) {
    die("Cannot share - Not friends with this user");
}

// Share collection (INSERT IGNORE prevents duplicates)
$stmt = $pdo->prepare("
    INSERT IGNORE INTO hasaccess (pkfk_user, pkfk_collection)
    VALUES (?, ?)
");
$stmt->execute([$friend, $cid]);

header("Location: view_collection.php?id=" . urlencode($cid));
exit;
