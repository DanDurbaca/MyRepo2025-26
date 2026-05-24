<?php
include("conndb.php");

function userAlreadyExists($username)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE pk_username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
function emailAlreadyExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
?>

