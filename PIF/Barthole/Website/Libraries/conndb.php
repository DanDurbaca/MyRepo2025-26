<?php
$host = "localhost";
$uname = "root";
$psw = "";
$dbName = "portableindoorfeedback";
$conn = new mysqli($host, $uname, $psw, $dbName);
if (session_status() != PHP_SESSION_ACTIVE) session_start();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}