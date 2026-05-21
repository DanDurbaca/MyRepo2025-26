<?php
session_start();
require "../Libraries/conndb.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    exit("Unauthorized");
}

$allowedFields = ['name', 'description'];

if (
    empty($_POST['serial']) ||
    empty($_POST['field']) ||
    !in_array($_POST['field'], $allowedFields, true)
) {
    http_response_code(400);
    exit("Invalid request");
}

$serial = $_POST['serial'];
$field  = $_POST['field'];
$value  = $_POST['value'] ?? '';

$sql = "UPDATE stations SET $field = ? WHERE pk_serialNumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $value, $serial);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    exit("Station not found or no change made");
}
