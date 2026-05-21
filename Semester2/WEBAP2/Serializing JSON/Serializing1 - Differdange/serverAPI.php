<?php
header('Content-Type: application/json');

$dataToSerialize = []; // let us read from db

$connection = new mysqli("localhost", "root", "", "RawIngredients");
$sqlSelect = $connection->prepare("SELECT * from SomeIngredients");
// no bind
$sqlSelect->execute();
$result = $sqlSelect->get_result();
while ($row = $result->fetch_assoc()) {
    //array_push($dataToSerialize, $row);
    $dataToSerialize[] = $row; // same thing as above
}


$jsonData = json_encode($dataToSerialize);
print($jsonData);
