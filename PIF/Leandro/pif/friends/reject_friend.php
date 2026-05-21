<?php
/*
 * friends/reject_friend.php
 * Purpose: Reject an incoming friend request by deleting the request row.
 */
require "../includes/config.php";
require "../includes/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: friends.php");
    exit;
}

$friend = $_POST['friend'];
$user = $_SESSION['username'];

/* Remove request */
$stmt = $pdo->prepare("
    DELETE FROM isfriend
    WHERE pkfk_user_user = ? AND pkfk_user_friend = ?
");
$stmt->execute([$friend, $user]);

header("Location: friends.php");
exit;
?>