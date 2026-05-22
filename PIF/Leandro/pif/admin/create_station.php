<?php
/*
 * admin/create_station.php
 * Purpose: Admin endpoint to create a new station record.
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $serial = trim($_POST['serial']);

    $stmt = $pdo->prepare("
        INSERT INTO station (pk_serialNumber)
        VALUES (?)
    ");
    $stmt->execute([$serial]);

    header("Location: /admin/stations.php");
    exit;
}
