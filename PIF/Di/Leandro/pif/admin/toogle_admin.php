<?php
/*
 * admin/toogle_admin.php
 * Purpose: Toggle a user's role between `Admin` and `User`.
 * Notes: Prevents an admin from revoking their own admin rights.
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: users.php");
    exit;
}

$username = $_POST['username'] ?? null;

if (!$username) {
    header("Location: users.php");
    exit;
}

/* Do NOT allow admin to remove their own admin rights */
if ($username === $_SESSION['username']) {
    header("Location: users.php");
    exit;
}

/* Toggle role */
$stmt = $pdo->prepare("
    UPDATE user
    SET role = CASE
        WHEN role = 'Admin' THEN 'User'
        ELSE 'Admin'
    END
    WHERE pk_username = ?
");

$stmt->execute([$username]);

header("Location: users.php");
exit;
