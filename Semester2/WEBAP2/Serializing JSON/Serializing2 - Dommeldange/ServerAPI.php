<?php

// Sample array
$data = [];

$connection = new mysqli("localhost", "root", "", "RawIngredients");
$sqlSelect = $connection->prepare("Select * from SomeIngredients");
$sqlSelect->execute();
$result = $sqlSelect->get_result();
while ($row = $result->fetch_assoc()) {
    // array_push($data, $row);
    $data[] = $row;
}

// Convert array to JSON
$json = json_encode($data, JSON_PRETTY_PRINT);

// Output the JSON
header('Content-Type: application/json');
echo $json;
