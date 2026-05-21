<?php
session_start();
require "../Libraries/conndb.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    exit("Unauthorized");
}

$allowedFields = ['firstName', 'lastName', 'email'];

if (
    empty($_POST['username']) ||
    empty($_POST['field']) ||
    !in_array($_POST['field'], $allowedFields, true)
) {
    http_response_code(400);
    exit("Invalid request");
}

$username = $_POST['username'];
$field = $_POST['field'];
$value = trim($_POST['value']);

$sql = "UPDATE users SET $field = ? WHERE pk_username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $value, $username);
$stmt->execute();
