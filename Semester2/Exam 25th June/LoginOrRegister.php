<?php
session_start();

if (isset($_POST["UserName"])) {
    $_SESSION["UserName"] = $_POST["UserName"];
}

if (!isset($_SESSION["UserName"])) {
    header("location:Start.php");
    die();
}
if (!isset($_SESSION["Login"])) $_SESSION["Login"] = false;

$connection = mysqli_connect("localhost", "root", "", "Credits");
if (!$connection) {
    die("Error creating connection");
}

$SqlGetUser = $connection->prepare("SELECT * from Users where Name=?");
$SqlGetUser->bind_param("s", $_SESSION["UserName"]);
$SqlGetUser->execute();
$result = $SqlGetUser->get_result();
if ($result->num_rows == 0) {
    $SqlInsertUser = $connection->prepare("INSERT INTO USERS(Name,Password,Money) Values(?,'',10000)");
    $SqlInsertUser->bind_param("s", $_SESSION["UserName"]);
    $SqlInsertUser->execute();
    header("location:Register.php");
    die();
}

$rowUser = $result->fetch_assoc();

if ($rowUser["Password"] == "") {
    header("location:Register.php");
    die();
}

header("location:Login.php");
die();
