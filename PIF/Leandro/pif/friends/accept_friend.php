<?php
/*
 * friends/accept_friend.php
 * Purpose: Accept an incoming friend request by inserting the reciprocal `isfriend` row.
 */
require "../includes/config.php";
require "../includes/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: friends.php");
    exit;
}

$friend = $_POST['friend'];
$user = $_SESSION['username'];

/* Add reverse relationship */
$stmt = $pdo->prepare("
    INSERT IGNORE INTO isfriend (pkfk_user_user, pkfk_user_friend)
    VALUES (?, ?)
");
$stmt->execute([$user, $friend]);

header("Location: friends.php");
exit;
?>