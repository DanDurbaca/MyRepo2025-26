<?php
/*
 * admin/delete_station.php
 * Purpose: Admin action to delete a station by serial number (expects `sn` GET param).
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

$sn = $_GET['sn'] ?? null;

if (!$sn) {
    die("No station specified");
}

$stmt = $pdo->prepare("DELETE FROM station WHERE pk_serialNumber = ?");
$stmt->execute([$sn]);

header("Location: stations.php");
exit;
