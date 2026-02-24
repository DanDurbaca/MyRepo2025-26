<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "MiniWebShop";
$conn = new mysqli($servername, $username, $password, $db);
if (isset($_POST["selectfruits"])) {
    $stmt = $conn->prepare("SELECT * FROM items");
    $stmt->execute();
    $results = $stmt->get_result();
    $fruits = [];
    while ($row = $results->fetch_assoc()) {
        $fruits[] = $row;
    }
    echo json_encode($fruits);
}
if(isset($_POST["order"])) {
    $getnumber = $conn->prepare("SELECT stock FROM items WHERE itemId = ?");
    $getnumber->bind_param("i", $_POST["itemId"]);
    $getnumber->execute();
    $resulting = $getnumber->get_result();
    $row = $resulting->fetch_assoc();
    $number = ($row["stock"]-$_POST["quantity"]);
    $updatefruits = $conn->prepare("UPDATE items SET stock = ? WHERE itemId = ?");
    $updatefruits->bind_param("ii", $number, $_POST["itemId"]);
    $updatefruits->execute();
}