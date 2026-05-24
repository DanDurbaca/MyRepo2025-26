<?php
/*
 * admin/delete_collections.php
 * Purpose: Admin action to delete a collection by id (expects `id` GET param).
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    die("No collection specified");
}

$stmt = $pdo->prepare("DELETE FROM collection WHERE pk_collection = ?");
$stmt->execute([$id]);

header("Location: collections.php");
exit;
